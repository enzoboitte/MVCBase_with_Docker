<?php

// accounts == bank accounts

#[CMiddleware(middleware: ['auth'])]
class AccountController extends Controller
{
    private function getBridgeApi(): CBridgeApi
    {
        return new CBridgeApi(
            getenv('BRIDGE_CLIENT_ID') ?: 'sandbox_id_4688615bb0e7451fa4679d41c11650e9',
            getenv('BRIDGE_CLIENT_SECRET') ?: 'sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh'
        );
    }

    // ========================
    // PAGES (Views)
    // ========================

    #[CRoute('/accounts', CHTTPMethod::GET)]
    public function index(): void
    {
        $this->view('account/index', [
            'title' => 'Mes Comptes',
        ]);
    }

    #[CRoute('/accounts/create', CHTTPMethod::GET)]
    public function create(): void
    {
        $this->view('account/create', [
            'title' => 'Ajouter un compte',
        ]);
    }

    #[CRoute('/accounts/create/custom', CHTTPMethod::GET)]
    public function createCustom(): void
    {
        $this->view('account/create_custom', [
            'title' => 'Créer un compte personnalisé',
        ]);
    }

    #[CRoute('/accounts/create/import', CHTTPMethod::GET)]
    public function createImport(): void
    {
        $this->view('account/create_import', [
            'title' => 'Importer depuis ma banque',
        ]);
    }

    #[CRoute('/accounts/{id}', CHTTPMethod::GET)]
    public function show(string $id): void
    {
        $this->view('account/show', [
            'title' => 'Détails du compte',
            'accountId' => $id,
        ]);
    }

    #[CRoute('/accounts/{id}/edit', CHTTPMethod::GET)]
    public function edit(string $id): void
    {
        $this->view('account/edit', [
            'title' => 'Modifier le compte',
            'accountId' => $id,
        ]);
    }

    // ========================
    // API CRUD (REST)
    // ========================

    #[CRoute('/api/accounts', CHTTPMethod::GET)]
    public function apiGetAccounts(): void
    {
        $conn = Model::getConnection();
        $stmt = $conn->prepare('SELECT * FROM Account WHERE admin_id = :admin_id ORDER BY created_at DESC');
        $stmt->execute(['admin_id' => $_SESSION['user']['id']]);
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->json(['code' => 200, 'data' => $accounts]);
    }

    #[CRoute('/api/accounts/{id}', CHTTPMethod::GET)]
    public function apiGetAccount(string $id): void
    {
        $conn = Model::getConnection();
        $stmt = $conn->prepare('SELECT * FROM Account WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$account) {
            $this->json(['code' => 404, 'error' => 'Compte non trouvé']);
            return;
        }
        
        $this->json(['code' => 200, 'data' => $account]);
    }

    #[CRoute('/api/accounts', CHTTPMethod::POST)]
    public function apiCreateAccount(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name'])) {
            $this->json(['code' => 400, 'error' => 'Le nom est requis']);
            return;
        }

        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            INSERT INTO Account (admin_id, name, balance, accounting_balance, instant_balance, color, type, id_bridge) 
            VALUES (:admin_id, :name, :balance, :accounting_balance, :instant_balance, :color, :type, :id_bridge)
        ');
        
        $stmt->execute([
            'admin_id' => $_SESSION['user']['id'],
            'name' => $data['name'],
            'balance' => $data['balance'] ?? 0,
            'accounting_balance' => $data['accounting_balance'] ?? $data['balance'] ?? 0,
            'instant_balance' => $data['instant_balance'] ?? $data['balance'] ?? 0,
            'color' => $data['color'] ?? '#2563eb',
            'type' => $data['type'] ?? 'other',
            'id_bridge' => $data['id_bridge'] ?? null,
        ]);

