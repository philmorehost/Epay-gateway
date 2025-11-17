<?php
// app/modules/ApiModule.php

/**
 * Interface ApiModule
 * Defines the standard contract for all third-party service provisioning modules.
 */
interface ApiModule {
    /**
     * Creates a new service account.
     *
     * @param array $params An associative array of parameters needed for account creation (e.g., username, domain, package).
     * @return array An associative array with the result (e.g., ['success' => true, 'message' => 'Account created successfully']).
     */
    public function createAccount(array $params): array;

    /**
     * Suspends an existing service account.
     *
     * @param array $params An associative array of parameters to identify the account (e.g., username, domain).
     * @return array An associative array with the result.
     */
    public function suspendAccount(array $params): array;

    /**
     * Unsuspends a previously suspended service account.
     *
     * @param array $params An associative array of parameters to identify the account.
     * @return array An associative array with the result.
     */
    public function unsuspendAccount(array $params): array;

    /**
     * Terminates a service account. This action is usually irreversible.
     *
     * @param array $params An associative array of parameters to identify the account.
     * @return array An associative array with the result.
     */
    public function terminateAccount(array $params): array;

    /**
     * Modifies an existing service account's package or plan.
     *
     * @param array $params An associative array of parameters, including the new package/plan.
     * @return array An associative array with the result.
     */
    public function modifyPackage(array $params): array;
}
