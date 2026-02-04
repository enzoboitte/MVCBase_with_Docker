<?php ob_start();

$l_sIconHtml = EFinanceIcon::Income->getHtmlSvg('list-icon-svg');

class C_BankAccount {
    public function __construct(
        public int $i_iId,
        public string $s_sName,
        public string $s_sType,
        public float $f_fBalance,
        public string $s_sColor
    ) {}
}

class C_Transaction {
    public function __construct(
        public string $s_sLabel,
        public string $s_sCategory,
        public float $f_fAmount,
        public string $s_sDate,
        public string $s_sType
    ) {}
}

$G_l_cAccounts = [
    new C_BankAccount(1, 'Compte Courant', 'Courant', 2847.32, '#2563eb'),
    new C_BankAccount(2, 'Livret A', 'Épargne', 15420.00, '#16a34a'),
    new C_BankAccount(3, 'PEL', 'Épargne', 8250.50, '#d97706'),
    new C_BankAccount(4, 'Compte Entreprise', 'Professionnel', 4128.90, '#8b5cf6')
];

$G_l_cTransactions = [
    new C_Transaction('Netflix', 'Abonnements', -15.99, '01/02/2026', 'expense'),
    new C_Transaction('Spotify', 'Abonnements', -9.99, '01/02/2026', 'expense'),
    new C_Transaction('OVH Cloud', 'Abonnements', -24.99, '02/02/2026', 'expense'),
    new C_Transaction('Adobe CC', 'Abonnements', -59.99, '03/02/2026', 'expense'),
    new C_Transaction('Loyer', 'Logement', -850.00, '01/02/2026', 'expense'),
    new C_Transaction('Courses Carrefour', 'Alimentaire', -127.45, '02/02/2026', 'expense'),
    new C_Transaction('Essence Total', 'Transport', -68.50, '03/02/2026', 'expense'),
    new C_Transaction('Restaurant', 'Loisirs', -42.80, '03/02/2026', 'expense'),
    new C_Transaction('Salaire', 'Revenus', 3200.00, '31/01/2026', 'income'),
    new C_Transaction('Freelance Client A', 'Revenus', 1500.00, '28/01/2026', 'income')
];

$G_l_fCategories = [
    'Abonnements' => 110.96,
    'Logement' => 850.00,
    'Alimentaire' => 127.45,
    'Transport' => 68.50,
    'Loisirs' => 42.80,
    'Santé' => 85.20,
    'Shopping' => 234.60
];

$l_fTotalBalance = array_sum(array_column($G_l_cAccounts, 'f_fBalance'));
$l_fTotalIncome = array_sum(array_map(fn($c_cTx) => $c_cTx->s_sType === 'income' ? $c_cTx->f_fAmount : 0, $G_l_cTransactions));
$l_fTotalExpense = abs(array_sum(array_map(fn($c_cTx) => $c_cTx->s_sType === 'expense' ? $c_cTx->f_fAmount : 0, $G_l_cTransactions)));
$l_fMonthlySubscriptions = array_sum(array_filter($G_l_fCategories, fn($l_sKey) => $l_sKey === 'Abonnements', ARRAY_FILTER_USE_KEY));
$l_fProjectedEndMonth = $l_fTotalBalance + ($l_fTotalIncome - $l_fTotalExpense);
$l_fProjectedDelta = $l_fTotalIncome - $l_fTotalExpense;

function F_sFormatCurrency(float $l_fAmount): string {
    return number_format($l_fAmount, 2, ',', ' ') . ' €';
}
?>

<div class="app-shell">
    <?php include ROOT . '/app/views/components/menu.php'; ?>

    <main class="app-main">
        
        <section class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::Wallet->getHtmlSvg() ?> Solde total</div>
                        <span class="badge badge-success">Actif</span>
                    </div>
                    <div class="card-value"><?= F_sFormatCurrency($l_fTotalBalance) ?></div>
                    <div class="card-footer text-muted">Mise à jour : 13:20</div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?= EFinanceIcon::Calendar->getHtmlSvg() ?> Prévision</div>
                    </div>
                    <div class="card-value"><?= F_sFormatCurrency($l_fProjectedEndMonth) ?></div>
                    <div class="card-footer text-success">+<?= F_sFormatCurrency($l_fProjectedDelta) ?></div>
                </div>
            </div>
        </section>

        <section class="row center">
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Revenus</div></div>
                    <div class="card-value text-success"><?= F_sFormatCurrency($l_fTotalIncome) ?></div>
                </div>
            </div>
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Dépenses</div></div>
                    <div class="card-value text-danger">- <?= F_sFormatCurrency($l_fTotalExpense) ?></div>
                </div>
            </div>
            <div class="col-4 flex-1">
                <div class="card nohover">
                    <div class="card-header"><div class="card-title">Abonnements</div></div>
                    <div class="card-value"><?= F_sFormatCurrency($l_fMonthlySubscriptions) ?></div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div class="card-title"><?= EFinanceIcon::Card->getHtmlSvg() ?> Mes comptes</div>
            </div>
            <div class="card-body" style="overflow-x: auto; overflow-y: hidden;">
                <div class="accounts-scroll center">
                    <?php foreach($G_l_cAccounts as $acc): ?>
                    <div class="account-card-item">
                        <div class="card nohover" style="background:<?= $acc->s_sColor ?>15;">
                            <b><?= $acc->s_sName ?></b><br>
                            <?= F_sFormatCurrency($acc->f_fBalance) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="card flex-1">
            <div class="card-header">
                <div class="card-title">
                    <?= EFinanceIcon::Filter->getHtmlSvg() ?>
                    Dépenses par catégorie
                </div>
                <button class="btn btn-ghost btn-sm">Février 2026</button>
            </div>

            <div class="card-body">
                <?php 
                $l_fTotalCategories = array_sum($G_l_fCategories);
                arsort($G_l_fCategories);
                ?>
                <div style="display:flex;flex-direction:column;gap:1rem;">
                    <?php 
                    // Je double la boucle juste pour te prouver que ça scrolle ;)
                    $demodata = array_merge($G_l_fCategories, $G_l_fCategories, $G_l_fCategories); 
                    foreach($demodata as $l_sCategory => $l_fAmount): 
                        $l_fPercent = ($l_fAmount / $l_fTotalCategories) * 100;
                    ?>
                    <div style="display:flex;flex-direction:column;gap:0.4rem;">
                        <div style="display:flex;justify-content:space-between;">
                            <span><?= htmlspecialchars($l_sCategory) ?></span>
                            <b><?= F_sFormatCurrency($l_fAmount) ?></b>
                        </div>
                        <div style="height:6px;background:var(--bg-secondary);border-radius:9px;">
                            <div style="height:100%;background:var(--primary-color);width:<?= $l_fPercent ?>%;border-radius:9px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card-footer">
                <div style="display:flex;justify-content:space-between;">
                    <span class="text-muted">Total</span>
                    <span style="font-weight:700;"><?= F_sFormatCurrency($l_fTotalCategories) ?></span>
                </div>
            </div>
        </section>

    </main>
</div>
<?php $content = ob_get_clean();

$customCss = ['/public/src/css/framework.css'];
$bodyClass = 'home-page'; // Assure-toi que ton layout.php ajoute cette classe au body
require ROOT . '/app/views/layout.php'; 
?>