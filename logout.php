<?php
// Удаляем все cookies
setcookie('user_id', '', time() - 3600, '/');
setcookie('auth_hash', '', time() - 3600, '/');
setcookie('bg_color', '', time() - 3600, '/');
setcookie('text_color', '', time() - 3600, '/');

// Редирект на login.php
header('Location: login.php');
exit;
?>