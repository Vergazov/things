<?php
require_once 'helpers.php';
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
                $errors[$key] = "Поле $key должно быть заполнено";
            }
        }
        $errors = array_filter($errors);

        if(empty($errors)){
            $userData = getUserData($con,$authUser['email']);
            if(password_verify($authUser['password'],$userData[0]['password'])){
                session_start();
                $_SESSION['user']['name'] = $userData['name'];
                $_SESSION['user']['id'] = $userData['id'];
                $_SESSION['user']['email'] = $userData['email'];

                header("Location:" . getAbsolutePath('index.php'));
//                header("Location: /index.php");
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
