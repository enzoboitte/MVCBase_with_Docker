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
    case Sync        = 'sync';
    case View        = 'view';
    case Edit        = 'edit';
    case Delete      = 'delete';
    case Back        = 'back';
    case Import      = 'import';
    case Link        = 'link';
    case Save        = 'save';

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
HTML,
            self::Sync => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46A7.93 7.93 0 0 0 20 12c0-4.42-3.58-8-8-8zm0 14c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74A7.93 7.93 0 0 0 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/>
</svg>
HTML,
            self::View => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
</svg>
HTML,
            self::Edit => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
</svg>
HTML,
            self::Delete => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
</svg>
HTML,
            self::Back => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
</svg>
HTML,
            self::Import => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M5 20h14v-2H5v2zM19 9h-4V3H9v6H5l7 7 7-7z"/>
</svg>
HTML,
            self::Link => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/>
</svg>
HTML,
            self::Save => <<<HTML
<svg class="$l_sClassAttr" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="currentColor" d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
</svg>
HTML
        };
    }
}