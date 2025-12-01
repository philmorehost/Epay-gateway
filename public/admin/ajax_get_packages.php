<?php
header('Content-Type: application/json');

require_once '../../app/core/bootstrap.php';
require_once '../../app/modules/Cpanel.php';

try {
    $cpanel = new Cpanel();
    $result = $cpanel->list_packages();

    if (isset($result['metadata']['reason']) && $result['metadata']['reason'] === 'OK') {
        $packages = [];
        foreach ($result['data']['pkg'] as $pkg) {
            $packages[] = $pkg['name'];
        }
        echo json_encode(['packages' => $packages]);
    } else {
        throw new Exception($result['metadata']['reason'] ?? 'Failed to fetch packages from WHM.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
