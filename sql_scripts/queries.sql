# Удаление базы данных
# DROP DATABASE things_are_fine;

# Добавление пользователей
#
# INSERT INTO things_are_fine.users (reg_date, email, name, password)
# VALUES ('2020-12-29 15:43:10', 'saharov@mail.ru','Илья','qweasd111'),
# ('2020-12-29 15:43:10', 'dima@mail.ru','Дмитрий','qweasd222');

# Удаление пользователей
# DELETE FROM things_are_fine.users;

# Добавление проектов

# INSERT INTO things_are_fine.projects(name, user_id)
# VALUES ('Входящие',1),
# ('Учеба',1),
# ('Работа',1),
# ('Домашние дела',1),
# ('Авто',2);

# Удаление проектов
# DELETE FROM things_are_fine.projects;

# Добавление задач
#
# INSERT INTO things_are_fine.tasks(creation_date, status, name, file, completion_date, user_id, project_id)
# VALUES ('2020-12-29 15:43:10',0,'Собеседование в IT компании','data/jpeg/file1','2023-12-30',1,3),
# ('2020-12-29 15:43:10',0,'Выполнить тестовое задание','data/jpeg/file2','2023-12-30',1,3),
# ('2020-12-29 15:43:10',1,'Сделать задание первого раздела','data/jpeg/file3','2023-12-31',1,2),
# ('2020-12-29 15:43:10',0,'Встреча с другом','data/jpeg/file4','2023-12-31',1,1),
# ('2020-12-29 15:43:10',0,'Купить корм для кота','data/jpeg/file5','2023-12-29',1,4),
# ('2020-12-29 15:43:10',0,'Заказать пиццу','data/jpeg/file6','2023-12-29',1,4);

# Удаление задач
# DELETE FROM things_are_fine.tasks;

# Получить список из всех проектов для одного пользователя
# SELECT projects.name from things_are_fine.projects
#     JOIN things_are_fine.users
#         ON projects.user_id = users.id
# WHERE users.name = 'Илья';

# Получить список из всех задач для одного проекта
# SELECT tasks.name from things_are_fine.tasks
#     JOIN things_are_fine.projects
#         ON tasks.project_id = projects.id
# WHERE projects.name = 'Работа';

# Пометить задачу как выполненную
# UPDATE things_are_fine.tasks
#     SET status = 1
# WHERE tasks.id = 1;

# Обновить название задачи по её идентификатору
# UPDATE things_are_fine.tasks
# SET name = 'Встреча с друзьями'
# WHERE tasks.id = 4;