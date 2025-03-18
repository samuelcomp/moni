<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_ENV['APP_NAME'] ?> - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1><?= $_ENV['APP_NAME'] ?></h1>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/login">Login</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <h2>Welcome to the Attendance System</h2>
        <p>This is the starting point for your advanced attendance system.</p>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> <?= $_ENV['APP_NAME'] ?></p>
    </footer>
    
    <script src="assets/js/main.js"></script>
</body>
</html> 