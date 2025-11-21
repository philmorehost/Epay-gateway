<?php
// app/modules/ApiModule.php

/**
 * Interface ApiModule
 *
 * Defines the standard structure for all service provisioning modules.
 * Each module (e.g., Cpanel, Nocix, Time4VPS) must implement this interface
 * to ensure consistent integration with the billing system.
 */
interface ApiModule {

    /**
     * ApiModule constructor.
     *
     * @param array $server An associative array containing server details
     *                      (e.g., hostname, api_key_1, api_key_2).
     */
    public function __construct($server);

    /**
     * Creates a new service account.
     *
     * This function is called automatically when an order's invoice is paid.
     *
     * @param array $params An associative array of parameters needed for account creation,
     *                      such as 'domain', 'username', 'password', 'package', 'client_details'.
     * @return array An associative array with 'success' (bool) and 'message' (string).
     */
    public function createAccount($params);

    /**
     * Suspends a service account.
     *
     * This function is called when an account is overdue.
     *
     * @param array $params An associative array identifying the account to suspend,
     *                      e.g., 'username' or 'service_id'.
     * @return array An associative array with 'success' (bool) and 'message' (string).
     */
    public function suspendAccount($params);

    /**
     * Unsuspends a service account.
     *
     * This function is called when a late payment is made.
     *
     * @param array $params An associative array identifying the account to unsuspend.
     * @return array An associative array with 'success' (bool) and 'message' (string).
     */
    public function unsuspendAccount($params);

    /**
     * Terminates a service account.
     *
     * This function is called after an account has been suspended for a configurable period.
     *
     * @param array $params An associative array identifying the account to terminate.
     * @return array An associative array with 'success' (bool) and 'message' (string).
     */
    public function terminateAccount($params);

    /**
     * Changes the package/plan for a service account.
     *
     * @param array $params An associative array identifying the account and the new package.
     * @return array An associative array with 'success' (bool) and 'message' (string).
     */
    public function changePackage($params);

    /**
     * Tests the connection to the server's API.
     *
     * @return array An associative array with 'success' (bool) and 'message' (string).
     */
    public function testConnection();
}
