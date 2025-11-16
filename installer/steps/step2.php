<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Step 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Step 2: Database Configuration</h1>
        <form action="index.php?step=3" method="post">
            <div class="mb-3">
                <label for="db_host" class="form-label">Database Host</label>
                <input type="text" class="form-control" id="db_host" name="db_host" required>
            </div>
            <div class="mb-3">
                <label for="db_name" class="form-label">Database Name</label>
                <input type="text" class="form-control" id="db_name" name="db_name" required>
            </div>
            <div class="mb-3">
                <label for="db_user" class="form-label">Database Username</label>
                <input type="text" class="form-control" id="db_user" name="db_user" required>
            </div>
            <div class="mb-3">
                <label for="db_pass" class="form-label">Database Password</label>
                <input type="password" class="form-control" id="db_pass" name="db_pass">
            </div>
            <button type="submit" class="btn btn-primary">Next</button>
        </form>
    </div>
</body>
</html>
