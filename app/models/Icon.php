<?php

enum EFinanceIcon: string
{
    case Dashboard = 'dashboard';
    case Wallet    = 'wallet';
    case Income    = 'income';
    case Expense   = 'expense';
    case Transfer  = 'transfer';
    case Recurring = 'recurring';
    case Savings   = 'savings';
    case Card      = 'card';
    case TrendUp   = 'trend_up';
    case TrendDown = 'trend_down';
    case Calendar  = 'calendar';
    case Filter    = 'filter';
    case Plus      = 'plus';
    case Search    = 'search';
    case Settings  = 'settings';
    case Alert     = 'alert';
    case Transaction = 'transaction';
    case Category    = 'category';
    case Logout      = 'logout';

    public function getHtmlSvg(string $l_sCssClass = ''): string
    {
        $l_sClassAttr = trim('icon ' . $l_sCssClass);

        return match ($this) {
            self::Dashboard => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M4 4h7v7H4V4zm9 0h7v5h-7V4zM4 13h7v7H4v-7zm9-4h7v11h-7V9z"/>
</svg>
HTML,
            self::Wallet => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M4 6h14a2 2 0 0 1 2 2v1h-3.5a3.5 3.5 0 0 0 0 7H20v1a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2zm12.5 6H20v3h-3.5a1.5 1.5 0 0 1 0-3z"/>
</svg>
HTML,
            self::Income => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M12 3l4 4h-3v7h-2V7H8l4-4zm-7 9v9h14v-9h-2v7H7v-7H5z"/>
</svg>
HTML,
            self::Expense => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M12 21l-4-4h3V10h2v7h3l-4 4zM5 4h14v9h-2V6H7v7H5V4z"/>
</svg>
HTML,
            self::Transfer => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M7 7h10.17l-2.58-2.59L16 3l5 5-5 5-1.41-1.41L17.17 9H7V7zm10 10H6.83l2.58 2.59L8 21l-5-5 5-5 1.41 1.41L6.83 15H17v2z"/>
</svg>
HTML,
            self::Recurring => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M12 6V3L8 7l4 4V8a4 4 0 1 1-4 4H6a6 6 0 1 0 6-6z"/>
</svg>
HTML,
            self::Savings => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M5 7a7 7 0 0 1 14 0h-2a5 5 0 0 0-10 0H5zm-1 3h16l1 4v6H3v-6l1-4zm2.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>
</svg>
HTML,
            self::Card => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M3 5h18a2 2 0 0 1 2 2v2H1V7a2 2 0 0 1 2-2zm-2 7h22v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-5zm4 3v2h4v-2H5z"/>
</svg>
HTML,
            self::TrendUp => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M5 17l5-5 3 3 6-7 1.5 1.3L13 17l-3-3-5 5-2-2z"/>
</svg>
HTML,
            self::TrendDown => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M5 7l5 5 3-3 6 7 1.5-1.3L13 7l-3 3-5-5-2 2z"/>
</svg>
HTML,
            self::Calendar => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M7 3h2v2h6V3h2v2h3v16H4V5h3V3zm12 6H5v10h14V9z"/>
</svg>
HTML,
            self::Filter => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M4 5h16v2l-6 6v5l-4 2v-7L4 7V5z"/>
</svg>
HTML,
            self::Plus => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5z"/>
</svg>
HTML,
            self::Search => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M10 4a6 6 0 1 1 0 12 6 6 0 0 1 0-12zm8.71 9.29L22 16.59 20.59 18l-3.29-3.29A8 8 0 1 1 10 2a8 8 0 0 1 8.71 11.29z"/>
</svg>
HTML,
            self::Settings => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8zm9 4a7 7 0 0 0-.1-1.2l2-1.55-1.9-3.29-2.34.76A7.1 7.1 0 0 0 16.7 5l-.37-2.4h-4.66L11.3 5a7.1 7.1 0 0 0-2.96 1.72L6 6l-1.9 3.29 2 1.55A7 7 0 0 0 6 12c0 .41.04.81.1 1.2l-2 1.55L6 18.04l2.34-.76A7.1 7.1 0 0 0 11.3 19l.37 2.4h4.66l.37-2.4a7.1 7.1 0 0 0 2.96-1.72L21 14.75l2-1.55-1.9-3.29-2 .69c.07.4.1.8.1 1.2z"/>
</svg>
HTML,
            self::Alert => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
</svg>
HTML,
            self::Transaction => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M4 6h16v2H4V6zm0 5h16v2H4v-2zm0 5h16v2H4v-2z"/>
</svg>
HTML,
            self::Category => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M4 4h7v7H4V4zm9 0h7v5h-7V4zM4 13h7v7H4v-7zm9-4h7v11h-7V9z"/>
</svg>
HTML,
            self::Logout => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M16 13h-5v-2h5V8l4 4-4 4zm-6 7H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h6v2H4v12h6v2z"/>
</svg>
HTML
        };
    }
}