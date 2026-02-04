<?php

#[CMiddleware(middleware: ['auth'])]
class TransactionController extends Controller
{
    private function getBridgeApi(): CBridgeApi
    {
        return new CBridgeApi(
            getenv('BRIDGE_CLIENT_ID') ?: 'sandbox_id_4688615bb0e7451fa4679d41c11650e9',
            getenv('BRIDGE_CLIENT_SECRET') ?: 'sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh'
        );
    }

    /**
     * Génère l'UUID Bridge unique basé sur l'admin
     * Format: sha256(id + created_at + "accounting_bank")
     */
    private function generateBridgeUserUuid(): string
    {
        $adminId = $_SESSION['user']['id'];
        $conn = Model::getConnection();
        $stmt = $conn->prepare('SELECT id, created_at FROM Admin WHERE id = :id');
        $stmt->execute(['id' => $adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return hash('sha256', $admin['id'] . $admin['created_at'] . 'accounting_bank');
    }

    /**
     * Initialise Bridge avec l'UUID unique de l'admin
     */
    private function initializeBridge(): ?CBridgeApi
    {
        $bridge = $this->getBridgeApi();
        $userUuid = $this->generateBridgeUserUuid();
        
        $result = $bridge->F_lInitializeUser($userUuid);
        
        if ($result['success']) {
            $bridge->F_vSaveToSession();
            return $bridge;
        }
        
        return null;
    }

    // ========================
    // PAGES (Views)
    // ========================

    #[CRoute('/transactions', CHTTPMethod::GET)]
    public function index(): void
    {
        $this->view('transaction/index', [
            'title' => 'Transactions',
        ]);
    }

    #[CRoute('/transactions/add', CHTTPMethod::GET)]
    public function create(): void
    {
        $this->view('transaction/create', [
            'title' => 'Ajouter une transaction',
        ]);
    }

    #[CRoute('/transactions/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $this->view('transaction/show', [
            'title' => 'Détails de la transaction',
            'transactionId' => $id,
        ]);
    }

    #[CRoute('/transactions/{id}/edit', CHTTPMethod::GET)]
    public function edit(string $id): void
    {
        $this->view('transaction/edit', [
            'title' => 'Modifier la transaction',
            'transactionId' => $id,
        ]);
    }

    // ========================
    // API CRUD (REST)
    // ========================

    #[CRoute('/api/transactions', CHTTPMethod::GET)]
    public function apiGetTransactions(): void
    {
        $conn = Model::getConnection();
        
        // Récupérer les filtres
        $accountId = $_GET['account_id'] ?? null;
        $categoryId = $_GET['category_id'] ?? null;
        $type = $_GET['type'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        $sql = '
            SELECT t.*, a.name as account_name, a.color as account_color, c.name as category_name 
            FROM Transaction t
            LEFT JOIN Account a ON t.account_id = a.id
            LEFT JOIN Category c ON t.category_id = c.id
            WHERE t.admin_id = :admin_id
        ';
        $params = ['admin_id' => $_SESSION['user']['id']];

        if ($accountId) {
            $sql .= ' AND t.account_id = :account_id';
            $params['account_id'] = $accountId;
        }
        if ($categoryId) {
            $sql .= ' AND t.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        if ($type) {
            $sql .= ' AND t.type = :type';
            $params['type'] = $type;
        }
        if ($startDate) {
            $sql .= ' AND t.date >= :start_date';
            $params['start_date'] = $startDate;
        }
        if ($endDate) {
            $sql .= ' AND t.date <= :end_date';
            $params['end_date'] = $endDate;
        }

        $sql .= ' ORDER BY t.date DESC, t.id DESC LIMIT ' . $limit . ' OFFSET ' . $offset;

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compter le total
        $countSql = '
            SELECT COUNT(*) as total FROM Transaction t
            WHERE t.admin_id = :admin_id
        ';
        $countParams = ['admin_id' => $_SESSION['user']['id']];
        
        if ($accountId) {
            $countSql .= ' AND t.account_id = :account_id';
            $countParams['account_id'] = $accountId;
        }
        if ($categoryId) {
            $countSql .= ' AND t.category_id = :category_id';
            $countParams['category_id'] = $categoryId;
        }
        if ($type) {
            $countSql .= ' AND t.type = :type';
            $countParams['type'] = $type;
        }
        if ($startDate) {
            $countSql .= ' AND t.date >= :start_date';
            $countParams['start_date'] = $startDate;
        }
        if ($endDate) {
            $countSql .= ' AND t.date <= :end_date';
            $countParams['end_date'] = $endDate;
        }

        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($countParams);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $this->json([
            'code' => 200, 
            'data' => $transactions,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Statistiques mensuelles (revenus, dépenses, balance du mois)
     * IMPORTANT: Cette route doit être AVANT /api/transactions/{id} pour éviter le conflit
     */
    #[CRoute('/api/transactions/stats', CHTTPMethod::GET)]
    public function apiGetStats(): void
    {
        $conn = Model::getConnection();
        $adminId = $_SESSION['user']['id'];
        
        $month = $_GET['month'] ?? date('Y-m');
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        // Total revenus du mois
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(amount), 0) as total 
            FROM Transaction 
            WHERE admin_id = :admin_id AND type = 'income' AND date BETWEEN :start AND :end
        ");
        $stmt->execute(['admin_id' => $adminId, 'start' => $startDate, 'end' => $endDate]);
        $totalIncome = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total dépenses du mois
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(ABS(amount)), 0) as total 
            FROM Transaction 
            WHERE admin_id = :admin_id AND type = 'expense' AND date BETWEEN :start AND :end
        ");
        $stmt->execute(['admin_id' => $adminId, 'start' => $startDate, 'end' => $endDate]);
        $totalExpense = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Par catégorie
        $stmt = $conn->prepare("
            SELECT c.name, c.type, SUM(ABS(t.amount)) as total
            FROM Transaction t
            JOIN Category c ON t.category_id = c.id
            WHERE t.admin_id = :admin_id AND t.date BETWEEN :start AND :end
            GROUP BY c.id, c.name, c.type
            ORDER BY total DESC
        ");
        $stmt->execute(['admin_id' => $adminId, 'start' => $startDate, 'end' => $endDate]);
        $byCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json([
            'code' => 200,
            'data' => [
                'month' => $month,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'balance' => $totalIncome - $totalExpense,
                'by_category' => $byCategory
            ]
        ]);
    }

    #[CRoute('/api/transactions/{id}', CHTTPMethod::GET)]
    public function apiGetTransaction(string $id): void
    {
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT t.*, a.name as account_name, a.color as account_color, c.name as category_name 
            FROM Transaction t
            LEFT JOIN Account a ON t.account_id = a.id
            LEFT JOIN Category c ON t.category_id = c.id
            WHERE t.id = :id AND t.admin_id = :admin_id
        ');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transaction) {
            $this->json(['code' => 404, 'error' => 'Transaction non trouvée']);
            return;
        }
        
        $this->json(['code' => 200, 'data' => $transaction]);
    }

    #[CRoute('/api/transactions', CHTTPMethod::POST)]
    public function apiCreateTransaction(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['account_id']) || empty($data['category_id']) || !isset($data['amount'])) {
            $this->json(['code' => 400, 'error' => 'Compte, catégorie et montant requis']);
            return;
        }

        $conn = Model::getConnection();
        
        // Vérifier que le compte appartient à l'utilisateur
        $stmt = $conn->prepare('SELECT id FROM Account WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $data['account_id'], 'admin_id' => $_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            $this->json(['code' => 404, 'error' => 'Compte non trouvé']);
            return;
        }

        $amount = abs((float)$data['amount']);
        $type = $data['type'] ?? 'expense';
        
        $stmt = $conn->prepare('
            INSERT INTO Transaction (admin_id, account_id, category_id, amount, type, date, description) 
            VALUES (:admin_id, :account_id, :category_id, :amount, :type, :date, :description)
        ');
        
        $stmt->execute([
            'admin_id' => $_SESSION['user']['id'],
            'account_id' => $data['account_id'],
            'category_id' => $data['category_id'],
            'amount' => $type === 'expense' ? -$amount : $amount,
            'type' => $type,
            'date' => $data['date'] ?? date('Y-m-d'),
            'description' => $data['description'] ?? null,
        ]);

        // Mettre à jour le solde du compte
        $balanceChange = $type === 'expense' ? -$amount : $amount;
        $stmt = $conn->prepare('UPDATE Account SET balance = balance + :amount WHERE id = :id');
        $stmt->execute(['amount' => $balanceChange, 'id' => $data['account_id']]);

        $this->json(['code' => 201, 'data' => ['id' => $conn->lastInsertId()], 'message' => 'Transaction créée']);
    }

    #[CRoute('/api/transactions/{id}', CHTTPMethod::PUT)]
    public function apiUpdateTransaction(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $conn = Model::getConnection();
        
        // Récupérer l'ancienne transaction
        $stmt = $conn->prepare('SELECT * FROM Transaction WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        $oldTransaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$oldTransaction) {
            $this->json(['code' => 404, 'error' => 'Transaction non trouvée']);
            return;
        }

        // Ne pas permettre la modification des transactions Bridge
        if (!empty($oldTransaction['id_bridge'])) {
            $this->json(['code' => 403, 'error' => 'Les transactions importées ne peuvent pas être modifiées']);
            return;
        }

        $amount = abs((float)($data['amount'] ?? $oldTransaction['amount']));
        $type = $data['type'] ?? $oldTransaction['type'];
        $newAmount = $type === 'expense' ? -$amount : $amount;

        // Annuler l'effet de l'ancienne transaction sur le solde
        $oldEffect = (float)$oldTransaction['amount'];
        $stmt = $conn->prepare('UPDATE Account SET balance = balance - :amount WHERE id = :id');
        $stmt->execute(['amount' => $oldEffect, 'id' => $oldTransaction['account_id']]);

        // Mettre à jour la transaction
        $stmt = $conn->prepare('
            UPDATE Transaction 
            SET account_id = :account_id, category_id = :category_id, amount = :amount, 
                type = :type, date = :date, description = :description
            WHERE id = :id AND admin_id = :admin_id
        ');
        
        $newAccountId = $data['account_id'] ?? $oldTransaction['account_id'];
        
        $stmt->execute([
            'id' => $id,
            'admin_id' => $_SESSION['user']['id'],
            'account_id' => $newAccountId,
            'category_id' => $data['category_id'] ?? $oldTransaction['category_id'],
            'amount' => $newAmount,
            'type' => $type,
            'date' => $data['date'] ?? $oldTransaction['date'],
            'description' => $data['description'] ?? $oldTransaction['description'],
        ]);

        // Appliquer le nouvel effet sur le solde
        $stmt = $conn->prepare('UPDATE Account SET balance = balance + :amount WHERE id = :id');
        $stmt->execute(['amount' => $newAmount, 'id' => $newAccountId]);

        $this->json(['code' => 200, 'message' => 'Transaction modifiée']);
    }

    #[CRoute('/api/transactions/{id}', CHTTPMethod::DELETE)]
    public function apiDeleteTransaction(string $id): void
    {
        $conn = Model::getConnection();
        
        // Récupérer la transaction
        $stmt = $conn->prepare('SELECT * FROM Transaction WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transaction) {
            $this->json(['code' => 404, 'error' => 'Transaction non trouvée']);
            return;
        }

        // Annuler l'effet sur le solde
        $stmt = $conn->prepare('UPDATE Account SET balance = balance - :amount WHERE id = :id');
        $stmt->execute(['amount' => (float)$transaction['amount'], 'id' => $transaction['account_id']]);

        // Supprimer la transaction
        $stmt = $conn->prepare('DELETE FROM Transaction WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);

        $this->json(['code' => 200, 'message' => 'Transaction supprimée']);
    }

    // ========================
    // API Helpers
    // ========================

    #[CRoute('/api/categories', CHTTPMethod::GET)]
    public function apiGetCategories(): void
    {
        $conn = Model::getConnection();
        $type = $_GET['type'] ?? null;
        
        $sql = 'SELECT * FROM Category WHERE admin_id IS NULL OR admin_id = :admin_id';
        $params = ['admin_id' => $_SESSION['user']['id']];
        
        if ($type) {
            $sql .= ' AND type = :type';
            $params['type'] = $type;
        }
        
        $sql .= ' ORDER BY name ASC';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(['code' => 200, 'data' => $categories]);
    }

    // ========================
    // API BRIDGE SYNC
    // ========================

    #[CRoute('/api/bridge/transactions/sync', CHTTPMethod::POST)]
    public function apiBridgeSyncTransactions(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $accountId = $data['account_id'] ?? null;
        
        // Initialiser Bridge avec l'UUID unique de l'admin
        $bridge = $this->initializeBridge();
        
        if (!$bridge) {
            $this->json(['code' => 401, 'error' => 'Impossible d\'initialiser la session Bridge']);
            return;
        }

        $conn = Model::getConnection();
        
        // Récupérer les comptes à synchroniser
        $sql = 'SELECT * FROM Account WHERE admin_id = :admin_id AND id_bridge IS NOT NULL';
        $params = ['admin_id' => $_SESSION['user']['id']];
        
        if ($accountId) {
            $sql .= ' AND id = :account_id';
            $params['account_id'] = $accountId;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($accounts)) {
            $this->json(['code' => 404, 'error' => 'Aucun compte Bridge trouvé']);
            return;
        }

        $totalImported = 0;
        $totalSkipped = 0;

        // Récupérer la catégorie par défaut
        $stmt = $conn->prepare("SELECT id FROM Category WHERE name = 'Autres' AND type = 'expense' LIMIT 1");
        $stmt->execute();
        $defaultExpenseCategory = $stmt->fetch(PDO::FETCH_ASSOC)['id'] ?? 10;
        
        $stmt = $conn->prepare("SELECT id FROM Category WHERE name = 'Autres' AND type = 'income' LIMIT 1");
        $stmt->execute();
        $defaultIncomeCategory = $stmt->fetch(PDO::FETCH_ASSOC)['id'] ?? 11;

        foreach ($accounts as $account) {
            // Calculer le timestamp depuis la dernière sync
            $sinceTimestamp = null;
            if ($account['last_sync']) {
                $sinceTimestamp = strtotime($account['last_sync']) * 1000;
            } else {
                // Par défaut, récupérer les 90 derniers jours
                $sinceTimestamp = strtotime('-90 days') * 1000;
            }

            // Récupérer les transactions depuis Bridge
            $result = $bridge->F_lGetTransactions($sinceTimestamp, [
                'account_id' => $account['id_bridge']
            ]);

            if (!$result['success']) {
                continue;
            }

            $bridgeTransactions = $result['response']['resources'] ?? [];

            foreach ($bridgeTransactions as $bridgeTx) {
                // Vérifier si la transaction existe déjà
                $stmt = $conn->prepare('SELECT id FROM Transaction WHERE id_bridge = :id_bridge');
                $stmt->execute(['id_bridge' => $bridgeTx['id']]);
                
                if ($stmt->fetch()) {
                    $totalSkipped++;
                    continue;
                }

                // Déterminer le type
                $amount = (float)$bridgeTx['amount'];
                $type = $amount >= 0 ? 'income' : 'expense';
                $categoryId = $type === 'income' ? $defaultIncomeCategory : $defaultExpenseCategory;

                // Insérer la transaction
                $stmt = $conn->prepare('
                    INSERT INTO Transaction (id_bridge, admin_id, account_id, category_id, amount, type, date, description) 
                    VALUES (:id_bridge, :admin_id, :account_id, :category_id, :amount, :type, :date, :description)
                ');
                
                $stmt->execute([
                    'id_bridge' => $bridgeTx['id'],
                    'admin_id' => $_SESSION['user']['id'],
                    'account_id' => $account['id'],
                    'category_id' => $categoryId,
                    'amount' => $amount,
                    'type' => $type,
                    'date' => $bridgeTx['date'] ?? date('Y-m-d'),
                    'description' => $bridgeTx['description'] ?? $bridgeTx['clean_description'] ?? 'Transaction Bridge',
                ]);
                
                $totalImported++;
            }

            // Mettre à jour last_sync
            $stmt = $conn->prepare('UPDATE Account SET last_sync = NOW() WHERE id = :id');
            $stmt->execute(['id' => $account['id']]);
        }

        $this->json([
            'code' => 200, 
            'message' => "$totalImported transaction(s) importée(s), $totalSkipped ignorée(s)",
            'imported' => $totalImported,
            'skipped' => $totalSkipped
        ]);
    }

    // ========================
    // STATS
    // ========================

    /**
     * Dashboard complet avec toutes les statistiques
     * - Revenu/dépense total (mois en cours)
     * - Balance actuelle (différence revenus-dépenses du mois)
     * - Total abonnements mensuels
     * - Solde total de tous les comptes
     * - Prévision fin de mois (global et par compte)
     */
    #[CRoute('/api/stats/dashboard', CHTTPMethod::GET)]
    public function apiGetDashboardStats(): void
    {
        $conn = Model::getConnection();
        $adminId = $_SESSION['user']['id'];
        
        $today = new DateTime();
        $currentMonth = $today->format('Y-m');
        $startOfMonth = $currentMonth . '-01';
        $endOfMonth = $today->format('Y-m-t');
        $daysInMonth = (int)$today->format('t');
        $currentDay = (int)$today->format('j');
        $remainingDays = $daysInMonth - $currentDay;

        // ========================================
        // 1. REVENU/DEPENSE TOTAL DU MOIS
        // ========================================
        $stmt = $conn->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN ABS(amount) ELSE 0 END), 0) as total_expense
            FROM Transaction 
            WHERE admin_id = :admin_id AND date BETWEEN :start AND :end
        ");
        $stmt->execute(['admin_id' => $adminId, 'start' => $startOfMonth, 'end' => $endOfMonth]);
        $monthlyTotals = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalIncome = (float)$monthlyTotals['total_income'];
        $totalExpense = (float)$monthlyTotals['total_expense'];
        $monthlyBalance = $totalIncome - $totalExpense;

        // ========================================
        // 2. SOLDE TOTAL DE TOUS LES COMPTES
        // ========================================
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(balance), 0) as total_balance 
            FROM Account 
            WHERE admin_id = :admin_id
        ");
        $stmt->execute(['admin_id' => $adminId]);
        $totalBalance = (float)$stmt->fetch(PDO::FETCH_ASSOC)['total_balance'];

        // ========================================
        // 3. TOTAL ABONNEMENTS MENSUELS
        // ========================================
        $stmt = $conn->prepare("
            SELECT 
                type,
                type_period,
                amount
            FROM Subscription 
            WHERE admin_id = :admin_id
        ");
        $stmt->execute(['admin_id' => $adminId]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $subscriptionIncomeMonthly = 0;
        $subscriptionExpenseMonthly = 0;
        
        foreach ($subscriptions as $sub) {
            $amount = (float)$sub['amount'];
            $monthlyAmount = match($sub['type_period']) {
                'weekly' => $amount * 4.33,
                'monthly' => $amount,
                'yearly' => $amount / 12,
                default => $amount
            };
            
            if ($sub['type'] === 'income') {
                $subscriptionIncomeMonthly += $monthlyAmount;
            } else {
                $subscriptionExpenseMonthly += $monthlyAmount;
            }
        }
        $subscriptionNetMonthly = $subscriptionIncomeMonthly - $subscriptionExpenseMonthly;

        // ========================================
        // 4. SOLDE PAR COMPTE
        // ========================================
        $stmt = $conn->prepare("
            SELECT id, name, balance, color, type
            FROM Account 
            WHERE admin_id = :admin_id
            ORDER BY name ASC
        ");
        $stmt->execute(['admin_id' => $adminId]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ========================================
        // 5. PREVISION FIN DE MOIS
        // ========================================
        
        // Calculer les moyennes journalières basées sur le mois en cours
        $avgDailyIncome = $currentDay > 0 ? $totalIncome / $currentDay : 0;
        $avgDailyExpense = $currentDay > 0 ? $totalExpense / $currentDay : 0;
        
        // Revenus et dépenses prévus pour le reste du mois
        $projectedRemainingIncome = $avgDailyIncome * $remainingDays;
        $projectedRemainingExpense = $avgDailyExpense * $remainingDays;
        
        // Ajouter les abonnements restants du mois (estimés)
        $remainingSubscriptionIncome = ($subscriptionIncomeMonthly / $daysInMonth) * $remainingDays;
        $remainingSubscriptionExpense = ($subscriptionExpenseMonthly / $daysInMonth) * $remainingDays;
        
        // Prévision totale fin de mois (tous comptes confondus)
        $projectedEndOfMonthBalance = $totalBalance 
            + $projectedRemainingIncome 
            - $projectedRemainingExpense
            + $remainingSubscriptionIncome 
            - $remainingSubscriptionExpense;
        
        $projectedMonthlyIncome = $totalIncome + $projectedRemainingIncome;
        $projectedMonthlyExpense = $totalExpense + $projectedRemainingExpense;

        // ========================================
        // 6. PREVISION PAR COMPTE
        // ========================================
        $forecastByAccount = [];
        
        foreach ($accounts as $account) {
            // Transactions du mois pour ce compte
            $stmt = $conn->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                    COALESCE(SUM(CASE WHEN type = 'expense' THEN ABS(amount) ELSE 0 END), 0) as expense
                FROM Transaction 
                WHERE admin_id = :admin_id AND account_id = :account_id AND date BETWEEN :start AND :end
            ");
            $stmt->execute([
                'admin_id' => $adminId, 
                'account_id' => $account['id'],
                'start' => $startOfMonth, 
                'end' => $endOfMonth
            ]);
            $accountTotals = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $accountIncome = (float)$accountTotals['income'];
            $accountExpense = (float)$accountTotals['expense'];
            
            $avgAccountDailyIncome = $currentDay > 0 ? $accountIncome / $currentDay : 0;
            $avgAccountDailyExpense = $currentDay > 0 ? $accountExpense / $currentDay : 0;
            
            $projectedAccountEndBalance = (float)$account['balance']
                + ($avgAccountDailyIncome * $remainingDays)
                - ($avgAccountDailyExpense * $remainingDays);
            
            $forecastByAccount[] = [
                'id' => $account['id'],
                'name' => $account['name'],
                'color' => $account['color'],
                'type' => $account['type'],
                'current_balance' => (float)$account['balance'],
                'month_income' => $accountIncome,
                'month_expense' => $accountExpense,
                'projected_end_balance' => round($projectedAccountEndBalance, 2),
                'variation' => round($projectedAccountEndBalance - (float)$account['balance'], 2)
            ];
        }

        // ========================================
        // 7. DEPENSES PAR CATEGORIE
        // ========================================
        $stmt = $conn->prepare("
            SELECT c.id, c.name, SUM(ABS(t.amount)) as total
            FROM Transaction t
            JOIN Category c ON t.category_id = c.id
            WHERE t.admin_id = :admin_id AND t.type = 'expense' AND t.date BETWEEN :start AND :end
            GROUP BY c.id, c.name
            ORDER BY total DESC
        ");
        $stmt->execute(['admin_id' => $adminId, 'start' => $startOfMonth, 'end' => $endOfMonth]);
        $expensesByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $expenseCategories = [];
        foreach ($expensesByCategory as $cat) {
            $catTotal = (float)$cat['total'];
            $percentage = $totalExpense > 0 ? ($catTotal / $totalExpense) * 100 : 0;
            $expenseCategories[] = [
                'id' => $cat['id'],
                'name' => $cat['name'],
                'total' => round($catTotal, 2),
                'percentage' => round($percentage, 2)
            ];
        }

        // ========================================
        // 8. REVENUS PAR CATEGORIE
        // ========================================
        $stmt = $conn->prepare("
            SELECT c.id, c.name, SUM(t.amount) as total
            FROM Transaction t
            JOIN Category c ON t.category_id = c.id
            WHERE t.admin_id = :admin_id AND t.type = 'income' AND t.date BETWEEN :start AND :end
            GROUP BY c.id, c.name
            ORDER BY total DESC
        ");
        $stmt->execute(['admin_id' => $adminId, 'start' => $startOfMonth, 'end' => $endOfMonth]);
        $incomesByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $incomeCategories = [];
        foreach ($incomesByCategory as $cat) {
            $catTotal = (float)$cat['total'];
            $percentage = $totalIncome > 0 ? ($catTotal / $totalIncome) * 100 : 0;
            $incomeCategories[] = [
                'id' => $cat['id'],
                'name' => $cat['name'],
                'total' => round($catTotal, 2),
                'percentage' => round($percentage, 2)
            ];
        }

        // ========================================
        // REPONSE
        // ========================================
        $this->json([
            'code' => 200,
            'data' => [
                // Période
                'period' => [
                    'month' => $currentMonth,
                    'days_in_month' => $daysInMonth,
                    'current_day' => $currentDay,
                    'remaining_days' => $remainingDays
                ],
                
                // Revenus/Dépenses du mois
                'monthly' => [
                    'income' => round($totalIncome, 2),
                    'expense' => round($totalExpense, 2),
                    'balance' => round($monthlyBalance, 2)
                ],
                
                // Solde total de tous les comptes
                'total_balance' => round($totalBalance, 2),
                
                // Abonnements mensuels
                'subscriptions' => [
                    'income' => round($subscriptionIncomeMonthly, 2),
                    'expense' => round($subscriptionExpenseMonthly, 2),
                    'net' => round($subscriptionNetMonthly, 2)
                ],
                
                // Prévisions globales
                'forecast' => [
                    'projected_income' => round($projectedMonthlyIncome, 2),
                    'projected_expense' => round($projectedMonthlyExpense, 2),
                    'projected_balance' => round($projectedEndOfMonthBalance, 2),
                    'projected_variation' => round($projectedEndOfMonthBalance - $totalBalance, 2)
                ],
                
                // Solde et prévisions par compte
                'accounts' => $forecastByAccount,
                
                // Dépenses par catégorie avec total et pourcentage
                'expenses_by_category' => $expenseCategories,
                
                // Revenus par catégorie avec total et pourcentage
                'incomes_by_category' => $incomeCategories
            ]
        ]);
    }

    /**
     * Total des abonnements uniquement
     */
    #[CRoute('/api/stats/subscriptions', CHTTPMethod::GET)]
    public function apiGetSubscriptionStats(): void
    {
        $conn = Model::getConnection();
        $adminId = $_SESSION['user']['id'];
        
        $stmt = $conn->prepare("
            SELECT id, name, amount, type, type_period, day_of_month, day_of_week, date_of_year
            FROM Subscription 
            WHERE admin_id = :admin_id
            ORDER BY name ASC
        ");
        $stmt->execute(['admin_id' => $adminId]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalIncome = 0;
        $totalExpense = 0;
        $detailedSubscriptions = [];
        
        foreach ($subscriptions as $sub) {
            $amount = (float)$sub['amount'];
            
            // Calcul du montant mensuel équivalent
            $monthlyAmount = match($sub['type_period']) {
                'weekly' => $amount * 4.33,
                'monthly' => $amount,
                'yearly' => $amount / 12,
                default => $amount
            };
            
            // Calcul du montant annuel
            $yearlyAmount = match($sub['type_period']) {
                'weekly' => $amount * 52,
                'monthly' => $amount * 12,
                'yearly' => $amount,
                default => $amount * 12
            };
            
            if ($sub['type'] === 'income') {
                $totalIncome += $monthlyAmount;
            } else {
                $totalExpense += $monthlyAmount;
            }
            
            $detailedSubscriptions[] = [
                'id' => $sub['id'],
                'name' => $sub['name'],
                'amount' => $amount,
                'type' => $sub['type'],
                'period' => $sub['type_period'],
                'monthly_amount' => round($monthlyAmount, 2),
                'yearly_amount' => round($yearlyAmount, 2)
            ];
        }
        
        $this->json([
            'code' => 200,
            'data' => [
                'total_monthly_income' => round($totalIncome, 2),
                'total_monthly_expense' => round($totalExpense, 2),
                'total_monthly_net' => round($totalIncome - $totalExpense, 2),
                'total_yearly_income' => round($totalIncome * 12, 2),
                'total_yearly_expense' => round($totalExpense * 12, 2),
                'total_yearly_net' => round(($totalIncome - $totalExpense) * 12, 2),
                'subscriptions' => $detailedSubscriptions
            ]
        ]);
    }

    /**
     * Solde total et par compte
     */
    #[CRoute('/api/stats/balances', CHTTPMethod::GET)]
    public function apiGetBalances(): void
    {
        $conn = Model::getConnection();
        $adminId = $_SESSION['user']['id'];
        
        $stmt = $conn->prepare("
            SELECT id, name, balance, color, type, id_bridge IS NOT NULL as is_synced
            FROM Account 
            WHERE admin_id = :admin_id
            ORDER BY balance DESC
        ");
        $stmt->execute(['admin_id' => $adminId]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalBalance = 0;
        $formattedAccounts = [];
        
        foreach ($accounts as $account) {
            $balance = (float)$account['balance'];
            $totalBalance += $balance;
            
            $formattedAccounts[] = [
                'id' => $account['id'],
                'name' => $account['name'],
                'balance' => $balance,
                'color' => $account['color'],
                'type' => $account['type'],
                'is_synced' => (bool)$account['is_synced']
            ];
        }
        
        $this->json([
            'code' => 200,
            'data' => [
                'total_balance' => round($totalBalance, 2),
                'accounts' => $formattedAccounts
            ]
        ]);
    }

    /**
     * Prévision de fin de mois détaillée
     */
    #[CRoute('/api/stats/forecast', CHTTPMethod::GET)]
    public function apiGetForecast(): void
    {
        $conn = Model::getConnection();
        $adminId = $_SESSION['user']['id'];
        
        $today = new DateTime();
        $month = $_GET['month'] ?? $today->format('Y-m');
        $targetDate = new DateTime($month . '-01');
        $startOfMonth = $targetDate->format('Y-m-01');
        $endOfMonth = $targetDate->format('Y-m-t');
        $daysInMonth = (int)$targetDate->format('t');
        
        // Si c'est le mois en cours, utiliser le jour actuel
        if ($month === $today->format('Y-m')) {
            $currentDay = (int)$today->format('j');
        } else {
            $currentDay = $daysInMonth;
        }
        $remainingDays = $daysInMonth - $currentDay;

        // Récupérer les comptes
        $stmt = $conn->prepare("SELECT id, name, balance, color, type FROM Account WHERE admin_id = :admin_id");
        $stmt->execute(['admin_id' => $adminId]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les abonnements
        $stmt = $conn->prepare("SELECT type, type_period, amount FROM Subscription WHERE admin_id = :admin_id");
        $stmt->execute(['admin_id' => $adminId]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $subscriptionMonthlyNet = 0;
        foreach ($subscriptions as $sub) {
            $amount = (float)$sub['amount'];
            $monthlyAmount = match($sub['type_period']) {
                'weekly' => $amount * 4.33,
                'monthly' => $amount,
                'yearly' => $amount / 12,
                default => $amount
            };
            $subscriptionMonthlyNet += ($sub['type'] === 'income' ? $monthlyAmount : -$monthlyAmount);
        }

        $globalForecast = [
            'current_total_balance' => 0,
            'projected_total_balance' => 0,
            'projected_variation' => 0
        ];
        
        $accountForecasts = [];
        
        foreach ($accounts as $account) {
            // Transactions du mois pour ce compte
            $stmt = $conn->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                    COALESCE(SUM(CASE WHEN type = 'expense' THEN ABS(amount) ELSE 0 END), 0) as expense
                FROM Transaction 
                WHERE admin_id = :admin_id AND account_id = :account_id AND date BETWEEN :start AND :end
            ");
            $stmt->execute([
                'admin_id' => $adminId,
                'account_id' => $account['id'],
                'start' => $startOfMonth,
                'end' => $endOfMonth
            ]);
            $totals = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $income = (float)$totals['income'];
            $expense = (float)$totals['expense'];
            $balance = (float)$account['balance'];
            
            // Moyennes quotidiennes
            $avgDailyIncome = $currentDay > 0 ? $income / $currentDay : 0;
            $avgDailyExpense = $currentDay > 0 ? $expense / $currentDay : 0;
            $avgDailyNet = $avgDailyIncome - $avgDailyExpense;
            
            // Prévision pour ce compte
            $projectedChange = $avgDailyNet * $remainingDays;
            $projectedBalance = $balance + $projectedChange;
            
            $globalForecast['current_total_balance'] += $balance;
            $globalForecast['projected_total_balance'] += $projectedBalance;
            
            $accountForecasts[] = [
                'id' => $account['id'],
                'name' => $account['name'],
                'color' => $account['color'],
                'type' => $account['type'],
                'current_balance' => round($balance, 2),
                'month_income' => round($income, 2),
                'month_expense' => round($expense, 2),
                'avg_daily_income' => round($avgDailyIncome, 2),
                'avg_daily_expense' => round($avgDailyExpense, 2),
                'avg_daily_net' => round($avgDailyNet, 2),
                'projected_remaining_change' => round($projectedChange, 2),
                'projected_end_balance' => round($projectedBalance, 2),
                'variation_percent' => $balance != 0 ? round(($projectedChange / abs($balance)) * 100, 2) : 0
            ];
        }
        
        // Ajouter l'effet des abonnements au global
        $remainingSubscriptionEffect = ($subscriptionMonthlyNet / $daysInMonth) * $remainingDays;
        $globalForecast['projected_total_balance'] += $remainingSubscriptionEffect;
        $globalForecast['projected_variation'] = $globalForecast['projected_total_balance'] - $globalForecast['current_total_balance'];
        
        $this->json([
            'code' => 200,
            'data' => [
                'period' => [
                    'month' => $month,
                    'days_in_month' => $daysInMonth,
                    'current_day' => $currentDay,
                    'remaining_days' => $remainingDays
                ],
                'subscriptions_monthly_net' => round($subscriptionMonthlyNet, 2),
                'global' => [
                    'current_total_balance' => round($globalForecast['current_total_balance'], 2),
                    'projected_total_balance' => round($globalForecast['projected_total_balance'], 2),
                    'projected_variation' => round($globalForecast['projected_variation'], 2)
                ],
                'accounts' => $accountForecasts
            ]
        ]);
    }
}
