<?php
// app/modules/ConnectReseller.php

require_once 'ApiModule.php';

class ConnectReseller implements ApiModule {
    private $api_key;

    // The constructor for this module is simpler as it doesn't need server-specific details.
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }

    private function api_query($endpoint, $params = []) {
        $params['APIKey'] = $this->api_key;
        $url = "https://api.connectreseller.com/ConnectReseller/ESHOP/{$endpoint}?" . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Registers a domain name.
     * Note: This module's 'createAccount' is repurposed for domain registration.
     */
    public function createAccount($params) {
        // The API requires a customer ID. For this implementation, we will assume a default
        // customer is created in the registrar account. This could be enhanced later to manage customers via API.
        $customer_id = $params['customer_id'] ?? 1; // Defaulting to 1 for simplicity.

        $api_params = [
            'ProductType' => 1,
            'Websitename' => $params['domain'],
            'Duration' => 1, // Register for 1 year
            'IsWhoisProtection' => false,
            'ns1' => $params['ns1'] ?? 'ns1.default.com',
            'ns2' => $params['ns2'] ?? 'ns2.default.com',
            'Id' => $customer_id,
            'isEnablePremium' => 0
        ];

        $result = $this->api_query('domainorder', $api_params);

        if (isset($result['responseMsg']['statusCode']) && $result['responseMsg']['statusCode'] == 200) {
            return ['success' => true, 'message' => 'Domain registered successfully.'];
        } else {
            $reason = $result['responseMsg']['message'] ?? 'Unknown error.';
            return ['success' => false, 'message' => "Domain registration failed: " . $reason];
        }
    }

    // Other ApiModule methods are not applicable to this module type.
    public function suspendAccount($params) { return ['success' => true, 'message' => 'Not applicable for this module.']; }
    public function unsuspendAccount($params) { return ['success' => true, 'message' => 'Not applicable for this module.']; }
    public function terminateAccount($params) { return ['success' => true, 'message' => 'Not applicable for this module.']; }
    public function changePackage($params) { return ['success' => true, 'message' => 'Not applicable for this module.']; }
    public function testConnection() {
        // A good test would be to check available funds.
        $result = $this->api_query('availablefund');
        if (isset($result['responseData'])) {
            return ['success' => true, 'message' => 'Connection successful. Available funds: ' . $result['responseData']];
        }
        return ['success' => false, 'message' => 'Connection failed. Check API key.'];
    }
}
