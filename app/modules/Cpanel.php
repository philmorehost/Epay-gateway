<?php
// app/modules/Cpanel.php

require_once __DIR__ . '/ApiModule.php';

/**
 * Class Cpanel
 * Implements the ApiModule interface for WHM/cPanel servers.
 */
class Cpanel implements ApiModule {
    private string $host;
    private string $username;
    private string $apiToken;

    /**
     * Cpanel constructor.
     *
     * @param string $host The server hostname or IP address.
     * @param string $username The WHM username.
     * @param string $apiToken The WHM API token.
     */
    public function __construct(string $host, string $username, string $apiToken) {
        $this->host = rtrim($host, '/');
        $this->username = $username;
        $this->apiToken = $apiToken;
    }

    /**
     * Makes a request to the WHM API.
     *
     * @param string $function The WHM API function to call.
     * @param array $params The parameters for the API call.
     * @return array The decoded JSON response from the API.
     */
    private function apiRequest(string $function, array $params = []): array {
        $url = "https://{$this->host}:2087/json-api/{$function}?" . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: whm {$this->username}:{$this->apiToken}",
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => "cURL Error: " . $error];
        }

        $decodedResponse = json_decode($response, true);

        // Basic WHM API error checking
        if (isset($decodedResponse['metadata']['result']) && $decodedResponse['metadata']['result'] === 0) {
            return ['success' => false, 'message' => $decodedResponse['metadata']['reason']];
        }

        return ['success' => true, 'data' => $decodedResponse];
    }

    public function createAccount(array $params): array {
        // Required params for createacct: username, domain, password, plan
        return $this->apiRequest('createacct', $params);
    }

    public function suspendAccount(array $params): array {
        // Required param for suspendacct: user
        return $this->apiRequest('suspendacct', $params);
    }

    public function unsuspendAccount(array $params): array {
        // Required param for unsuspendacct: user
        return $this->apiRequest('unsuspendacct', $params);
    }

    public function terminateAccount(array $params): array {
        // Required param for removeacct: user
        return $this->apiRequest('removeacct', $params);
    }

    public function modifyPackage(array $params): array {
        // Required params for changepackage: user, pkg
        return $this->apiRequest('changepackage', $params);
    }

    /**
     * Installs WordPress on a cPanel account.
     * Assumes WP Toolkit is available.
     *
     * @param array $params An associative array of parameters (e.g., cpanel_user, domain).
     * @return array The result of the operation.
     */
    public function installWordPress(array $params): array {
        $cpanel_user = $params['cpanel_user'];
        $domain = $params['domain'];
        // In a real system, admin user/pass should be securely generated and stored/emailed.
        $wp_params = [
            'domain' => $domain,
            'props' => json_encode([
                'admin_user' => 'admin',
                'admin_pass' => $params['admin_pass'],
                'admin_email' => $params['admin_email'],
                'title' => 'My WordPress Site',
                'language' => 'en',
            ]),
        ];
        return $this->cpanelApiRequest($cpanel_user, 'WpToolkit', 'install', $wp_params);
    }

    /**
     * Lists WordPress installations for a cPanel account.
     *
     * @param string $cpanel_user The cPanel username.
     * @return array The list of installations or an error.
     */
    public function getWordPressInstallations(string $cpanel_user): array {
        return $this->cpanelApiRequest($cpanel_user, 'WpToolkit', 'get_installations');
    }

    /**
     * Gets a one-time login URL for a WordPress installation.
     *
     * @param string $cpanel_user The cPanel username.
     * @param int $install_id The ID of the WordPress installation.
     * @return array The login URL or an error.
     */
    public function getWordPressLoginUrl(string $cpanel_user, int $install_id): array {
        return $this->cpanelApiRequest($cpanel_user, 'WpToolkit', 'get_login_url', ['id' => $install_id]);
    }

    /**
     * Makes a request to the cPanel UAPI.
     *
     * @param string $cpanel_user The cPanel user for the context of the call.
     * @param string $module The cPanel API module.
     * @param string $function The cPanel API function.
     * @param array $params The parameters for the API call.
     * @return array The decoded JSON response.
     */
    private function cpanelApiRequest(string $cpanel_user, string $module, string $function, array $params = []): array {
        $query = http_build_query(array_merge(['cpanel_jsonapi_user' => $cpanel_user, 'cpanel_jsonapi_module' => $module, 'cpanel_jsonapi_func' => $function], $params));
        $url = "https://{$this->host}:2087/json-api/cpanel?{$query}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: whm {$this->username}:{$this->apiToken}",
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => "cURL Error: " . $error];
        }

        $decodedResponse = json_decode($response, true);

        if (isset($decodedResponse['cpanelresult']['error'])) {
            return ['success' => false, 'message' => $decodedResponse['cpanelresult']['error']];
        }

        return ['success' => true, 'data' => $decodedResponse['cpanelresult']['data']];
    }
}
