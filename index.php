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
    if(filter_input(INPUT_GET,'id') !== ''){
        $projectId = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
        $filteredByProjTasks = getCurrentUserData($con,$projectId,getQueryFilteredByProjTasks());

        if(empty($filteredByProjTasks)) {
            return http_response_code(404);
        }

    }else{
        return http_response_code(404);
    }
}
// TODO: подумать как доработать. Мне не нравится, что дублируются задачи и не нравится условный оператор в currentUserTasks

$content = include_template('main.php', [
    'currentUserProjects' => $currentUserProjects,
    'tasksForCount' => $currentUserTasks,
    'currentUserTasks' => (($projectId === '') ? $currentUserTasks : $filteredByProjTasks),
    'projectId' => $projectId,
    'show_complete_tasks' => $show_complete_tasks,
]);

$layOut = include_template('layout.php', [
    'titleName' => $titleName,
    'currentUser' => $currentUser,
    'content' => $content,
]);
print($layOut);