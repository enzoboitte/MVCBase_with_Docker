<?php

class TransactionController extends Controller
{
    /**
     * Liste les transactions avec filtres
     */
    #[CRoute('/api/transactions', CHTTPMethod::GET, middleware: ['auth'])]
    public function list(): void
    {
        $userId = $_SESSION['user']['id'];
        
        // Filtres optionnels
        $accountId = $_GET['account_id'] ?? null;
        $categoryId = $_GET['category_id'] ?? null;
        $type = $_GET['type'] ?? null;
        $month = $_GET['month'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $limit = intval($_GET['limit'] ?? 50);
        $offset = intval($_GET['offset'] ?? 0);
        
        $conn = Model::getConnection();
        
        $sql = '
            SELECT 
                t.id,
                t.account_id,
                a.name as account_name,
                t.category_id,
                c.name as category_name,
                c.icon as category_icon,
                c.color as category_color,
                t.type,
                t.amount,
                t.description,
                t.date,
                t.is_recurring,
                t.notes,
                t.transfer_account_id,
                ta.name as transfer_account_name
            FROM Transaction t
            LEFT JOIN Account a ON t.account_id = a.id
            LEFT JOIN Category c ON t.category_id = c.id
            LEFT JOIN Account ta ON t.transfer_account_id = ta.id
            WHERE t.user_id = :user_id
        ';
        $params = ['user_id' => $userId];
        
        if ($accountId) {
            $sql .= ' AND t.account_id = :account_id';
            $params['account_id'] = $accountId;
        }
        
        if ($categoryId) {
            $sql .= ' AND t.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        
        if ($type && in_array($type, ['income', 'expense', 'transfer'])) {
            $sql .= ' AND t.type = :type';
            $params['type'] = $type;
        }
        
        if ($month) {
            $sql .= ' AND DATE_FORMAT(t.date, "%Y-%m") = :month';
            $params['month'] = $month;
        }
        
        if ($startDate) {
            $sql .= ' AND t.date >= :start_date';
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= ' AND t.date <= :end_date';
            $params['end_date'] = $endDate;
        }
        
        $sql .= ' ORDER BY t.date DESC, t.id DESC LIMIT :limit OFFSET :offset';
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Compter le total
        $countSql = 'SELECT COUNT(*) FROM Transaction t WHERE t.user_id = :user_id';
        $countParams = ['user_id' => $userId];
        
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
        if ($month) {
            $countSql .= ' AND DATE_FORMAT(t.date, "%Y-%m") = :month';
            $countParams['month'] = $month;
        }
        
        $countStmt = $conn->prepare($countSql);
        $countStmt->execute($countParams);
        $total = $countStmt->fetchColumn();
        
        $this->json([
            'code' => 200,
            'data' => $transactions,
            'total' => intval($total),
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    /**
     * Récupérer une transaction par ID
     */
    #[CRoute('/api/transactions/{id}', CHTTPMethod::GET, middleware: ['auth'])]
    public function show(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT 
                t.*,
                a.name as account_name,
                c.name as category_name,
                c.icon as category_icon,
                c.color as category_color
            FROM Transaction t
            LEFT JOIN Account a ON t.account_id = a.id
            LEFT JOIN Category c ON t.category_id = c.id
            WHERE t.id = :id AND t.user_id = :user_id
        ');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$transaction) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Transaction non trouvée']);
            return;
        }
        
        $this->json(['code' => 200, 'data' => $transaction]);
    }

    /**
     * Créer une nouvelle transaction
     */
    #[CRoute('/api/transactions', CHTTPMethod::POST, middleware: ['auth'])]
    public function create(): void
    {
        $userId = $_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['account_id']) || !isset($input['type']) || !isset($input['amount']) || !isset($input['description']) || !isset($input['date'])) {
            http_response_code(400);
            $this->json(['code' => 400, 'message' => 'Champs requis: account_id, type, amount, description, date']);
            return;
        }
        
        $conn = Model::getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Créer la transaction
            $stmt = $conn->prepare('
                INSERT INTO Transaction (user_id, account_id, category_id, type, amount, description, date, is_recurring, notes)
                VALUES (:user_id, :account_id, :category_id, :type, :amount, :description, :date, :is_recurring, :notes)
            ');
            
            $amount = abs(floatval($input['amount']));
            
            $stmt->execute([
                'user_id' => $userId,
                'account_id' => intval($input['account_id']),
                'category_id' => isset($input['category_id']) ? intval($input['category_id']) : null,
                'type' => $input['type'],
                'amount' => $amount,
                'description' => trim($input['description']),
                'date' => $input['date'],
                'is_recurring' => isset($input['is_recurring']) ? (bool)$input['is_recurring'] : false,
                'notes' => $input['notes'] ?? null
            ]);
            
            $transactionId = $conn->lastInsertId();
            
            // Mettre à jour le solde du compte
            $balanceChange = $input['type'] === 'income' ? $amount : -$amount;
            $stmt = $conn->prepare('UPDATE Account SET current_balance = current_balance + :change WHERE id = :id');
            $stmt->execute(['change' => $balanceChange, 'id' => $input['account_id']]);
            
            $conn->commit();
            
            $this->json([
                'code' => 201,
                'message' => 'Transaction créée',
                'data' => ['id' => $transactionId]
            ]);
            
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            $this->json(['code' => 500, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Mettre à jour une transaction
     */
    #[CRoute('/api/transactions/{id}', CHTTPMethod::PUT, middleware: ['auth'])]
    public function update(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            $this->json(['code' => 400, 'message' => 'Données invalides']);
            return;
        }
        
        $conn = Model::getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Récupérer l'ancienne transaction
            $stmt = $conn->prepare('SELECT * FROM Transaction WHERE id = :id AND user_id = :user_id');
            $stmt->execute(['id' => $id, 'user_id' => $userId]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$old) {
                http_response_code(404);
                $this->json(['code' => 404, 'message' => 'Transaction non trouvée']);
                return;
            }
            
            $newAmount = abs(floatval($input['amount'] ?? $old['amount']));
            $newType = $input['type'] ?? $old['type'];
            $newAccountId = intval($input['account_id'] ?? $old['account_id']);
            
            // Annuler l'effet de l'ancienne transaction sur le compte
            $oldBalanceChange = $old['type'] === 'income' ? -floatval($old['amount']) : floatval($old['amount']);
            $stmt = $conn->prepare('UPDATE Account SET current_balance = current_balance + :change WHERE id = :id');
            $stmt->execute(['change' => $oldBalanceChange, 'id' => $old['account_id']]);
            
            // Appliquer le nouvel effet
            $newBalanceChange = $newType === 'income' ? $newAmount : -$newAmount;
            $stmt = $conn->prepare('UPDATE Account SET current_balance = current_balance + :change WHERE id = :id');
            $stmt->execute(['change' => $newBalanceChange, 'id' => $newAccountId]);
            
            // Mettre à jour la transaction
            $stmt = $conn->prepare('
                UPDATE Transaction 
                SET account_id = :account_id, category_id = :category_id, type = :type, 
                    amount = :amount, description = :description, date = :date, notes = :notes
                WHERE id = :id AND user_id = :user_id
            ');
            
            $stmt->execute([
                'id' => $id,
                'user_id' => $userId,
                'account_id' => $newAccountId,
                'category_id' => isset($input['category_id']) ? intval($input['category_id']) : null,
                'type' => $newType,
                'amount' => $newAmount,
                'description' => trim($input['description'] ?? $old['description']),
                'date' => $input['date'] ?? $old['date'],
                'notes' => $input['notes'] ?? $old['notes']
            ]);
            
            $conn->commit();
            
            $this->json(['code' => 200, 'message' => 'Transaction mise à jour']);
            
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            $this->json(['code' => 500, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Supprimer une transaction
     */
    #[CRoute('/api/transactions/{id}', CHTTPMethod::DELETE, middleware: ['auth'])]
    public function delete(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Récupérer la transaction
            $stmt = $conn->prepare('SELECT * FROM Transaction WHERE id = :id AND user_id = :user_id');
            $stmt->execute(['id' => $id, 'user_id' => $userId]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$transaction) {
                http_response_code(404);
                $this->json(['code' => 404, 'message' => 'Transaction non trouvée']);
                return;
            }
            
            // Annuler l'effet sur le solde du compte
            $balanceChange = $transaction['type'] === 'income' ? -floatval($transaction['amount']) : floatval($transaction['amount']);
            $stmt = $conn->prepare('UPDATE Account SET current_balance = current_balance + :change WHERE id = :id');
            $stmt->execute(['change' => $balanceChange, 'id' => $transaction['account_id']]);
            
            // Supprimer la transaction
            $stmt = $conn->prepare('DELETE FROM Transaction WHERE id = :id');
            $stmt->execute(['id' => $id]);
            
            $conn->commit();
            
            $this->json(['code' => 200, 'message' => 'Transaction supprimée']);
            
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            $this->json(['code' => 500, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Statistiques mensuelles
     */
    #[CRoute('/api/transactions/stats', CHTTPMethod::GET, middleware: ['auth'])]
    public function stats(): void
    {
        $userId = $_SESSION['user']['id'];
        $month = $_GET['month'] ?? date('Y-m');
        
        $conn = Model::getConnection();
        
        // Revenus et dépenses du mois
        $stmt = $conn->prepare('
            SELECT 
                type,
                SUM(amount) as total
            FROM Transaction 
            WHERE user_id = :user_id 
                AND type IN ("income", "expense")
                AND DATE_FORMAT(date, "%Y-%m") = :month
            GROUP BY type
        ');
        $stmt->execute(['user_id' => $userId, 'month' => $month]);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $income = floatval($results['income'] ?? 0);
        $expense = floatval($results['expense'] ?? 0);
        
        // Dernières transactions
        $stmt = $conn->prepare('
            SELECT 
                t.id, t.type, t.amount, t.description, t.date,
                c.name as category_name, c.icon as category_icon, c.color as category_color
            FROM Transaction t
            LEFT JOIN Category c ON t.category_id = c.id
            WHERE t.user_id = :user_id 
            ORDER BY t.date DESC, t.id DESC 
            LIMIT 5
        ');
        $stmt->execute(['user_id' => $userId]);
        $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json([
            'code' => 200,
            'month' => $month,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'recent_transactions' => $recentTransactions
        ]);
    }

    /**
     * Page des transactions
     */
    #[CRoute('/transactions', CHTTPMethod::GET, middleware: ['auth'])]
    public function index(): void
    {
        $this->view('finance/transactions', [
            'title' => 'Transactions',
            'customCss' => '/public/src/css/finance/transactions.css',
            'customJs' => '/public/src/js/finance/transactions.js'
        ]);
    }
}
