<?php
// app/modules/Cpanel.php

require_once 'ApiModule.php';

class Cpanel implements ApiModule {
    private $hostname;
    private $api_token; // WHM API Token

    public function __construct($server) {
        $this->hostname = $server['hostname'];
        $this->api_token = $server['api_key_1'];
    }

    private function api_query($function, $params = []) {
        $url = "https://{$this->hostname}:2087/json-api/{$function}?" . http_build_query($params);
        return $this->curl_request($url);
    }

    public function uapi_query($cpanel_user, $module, $function, $params = []) {
        $url = "https://{$this->hostname}:2083/execute/{$module}/{$function}?" . http_build_query($params);
        return $this->curl_request($url, $cpanel_user);
    }

    /**
     * Executes a wp-cli command for a specific WordPress installation.
     */
    public function wp_cli($cpanel_user, $install_id, $command) {
        $params = [
            'install_id' => $install_id,
            'command' => base64_encode($command)
        ];
        return $this->uapi_query($cpanel_user, 'WpCli', 'execute_command', $params);
    }

    private function curl_request($url, $cpanel_user = null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $auth_header = $cpanel_user ? "cpanel {$cpanel_user}:{$this->api_token}" : "whm root:{$this->api_token}";
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: {$auth_header}"]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) return ['success' => false, 'message' => "CURL Error: " . $error];
        return json_decode($response, true);
    }

    public function createAccount($params) {
        if (empty($params['password'])) $params['password'] = bin2hex(random_bytes(12));
        $api_params = ['api.version' => 1, 'username' => $params['username'], 'domain' => $params['domain'], 'password' => $params['password'], 'plan' => $params['package'], 'contactemail' => $params['client_details']['email']];
        $result = $this->api_query('createacct', $api_params);
        if (isset($result['metadata']['result']) && $result['metadata']['result'] == 1) return ['success' => true, 'message' => 'cPanel account created successfully.'];
        return ['success' => false, 'message' => "cPanel account creation failed: " . ($result['metadata']['reason'] ?? 'Unknown error.')];
    }

    public function createLoginSession($cpanel_user) {
        $result = $this->api_query('create_user_session', ['api.version' => 1, 'user' => $cpanel_user, 'service' => 'cpaneld']);
        if (isset($result['data']['url'])) return ['success' => true, 'url' => $result['data']['url']];
        return ['success' => false, 'message' => 'Failed to create cPanel session.'];
    }

    public function suspendAccount($params) { return ['success' => true, 'message' => 'Suspend function called (not implemented yet).']; }
    public function unsuspendAccount($params) { return ['success' => true, 'message' => 'Unsuspend function called (not implemented yet).']; }
    public function terminateAccount($params) { return ['success' => true, 'message' => 'Terminate function called (not implemented yet).']; }
    public function changePackage($params) { return ['success' => true, 'message' => 'Change package function called (not implemented yet).']; }

    public function testConnection() {
        $result = $this->api_query('version');
        if (isset($result['data']['version'])) return ['success' => true, 'message' => 'Connection successful! WHM Version: ' . $result['data']['version']];
        $error_message = 'Connection failed. Check hostname and API token.';
        if(isset($result['message'])) $error_message .= ' Details: ' . $result['message'];
        return ['success' => false, 'message' => $error_message];
    }
}
