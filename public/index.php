<?php

session_start();
require_once '../functions/db.php';
require_once '../functions/template.php';
require_once '../functions/validators.php';
require_once '../db/db.php';

$showCompleted = filter_input(INPUT_GET, 'show_completed');
if ($showCompleted) {
    $show_complete_tasks = 1;
} else {
    $show_complete_tasks = 0;
}
//$show_complete_tasks = rand(0, 1);
$titleName = 'Дела в порядке';
$currentUserId = '';
$currentUserName = '';
$currentUserEmail = '';

if (!empty($_SESSION['user']['id'])) {
    $currentUserId = $_SESSION['user']['id'];
    $currentUser = getUserDataById($con, $currentUserId);
    $currentUserEmail = $currentUser['email'];
    $currentUserName = $currentUser['name'];
}

$currentUserProjects = '';
$currentUserTasks = '';
$currentUserAllTasks = '';
$projectId = '';
$taskStatus = 0;
$filter = '';
$filterCombine = [];
$layOut = '';

if (!$con) {

    $error = mysqli_connect_error();
} else {

    $currentUserProjects = getCurrentUserData($con, $currentUserId, getQueryCurrentUserProjects());
    $currentUserAllTasks = getCurrentUserData($con, $currentUserId, getQueryCurrentUserTasks());
    $currentUserTasks = $currentUserAllTasks;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $ftSearchTask = trim(filter_input(INPUT_GET, 'ft_search'));
        if ($ftSearchTask !== '') {
            $currentUserTasks = getCurrentUserData($con, [$ftSearchTask, $currentUserId],
                getQueryFtSearchCurrentUserTasks());
        }

        $filter = filter_input(INPUT_GET, 'filter');
        if ($filter === 'all_tasks') {
            $currentUserTasks = $currentUserAllTasks;
        }
        if ($filter === 'today') {
            $currentUserTasks = getCurrentUserData($con, $currentUserId, getQuerySearchTodayTasks());
        }
        if ($filter === 'tomorrow') {
            $currentUserTasks = getCurrentUserData($con, $currentUserId, getQuerySearchTomorrowTasks());
        }
        if ($filter === 'overdue') {
            $currentUserTasks = getCurrentUserData($con, $currentUserId, getQuerySearchOverdueTasks());
        }

        $projectId = filter_input(INPUT_GET, 'project_id', FILTER_SANITIZE_NUMBER_INT);
        if (!isset($filter)) {
            if ($projectId) {
                $currentUserTasks = getCurrentUserData($con, $projectId, getQueryFilteredByProjTasks());
                $projExists = isProjExists($con, $projectId, $currentUserId);
                if (empty($currentUserTasks) && $projExists !== null) {
                    return http_response_code(404);
                }
            }
            if ($projectId === '') {
                return http_response_code(404);
            }
        }

        $filterCombine = filter_input_array(INPUT_GET, ['project_id' => FILTER_DEFAULT, 'filter' => FILTER_DEFAULT,],
            false);
        if ($filterCombine !== null && count($filterCombine) === 2) {
            if ($filterCombine['filter'] === 'all_tasks') {
                $currentUserTasks = getCurrentUserData($con, [$currentUserId, $filterCombine['project_id']],
                    getQuerySearchAllTasksByProject());
            }
            if ($filterCombine['filter'] === 'today') {
                $currentUserTasks = getCurrentUserData($con, [$currentUserId, $filterCombine['project_id']],
                    getQuerySearchTodayTasksByProject());
            }
            if ($filterCombine['filter'] === 'tomorrow') {
                $currentUserTasks = getCurrentUserData($con, [$currentUserId, $filterCombine['project_id']],
                    getQuerySearchTomorrowTasksByProject());
            }
            if ($filterCombine['filter'] === 'overdue') {
                $currentUserTasks = getCurrentUserData($con, [$currentUserId, $filterCombine['project_id']],
                    getQuerySearchOverdueTasksByProject());
            }
        }

        $taskId = filter_input(INPUT_GET, 'task_id', FILTER_SANITIZE_NUMBER_INT);
        if ($taskId) {
            $task = getCurrentUserData($con, [$taskId, $currentUserId], getQuerySearchTaskById());
            if ($task[0]['status'] === 0) {
                $taskStatus = 1;
            }
            $stmt = db_get_prepare_stmt($con, getQueryInvertTaskStatus(), [$taskStatus, $taskId]);
            $taskForInvert = mysqli_stmt_execute($stmt);
            if ($taskForInvert) {
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
} else {
    $content = include_template('main.php', [
        'currentUserProjects' => $currentUserProjects,
        'currentUserTasks' => $currentUserTasks,
        'tasksForCount' => $currentUserAllTasks,
        'projectId' => $projectId,
        'filter' => $filter,
        'show_complete_tasks' => $show_complete_tasks,
    ]);

    $layOut = include_template('layout.php', [
        'titleName' => $titleName,
        'user' => $currentUserName,
        'content' => $content,
    ]);
}

print($layOut);