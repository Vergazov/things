<div class="content">
    <section class="content__side">
        <h2 class="content__side-heading">Проекты</h2>

        <nav class="main-navigation">
            <ul class="main-navigation__list">
                <?php foreach($currentUserProjects as $project): ?>
                    <li class="main-navigation__list-item <?php if($project['id'] === $projectId): ?>main-navigation__list-item--active<?php endif; ?> ">
                        <a class="main-navigation__list-item-link" href="<?=getAbsolutePath('index.php')?>?id=<?=$project['id']?>"><?=htmlspecialchars($project['name'])?></a>
                        <span class="main-navigation__list-item-count"><?=countTasksForProject($tasksForCount,$project['name'])?></span>
                    </li>
                <?php endforeach;?>
            </ul>
        </nav>

        <a class="button button--transparent button--plus content__side-button" href="<?=getAbsolutePath('add_project.php')?>">Добавить проект</a>
    </section>

    <main class="content__main">
        <h2 class="content__main-heading">Добавление задачи</h2>

        <form class="form"  action="<?=getAbsolutePath('add.php')?>" method="post" autocomplete="off" enctype="multipart/form-data">
            <div class="form__row">
                <?php if(isset($errors['name'])): ?>
                    <p class="form__message"><?=$errors['name']?></p>
                <?php endif; ?>
                <label class="form__label" for="name" >Название <sup>*</sup></label>

                <input class="form__input
                <?php if(isset($errors['name'])): ?> form__input--error <?php endif; ?>"
                       type="text" name="name" id="name" value="<?=getPostVal('name')?>" placeholder="Введите название">
            </div>

            <div class="form__row">
                <?php if(isset($errors['project'])): ?>
                <p class="form__message"><?=$errors['project']?></p>
                <?php endif; ?>
                <label class="form__label" for="project">Проект <sup>*</sup></label>

                <select class="form__input form__input--select
                <?php if(isset($errors['project'])): ?> form__input--error <?php endif; ?>" name="project" id="project">
                    <option value=""></option>
                    <?php foreach($currentUserProjects as $project): ?>
                        <option value="<?=$project['id']?>"
                            <?php if(getPostVal('project') == $project['id']): ?> selected <?php endif; ?> ><?=$project['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__row">
                <?php if(isset($errors['date'])): ?>
                    <p class="form__message"><?=$errors['date']?></p>
                <?php endif; ?>
                <label class="form__label" for="date">Дата выполнения</label>

                <input class="form__input form__input--date
                <?php if(isset($errors['date'])): ?> form__input--error <?php endif; ?>" type="text" name="date" id="date" value="<?=getPostVal('date')?>" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
            </div>

            <div class="form__row">
                <label class="form__label" for="file">Файл</label>
                <div class="form__input-file">
                    <input class="visually-hidden" type="file" name="file" id="file" value="">

                    <label class="button button--transparent" for="file">
                        <span>Выберите файл</span>
                    </label>
                </div>
            </div>

            <div class="form__row form__row--controls">
                <input class="button" type="submit" name="" value="Добавить">
            </div>
        </form>
    </main>
</div>
