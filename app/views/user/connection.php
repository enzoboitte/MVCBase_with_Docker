<?php ob_start(); ?>

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

<style>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    padding: 40px;
    width: 100%;
    max-width: 400px;
}

.login-header {
    text-align: center;
    margin-bottom: 32px;
}

.login-header h1 {
    font-size: 28px;
    color: #1e293b;
    margin-bottom: 8px;
}

.login-header p {
    color: #64748b;
    font-size: 14px;
}

.login-form .form-group {
    margin-bottom: 20px;
}

.login-form label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.login-form input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 15px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.login-form input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 48px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    padding: 4px;
    transition: color 0.2s;
}

.toggle-password:hover {
    color: #667eea;
}

.form-error {
    background: #fef2f2;
    color: #dc2626;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 16px;
    display: none;
}

.form-error.show {
    display: block;
}

.login-button {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.login-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(102, 126, 234, 0.4);
}

.login-button:active {
    transform: translateY(0);
}

.login-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.back-button {
    position: absolute;
    top: 20px;
    left: 20px;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

.back-button:hover {
    text-decoration: underline;
}
</style>

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
