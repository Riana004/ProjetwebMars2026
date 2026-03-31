<?php
session_start();

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!empty($_SESSION['is_admin'])) {
    header('Location: editor.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === 'admin' && $password === 'admin') {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_username'] = 'admin';
        header('Location: editor.php');
        exit;
    }

    $error = 'Identifiants invalides.';
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Backoffice</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #1f2a37;
        }

        .login-card {
            width: 100%;
            max-width: 360px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 24px;
        }

        h1 {
            margin: 0 0 16px;
            font-size: 22px;
        }

        label {
            display: block;
            font-size: 14px;
            margin: 10px 0 6px;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cfd8e3;
            border-radius: 8px;
            font-size: 14px;
        }

        button {
            width: 100%;
            margin-top: 14px;
            padding: 10px 12px;
            border: 0;
            border-radius: 8px;
            background: #1f6feb;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        .error {
            margin-top: 10px;
            color: #b42318;
            font-size: 14px;
        }

        .hint {
            margin-top: 12px;
            color: #667085;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <form method="post" class="login-card" autocomplete="off">
        <h1>Backoffice</h1>

        <label for="username">Identifiant</label>
        <input id="username" name="username" type="text" required>

        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" required>

        <button type="submit">Se connecter</button>

        <?php if ($error !== ''): ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
        <?php endif; ?>

        <p class="hint">Identifiants par défaut : admin / admin</p>
    </form>
</body>

</html>