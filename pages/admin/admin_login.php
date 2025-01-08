<?php


require_once __DIR__ . '/../../config/app.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputKey = $_POST['api_key'] ?? '';
    // Testa flera sätt att få API nyckeln
    $validKey = $_ENV['API_KEY'] ?? getenv('API_KEY') ?? null;

    if ($validKey === null) {
        die('API_KEY saknas i miljövariabler. Kontrollera .env filen.');
    }

    if ($inputKey === $validKey) {
        $_SESSION['is_admin'] = true;
        header('Location: update_admin.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Login</title>
</head>

<body>
    <form method="POST">
        <input type="password" name="api_key" placeholder="Ange API nyckel" required>
        <button type="submit">Logga in</button>
    </form>
</body>

</html>