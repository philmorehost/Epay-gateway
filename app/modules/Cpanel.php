<?php
// app/modules/Cpanel.php

class Cpanel {
    private $whm_host;
    private $whm_user;
    private $whm_api_token;

    public function __construct() {
        global $db;

        // Fetch WHM settings from the database
        $settings_result = $db->query("SELECT * FROM settings WHERE setting LIKE 'whm_%'");
        $settings = [];
        while ($row = $settings_result->fetch_assoc()) {
            $settings[$row['setting']] = $row['value'];
        }

        $this->whm_host = rtrim($settings['whm_host'], '/');
        $this->whm_user = $settings['whm_user'];
        $this->whm_api_token = $settings['whm_api_token'];
    }

    private function api_query($function, $params = []) {
        $query = $this->whm_host . "/json-api/" . $function . "?api.version=1&" . http_build_query($params);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $header[0] = "Authorization: whm " . $this->whm_user . ":" . $this->whm_api_token;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $query);

        $result = curl_exec($curl);
        if ($result === false) {
            throw new Exception("cURL Error: " . curl_error($curl));
        }

        curl_close($curl);

        return json_decode($result, true);
    }

    public function list_packages() {
        return $this->api_query('listpkgs');
    }

    public function create_account($domain, $username, $password, $package) {
        $params = [
            'domain' => $domain,
            'username' => $username,
            'password' => $password,
            'plan' => $package,
        ];
        return $this->api_query('createacct', $params);
    }

    public function suspend_account($username, $reason) {
        return $this->api_query('suspendacct', ['user' => $username, 'reason' => $reason]);
    }

    public function unsuspend_account($username) {
        return $this->api_query('unsuspendacct', ['user' => $username]);
    }

    public function terminate_account($username) {
        return $this->api_query('removeacct', ['user' => $username, 'keepdns' => 0]);
    }
}
