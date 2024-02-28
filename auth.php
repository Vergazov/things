<?php
require_once 'functions/helpers.php';
require_once 'init.php';

$titleName = 'Дела в порядке';
$errors = [];
if (!$con) {
    $error = mysqli_connect_error();
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $required = ['email', 'password'];

        $rules = [
            'email' => function($value) use ($con) {
                if (validateFilled($value)) {
                    return validateFilled($value);
                }
                if (isEmailExistsForAuth($con, $value)) {
                    return isEmailExistsForAuth($con,$value);
                }
            },
            'password' => function($value){
                if (validateFilled($value)) {
                    return validateFilled($value);
                }
            }
        ];

        $authUser = filter_input_array(INPUT_POST, ['email' => FILTER_DEFAULT, 'password' => FILTER_DEFAULT]);

        foreach ($authUser as $key => $value) {
            if(isset($rules[$key])){
                $rule = $rules[$key];
                $errors[$key] = $rule($key);
            }
            // TODO думаю можно сделать без этого куска кода. Подумать можно ли его запихнуть в функцию  validateFilled()
            if(in_array($key, $required, true) && empty(trim($value))){
                $errors[$key] = "Вы ввели неверный $key";
            }
        }

        $errors = array_filter($errors);

        if(empty($errors)){
            $userData = getUserDataByEmail($con,$authUser['email']);
            if(password_verify($authUser['password'],$userData['password'])){

                $session = session_start();
                $_SESSION['user']['id'] = $userData['id'];

                header("Location:" . getAbsolutePath('index.php'));

            }else{
                $errors['password'] = 'Вы ввели неверный пароль';
            }
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
