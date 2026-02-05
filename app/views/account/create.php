<?php ob_start(); ?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        <!-- Header -->
        <section class="card nohover">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Plus->getHtmlSvg() ?>
                    Ajouter un compte
                </div>
                <a href="/accounts" class="btn btn-ghost btn-sm">
                    <?= EFinanceIcon::Back->getHtmlSvg('icon-sm') ?>
                    Retour
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted">Choisissez comment vous souhaitez ajouter un compte :</p>
            </div>
        </section>

        <!-- Choix -->
        <section class="row">
            <div class="col-6">
                <a href="/accounts/create/custom" class="card" style="text-decoration:none;display:block;">
                    <div class="card-header">
                        <div class="card-title">
                            <?= EFinanceIcon::Edit->getHtmlSvg() ?>
                            Compte personnalisé
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="margin:0;">
                            Créez un compte manuellement en saisissant les informations vous-même.
                            Idéal pour les comptes d'espèces ou les comptes non bancaires.
                        </p>
                    </div>
                    <div class="card-footer">
                        <span class="btn btn-outline btn-sm">Créer manuellement</span>
                    </div>
                </a>
            </div>

            <div class="col-6">
                <a href="/accounts/create/import" class="card" style="text-decoration:none;display:block;">
                    <div class="card-header">
                        <div class="card-title">
                            <?= EFinanceIcon::Import->getHtmlSvg() ?>
                            Importer depuis ma banque
                        </div>
                        <span class="badge badge-success">Recommandé</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted" style="margin:0;">
                            Connectez-vous à votre banque via Bridge pour importer automatiquement 
                            vos comptes et synchroniser vos transactions.
                        </p>
                    </div>
                    <div class="card-footer">
                        <span class="btn btn-primary btn-sm">Connecter ma banque</span>
                    </div>
                </a>
            </div>
        </section>
    </main>
</div>

<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css', '/public/src/css/account.css'];
$bodyClass = 'home-page';
require ROOT . '/app/views/layout.php'; 
?>
