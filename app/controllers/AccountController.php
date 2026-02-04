<?php

class AccountController extends Controller
{
    /**
     * Liste tous les comptes de l'utilisateur connecté
     */
    #[CRoute('/api/accounts', CHTTPMethod::GET, middleware: ['auth'])]
    public function list(): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT id, name, type, current_balance, icon, color, include_in_net_worth, created_at 
            FROM Account 
            WHERE user_id = :user_id 
            ORDER BY type, name
        ');
        $stmt->execute(['user_id' => $userId]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer le Net Worth
        $netWorth = 0;
        foreach ($accounts as $account) {
            if ($account['include_in_net_worth']) {
                $netWorth += floatval($account['current_balance']);
            }
        }
        
        $this->json([
            'code' => 200,
            'data' => $accounts,
            'net_worth' => $netWorth
        ]);
    }

    /**
     * Récupérer un compte par ID
     */
    #[CRoute('/api/accounts/{id}', CHTTPMethod::GET, middleware: ['auth'])]
    public function show(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT id, name, type, current_balance, icon, color, include_in_net_worth, created_at 
            FROM Account 
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Compte non trouvé']);
            return;
        }
        
        $this->json(['code' => 200, 'data' => $account]);
    }

    /**
     * Créer un nouveau compte
     */
    #[CRoute('/api/accounts', CHTTPMethod::POST, middleware: ['auth'])]
    public function create(): void
    {
        $userId = $_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name']) || !isset($input['type'])) {
            http_response_code(400);
            $this->json(['code' => 400, 'message' => 'Nom et type requis']);
            return;
        }
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            INSERT INTO Account (user_id, name, type, current_balance, icon, color, include_in_net_worth)
            VALUES (:user_id, :name, :type, :balance, :icon, :color, :include_net_worth)
        ');
        
        $stmt->execute([
            'user_id' => $userId,
            'name' => trim($input['name']),
            'type' => $input['type'],
            'balance' => floatval($input['current_balance'] ?? 0),
            'icon' => $input['icon'] ?? 'fa-university',
            'color' => $input['color'] ?? '#2563eb',
            'include_net_worth' => isset($input['include_in_net_worth']) ? (bool)$input['include_in_net_worth'] : true
        ]);
        
        $this->json([
            'code' => 201,
            'message' => 'Compte créé avec succès',
            'data' => ['id' => $conn->lastInsertId()]
        ]);
    }

    /**
     * Mettre à jour un compte
     */
    #[CRoute('/api/accounts/{id}', CHTTPMethod::PUT, middleware: ['auth'])]
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
        
        // Vérifier que le compte appartient à l'utilisateur
        $stmt = $conn->prepare('SELECT id FROM Account WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Compte non trouvé']);
            return;
        }
        
        $stmt = $conn->prepare('
            UPDATE Account 
            SET name = :name, type = :type, current_balance = :current_balance, icon = :icon, color = :color, include_in_net_worth = :include_net_worth
            WHERE id = :id AND user_id = :user_id
        ');
        
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => trim($input['name']),
            'type' => $input['type'],
            'current_balance' => floatval($input['current_balance'] ?? 0),
            'icon' => $input['icon'] ?? 'fa-university',
            'color' => $input['color'] ?? '#2563eb',
            'include_net_worth' => isset($input['include_in_net_worth']) ? ($input['include_in_net_worth'] ? 1 : 0) : 1
        ]);
        
        $this->json(['code' => 200, 'message' => 'Compte mis à jour']);
    }

    /**
     * Supprimer un compte
     */
    #[CRoute('/api/accounts/{id}', CHTTPMethod::DELETE, middleware: ['auth'])]
    public function delete(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('DELETE FROM Account WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Compte non trouvé']);
            return;
        }
        
        $this->json(['code' => 200, 'message' => 'Compte supprimé']);
    }

    /**
     * Effectuer un virement interne entre deux comptes
     * Ne compte pas comme dépense/revenu dans les stats
     */
    #[CRoute('/api/accounts/transfer', CHTTPMethod::POST, middleware: ['auth'])]
    public function transfer(): void
    {
        $userId = $_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['from_account_id']) || !isset($input['to_account_id']) || !isset($input['amount'])) {
            http_response_code(400);
            $this->json(['code' => 400, 'message' => 'Comptes source, destination et montant requis']);
            return;
        }
        
        $fromId = intval($input['from_account_id']);
        $toId = intval($input['to_account_id']);
        $amount = floatval($input['amount']);
        $description = $input['description'] ?? 'Virement interne';
        
        if ($amount <= 0) {
            http_response_code(400);
            $this->json(['code' => 400, 'message' => 'Le montant doit être positif']);
            return;
        }
        
        if ($fromId === $toId) {
            http_response_code(400);
            $this->json(['code' => 400, 'message' => 'Les comptes source et destination doivent être différents']);
            return;
        }
        
        $conn = Model::getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Vérifier que les deux comptes appartiennent à l'utilisateur
            $stmt = $conn->prepare('SELECT id, current_balance FROM Account WHERE id IN (:from, :to) AND user_id = :user_id');
            $stmt->execute(['from' => $fromId, 'to' => $toId, 'user_id' => $userId]);
            $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($accounts) < 2) {
                throw new Exception('Un ou plusieurs comptes non trouvés');
            }
            
            // Débiter le compte source
            $stmt = $conn->prepare('UPDATE Account SET current_balance = current_balance - :amount WHERE id = :id');
            $stmt->execute(['amount' => $amount, 'id' => $fromId]);
            
            // Créditer le compte destination
            $stmt = $conn->prepare('UPDATE Account SET current_balance = current_balance + :amount WHERE id = :id');
            $stmt->execute(['amount' => $amount, 'id' => $toId]);
            
            // Créer la transaction de type 'transfer'
            $stmt = $conn->prepare('
                INSERT INTO Transaction (user_id, account_id, type, amount, description, date, transfer_account_id)
                VALUES (:user_id, :from_account, "transfer", :amount, :description, CURDATE(), :to_account)
            ');
            $stmt->execute([
                'user_id' => $userId,
                'from_account' => $fromId,
                'amount' => $amount,
                'description' => $description,
                'to_account' => $toId
            ]);
            
            $conn->commit();
            
            $this->json(['code' => 200, 'message' => 'Virement effectué avec succès']);
            
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            $this->json(['code' => 500, 'message' => 'Erreur lors du virement: ' . $e->getMessage()]);
        }
    }

    /**
     * Page de gestion des comptes
     */
    #[CRoute('/accounts', CHTTPMethod::GET, middleware: ['auth'])]
    public function index(): void
    {
        $this->view('finance/accounts', [
            'title' => 'Mes Comptes',
            'customCss' => '/public/src/css/finance/accounts.css',
            'customJs' => '/public/src/js/finance/accounts.js'
        ]);
    }
}
