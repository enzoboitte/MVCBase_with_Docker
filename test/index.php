<?php
/*
    Si $_GET['user_uuid'] est défini, alors on accède directement à l'utilisateur existant.
    Sinon, on crée un nouvel utilisateur Bridge.
*/

function fetchCurl($l_sMethod, $s_url, $l_headers, $l_data = []) {
    $c_curl = curl_init($s_url);

    switch (strtoupper($l_sMethod)) {
        case 'POST':
            curl_setopt($c_curl, CURLOPT_POST, true);
            break;
        case 'PUT':
            curl_setopt($c_curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            break;
        case 'DELETE':
            curl_setopt($c_curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        case 'GET':
        default:
            // GET is default
            break;
    }

    curl_setopt($c_curl, CURLOPT_HTTPGET, true);
    if(!empty($l_data))
        curl_setopt($c_curl, CURLOPT_POSTFIELDS, json_encode($l_data));
    curl_setopt($c_curl, CURLOPT_HTTPHEADER, $l_headers);
    curl_setopt($c_curl, CURLOPT_RETURNTRANSFER, true);

    $s_response = curl_exec($c_curl);
    $i_httpCode = curl_getinfo($c_curl, CURLINFO_HTTP_CODE);
    curl_close($c_curl);

    return ['http_code' => $i_httpCode, 'response' => json_decode($s_response, true)];
}

$s_url = 'https://api.bridgeapi.io/v3/aggregation/users';
$l_headers = [
    'Bridge-Version: 2025-01-15',
    'Client-Id: sandbox_id_4688615bb0e7451fa4679d41c11650e9',
    'Client-Secret: sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh',
    'Accept: application/json',
    'Content-Type: application/json'
];
$l_data = [
    'external_user_id' => "user_" . time()
];

$l_result = null;
if(!isset($_GET['user_uuid']))
{
    $l_curlResult = fetchCurl("POST", $s_url, $l_headers, $l_data);
    $i_httpCode = $l_curlResult['http_code'];
    $s_response = json_encode($l_curlResult['response']);
    $l_result = $l_curlResult['response'];
}

if (($l_result && isset($l_result['uuid'])) || isset($_GET['user_uuid'])) 
{
    $s_userUuid = $l_result['uuid'] ?? $_GET['user_uuid'];
    
    /*
    curl --request POST \
     --url https://api.bridgeapi.io/v3/aggregation/authorization/token \
     --header 'Bridge-Version: BRIDGE_VERSION' \
     --header 'accept: application/json' \
     --header 'content-type: application/json' \
     --data '
{
  "user_uuid": "$s_userUuid"
}
'
    */
    $l_lHeadersToken = [
        'Bridge-Version: 2025-01-15',
        'Client-Id: sandbox_id_4688615bb0e7451fa4679d41c11650e9',
        'Client-Secret: sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh',
        'Accept: application/json',
        'Content-Type: application/json'
    ];
    $l_lDataToken = [
        isset($_GET['user_uuid']) ? 'external_user_id' : 'user_uuid' => $s_userUuid
    ];
    $s_urlToken = 'https://api.bridgeapi.io/v3/aggregation/authorization/token';
    $l_curlResultToken = fetchCurl("POST", $s_urlToken, $l_lHeadersToken, $l_lDataToken);
    $i_httpCodeToken = $l_curlResultToken['http_code'];
    $s_responseToken = json_encode($l_curlResultToken['response']);
    $l_resultToken = $l_curlResultToken['response'];
    
    $_SESSION['user_uuid'] = $s_userUuid;
    $_SESSION['access_token'] = $l_resultToken['access_token'];

    /*
    curl --request GET \
     --url https://api.bridgeapi.io/v3/aggregation/accounts \
     --header 'Bridge-Version: 2025-01-15' \
     --header 'Client-Id: sandbox_id_4688615bb0e7451fa4679d41c11650e9' \
     --header 'Client-Secret: sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh' \
     --header 'accept: application/json'
    */

     $l_lHeadersAccounts = [
        'Bridge-Version: 2025-01-15',
        'Client-Id: sandbox_id_4688615bb0e7451fa4679d41c11650e9',
        'Client-Secret: sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh',
        'Accept: application/json',
        'Authorization: Bearer ' . $_SESSION['access_token']
    ];
    $s_urlAccounts = 'https://api.bridgeapi.io/v3/aggregation/accounts';
    $l_curlResultAccounts = fetchCurl("GET", $s_urlAccounts, $l_lHeadersAccounts);
    $i_httpCodeAccounts = $l_curlResultAccounts['http_code'];
    $s_responseAccounts = json_encode($l_curlResultAccounts['response']);
    $l_resultAccounts = $l_curlResultAccounts['response'];
    
    $_SESSION['accounts'] = $l_resultAccounts['resources'];


    /* add account if never exists 
    
    curl --request POST \
     --url https://api.bridgeapi.io/v3/aggregation/connect-sessions \
     --header 'Bridge-Version: BRIDGE-VERSION' \
     --header 'accept: application/json' \
     --header 'content-type: application/json' \
     --header 'Authorization: Bearer TOP_SECRET_ACCESS_TOKEN' \
     --data '
		{
  		"user_email": "john.doe@acme.com"
		}
		'
    */

    $l_lHeadersConnectSession = [
        'Bridge-Version: 2025-01-15',
        'Client-Id: sandbox_id_4688615bb0e7451fa4679d41c11650e9',
        'Client-Secret: sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh',
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['access_token']
    ];
    $l_lDataConnectSession = [
        'user_email' => 'test@test.com'
    ];
    $s_urlConnectSession = 'https://api.bridgeapi.io/v3/aggregation/connect-sessions';
    $l_curlResultConnectSession = fetchCurl("POST", $s_urlConnectSession, $l_lHeadersConnectSession, $l_lDataConnectSession);
    $i_httpCodeConnectSession = $l_curlResultConnectSession['http_code'];
    $s_responseConnectSession = json_encode($l_curlResultConnectSession['response']);
    $l_resultConnectSession = $l_curlResultConnectSession['response'];

    // open page _BLANK with URL:
    // $l_resultConnectSession['url']
    echo "<h1>Utilisateur créé avec succès</h1>";
    echo "<p>UUID de l'utilisateur : " . htmlspecialchars($s_userUuid) . "</p>";
    echo "<p>Token d'accès : " . htmlspecialchars($l_resultToken['access_token']) . "</p>";
    echo "<p>Comptes liés : " . htmlspecialchars(count($_SESSION['accounts'])) . "</p>";
    echo "<p>Session de connexion créée. Ouvre cette URL dans une nouvelle fenêtre pour ajouter un compte : <a href='" . htmlspecialchars($l_resultConnectSession['url']) . "' target='_blank'>" . htmlspecialchars($l_resultConnectSession['url']) . "</a></p>";

    /*
    
    curl --request GET \
     --url 'https://api.bridgeapi.io/v3/aggregation/transactions?since=${timestamp}' \
     --header 'Bridge-Version: 2025-01-15' \
     --header 'Client-Id: sandbox_id_4688615bb0e7451fa4679d41c11650e9' \
     --header 'Client-Secret: sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh' \
     --header 'accept: application/json' \
     --header 'authorization: Bearer fdsgsdffsd'
    */

    $l_lHeadersTransactions = [
        'Bridge-Version: 2025-01-15',
        'Client-Id: sandbox_id_4688615bb0e7451fa4679d41c11650e9',
        'Client-Secret: sandbox_secret_OptrCj0rXy6QkUq4iXZFNdTViyxLk3CDwgpEUmWI9Umf28W8AjZOIHa5BAYOVnkh',
        'Accept: application/json',
        'Authorization: Bearer ' . $_SESSION['access_token']
    ];
    $s_sinceTimestamp = strtotime('-30 days') * 1000; // timestamp in milliseconds
    $s_urlTransactions = 'https://api.bridgeapi.io/v3/aggregation/transactions?since=' . $s_sinceTimestamp;
    $l_curlResultTransactions = fetchCurl("GET", $s_urlTransactions, $l_lHeadersTransactions);
    $i_httpCodeTransactions = $l_curlResultTransactions['http_code'];
    $s_responseTransactions = json_encode($l_curlResultTransactions['response']);
    $l_resultTransactions = $l_curlResultTransactions['response'];

    echo "<p>Transactions récupérées au cours des 30 derniers jours : " . htmlspecialchars(count($l_resultTransactions['resources'])) . "</p>";
    
} else {
    echo "<h1>Erreur lors de la création de l'utilisateur</h1>";
    echo "<p>Code HTTP : " . htmlspecialchars($i_httpCode) . "</p>";
    echo "<p>Réponse : " . htmlspecialchars($s_response) . "</p>";
}
