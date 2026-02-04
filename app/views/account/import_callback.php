<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Sync->getHtmlSvg() ?>
                    Import en cours...
                </div>
            </div>
            <div class="card-body" style="text-align:center;padding:3rem;">
                <div id="loadingState">
                    <div style="font-size:3rem;margin-bottom:1rem;">⏳</div>
                    <p>Récupération de vos comptes bancaires...</p>
                    <p class="text-muted">Veuillez patienter quelques instants.</p>
                </div>
                
                <div id="successState" style="display:none;">
                    <div style="font-size:3rem;margin-bottom:1rem;">✅</div>
                    <p style="font-weight:600;color:#16a34a;">Connexion réussie !</p>
                    <p class="text-muted">Vos comptes ont été récupérés. Vous allez être redirigé...</p>
                </div>
                
                <div id="errorState" style="display:none;">
                    <div style="font-size:3rem;margin-bottom:1rem;">❌</div>
                    <p style="font-weight:600;color:#dc2626;">Une erreur est survenue</p>
                    <p class="text-muted" id="errorMessage">Impossible de récupérer vos comptes.</p>
                    <a href="/accounts/create/import" class="btn btn-primary mt-3">Réessayer</a>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
async function processCallback() {
    const loadingState = document.getElementById('loadingState');
    const successState = document.getElementById('successState');
    const errorState = document.getElementById('errorState');
    
    try {
        // Attendre un peu que Bridge ait fini de synchroniser
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Récupérer les comptes
        const result = await apiRequest('GET', '/api/bridge/accounts');
        
        if (result.code === 200 && result.data && result.data.length > 0) {
            loadingState.style.display = 'none';
            successState.style.display = 'block';
            
            // Rediriger vers la page d'import après 2 secondes
            setTimeout(() => {
                window.location.href = '/accounts/create/import';
            }, 2000);
        } else {
            throw new Error('Aucun compte trouvé');
        }
    } catch (error) {
        console.error('Erreur callback:', error);
        loadingState.style.display = 'none';
        errorState.style.display = 'block';
        document.getElementById('errorMessage').textContent = error.message || 'Impossible de récupérer vos comptes.';
    }
}

document.addEventListener('DOMContentLoaded', processCallback);
</script>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css', '/public/src/css/account.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
