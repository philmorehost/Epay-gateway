<?php
// app/core/acl.php

function has_permission($permission) {
    global $db;

    // If there's no staff ID in the session, deny access
    if (!isset($_SESSION['staff_id'])) {
        return false;
    }

    // Lazy load permissions into the session to avoid multiple DB calls
    if (!isset($_SESSION['staff_permissions'])) {
        $staff_id = $_SESSION['staff_id'];

        $stmt = $db->prepare("SELECT role_id FROM staff WHERE id = ?");
        $stmt->bind_param('i', $staff_id);
        $stmt->execute();
        $staff = $stmt->get_result()->fetch_assoc();

        if (!$staff || !$staff['role_id']) {
            $_SESSION['staff_permissions'] = [];
            return false;
        }

        // Super Admin (role 1) has all permissions
        if ($staff['role_id'] == 1) {
            $_SESSION['is_super_admin'] = true;
        }

        $stmt = $db->prepare("SELECT permission FROM staff_permissions WHERE role_id = ?");
        $stmt->bind_param('i', $staff['role_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission'];
        }
        $_SESSION['staff_permissions'] = $permissions;
    }

    // Super Admin always has permission
    if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) {
        return true;
    }

    // Check if the user has the specific permission
    return in_array($permission, $_SESSION['staff_permissions']);
}

function require_permission($permission) {
    if (!has_permission($permission)) {
        // You can customize this error page
        http_response_code(403);
        die("<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p><a href='index.php'>Go to Dashboard</a>");
    }
}
