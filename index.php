<?php
require_once 'helpers.php';
// показывать или нет выполненные задачи
$show_complete_tasks = rand(0, 1);
$titleName = 'Дела в порядке';
$userName = 'Артем';
$projects = ['Входящие', 'Учеба', 'Работа', 'Домашние дела', 'Авто'];
$tasks = [
    ['Собеседование в IT компании', date('d.m.Y', strtotime('28.12.2023')), 'Работа', false],
    ['Выполнить тестовое задание', date('d.m.Y', strtotime('27.12.2023')), 'Работа', false],
    ['Сделать задание первого раздела', date('d.m.Y', strtotime('29.12.2023')), 'Учеба', true],
    ['Встреча с другом', date('d.m.Y', strtotime('29.12.2023')), 'Входящие', false],
    ['Купить корм для кота', null, 'Домашние дела', false],
    ['Заказать пиццу', null, 'Домашние дела', false]
];
function countTasksForProject($tasks, $project)
{
    $tasksNumber = 0;
    foreach ($tasks as $task) {
        if ($project === $task[2]) {
            $tasksNumber++;
        }
    }
    return $tasksNumber;
}

function isTaskImportant($date)
{
    if(!validateDate($date)){
       return false;
    }
    $dateDiff =  (strtotime($date) - time()) / 3600;
    if($dateDiff <= 24){
        return true;
    }
    return false;
}


$content = include_template('main.php', ['projects' => $projects, 'tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks]);
$layOut = include_template('layout.php', ['titleName' => $titleName, 'userName' => $userName, 'content' => $content]);
print($layOut);