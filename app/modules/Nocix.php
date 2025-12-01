<?php
// app/modules/Nocix.php

class Nocix {
    private $api_key;
    private $api_endpoint = 'https://manage.nocix.net/api/v1/';

    public function __construct() {
        global $db;

        $settings_result = $db->query("SELECT value FROM settings WHERE setting = 'nocix_api_key'");
        $this->api_key = $settings_result->fetch_assoc()['value'] ?? '';
    }

    private function api_query($action, $params = [], $method = 'GET') {
        $params['key'] = $this->api_key;
        $url = $this->api_endpoint . $action . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function provision_server($identifier) {
        $params = [
            'plan' => $identifier,
        ];
        return $this->api_query('server', $params, 'POST');
    }

    // Server management methods will be added here
}
