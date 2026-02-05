<?php

class UserController extends Controller
{
    #[CRoute('/login', CHTTPMethod::GET)]
    public function connection(): void
    {
        // Si déjà connecté, rediriger vers dashboard
        if (isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }
        
        $this->view('user/connection', [
            'title' => 'Connexion'
        ]);
    }
    
    #[CRoute('/api/login', CHTTPMethod::POST)]
    public function login(): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['email']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['code' => 400, 'message' => 'Email et mot de passe requis']);
            return;
        }
        
        $email = trim($input['email']);
        $password = $input['password'];
        
        try {
            $conn = Model::getConnection();
            $stmt = $conn->prepare('SELECT * FROM Admin WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$admin || !password_verify($password, $admin['password'])) {
                http_response_code(401);
                echo json_encode(['code' => 401, 'message' => 'Identifiants incorrects']);
                return;
            }
            
            // Créer la session
            $_SESSION['user'] = [
                'id' => $admin['id'],
                'email' => $admin['email'],
                'name' => $admin['name'],
                'role' => 'admin'
            ];
            
            echo json_encode([
                'code' => 200,
                'message' => 'Connexion réussie',
                'redirect' => '/'
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['code' => 500, 'message' => 'Erreur serveur']);
        }
    }
    
    #[CRoute('/logout', CHTTPMethod::GET)]
    public function logout(): void
    {
        // Détruire la session
        session_unset();
        session_destroy();
        
        header('Location: /');
        exit;
    }


    #[CRoute('/user', CHTTPMethod::POST)]
    public function createAccount(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['email']) || !isset($input['password']) || !isset($input['name'])) {
            http_response_code(400);
            echo json_encode(['code' => 400, 'message' => 'Champs requis manquants']);
            return;
        }
        $email = trim($input['email']);
        $password = password_hash($input['password'], PASSWORD_BCRYPT);
        $name = trim($input['name']);
        try {
            $conn = Model::getConnection();
            $stmt = $conn->prepare('INSERT INTO Admin (email, password, name) VALUES (:email, :password, :name)');
            $stmt->execute([
                'email' => $email,
                'password' => $password,
                'name' => $name
            ]);
            echo json_encode(['code' => 201, 'message' => 'Compte créé avec succès']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['code' => 500, 'message' => 'Erreur serveur']);
        }
    }
    
    #[CRoute('/api/me', CHTTPMethod::GET, middleware: ['auth'])]
    public function me(): void
    {
        header('Content-Type: application/json');
        
        echo json_encode([
            'code' => 200,
            'user' => $_SESSION['user']
        ]);
    }

    #[CRoute('/user', CHTTPMethod::GET)]
    public function listUsers(): void
    {
        $conn = Model::getConnection();
        $stmt = $conn->prepare('SELECT id, email, name FROM Admin ORDER BY id ASC');
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json(['code' => (empty($users) ? 404 : 200), 'data' => $users]);
    }

    #[CRoute('/user/{id}', CHTTPMethod::GET)]
    public function showUser(string $id): void
    {
        $conn = Model::getConnection();
        $stmt = $conn->prepare('SELECT id, email, name FROM Admin WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $this->json(['code' => 200, 'data' => $user]);
        } else {
            $this->json(['code' => 404, 'message' => 'Utilisateur non trouvé']);
        }
    }

    #[CRoute('/user/{id}', CHTTPMethod::DELETE)]
    public function deleteUser(string $id): void
    {
        $conn = Model::getConnection();
        $stmt = $conn->prepare('DELETE FROM Admin WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $this->json(['code' => 200, 'message' => 'Utilisateur supprimé avec succès']);
    }

    #[CRoute('/user/{id}', CHTTPMethod::PUT)]
    public function updateUser(string $id): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['email']) || !isset($input['name'])) {
            http_response_code(400);
            echo json_encode(['code' => 400, 'message' => 'Champs requis manquants']);
            return;
        }
        $email  = trim($input['email']);
        $name   = trim($input['name']);
        $passwd = password_hash($input['password'], PASSWORD_BCRYPT) ?? null;

        $conn = Model::getConnection();
        if ($passwd) {
            $stmt = $conn->prepare('UPDATE Admin SET email = :email, name = :name, password = :password WHERE id = :id');
            $stmt->execute([
                'email'    => $email,
                'name'     => $name,
                'password' => $passwd,
                'id'       => $id
            ]);
        } else {
            $stmt = $conn->prepare('UPDATE Admin SET email = :email, name = :name WHERE id = :id');
            $stmt->execute([
                'email' => $email,
                'name'  => $name,
                'id'    => $id
            ]);
        }
        $this->json(['code' => 200, 'message' => 'Utilisateur mis à jour avec succès']);
    }
}
