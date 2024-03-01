<div class="content">
    <section class="content__side">
        <h2 class="content__side-heading">Проекты</h2>

        <nav class="main-navigation">
            <ul class="main-navigation__list">
                <?php foreach ($currentUserProjects as $project): ?>
                    <li class="main-navigation__list-item <?php if ($project['id'] === $projectId): ?>main-navigation__list-item--active<?php endif; ?> ">
                        <a class="main-navigation__list-item-link"
                           href="<?= getAbsolutePath('index.php') ?>?id=<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></a>
                        <span class="main-navigation__list-item-count"><?= countTasksForProject($tasksForCount,
                                $project['name']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <a class="button button--transparent button--plus content__side-button"
           href="<?= getAbsolutePath('add_project.php') ?>">Добавить проект</a>
    </section>

    <main class="content__main">
        <h2 class="content__main-heading">Добавление проекта</h2>

        <form class="form" action="<?= getAbsolutePath('add_project.php') ?>" method="post" autocomplete="off">
            <div class="form__row">
                <?php if (isset($errors['project_name'])): ?>
                    <p class="form__message"><?= $errors['project_name'] ?></p>
                <?php endif; ?>
                <label class="form__label" for="project_name">Название <sup>*</sup></label>

                <input class="form__input
                        <?php if (isset($errors['project_name'])): ?> form__input--error <?php endif; ?>"
                       type="text" name="project_name" id="project_name" value="<?=getPostVal('project_name')?>"
                       placeholder="Введите название проекта">
            </div>

            <div class="form__row form__row--controls">
                <input class="button" type="submit" name="" value="Добавить">
            </div>
        </form>
    </main>
</div>