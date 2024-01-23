<?php
require_once 'helpers.php';
require_once 'init.php';

$titleName = 'Дела в порядке';
$currentUser = "Илья";

if(!$con){
    $error = mysqli_connect_error();
}else {
    if($_SERVER['REQUEST_METHOD'] === 'POST') {

        $required = ['email','password','name'];

        $rules = [
            'email' => function($value){

            },
            'password' => function($value){

            },
            'name' => function($value){

            },
        ];
    }
}


$content = include_template('register.php',[]);

$layout = include_template('layout.php', [
    'titleName' => $titleName,
    'currentUser' => $currentUser,
    'content' => $content,
]);
print($layout);