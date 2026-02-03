<?php

class SubscriptionController extends Controller
{
    /**
     * Liste tous les abonnements de l'utilisateur
     */
    #[CRoute('/api/subscriptions', CHTTPMethod::GET, middleware: ['auth'])]
    public function list(): void
    {
        $userId = $_SESSION['user']['id'];
        $activeOnly = isset($_GET['active']) ? filter_var($_GET['active'], FILTER_VALIDATE_BOOLEAN) : null;
        
        $conn = Model::getConnection();
        
        $sql = '
            SELECT 
                s.id,
                s.name,
                s.amount,
                s.type,
                s.frequency,
                s.next_due_date,
                s.is_active,
                s.auto_renew,
                s.icon,
                s.color,
                s.notes,
                s.category_id,
                c.name as category_name,
                s.account_id,
                a.name as account_name
            FROM Subscription s
            LEFT JOIN Category c ON s.category_id = c.id
            LEFT JOIN Account a ON s.account_id = a.id
            WHERE s.user_id = :user_id
        ';
        $params = ['user_id' => $userId];
        
        if ($activeOnly !== null) {
            $sql .= ' AND s.is_active = :active';
            $params['active'] = $activeOnly ? 1 : 0;
        }
        
        $sql .= ' ORDER BY s.next_due_date ASC';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer le total mensuel
        $monthlyTotal = ['income' => 0, 'expense' => 0];
        foreach ($subscriptions as $sub) {
            if (!$sub['is_active']) continue;
            
            $monthlyAmount = floatval($sub['amount']);
            switch ($sub['frequency']) {
                case 'daily':
                    $monthlyAmount *= 30;
                    break;
                case 'weekly':
                    $monthlyAmount *= 4;
                    break;
                case 'yearly':
                    $monthlyAmount /= 12;
                    break;
            }
            $monthlyTotal[$sub['type']] += $monthlyAmount;
        }
        
        $this->json([
            'code' => 200,
            'data' => $subscriptions,
            'monthly_income' => round($monthlyTotal['income'], 2),
            'monthly_expense' => round($monthlyTotal['expense'], 2),
            'monthly_balance' => round($monthlyTotal['income'] - $monthlyTotal['expense'], 2)
        ]);
    }

    /**
     * Abonnements à venir (30 prochains jours)
     */
    #[CRoute('/api/subscriptions/upcoming', CHTTPMethod::GET, middleware: ['auth'])]
    public function upcoming(): void
    {
        $userId = $_SESSION['user']['id'];
        $days = intval($_GET['days'] ?? 30);
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT 
                s.id,
                s.name,
                s.amount,
                s.type,
                s.frequency,
                s.next_due_date,
                s.icon,
                s.color,
                c.name as category_name,
                a.name as account_name
            FROM Subscription s
            LEFT JOIN Category c ON s.category_id = c.id
            LEFT JOIN Account a ON s.account_id = a.id
            WHERE s.user_id = :user_id 
                AND s.is_active = 1
                AND s.next_due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY s.next_due_date ASC
        ');
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(['code' => 200, 'data' => $subscriptions]);
    }