        $this->json(['code' => 201, 'data' => ['id' => $conn->lastInsertId()], 'message' => 'Compte créé avec succès']);
    }

    #[CRoute('/api/accounts/{id}', CHTTPMethod::PUT)]
    public function apiUpdateAccount(string $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $conn = Model::getConnection();
        
        // Vérifier que le compte appartient à l'utilisateur
        $stmt = $conn->prepare('SELECT id FROM Account WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            $this->json(['code' => 404, 'error' => 'Compte non trouvé']);
            return;
        }

        $stmt = $conn->prepare('
            UPDATE Account 
            SET name = :name, balance = :balance, color = :color, type = :type 
            WHERE id = :id AND admin_id = :admin_id
        ');
        
        $stmt->execute([
            'id' => $id,
            'admin_id' => $_SESSION['user']['id'],
            'name' => $data['name'],
            'balance' => $data['balance'] ?? 0,
            'color' => $data['color'] ?? '#2563eb',
            'type' => $data['type'] ?? 'other',
        ]);

        $this->json(['code' => 200, 'message' => 'Compte modifié avec succès']);
    }

    #[CRoute('/api/accounts/{id}', CHTTPMethod::DELETE)]
    public function apiDeleteAccount(string $id): void
    {
        $conn = Model::getConnection();
        
        // Vérifier que le compte appartient à l'utilisateur
        $stmt = $conn->prepare('SELECT id FROM Account WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);
        if (!$stmt->fetch()) {
            $this->json(['code' => 404, 'error' => 'Compte non trouvé']);
            return;
        }

        $stmt = $conn->prepare('DELETE FROM Account WHERE id = :id AND admin_id = :admin_id');
        $stmt->execute(['id' => $id, 'admin_id' => $_SESSION['user']['id']]);

        $this->json(['code' => 200, 'message' => 'Compte supprimé avec succès']);
    }

    // ========================
    // API BRIDGE
    // ========================

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
     * Retourne l'instance CBridgeApi initialisée ou null si erreur
     */
    private ?array $bridgeError = null;
    
    private function initializeBridge(): ?CBridgeApi
    {
        $bridge = $this->getBridgeApi();
        $userUuid = $this->generateBridgeUserUuid();
        
        $result = $bridge->F_lInitializeUser($userUuid);
        
        if ($result['success']) {
            $bridge->F_vSaveToSession();
            return $bridge;
        }
        
        $this->bridgeError = $result;
        return null;
    }

    #[CRoute('/api/bridge/init', CHTTPMethod::POST)]
    public function apiBridgeInit(): void
    {
        $bridge = $this->getBridgeApi();
        
        // Générer l'UUID unique pour cet admin
        $userUuid = $this->generateBridgeUserUuid();
        
        // Initialiser l'utilisateur Bridge (crée s'il n'existe pas, sinon récupère)
        $result = $bridge->F_lInitializeUser($userUuid);
        
        if ($result['success']) {
            $bridge->F_vSaveToSession();
            $this->json([
                'code' => 200, 
                'message' => 'Session Bridge initialisée',
                'data' => [
                    'user_uuid' => $result['user_uuid'],
                    'accounts_count' => $result['accounts_count']
                ]
            ]);
        } else {
            $this->json(['code' => 500, 'error' => 'Erreur lors de l\'initialisation Bridge', 'details' => $result]);
        }
    }

    #[CRoute('/api/bridge/connect', CHTTPMethod::POST)]
    public function apiBridgeConnect(): void
    {
        $bridge = $this->initializeBridge();
        
        if (!$bridge) {
            $this->json(['code' => 500, 'error' => 'Impossible d\'initialiser Bridge', 'details' => $this->bridgeError]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? $_SESSION['user']['email'] ?? '';
        
        if (empty($email)) {
            $this->json(['code' => 400, 'error' => 'Email requis']);
            return;
        }

        // --- CONSTRUCTION AUTOMATIQUE DE L'URL ---
        // Détecte le protocole (http ou https)
        $protocol = "https://"; //(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        // Récupère le nom de domaine (ex: tyche-info.fr ou localhost:8080)
        $domainName = $_SERVER['HTTP_HOST'];
        
        // Définit le chemin de ton callback (le script qui reçoit le retour de la banque)
        $callbackPath = '/accounts/create/import/callback';
        $autoCallbackUrl = $protocol . $domainName . $callbackPath;

        die($autoCallbackUrl); // Debug pour vérifier l'URL générée

        $result = $bridge->F_lCreateConnectSession($email, [
            // Utilise l'URL auto si rien n'est envoyé dans le body JSON
            'callback_url' => ($data['callback_url'] ?? $autoCallbackUrl),
        ]);
        
        if ($result['success']) {
            $this->json([
                'code' => 200, 
                'data' => [
                    'connect_url' => $result['response']['url'] ?? $result['response']['connect_url'] ?? null,
                    'session_id' => $result['response']['id'] ?? null,
                    'callback_used' => $autoCallbackUrl // Debug pour vérifier l'URL générée
                ],
                'debug' => $result['response']
            ]);
        } else {
            $this->json(['code' => 500, 'error' => 'Erreur lors de la création de session', 'details' => $result]);
        }
    }

    #[CRoute('/api/bridge/accounts', CHTTPMethod::GET)]
    public function apiBridgeGetAccounts(): void
    {
        $bridge = $this->initializeBridge();
        
        if (!$bridge) {
            $this->json(['code' => 500, 'error' => 'Impossible d\'initialiser Bridge']);
            return;
        }

        $result = $bridge->F_lGetAccounts();
        
        if ($result['success']) {
            $this->json(['code' => 200, 'data' => $result['response']['resources'] ?? []]);
        } else {
            $this->json(['code' => 500, 'error' => 'Erreur lors de la récupération des comptes', 'details' => $result]);
        }
    }

    #[CRoute('/api/bridge/accounts/import', CHTTPMethod::POST)]
    public function apiBridgeImportAccounts(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $accountIds = $data['account_ids'] ?? [];
        
        if (empty($accountIds)) {
            $this->json(['code' => 400, 'error' => 'Aucun compte sélectionné']);
            return;
        }

        $bridge = $this->initializeBridge();
        
        if (!$bridge) {
            $this->json(['code' => 500, 'error' => 'Impossible d\'initialiser Bridge']);
            return;
        }

        $result = $bridge->F_lGetAccounts();
        
        if (!$result['success']) {
            $this->json(['code' => 500, 'error' => 'Erreur lors de la récupération des comptes Bridge']);
            return;
        }

        $bridgeAccounts = $result['response']['resources'] ?? [];
        $conn = Model::getConnection();
        $imported = 0;
        
        // Mapping des types Bridge vers nos types
        $typeMapping = [
            'checking' => 'checking',
            'savings' => 'savings',
            'card' => 'credit_card',
            'loan' => 'other',
            'market' => 'other',
            'life_insurance' => 'other',
        ];
        
        // Couleurs par type
        $colorMapping = [
            'checking' => '#2563eb',
            'savings' => '#16a34a',
            'credit_card' => '#dc2626',
            'cash' => '#f59e0b',
            'other' => '#8b5cf6',
        ];

        foreach ($bridgeAccounts as $bridgeAccount) {
            if (!in_array($bridgeAccount['id'], $accountIds)) {
                continue;
            }

            // Vérifier si le compte n'est pas déjà importé
            $stmt = $conn->prepare('SELECT id FROM Account WHERE id_bridge = :id_bridge AND admin_id = :admin_id');
            $stmt->execute(['id_bridge' => $bridgeAccount['id'], 'admin_id' => $_SESSION['user']['id']]);
            
            if ($stmt->fetch()) {
                continue; // Déjà importé
            }

            $type = $typeMapping[$bridgeAccount['type'] ?? 'other'] ?? 'other';
            $color = $colorMapping[$type] ?? '#8b5cf6';

            $stmt = $conn->prepare('
                INSERT INTO Account (admin_id, name, balance, accounting_balance, instant_balance, color, type, id_bridge) 
                VALUES (:admin_id, :name, :balance, :accounting_balance, :instant_balance, :color, :type, :id_bridge)
            ');
            
            $stmt->execute([
                'admin_id' => $_SESSION['user']['id'],
                'name' => $bridgeAccount['name'] ?? 'Compte importé',
                'balance' => $bridgeAccount['balance'] ?? 0,
                'accounting_balance' => $bridgeAccount['accounting_balance'] ?? $bridgeAccount['balance'] ?? 0,
                'instant_balance' => $bridgeAccount['instant_balance'] ?? $bridgeAccount['balance'] ?? 0,
                'color' => $color,
                'type' => $type,
                'id_bridge' => $bridgeAccount['id'],
            ]);
            
            $imported++;
        }

        $this->json(['code' => 200, 'message' => "$imported compte(s) importé(s) avec succès"]);
    }

    #[CRoute('/api/bridge/sync', CHTTPMethod::POST)]
    public function apiBridgeSync(): void
    {
        $bridge = $this->initializeBridge();
        
        if (!$bridge) {
            $this->json(['code' => 500, 'error' => 'Impossible d\'initialiser Bridge']);
            return;
        }

        $result = $bridge->F_lGetAccounts();
        
        if (!$result['success']) {
            $this->json(['code' => 500, 'error' => 'Erreur lors de la synchronisation']);
            return;
        }

        $bridgeAccounts = $result['response']['resources'] ?? [];
        $conn = Model::getConnection();
        $synced = 0;

        foreach ($bridgeAccounts as $bridgeAccount) {
            $stmt = $conn->prepare('
                UPDATE Account 
                SET balance = :balance, 
                    accounting_balance = :accounting_balance, 
                    instant_balance = :instant_balance 
                WHERE id_bridge = :id_bridge AND admin_id = :admin_id
            ');
            
            $stmt->execute([
                'balance' => $bridgeAccount['balance'] ?? 0,
                'accounting_balance' => $bridgeAccount['accounting_balance'] ?? 0,
                'instant_balance' => $bridgeAccount['instant_balance'] ?? 0,
                'id_bridge' => $bridgeAccount['id'],
                'admin_id' => $_SESSION['user']['id'],
            ]);
            
            if ($stmt->rowCount() > 0) {
                $synced++;
            }
        }

        $this->json(['code' => 200, 'message' => "$synced compte(s) synchronisé(s)"]);
    }

    #[CRoute('/accounts/create/import/callback', CHTTPMethod::GET)]
    public function importCallback(): void
    {
        // Page de retour après connexion Bridge
        $this->view('account/import_callback', [
            'title' => 'Import en cours...',
        ]);
    }
}