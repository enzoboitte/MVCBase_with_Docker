<?php

class CategoryController extends Controller
{
    /**
     * Liste toutes les catégories de l'utilisateur
     */
    #[CRoute('/api/categories', CHTTPMethod::GET, middleware: ['auth'])]
    public function list(): void
    {
        $userId = $_SESSION['user']['id'];
        $type = $_GET['type'] ?? null; // 'income' ou 'expense'
        
        $conn = Model::getConnection();
        
        $sql = 'SELECT id, name, type, icon, color, budget_amount FROM Category WHERE user_id = :user_id';
        $params = ['user_id' => $userId];
        
        if ($type && in_array($type, ['income', 'expense'])) {
            $sql .= ' AND type = :type';
            $params['type'] = $type;
        }
        
        $sql .= ' ORDER BY type, name';
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(['code' => 200, 'data' => $categories]);
    }

    /**
     * Récupérer une catégorie par ID
     */
    #[CRoute('/api/categories/{id}', CHTTPMethod::GET, middleware: ['auth'])]
    public function show(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT id, name, type, icon, color, budget_amount 
            FROM Category 
            WHERE id = :id AND user_id = :user_id
        ');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Catégorie non trouvée']);
            return;
        }
        
        $this->json(['code' => 200, 'data' => $category]);
    }

    /**
     * Créer une nouvelle catégorie
     */
    #[CRoute('/api/categories', CHTTPMethod::POST, middleware: ['auth'])]
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
            INSERT INTO Category (user_id, name, type, icon, color, budget_amount)
            VALUES (:user_id, :name, :type, :icon, :color, :budget)
        ');
        
        $stmt->execute([
            'user_id' => $userId,
            'name' => trim($input['name']),
            'type' => $input['type'],
            'icon' => $input['icon'] ?? 'fa-tag',
            'color' => $input['color'] ?? '#64748b',
            'budget' => isset($input['budget_amount']) ? floatval($input['budget_amount']) : null
        ]);
        
        $this->json([
            'code' => 201,
            'message' => 'Catégorie créée',
            'data' => ['id' => $conn->lastInsertId()]
        ]);
    }

    /**
     * Mettre à jour une catégorie
     */
    #[CRoute('/api/categories/{id}', CHTTPMethod::PUT, middleware: ['auth'])]
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
        
        // Vérifier que la catégorie appartient à l'utilisateur
        $stmt = $conn->prepare('SELECT id FROM Category WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Catégorie non trouvée']);
            return;
        }
        
        $stmt = $conn->prepare('
            UPDATE Category 
            SET name = :name, type = :type, icon = :icon, color = :color, budget_amount = :budget
            WHERE id = :id AND user_id = :user_id
        ');
        
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => trim($input['name']),
            'type' => $input['type'],
            'icon' => $input['icon'] ?? 'fa-tag',
            'color' => $input['color'] ?? '#64748b',
            'budget' => isset($input['budget_amount']) ? floatval($input['budget_amount']) : null
        ]);
        
        $this->json(['code' => 200, 'message' => 'Catégorie mise à jour']);
    }

    /**
     * Supprimer une catégorie
     */
    #[CRoute('/api/categories/{id}', CHTTPMethod::DELETE, middleware: ['auth'])]
    public function delete(string $id): void
    {
        $userId = $_SESSION['user']['id'];
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('DELETE FROM Category WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            $this->json(['code' => 404, 'message' => 'Catégorie non trouvée']);
            return;
        }
        
        $this->json(['code' => 200, 'message' => 'Catégorie supprimée']);
    }

    /**
     * Statistiques des dépenses par catégorie pour le mois en cours
     */
    #[CRoute('/api/categories/stats', CHTTPMethod::GET, middleware: ['auth'])]
    public function stats(): void
    {
        $userId = $_SESSION['user']['id'];
        $month = $_GET['month'] ?? date('Y-m');
        
        $conn = Model::getConnection();
        $stmt = $conn->prepare('
            SELECT 
                c.id,
                c.name,
                c.icon,
                c.color,
                c.budget_amount,
                COALESCE(SUM(t.amount), 0) as spent
            FROM Category c
            LEFT JOIN Transaction t ON t.category_id = c.id 
                AND t.type = "expense"
                AND DATE_FORMAT(t.date, "%Y-%m") = :month
            WHERE c.user_id = :user_id AND c.type = "expense"
            GROUP BY c.id
            ORDER BY spent DESC
        ');
        $stmt->execute(['user_id' => $userId, 'month' => $month]);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer le pourcentage du budget utilisé
        foreach ($stats as &$stat) {
            $stat['spent'] = floatval($stat['spent']);
            $stat['budget_amount'] = $stat['budget_amount'] ? floatval($stat['budget_amount']) : null;
            if ($stat['budget_amount']) {
                $stat['percentage'] = round(($stat['spent'] / $stat['budget_amount']) * 100, 1);
                $stat['remaining'] = $stat['budget_amount'] - $stat['spent'];
            } else {
                $stat['percentage'] = null;
                $stat['remaining'] = null;
            }
        }
        
        $this->json(['code' => 200, 'data' => $stats, 'month' => $month]);
    }

    /**
     * Page de gestion des catégories
     */
    #[CRoute('/categories', CHTTPMethod::GET, middleware: ['auth'])]
    public function index(): void
    {
        $this->view('finance/categories', [
            'title' => 'Catégories',
            'customCss' => [
                '/public/src/css/finance/dashboard.css',
                '/public/src/css/finance/categories.css'
            ],
            'customJs' => '/public/src/js/finance/categories.js'
        ]);
    }
}