    /**
     * Récupérer un abonnement par ID
     */
    #[CRoute('/api/subscriptions/{id}', CHTTPMethod::GET, middleware: ['auth'])]
    public function show(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT s.*, c.name as category_name, a.name as account_name
            FROM Subscription s
            LEFT JOIN Category c ON s.category_id = c.id
            LEFT JOIN Account a ON s.account_id = a.id
            WHERE s.id = :id AND s.user_id = :user_id
        ');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subscription) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Abonnement non trouvé']);
            return;
        }
        
        $this->json(['code' => 200, 'data' => $subscription]);
    }

    /**
     * Créer un nouvel abonnement
     */
    #[CRoute('/api/subscriptions', CHTTPMethod::POST, middleware: ['auth'])]
    public function create(): void
    {
        $userId = $_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name']) || !isset($input['amount']) || !isset($input['account_id']) || !isset($input['next_due_date'])) {
            http_response_code(400);
            $this->json(['code' => 400, 'message' => 'Champs requis: name, amount, account_id, next_due_date']);
            return;
        }
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            INSERT INTO Subscription (user_id, name, amount, category_id, account_id, type, frequency, next_due_date, is_active, auto_renew, icon, color, notes)
            VALUES (:user_id, :name, :amount, :category_id, :account_id, :type, :frequency, :next_due_date, :is_active, :auto_renew, :icon, :color, :notes)
        ');
        
        $stmt->execute([
            'user_id' => $userId,
            'name' => trim($input['name']),
            'amount' => abs(floatval($input['amount'])),
            'category_id' => isset($input['category_id']) ? intval($input['category_id']) : null,
            'account_id' => intval($input['account_id']),
            'type' => $input['type'] ?? 'expense',
            'frequency' => $input['frequency'] ?? 'monthly',
            'next_due_date' => $input['next_due_date'],
            'is_active' => isset($input['is_active']) ? (bool)$input['is_active'] : true,
            'auto_renew' => isset($input['auto_renew']) ? (bool)$input['auto_renew'] : true,
            'icon' => $input['icon'] ?? 'fa-repeat',
            'color' => $input['color'] ?? '#6366f1',
            'notes' => $input['notes'] ?? null
        ]);
        
        $this->json([
            'code' => 201,
            'message' => 'Abonnement créé',
            'data' => ['id' => $conn->lastInsertId()]
        ]);
    }

    /**
     * Mettre à jour un abonnement
     */
    #[CRoute('/api/subscriptions/{id}', CHTTPMethod::PUT, middleware: ['auth'])]
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
        
        // Vérifier que l'abonnement appartient à l'utilisateur
        $stmt = $conn->prepare('SELECT id FROM Subscription WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Abonnement non trouvé']);
            return;
        }
        
        $stmt = $conn->prepare('
            UPDATE Subscription 
            SET name = :name, amount = :amount, category_id = :category_id, account_id = :account_id,
                type = :type, frequency = :frequency, next_due_date = :next_due_date,
                is_active = :is_active, auto_renew = :auto_renew, icon = :icon, color = :color, notes = :notes
            WHERE id = :id AND user_id = :user_id
        ');
        
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => trim($input['name']),
            'amount' => abs(floatval($input['amount'])),
            'category_id' => isset($input['category_id']) ? intval($input['category_id']) : null,
            'account_id' => intval($input['account_id']),
            'type' => $input['type'] ?? 'expense',
            'frequency' => $input['frequency'] ?? 'monthly',
            'next_due_date' => $input['next_due_date'],
            'is_active' => isset($input['is_active']) ? (bool)$input['is_active'] : true,
            'auto_renew' => isset($input['auto_renew']) ? (bool)$input['auto_renew'] : true,
            'icon' => $input['icon'] ?? 'fa-repeat',
            'color' => $input['color'] ?? '#6366f1',
            'notes' => $input['notes'] ?? null
        ]);
        
        $this->json(['code' => 200, 'message' => 'Abonnement mis à jour']);
    }

    /**
     * Supprimer un abonnement
     */
    #[CRoute('/api/subscriptions/{id}', CHTTPMethod::DELETE, middleware: ['auth'])]
    public function delete(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('DELETE FROM Subscription WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Abonnement non trouvé']);
            return;
        }
        
        $this->json(['code' => 200, 'message' => 'Abonnement supprimé']);
    }

    /**
     * Convertir un abonnement en transaction (valider le paiement)
     */
    #[CRoute('/api/subscriptions/{id}/convert', CHTTPMethod::POST, middleware: ['auth'])]
    public function convertToTransaction(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Récupérer l'abonnement
            $stmt = $conn->prepare('SELECT * FROM Subscription WHERE id = :id AND user_id = :user_id');
            $stmt->execute(['id' => $id, 'user_id' => $userId]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subscription) {
                http_response_code(404);
                $this->json(['code' => 404, 'message' => 'Abonnement non trouvé']);
                return;
            }
            
            // Créer la transaction
            $stmt = $conn->prepare('
                INSERT INTO Transaction (user_id, account_id, category_id, type, amount, description, date, is_recurring, subscription_id)
                VALUES (:user_id, :account_id, :category_id, :type, :amount, :description, :date, 1, :subscription_id)
            ');
            
            $stmt->execute([
                'user_id' => $userId,
                'account_id' => $subscription['account_id'],
                'category_id' => $subscription['category_id'],
                'type' => $subscription['type'],
                'amount' => $subscription['amount'],
                'description' => $subscription['name'],
                'date' => $subscription['next_due_date'],
                'subscription_id' => $id
            ]);
            
            // Mettre à jour le solde du compte
            $balanceChange = $subscription['type'] === 'income' ? floatval($subscription['amount']) : -floatval($subscription['amount']);
            $stmt = $conn->prepare('UPDATE Account SET current_balance = current_balance + :change WHERE id = :id');
            $stmt->execute(['change' => $balanceChange, 'id' => $subscription['account_id']]);
            
            // Calculer la prochaine date d'échéance
            $nextDate = new DateTime($subscription['next_due_date']);
            switch ($subscription['frequency']) {
                case 'daily':
                    $nextDate->modify('+1 day');
                    break;
                case 'weekly':
                    $nextDate->modify('+1 week');
                    break;
                case 'monthly':
                    $nextDate->modify('+1 month');
                    break;
                case 'yearly':
                    $nextDate->modify('+1 year');
                    break;
            }
            
            // Mettre à jour la date de prochain prélèvement
            $stmt = $conn->prepare('UPDATE Subscription SET next_due_date = :next_date WHERE id = :id');
            $stmt->execute(['next_date' => $nextDate->format('Y-m-d'), 'id' => $id]);
            
            $conn->commit();
            
            $this->json([
                'code' => 200,
                'message' => 'Paiement validé et transaction créée',
                'next_due_date' => $nextDate->format('Y-m-d')
            ]);
            
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            $this->json(['code' => 500, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Données pour le calendrier des abonnements
     */
    #[CRoute('/api/subscriptions/calendar', CHTTPMethod::GET, middleware: ['auth'])]
    public function calendar(): void
    {
        $userId = $_SESSION['user']['id'];
        $month = $_GET['month'] ?? date('Y-m');
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT 
                s.id,
                s.name,
                s.amount,
                s.type,
                s.next_due_date,
                s.icon,
                s.color
            FROM Subscription s
            WHERE s.user_id = :user_id 
                AND s.is_active = 1
                AND DATE_FORMAT(s.next_due_date, "%Y-%m") = :month
            ORDER BY s.next_due_date ASC
        ');
        $stmt->execute(['user_id' => $userId, 'month' => $month]);
        
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Grouper par jour
        $calendar = [];
        foreach ($subscriptions as $sub) {
            $day = date('j', strtotime($sub['next_due_date']));
            if (!isset($calendar[$day])) {
                $calendar[$day] = [];
            }
            $calendar[$day][] = $sub;
        }
        
        $this->json(['code' => 200, 'data' => $calendar, 'month' => $month]);
    }

    /**
     * Page des abonnements
     */
    #[CRoute('/subscriptions', CHTTPMethod::GET, middleware: ['auth'])]
    public function index(): void
    {
        $this->view('finance/subscriptions', [
            'title' => 'Abonnements',
            'customCss' => '/public/src/css/finance/subscriptions.css',
            'customJs' => '/public/src/js/finance/subscriptions.js'
        ]);
    }
}
