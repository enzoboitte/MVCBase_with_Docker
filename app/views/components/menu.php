<? include_once __DIR__ . '/../../models/Icon.php'; ?>
<aside class="app-sidebar">
    <div class="card nohover flex-1" style="padding:1rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
            <?= EFinanceIcon::Dashboard->getHtmlSvg('icon-lg') ?>
            <a href="/" style="font-size:1.1rem;font-weight:700;margin:0;text-decoration:none;color:inherit;">Navigation</a>
        </div>
        <nav class="flex-1" style="display:flex;flex-direction:column;gap:0.5rem;">
            <a href="/accounts" class="btn btn-ghost" style="justify-content:flex-start;border-radius:8px;">
                <?= EFinanceIcon::Wallet->getHtmlSvg() ?> <span>Mes comptes</span>
            </a>
            <a href="/transactions" class="btn btn-ghost" style="justify-content:flex-start;border-radius:8px;">
                <?= EFinanceIcon::Transaction->getHtmlSvg() ?> <span>Transactions</span>
            </a>
            <a href="/subscriptions" class="btn btn-ghost" style="justify-content:flex-start;border-radius:8px;">
                <?= EFinanceIcon::Recurring->getHtmlSvg() ?> <span>Abonnements</span>
            </a>


            <a href="/transactions/add" class="btn btn-primary" style="justify-content:flex-start;border-radius:8px;font-size:0.7rem;">
                <?= EFinanceIcon::Plus->getHtmlSvg() ?> <span>Ajouter une transaction</span>
            </a>
        </nav>
        <nav style="display:flex;flex-direction:column;gap:0.5rem;">
            <a href="/settings" class="btn btn-ghost" style="justify-content:flex-start;border-radius:8px;">
                <?= EFinanceIcon::Settings->getHtmlSvg() ?> <span>Paramètres</span>
            </a>
            <a href="/logout" class="btn btn-ghost" style="justify-content:flex-start;border-radius:8px;">
                <?= EFinanceIcon::Logout->getHtmlSvg() ?> <span>Déconnexion</span>
            </a>
        </nav>
    </div>
</aside>