<?php
require_once 'helpers.php';
require_once 'init.php';

// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);
$titleName = 'Дела в порядке';
$userName = "Илья";
if(!$con){
    $error = mysqli_connect_error();
}else {

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

    $projectId = '';

    //Фильтрация задач по проекту
    if(filter_input(INPUT_GET,'id') !== NULL){
        $projectId = filter_input(INPUT_GET,'id',FILTER_SANITIZE_NUMBER_INT);
        $sql = 'SELECT tasks.name, tasks.completion_date, projects.name project, tasks.status FROM things_are_fine.tasks '
                . 'JOIN things_are_fine.projects '
                . 'ON tasks.project_id = projects.id '
                . 'WHERE project_id = ' . $projectId;

        // Проверка на присутствие параметра запроса
        if(empty(filter_input(INPUT_GET,'id'))){
            return http_response_code(404);
        }
        $result = mysqli_query($con,$sql);

        // Если по ID не найдено ни одной записи
        if($result->num_rows === 0) {
            return http_response_code(404);
        }
        if($result){
            $filteredByProjTasks = mysqli_fetch_all($result,MYSQLI_ASSOC);
        }else {
            $error = mysqli_error($con);
        }
    }

}

$content = include_template('main.php', [
    'currentUserProjects' => $currentUserProjects,
    'tasksForCount' => $currentUserTasks,
    'currentUserTasks' => (($projectId === '') ? $currentUserTasks : $filteredByProjTasks),
    'projectId' => $projectId,
    'show_complete_tasks' => $show_complete_tasks,
]);

$layOut = include_template('layout.php', [
    'titleName' => $titleName,
    'userName' => $userName,
    'content' => $content,
]);
print($layOut);