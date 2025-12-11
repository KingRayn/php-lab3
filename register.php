<?php
$error = '';

if ($_POST) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$first_name || !$last_name || !$password) {
        $error = 'Все поля обязательны.';
    } else {
        // Подключение к БД — как в ЛР2
        $conn = new mysqli('127.0.0.1', 'root', '', 'php_BAGROV');
        if ($conn->connect_error) {
            die("Ошибка подключения: " . $conn->connect_error);
        }

        // Проверка, не занят ли last_name (простой вариант — как уникальный логин)
        $check = $conn->prepare("SELECT id FROM readers WHERE last_name = ?");
        $check->bind_param('s', $last_name);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = 'Читатель с такой фамилией уже существует.';
            $check->close();
            $conn->close();
        } else {
            $check->close();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $bg = '#ffffff';
            $text = '#000000';

            $stmt = $conn->prepare("INSERT INTO readers (first_name, last_name, password_hash, bg_color, text_color) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $first_name, $last_name, $hash, $bg, $text);
            if ($stmt->execute()) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Ошибка при регистрации: ' . $stmt->error;
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
</head>
<body>
    <h2>Регистрация читателя</h2>
    <?php if ($error): ?>
        <p style="color: red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['registered'])): ?>
        <p style="color: green">Регистрация прошла успешно! Войдите.</p>
    <?php endif; ?>
    <form method="POST">
        Имя: <input type="text" name="first_name" required><br><br>
        Фамилия (логин): <input type="text" name="last_name" required><br><br>
        Пароль: <input type="password" name="password" required><br><br>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p><a href="login.php">Уже есть аккаунт? Войти</a></p>
</body>
</html>