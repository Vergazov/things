<?php
require_once 'helpers.php';
require_once 'init.php';
session_start();

$titleName = 'Дела в порядке';
$currentUserName = '';
$currentUserId = '';
$currentUserEmail = '';

if(!empty($_SESSION['user']['id'])){
    $currentUserId = $_SESSION['user']['id'];
    $currentUser = getUserDataById($con,$currentUserId);
    $currentUserEmail = $currentUser['email'];
    $currentUserName = $currentUser['name'];
}

$currentUserProjects = '';
$currentUserTasks = '';
$errors = [];

if(!$con){
    $error = mysqli_connect_error();
}else {
    $currentUserProjects = getCurrentUserData($con,$currentUserName,getQueryCurrentUserProjects());
    $currentUserTasks = getCurrentUserData($con,$currentUserName,getQueryCurrentUserTasks());

    if($_SERVER['REQUEST_METHOD'] === 'POST') {

        $required = ['project_name'];

        $rules = [
            'project_name' => function($value) use ($con) {
                if(validateFilled($value)){
                    return validateFilled($value);
                }
                if(isProjExistsByName($con, $value)){
                    return isProjExistsByName($con, $value);
                }
            },
        ];

        $project = filter_input_array(INPUT_POST,['project_name' => FILTER_DEFAULT]);

        foreach ($project as $key => $value) {
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

        if(empty($errors)){
            $stmt = db_get_prepare_stmt($con, getQueryAddProject(),[$project['project_name'],$currentUserId]);
            $res = mysqli_stmt_execute($stmt);
            if($res){
                header("Location:" . getAbsolutePath('index.php'));
            }
        }
    }
}

if (empty($_SESSION['user']['id'])) {
    $content = include_template('guest.php');

    $layOut = include_template('layout.php', [
        'content' => $content,
        'titleName' => $titleName,
    ]);
}else {

    $content = include_template('add_project.php', [
        'currentUserProjects' => $currentUserProjects,
        'tasksForCount' => $currentUserTasks,
        'currentUserTasks' => $currentUserTasks,
        'errors' => $errors,
    ]);
    $layOut = include_template('layout.php', [
        'titleName' => $titleName,
        'user' => $currentUserName,
        'content' => $content,
    ]);
}
print($layOut);
