<?php ob_start(); 
$customCss[] = '/public/src/css/dashboard/index.css';
$customCss[] = '/public/src/css/dashboard/connexion.css';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1>Connexion</h1>
            <p>Accédez à votre tableau de bord</p>
        </div>
        
        <form id="login-form" class="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="admin@example.com">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                    <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-error" id="login-error"></div>
            
            <button type="submit" class="login-button">
                <span class="button-text">Se connecter</span>
                <span class="button-loader" style="display: none;">
                    <i class="fa fa-spinner fa-spin"></i>
                </span>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('login-form');
    const errorDiv = document.getElementById('login-error');
    const togglePassword = document.querySelector('.toggle-password');
    const passwordInput = document.getElementById('password');
    const submitBtn = form.querySelector('.login-button');
    const buttonText = submitBtn.querySelector('.button-text');
    const buttonLoader = submitBtn.querySelector('.button-loader');

    // Toggle password visibility
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Reset error
        errorDiv.classList.remove('show');
        errorDiv.textContent = '';
        
        // Show loader
        buttonText.style.display = 'none';
        buttonLoader.style.display = 'inline-block';
        submitBtn.disabled = true;
        
        const formData = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        };
        
        try {
            const response = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Redirect to dashboard
                window.location.href = '/dashboard';
            } else {
                errorDiv.textContent = data.message || 'Identifiants incorrects';
                errorDiv.classList.add('show');
            }
        } catch (error) {
            errorDiv.textContent = 'Erreur de connexion au serveur';
            errorDiv.classList.add('show');
        } finally {
            buttonText.style.display = 'inline';
            buttonLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    });
});
</script>

<?php $content = ob_get_clean(); ?>
<?php require ROOT . '/app/views/layout.php'; ?>
