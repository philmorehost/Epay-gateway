<?php
// app/modules/ConnectReseller.php

class ConnectReseller {
    private $api_key;
    private $reseller_id;
    private $api_endpoint = 'https://api.connectreseller.com/api/';

    public function __construct() {
        global $db;

        $settings_result = $db->query("SELECT * FROM settings WHERE setting LIKE 'connectreseller_%'");
        $settings = [];
        while ($row = $settings_result->fetch_assoc()) {
            $settings[$row['setting']] = $row['value'];
        }

        $this->api_key = $settings['connectreseller_api_key'];
        $this->reseller_id = $settings['connectreseller_reseller_id'];
    }

    private function api_query($action, $params = []) {
        $params['api-key'] = $this->api_key;
        $params['reseller-id'] = $this->reseller_id;

        $url = $this->api_endpoint . $action . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function test_connection() {
        return $this->api_query('domains/get-tlds-pricing.json');
    }

    public function check_availability($domain) {
        $parts = explode('.', $domain, 2);
        $params = [
            'domain-name' => $parts[0],
            'tlds' => [$parts[1]],
        ];
        $response = $this->api_query('domains/check.json', $params);

        if (isset($response[$domain]['status'])) {
            return $response[$domain]['status']; // e.g., 'available', 'regthroughothers'
        }
        return 'unknown';
    }

    public function register_domain($domain, $customer_details) {
        $params = [
            'order-details' => [
                'domain-name' => $domain,
                'years' => '1',
                'contact' => [
                    'registrant' => [
                        'name' => $customer_details['name'],
                        'email' => $customer_details['email'],
                        'company' => $customer_details['company'] ?? 'N/A',
                        'address-line-1' => $customer_details['address'] ?? 'N/A',
                        'city' => $customer_details['city'] ?? 'N/A',
                        'state' => $customer_details['state'] ?? 'N/A',
                        'zipcode' => $customer_details['zipcode'] ?? 'N/A',
                        'country' => $customer_details['country'] ?? 'US',
                        'phone-cc' => $customer_details['phone_cc'] ?? '1',
                        'phone' => $customer_details['phone'] ?? '1234567890'
                    ],
                ]
            ]
        ];

        // The API requires the parameters to be JSON encoded in a POST request
        $url = $this->api_endpoint . 'domains/register.json';
        $params['api-key'] = $this->api_key;
        $params['reseller-id'] = $this->reseller_id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
