<?php
require_once 'helpers.php';
require_once 'init.php';

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);

$titleName = 'Дела в порядке';
$currentUser = "Илья";

$currentUserProjects = '';
$currentUserTasks = '';
$projectId = '';
$filteredByProjTasks = '';

if(!$con){
    $error = mysqli_connect_error();
}else {
    $currentUserProjects = getCurrentUserData($con,$currentUser,getQueryCurrentUserProjects());
    $currentUserTasks = getCurrentUserData($con,$currentUser,getQueryCurrentUserTasks());

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
}

$content = include_template('main.php', [
    'currentUserProjects' => $currentUserProjects,
    'currentUserTasks' => (($filteredByProjTasks === '') ? $currentUserTasks : $filteredByProjTasks),
    'tasksForCount' => $currentUserTasks,
    'projectId' => $projectId,
    'show_complete_tasks' => $show_complete_tasks,
]);

$layOut = include_template('layout.php', [
    'titleName' => $titleName,
    'currentUser' => $currentUser,
    'content' => $content,
]);
print($layOut);