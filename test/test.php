<?php

class CBridgeApi
{
    private string $g_sClientId;
    private string $g_sClientSecret;
    private string $g_sVersion;
    private string $g_sBaseUrl;
    private ?string $g_sUserUuid = null;
    private ?string $g_sAccessToken = null;
    private array $g_lAccounts = [];

    public function __construct(
        string $p_sClientId,
        string $p_sClientSecret,
        string $p_sVersion = '2025-01-15',
        string $p_sBaseUrl = 'https://api.bridgeapi.io/v3/aggregation'
    ) {
        $this->g_sClientId = $p_sClientId;
        $this->g_sClientSecret = $p_sClientSecret;
        $this->g_sVersion = $p_sVersion;
        $this->g_sBaseUrl = rtrim($p_sBaseUrl, '/');
    }

    private function f_lRequest(string $p_sMethod, string $p_sEndpoint, array $p_lData = [], bool $p_bUseAuth = false): array
    {
        $c_curl = curl_init($this->g_sBaseUrl . $p_sEndpoint);
        
        $l_lHeaders = [
            "Bridge-Version: {$this->g_sVersion}",
            "Client-Id: {$this->g_sClientId}",
            "Client-Secret: {$this->g_sClientSecret}",
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        if ($p_bUseAuth && $this->g_sAccessToken) {
            $l_lHeaders[] = "Authorization: Bearer {$this->g_sAccessToken}";
        }

        switch (strtoupper($p_sMethod)) {
            case 'POST':
                curl_setopt($c_curl, CURLOPT_POST, true);
                curl_setopt($c_curl, CURLOPT_POSTFIELDS, json_encode($p_lData));
                break;
            case 'PUT':
                curl_setopt($c_curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($c_curl, CURLOPT_POSTFIELDS, json_encode($p_lData));
                break;
            case 'DELETE':
                curl_setopt($c_curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
            default:
                break;
        }

        curl_setopt($c_curl, CURLOPT_HTTPHEADER, $l_lHeaders);
        curl_setopt($c_curl, CURLOPT_RETURNTRANSFER, true);

        $s_response = curl_exec($c_curl);
        $i_httpCode = curl_getinfo($c_curl, CURLINFO_HTTP_CODE);
        curl_close($c_curl);

        return [
            'http_code' => $i_httpCode,
            'response' => json_decode($s_response, true),
            'success' => $i_httpCode >= 200 && $i_httpCode < 300
        ];
    }

    public function F_lCreateUser(?string $p_sExternalUserId = null): array
    {
        $l_sExternalId = $p_sExternalUserId ?? "user_" . time();
        $l_lResult = $this->f_lRequest('POST', '/users', ['external_user_id' => $l_sExternalId]);
        
        if ($l_lResult['success'] && isset($l_lResult['response']['uuid'])) {
            $this->g_sUserUuid = $l_lResult['response']['uuid'];
        }
        
        return $l_lResult;
    }

    public function F_vSetUserUuid(string $p_sUserUuid): void
    {
        $this->g_sUserUuid = $p_sUserUuid;
    }

    public function F_sGetUserUuid(): ?string
    {
        return $this->g_sUserUuid;
    }

    public function F_lGenerateToken(bool $p_bUseExternalId = false): array
    {
        if (!$this->g_sUserUuid) {
            return ['success' => false, 'error' => 'No user UUID set'];
        }

        $l_sKey = $p_bUseExternalId ? 'external_user_id' : 'user_uuid';
        $l_lResult = $this->f_lRequest('POST', '/authorization/token', [$l_sKey => $this->g_sUserUuid]);
        
        if ($l_lResult['success'] && isset($l_lResult['response']['access_token'])) {
            $this->g_sAccessToken = $l_lResult['response']['access_token'];
        }
        
        return $l_lResult;
    }

    public function F_sGetAccessToken(): ?string
    {
        return $this->g_sAccessToken;
    }

    public function F_lGetAccounts(): array
    {
        $l_lResult = $this->f_lRequest('GET', '/accounts', [], true);
        
        if ($l_lResult['success'] && isset($l_lResult['response']['resources'])) {
            $this->g_lAccounts = $l_lResult['response']['resources'];
        }
        
        return $l_lResult;
    }

    public function F_lGetCachedAccounts(): array
    {
        return $this->g_lAccounts;
    }

    public function F_lCreateConnectSession(string $p_sUserEmail, array $p_lOptions = []): array
    {
        $l_lData = array_merge(['user_email' => $p_sUserEmail], $p_lOptions);
        return $this->f_lRequest('POST', '/connect-sessions', $l_lData, true);
    }

    public function F_lGetTransactions(int $p_iSinceTimestamp = null, array $p_lParams = []): array
    {
        $l_iTimestamp = $p_iSinceTimestamp ?? (strtotime('-30 days') * 1000);
        $l_sQuery = http_build_query(array_merge(['since' => $l_iTimestamp], $p_lParams));
        return $this->f_lRequest('GET', "/transactions?{$l_sQuery}", [], true);
    }

    public function F_lInitializeUser(?string $p_sUserUuid = null, ?string $p_sExternalUserId = null): array
    {
        if ($p_sUserUuid) {
            $this->F_vSetUserUuid($p_sUserUuid);
            $l_lTokenResult = $this->F_lGenerateToken(true);
        } else {
            $l_lUserResult = $this->F_lCreateUser($p_sExternalUserId);
            if (!$l_lUserResult['success']) {
                return $l_lUserResult;
            }
            $l_lTokenResult = $this->F_lGenerateToken();
        }

        if (!$l_lTokenResult['success']) {
            return $l_lTokenResult;
        }

        $l_lAccountsResult = $this->F_lGetAccounts();
        
        return [
            'success' => true,
            'user_uuid' => $this->g_sUserUuid,
            'access_token' => $this->g_sAccessToken,
            'accounts_count' => count($this->g_lAccounts),
            'accounts' => $this->g_lAccounts
        ];
    }

    public function F_vSaveToSession(): void
    {
        $_SESSION['bridge_user_uuid'] = $this->g_sUserUuid;
        $_SESSION['bridge_access_token'] = $this->g_sAccessToken;
        $_SESSION['bridge_accounts'] = $this->g_lAccounts;
    }

    public function F_bLoadFromSession(): bool
    {
        if (isset($_SESSION['bridge_user_uuid'], $_SESSION['bridge_access_token'])) {
            $this->g_sUserUuid = $_SESSION['bridge_user_uuid'];
            $this->g_sAccessToken = $_SESSION['bridge_access_token'];
            $this->g_lAccounts = $_SESSION['bridge_accounts'] ?? [];
            return true;
        }
        return false;
    }
}


session_start();

$c_bridge = new CBridgeApi(
    'sandbox_id_4688615bb0e7451fa4679d41c11650e9',
    'sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh'
);

$l_lResult = $c_bridge->F_lInitializeUser($_GET['user_uuid'] ?? null);

if ($l_lResult['success']) {
    $c_bridge->F_vSaveToSession();
    
    $l_lConnectSession = $c_bridge->F_lCreateConnectSession('test@test.com');
    $l_lTransactions = $c_bridge->F_lGetTransactions();
    
    echo "<h1>Utilisateur créé avec succès</h1>";
    echo "<p>UUID : " . htmlspecialchars($l_lResult['user_uuid']) . "</p>";
    echo "<p>Token : " . htmlspecialchars($l_lResult['access_token']) . "</p>";
    echo "<p>Comptes : " . $l_lResult['accounts_count'] . "</p>";
    echo "<p><a href='" . htmlspecialchars($l_lConnectSession['response']['url']) . "' target='_blank'>Ajouter un compte</a></p>";
    echo "<p>Transactions : " . count($l_lTransactions['response']['resources']) . "</p>";
} else {
    echo "<h1>Erreur</h1>";
}
