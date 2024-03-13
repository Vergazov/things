<?php

require_once '../bootstrap.php';

$currentUserId = '';
$currentUserName = '';
$currentUserEmail = '';

if (!empty($_SESSION['user']['id'])) {
    $currentUserId = $_SESSION['user']['id'];
    $currentUser = getUserDataById($con, $currentUserId);
    $currentUserEmail = $currentUser['email'];
    $currentUserName = $currentUser['name'];
}

$currentUserProjects = '';
$currentUserTasks = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $required = ['email', 'password', 'name'];
    $format = ['email'];
    $exists = ['email'];

    $newUser = filter_input_array(INPUT_POST, [
        'email' => FILTER_DEFAULT,
        'password' => FILTER_DEFAULT,
        'name' => FILTER_DEFAULT
    ]);

    foreach ($newUser as $key => $value) {
        if (in_array($key, $required, true) && isFilled($key) === false) {
            $errors[$key] = "Заполните поле $key";
        }
        if (in_array($key, $format, true) && empty($errors[$key]) && isEmailValid($key) === false) {
            $errors[$key] = "Неверный формат почты";
        }
        if (in_array($key, $exists, true) && empty($errors[$key]) && isEmailExists($con, $key) === true) {
            $errors[$key] = "Пользователь с такой почтой уже существует";
        }
    }

    $errors = array_filter($errors);

    if (empty($errors)) {
        $password = password_hash($newUser['password'], PASSWORD_DEFAULT);
        $stmt = db_get_prepare_stmt($con, getQueryAddUser(), [$newUser['email'], $newUser['name'], $password]);
        $res = mysqli_stmt_execute($stmt);
        if ($res) {
            header("Location:" . getAbsolutePath('index.php'));
        }
    }
}

$content = include_template('register.php', [
    'errors' => $errors,
]);

$layout = include_template('layout.php', [
    'titleName' => $titleName,
    'content' => $content,
]);
print($layout);