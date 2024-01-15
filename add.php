<?php

require_once 'helpers.php';
require_once 'init.php';

// Запрос для получения списка всех проектов
$titleName = 'Дела в порядке';
$userName = "Илья";
if(!$con){
    $error = mysqli_connect_error();
}else {
    // Запрос для получения списка всех проектов
    $sql =  getQueryAllProjects();
    $result = mysqli_query($con,$sql);
    if($result){
        $allProjects = mysqli_fetch_all($result,MYSQLI_ASSOC);
    }else {
        $error = mysqli_error($con);
    }

    // Запрос для получения списка проектов у текущего пользователя.
    $sql =  getQueryCurrentUserProjects();
    $result = mysqli_query($con,$sql);
    if($result){
        $currentUserProjects = mysqli_fetch_all($result,MYSQLI_ASSOC);
    }else {
        $error = mysqli_error($con);
    }

    // Запрос для получения списка из всех задач у текущего пользователя
    $sql =  getQueryCurrentUserTasks();
    $result = mysqli_query($con,$sql);
    if($result){
        $currentUserTasks = mysqli_fetch_all($result,MYSQLI_ASSOC);
    }else {
        $error = mysqli_error($con);
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $required = ['name', 'project'];
        $errors = [];

        $rules = [
            'name' => function($value){
                return validateFilled($value);
            },
            'project' => function($value){
                return validateFilled($value);
            },
        ];

        $task = filter_input_array(INPUT_POST,['name' => FILTER_DEFAULT, 'project' => FILTER_DEFAULT],true);

        dd($task);

        foreach ($task as $taskKey => $taskValue) {
            if(isset($rules[$taskKey])){
                $rule = $rules[$taskKey];
                $errors[$taskKey] = $rule($taskValue);
            }

            if(in_array($taskKey, $required) && empty($taskValue)){
                $errors[$taskKey] = "Поле $taskKey должно быть заполнено";
            }
        }

        $errors = array_filter($errors);

        dd($errors);
    }
}

$content = include_template('add.php', [
    'currentUserProjects' => $currentUserProjects,
    'tasksForCount' => $currentUserTasks,
    'currentUserTasks' => $currentUserTasks,
    'allProjects' => $allProjects,
]);
$layOut = include_template('layout.php', [
    'titleName' => $titleName,
    'userName' => $userName,
    'content' => $content,
]);
print($layOut);
