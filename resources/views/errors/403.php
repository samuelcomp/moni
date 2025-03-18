<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Access Denied</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .error-container {
            max-width: 600px;
            margin: 50px auto;
            text-align: center;
        }
        .error-code {
            font-size: 72px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            margin-bottom: 30px;
        }
        .back-link {
            display: inline-block;
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <h1><?= $_ENV['APP_NAME'] ?></h1>
        <nav>
            <ul>
                <li><a href="/dashboard">Dashboard</a></li>
                <li><a href="/logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <div class="error-container">
            <div class="error-code">403</div>
            <div class="error-message">Access Denied</div>
            <p>You don't have permission to access this resource.</p>
            <a href="/dashboard" class="back-link">Back to Dashboard</a>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?></p>
    </footer>
</body>
</html> 