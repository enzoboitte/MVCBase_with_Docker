<?php

class HomeController extends Controller
{
    #[CRoute('/', CHTTPMethod::GET)]
    public function index(): void
    {
        // Redirect to dashboard or login
        header('Location: /dashboard');
        exit;
    }

    #[CRoute('/login', CHTTPMethod::GET)]
    public function login(): void
    {
        $this->view('auth/login', [
            'title' => 'Connexion'
        ]);
    }

    #[CRoute('/login', CHTTPMethod::POST)]
    public function doLogin(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            header('Location: /login?error=1');
            exit;
        }

        $conn = Model::getConnection();
        $stmt = $conn->prepare("SELECT id, email, password, name FROM Admin WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            
            header('Location: /dashboard');
            exit;
        }

        // Invalid credentials - redirect back to login
        header('Location: /login?error=1');
        exit;
    }

    #[CRoute('/logout', CHTTPMethod::GET)]
    public function logout(): void
    {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }
}
