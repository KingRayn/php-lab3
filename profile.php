<?php
// Проверка cookies + автоматическая авторизация
if (!isset($_COOKIE['user_id']) || !isset($_COOKIE['auth_hash'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_COOKIE['user_id'];
$authHash = $_COOKIE['auth_hash'];

// Проверим подлинность cookies
$conn = new mysqli('127.0.0.1', 'root', '', 'php_BAGROV');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT id, password_hash, bg_color, text_color FROM readers WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !hash_equals($authHash, hash('sha256', $user['id'] . $user['password_hash'] . 'lab3_salt'))) {
    // Cookies невалидны — сброс
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('auth_hash', '', time() - 3600, '/');
    setcookie('bg_color', '', time() - 3600, '/');
    setcookie('text_color', '', time() - 3600, '/');
    header('Location: login.php');
    exit;
}

// Если POST — обновляем настройки
if ($_POST) {
    $bg = $_POST['bg_color'] ?? '#ffffff';
    $text = $_POST['text_color'] ?? '#000000';

    // Валидация HEX
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $bg)) $bg = '#ffffff';
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $text)) $text = '#000000';

    $update = $conn->prepare("UPDATE readers SET bg_color = ?, text_color = ? WHERE id = ?");
    $update->bind_param('ssi', $bg, $text, $userId);
    $update->execute();
    $update->close();

    // Обновляем cookies
    setcookie('bg_color', $bg, time() + 86400 * 30, '/', '', false, true);
    setcookie('text_color', $text, time() + 86400 * 30, '/', '', false, true);

    // Обновляем $user для отображения
    $user['bg_color'] = $bg;
    $user['text_color'] = $text;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <style>
        body {
            background-color: <?= htmlspecialchars($user['bg_color']) ?>;
            color: <?= htmlspecialchars($user['text_color']) ?>;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .form-group { margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Личный кабинет</h2>
    <p>Привет, <?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?>!</p>

    <h3>Настройки внешнего вида</h3>
    <form method="POST">
        <div class="form-group">
            Фон: <input type="color" name="bg_color" value="<?= htmlspecialchars($user['bg_color']) ?>">
        </div>
        <div class="form-group">
            Цвет текста: <input type="color" name="text_color" value="<?= htmlspecialchars($user['text_color']) ?>">
        </div>
        <button type="submit">Сохранить</button>
    </form>

    <p><a href="logout.php">Выйти</a></p>
</body>
</html>