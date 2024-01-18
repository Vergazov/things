<?php

require_once 'helpers.php';
require_once 'init.php';

// Запрос для получения списка всех проектов
$titleName = 'Дела в порядке';
$currentUser = "Илья";

$currentUserProjects = '';
$currentUserTasks = '';
$errors = [];

if(!$con){
    $error = mysqli_connect_error();
}else {

    $currentUserProjects = getCurrentUserData($con,$currentUser,getQueryCurrentUserProjects());
    $currentUserTasks = getCurrentUserData($con,$currentUser,getQueryCurrentUserTasks());
// TODO: Пока сделать полностью ту валидацию что у меня прописана в rules
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $required = ['name', 'project'];

        $rules = [
            'name' => function($value){
                return validateFilled($value);
            },
            // TODO: Сделать читабельнее
            'project' => function($value) use ($con) {
                if(validateFilled($value)){
                    return validateFilled($value);
                }
                if(isProjExist($con, $value)){
                    return isProjExist($con, $value);
                }
            },
            // TODO: Сделать читабельнее
            'date' => function($value){
                if(validateDateFormat($value)){
                    return validateDateFormat($value);
                }
                if(validateDateRange($value)){
                    return validateDateRange($value);
                }
            },
        ];

        $task = filter_input_array(INPUT_POST,['name' => FILTER_DEFAULT, 'project' => FILTER_DEFAULT, 'date' => FILTER_DEFAULT]);
        foreach ($task as $key => $value) {
            if(isset($rules[$key])){
                $rule = $rules[$key];
                $errors[$key] = $rule($key);
            }
            if(in_array($key, $required, true) && empty($value)){
                $errors[$key] = "Поле $key должно быть заполнено";
            }
        }

        $errors = array_filter($errors);
    }
}

$content = include_template('add.php', [
    'currentUserProjects' => $currentUserProjects,
    'tasksForCount' => $currentUserTasks,
    'currentUserTasks' => $currentUserTasks,
    'errors' => $errors,
]);
$layOut = include_template('layout.php', [
    'titleName' => $titleName,
    'currentUser' => $currentUser,
    'content' => $content,
]);
print($layOut);
