<?php

require_once 'helpers.php';
require_once 'init.php';

// Запрос для получения списка всех проектов
$titleName = 'Дела в порядке';
$currentUser = "Илья";

$currentUserProjects = '';
$currentUserTasks = '';

if(!$con){
    $error = mysqli_connect_error();
}else {

    $currentUserProjects = getCurrentUserData($con,$currentUser,getQueryCurrentUserProjects());
    $currentUserTasks = getCurrentUserData($con,$currentUser,getQueryCurrentUserTasks());

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
            'date' => function($value){
                return validateDate($value);
            },
        ];

        $task = filter_input_array(INPUT_POST,['name' => FILTER_DEFAULT, 'project' => FILTER_DEFAULT, 'date' => FILTER_DEFAULT]);
        dd($task);
        var_dump($task);
        foreach ($task as $key => $value) {
            if(isset($rules[$key])){
                $rule = $rules[$key];
                $errors[$key] = $rule($key);
            }
            if(in_array($key, $required, true) && empty($value)){
                $errors[$key] = "Поле $key должно быть заполнено";
            }
        }

        // Проверка на существование проекта
        if($task['project'] !== ''){
            $sql = 'SELECT projects.name FROM things_are_fine.projects WHERE id = ' . $task['project'];
            $result = mysqli_query($con,$sql);
            if($result){
                $currentProj = mysqli_fetch_all($result,MYSQLI_ASSOC);
                if(empty($currentProj)){
                    $errors['project'] = 'Такого проекта не существует';
                }
            }else {
                $error = mysqli_error($con);
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
]);
$layOut = include_template('layout.php', [
    'titleName' => $titleName,
    'currentUser' => $currentUser,
    'content' => $content,
]);
print($layOut);
