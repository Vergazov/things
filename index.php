<?php
require_once 'helpers.php';
require_once 'init.php';
//dd($_GET);
session_start();
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);
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
$currentUserAllTasks = '';
$projectId = '';
$filteredByProjTasks = '';
$taskStatus = 0;
if(!$con){
    $error = mysqli_connect_error();
}else {

    $currentUserProjects = getCurrentUserData($con,$currentUserName,getQueryCurrentUserProjects());
    $currentUserAllTasks = getCurrentUserData($con,$currentUserName,getQueryCurrentUserTasks());

    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        $ftSearchTask = trim(filter_input(INPUT_GET,'ft_search'));

        if($ftSearchTask !== ''){
            $currentUserTasks = getCurrentUserData($con,$ftSearchTask,getQueryFtSearchCurrentUserTasks());
        }else{
            $currentUserTasks = getCurrentUserData($con,$currentUserName,getQueryCurrentUserTasks());
        }
    }

    $projectId = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);

    if($projectId){
        $filteredByProjTasks = getCurrentUserData($con,$projectId,getQueryFilteredByProjTasks());

        if(empty($filteredByProjTasks)) {
            return http_response_code(404);
        }
    }
    if($projectId === ''){
        return http_response_code(404);
    }

    $taskId = filter_input(INPUT_GET,'task_id',FILTER_SANITIZE_NUMBER_INT);
    if($taskId){
        $sql = 'SELECT * FROM things_are_fine.tasks WHERE id = ?';
        $task = getCurrentUserData($con,$taskId,$sql);
        if($task[0]['status'] === 0){
            $taskStatus = 1;
        }
        $stmt = db_get_prepare_stmt($con, getQueryInvertTaskStatus(),[$taskStatus,$taskId]);
        $res = mysqli_stmt_execute($stmt);
        if($res){
            header("Location:" . getAbsolutePath('index.php'));
        }
    }
}

$layOut = '';

if (empty($_SESSION['user']['id'])) {
    $content = include_template('guest.php');

    $layOut = include_template('layout.php', [
        'content' => $content,
        'titleName' => $titleName,
    ]);
}else{
    $content = include_template('main.php', [
        'currentUserProjects' => $currentUserProjects,
        'currentUserTasks' => (($filteredByProjTasks === '') ? $currentUserTasks : $filteredByProjTasks),
        'tasksForCount' => $currentUserAllTasks,
        'projectId' => $projectId,
        'show_complete_tasks' => $show_complete_tasks,
    ]);

    $layOut = include_template('layout.php', [
        'titleName' => $titleName,
        'user' => $currentUserName,
        'content' => $content,
    ]);
}

print($layOut);