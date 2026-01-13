<?php
// This page is included by host_check.php when an invalid domain is used.
// The 404 header is sent by the host_check.php script before including this file.

// We need to fetch the system URL to provide a support link.
// A database connection would be needed if the bootstrap didn't already create one.
// For standalone use, you might need to connect manually.
$system_url = $db->query("SELECT value FROM settings WHERE setting = 'system_url'")->fetch_assoc()['value'] ?? '#';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Host</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f0f2f5;
            text-align: center;
        }
        .error-container {
            max-width: 600px;
        }
        .card {
            border-radius: 1rem;
            border: none;
            padding: 2rem;
        }
        .icon {
            font-size: 5rem;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container error-container">
        <div class="card shadow-sm">
             <div class="card-body">
                <div class="icon mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-shield-slash" viewBox="0 0 16 16">
                      <path d="M11.293 4.707 3.707 12.293l-1.414-1.414L9.879 3.293zM3.203 5.42 2.22 6.243a.5.5 0 0 0-.153.275l-.26 1.56a.5.5 0 0 0 .04.45l1.023 1.282c.11.14.11.336 0 .476l-1.023 1.282a.5.5 0 0 0-.04.45l.26 1.56a.5.5 0 0 0 .153.275l.983.823c.123.103.287.14.436.099l1.58-.296a.5.5 0 0 0 .303-.123L7.142 13.7a.5.5 0 0 0 .354-.112l.983-.823c.123-.103.287-.14.436-.099l1.58.296a.5.5 0 0 0 .303.123l1.17.218a.5.5 0 0 0 .436-.099l.983-.823a.5.5 0 0 0 .153-.275l.26-1.56a.5.5 0 0 0-.04-.45L13.7 9.421c-.11-.14-.11-.336 0-.476l1.023-1.282a.5.5 0 0 0 .04-.45l-.26-1.56a.5.5 0 0 0-.153-.275l-.983-.823a.5.5 0 0 0-.436-.099l-1.58.296a.5.5 0 0 0-.303.123L8.858 3.7a.5.5 0 0 0-.354.112L7.521 4.634a.5.5 0 0 0-.436.099l-1.58-.296a.5.5 0 0 0-.303.123L4.053 4.75a.5.5 0 0 0-.436.099z"/>
                    </svg>
                </div>
                <h1 class="display-5 fw-bold">Unauthorized Host</h1>
                <p class="lead">The domain name you are trying to access (<strong><?php echo htmlspecialchars($http_host); ?></strong>) is not configured to use this service.</p>
                <p>If you are the owner of this domain, please configure the correct CNAME record and add the domain to your reseller portal. If you are a customer, please contact your service provider.</p>
                <hr>
                <p>If you believe you have reached this page in error, please contact our support team.</p>
                <a href="<?php echo htmlspecialchars($system_url); ?>" class="btn btn-primary">Go to Main Site</a>
            </div>
        </div>
    </div>
</body>
</html>
