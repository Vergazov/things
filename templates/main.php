<?php
/** @var array $currentUserProjects */
/** @var array $tasksForCount */
/** @var int $projectId */
/** @var int $show_complete_tasks */
?>

<section class="content__side">
    <h2 class="content__side-heading">Проекты</h2>
    <nav class="main-navigation">
        <ul class="main-navigation__list">
            <?php foreach($currentUserProjects as $project): ?>
                <li class="main-navigation__list-item <?php if($project['id'] == $projectId): ?>main-navigation__list-item--active<?php endif; ?> ">
                    <a class="main-navigation__list-item-link" href="?project_id=<?=$project['id']?>"><?=htmlspecialchars($project['name'])?></a>
                    <span class="main-navigation__list-item-count"><?=countTasksForProject($tasksForCount,$project['name'])?></span>
                </li>
            <?php endforeach;?>
        </ul>
    </nav>

    <a class="button button--transparent button--plus content__side-button"
       href="<?=getAbsolutePath('add_project.php')?>" target="project_add">Добавить проект</a>
</section>

<main class="content__main">
    <h2 class="content__main-heading">Список задач</h2>

    <form class="search-form" action="<?=getAbsolutePath('index.php')?>" method="GET" autocomplete="off">
        <input class="search-form__input" type="text" name="ft_search" value="" placeholder="Поиск по задачам">

        <input class="search-form__submit" type="submit" name="" value="Искать">
    </form>

    <div class="tasks-controls">
        <nav class="tasks-switch">
            <a href="<?=getAbsolutePath('index.php')?>?filter=all_tasks<?php if(!empty($projectId)): ?>&project_id=<?=$projectId?> <?php endif; ?>" class="tasks-switch__item
            <?php if($filter === 'all_tasks'):?> <?='tasks-switch__item--active'?><?php endif ?>"">Все задачи</a>
            <a href="<?=getAbsolutePath('index.php')?>?filter=today<?php if(!empty($projectId)): ?>&project_id=<?=$projectId?> <?php endif; ?>" class="tasks-switch__item
            <?php if($filter === 'today'):?> <?='tasks-switch__item--active'?><?php endif ?>">Повестка дня</a>
            <a href="<?=getAbsolutePath('index.php')?>?filter=tomorrow<?php if(!empty($projectId)): ?>&project_id=<?=$projectId?> <?php endif; ?>" class="tasks-switch__item
            <?php if($filter === 'tomorrow'):?> <?='tasks-switch__item--active'?><?php endif ?>">Завтра</a>
            <a href="<?=getAbsolutePath('index.php')?>?filter=overdue<?php if(!empty($projectId)): ?>&project_id=<?=$projectId?> <?php endif; ?>" class="tasks-switch__item
            <?php if($filter === 'overdue'):?> <?='tasks-switch__item--active'?><?php endif ?>">Просроченные</a>
        </nav>

        <label class="checkbox">
            <input class="checkbox__input visually-hidden show_completed" <?php if($show_complete_tasks === 1): ?>checked<?php endif; ?> type="checkbox">
            <span class="checkbox__text">Показывать выполненные</span>
        </label>
    </div>

    <table class="tasks">
        <?php if(empty($currentUserTasks)): ?>
            <p>Ничего не найдено по вашему запросу</p>
        <?php endif; ?>
        <?php foreach($currentUserTasks as $task): ?>

            <?php if($task['status'] && $show_complete_tasks === 0): ?>
                <?php continue; ?>
            <?php endif; ?>
            <tr class="tasks__item task
            <?php if($task['status']): ?>task--completed <?php endif; ?>
            <?php if(isTaskImportant($task['completion_date'])): ?>task--important <?php endif; ?>">
                <td class="task__select">
                    <label class="checkbox task__checkbox">
                        <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="<?=$task['id']?>"
                               <?php if($task['status']): ?>checked <?php endif; ?>>
                        <span class="checkbox__text"><?=htmlspecialchars($task['name'])?></span>
                    </label>
                </td>

                <td class="task__file">
                    <?php if(!empty($task['file'])): ?>
                        <a class="download-link" href="<?=$task['file']?>"><?=getFileName($task['file'])?></a>
                    <?php endif; ?>
                </td>
                <td class="task__date">
                    <?php if($task['completion_date'] === null): ?>
                    <?php else: ?> <?=htmlspecialchars($task['completion_date'])?>
                    <?php endif?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if($show_complete_tasks === 1): ?>
        <?php endif; ?>
    </table>
</main>


