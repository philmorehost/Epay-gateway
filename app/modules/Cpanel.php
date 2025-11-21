<?php
// app/modules/Cpanel.php

require_once 'ApiModule.php';

class Cpanel implements ApiModule {
    private $hostname;
    private $api_token; // We will use API tokens for authentication

    public function __construct($server) {
        $this->hostname = $server['hostname'];
        // WHM API Token (api_key_1 is used for this)
        $this->api_token = $server['api_key_1'];
    }

    /**
     * Makes a query to the WHM API.
     */
    private function api_query($function, $params = []) {
        $url = "https://{$this->hostname}:2087/json-api/{$function}?" . http_build_query($params);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: whm root:{$this->api_token}"
        ]);
        // Enforce SSL verification for security.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            // In a production environment, you would log this error.
            // For now, we'll return it in the message for debugging.
            return ['success' => false, 'message' => "CURL Error: " . $error];
        }

        return json_decode($response, true);
    }

    public function createAccount($params) {
        // Generate a secure random password if one isn't provided
        if (empty($params['password'])) {
            $params['password'] = bin2hex(random_bytes(12));
        }

        $api_params = [
            'api.version' => 1,
            'username' => $params['username'],
            'domain' => $params['domain'],
            'password' => $params['password'],
            'plan' => $params['package'],
            'contactemail' => $params['client_details']['email'],
        ];

        $result = $this->api_query('createacct', $api_params);

        if (isset($result['metadata']['result']) && $result['metadata']['result'] == 1) {
            return ['success' => true, 'message' => 'cPanel account created successfully.'];
        } else {
            $reason = $result['metadata']['reason'] ?? 'Unknown error.';
            return ['success' => false, 'message' => "cPanel account creation failed: " . $reason];
        }
    }

    public function suspendAccount($params) {
        // Implementation for suspending an account
        return ['success' => true, 'message' => 'Suspend function called (not implemented yet).'];
    }

    public function unsuspendAccount($params) {
        // Implementation for unsuspending an account
        return ['success' => true, 'message' => 'Unsuspend function called (not implemented yet).'];
    }

    public function terminateAccount($params) {
        // Implementation for terminating an account
        return ['success' => true, 'message' => 'Terminate function called (not implemented yet).'];
    }

    public function changePackage($params) {
        // Implementation for changing a package
        return ['success' => true, 'message' => 'Change package function called (not implemented yet).'];
    }

    public function testConnection() {
        $result = $this->api_query('version');
        if (isset($result['data']['version'])) {
             return ['success' => true, 'message' => 'Connection successful! WHM Version: ' . $result['data']['version']];
        }
        $error_message = 'Connection failed. Check hostname and API token.';
        if(isset($result['message'])) {
            $error_message .= ' Details: ' . $result['message'];
        }
        return ['success' => false, 'message' => $error_message];
    }
}
