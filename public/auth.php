<?php
require_once '../functions/db.php';
require_once '../functions/template.php';
require_once '../functions/validators.php';
require_once '../db/db.php';

$titleName = 'Дела в порядке';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $required = ['email', 'password'];
    $exists = ['email'];

    $dataForAuth = filter_input_array(INPUT_POST, ['email' => FILTER_DEFAULT, 'password' => FILTER_DEFAULT]);
    foreach ($dataForAuth as $key => $value) {

        if (in_array($key, $required, true) && isFilled($key) === false) {
            $errors[$key] = "Заполните поле $key";
        }
        if (in_array($key, $exists, true) && empty($errors[$key]) && isEmailExists($con, $key) === false) {
            $errors[$key] = "Вы ввели неверный $key";
        }
    }

    $errors = array_filter($errors);

    if (empty($errors)) {
        $userData = getUserDataByEmail($con, $dataForAuth['email']);
        if (password_verify($dataForAuth['password'], $userData['password'])) {
            $session = session_start();
            $_SESSION['user']['id'] = $userData['id'];
            header("Location:" . getAbsolutePath('index.php'));
        } else {
            $errors['password'] = 'Вы ввели неверный пароль';
        }
    }
}

$content = include_template('auth.php', [
    'errors' => $errors,
]);
$layOut = include_template('layout.php', [
    'content' => $content,
    'titleName' => $titleName,
]);

print($layOut);
