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
                if(isProjExists($con, $value)){
                    return isProjExists($con, $value);
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
            // TODO Думаю можно сделать без этого куска кода. Подумать можно ли его запихнуть в функцию валидации validateFilled()
            if(in_array($key, $required, true) && empty(trim($value))){
                $errors[$key] = "Поле $key должно быть заполнено";
            }
        }

        $errors = array_filter($errors);
        
        $taskId = '';
        $file_path = '';
        $file_name = '';
        $fileUrl = '';

        if(!empty($_FILES['file']['name'])){
            $file_name = $_FILES['file']['name'];
            $file_path = __DIR__ . '/uploads/';
            $fileUrl = 'uploads/'. $file_name;
        }
        move_uploaded_file($_FILES['file']['tmp_name'], $file_path . $file_name);

        if(empty($errors)){
            $stmt = db_get_prepare_stmt($con, getQueryAddTask(),[$task['name'],$fileUrl, $task['date'], $task['project']]);
            $res = mysqli_stmt_execute($stmt);
            if($res){
                $taskId = mysqli_insert_id($con);
                if($fileUrl === ''){
                    header("Location:" . getAbsolutePath('index.php'));
                }else{
                    header("Location:" . getAbsolutePath('index.php') . "?fileUrl=" . $fileUrl . "&taskId=" . $taskId);
                }
            }
        }

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
