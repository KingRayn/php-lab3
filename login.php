<?php
$error = '';

if ($_POST) {
    $last_name = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$last_name || !$password) {
        $error = 'Фамилия и пароль обязательны.';
    } else {
        $conn = new mysqli('127.0.0.1', 'root', '', 'php_BAGROV');
        if ($conn->connect_error) {
            die("Ошибка подключения: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("SELECT id, password_hash, bg_color, text_color FROM readers WHERE last_name = ?");
        $stmt->bind_param('s', $last_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Устанавливаем cookies
            setcookie('user_id', $user['id'], time() + 86400 * 30, '/', '', false, true);
            setcookie('auth_hash', hash('sha256', $user['id'] . $user['password_hash'] . 'lab3_salt'), time() + 86400 * 30, '/', '', false, true);
            setcookie('bg_color', $user['bg_color'], time() + 86400 * 30, '/', '', false, true);
            setcookie('text_color', $user['text_color'], time() + 86400 * 30, '/', '', false, true);

            header('Location: profile.php');
            exit;
        } else {
            $error = 'Неверная фамилия или пароль.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
</head>
<body>
    <h2>Вход в систему</h2>
    <?php if ($error): ?>
        <p style="color: red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        Фамилия (логин): <input type="text" name="last_name" required><br><br>
        Пароль: <input type="password" name="password" required><br><br>
        <button type="submit">Войти</button>
    </form>
    <p><a href="register.php">Нет аккаунта? Зарегистрироваться</a></p>
</body>
</html>