<?php
require_once 'helpers.php';
require_once 'init.php';

$titleName = 'Дела в порядке';
$currentUser = "Илья";

$errors = [];

if (!$con) {
    $error = mysqli_connect_error();
} else {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $required = ['email', 'password', 'name'];

        $rules = [
            'email' => function ($value) use ($con) {
                if (validateFilled($value)) {
                    return validateFilled($value);
                }
                if (validateEmailFormat($value)) {
                    return validateEmailFormat($value);
                }
                if (isEmailExists($con, $value)) {
                    return isEmailExists($con,$value);
                }
            },
            'password' => function ($value) {
                return validateFilled($value);
            },
            'name' => function ($value) {
                return validateFilled($value);
            },
        ];

        $newUser = filter_input_array(
            INPUT_POST,
            ['email' => FILTER_DEFAULT, 'password' => FILTER_DEFAULT, 'name' => FILTER_DEFAULT]);

        foreach ($newUser as $key => $value) {
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
            $password = password_hash($newUser['password'],PASSWORD_DEFAULT);
            $stmt = db_get_prepare_stmt($con, getQueryAddUser(),[$newUser['email'], $newUser['name'],$password]);
            $res = mysqli_stmt_execute($stmt);
            if($res){
                header("Location:" . getAbsolutePath('index.php'));
            }
        }
    }
}


$content = include_template('register.php', [
    'errors' => $errors,
]);

$layout = include_template('layout.php', [
    'titleName' => $titleName,
    'currentUser' => $currentUser,
    'content' => $content,
]);
print($layout);