<?php

/**
 * Проверка существует ли проект. Осуществляется по id проекта в базе
 * @param $con . Ресурс соединения с БД
 * @param $name . Название ключа в $_POST массиве, по которому будем искать id проекта
 * @return string|null Возвращает либо ошибку о том что проекта нету либо null если проект существует
 */
function isProjExists($con, $name, $currentUserId): string|null
{
    if (!empty($_POST[$name])) {
        $projectId = $_POST[$name];
    } else {
        $projectId = $name;
    }
    $project = getCurrentUserData($con, [$projectId, $currentUserId], getQueryIsProjExists());
    if (empty($project)) {
        return 'Такого проекта не существует';
    }
    return null;
}

/**
 * Проверка существует ли проект. Осуществляется по имени проекта в базе
 * @param $con . Ресурс соединения с БД
 * @param $name . Название ключа в $_POST массиве, по которому будем искать имя проекта
 * @return string|null Возвращает либо ошибку о том что проекта нету либо null если проект существует
 */
function isProjExistsByName($con, $name, $userId): string|null
{
    $projectName = $_POST[$name];
    $project = getCurrentUserData($con, [$projectName, $userId], getQueryIsProjExistsByName());
    if (!empty($project)) {
        return 'Проект с таким названием уже есть';
    }
    return null;
}

/**
 * Проверяет почту в базе на дубликат
 * @param $con
 * @param $name
 * @return string|null
 * Если находит в базе почту идентичную той что ввел пользователь, то возвращает текст ошибки.
 * В противном случае возвращает null
 */
function isEmailExists($con, $name): string|null
{
    $emailFromForm = $_POST[$name];
    $emailFromBase = getCurrentUserData($con, $emailFromForm, getQueryIsEmailExists());
    if (!empty($emailFromBase)) {
        return 'Указанный почтовый ящик уже занят';
    }
    return null;
}

/**
 * Проверяет при аутентификации, существует ли такая почта в базе данных
 * @param $con . Ресурс соединения с БД
 * @param $name . Название ключа в $_POST массиве, по которому будем искать почту
 * @return bool Если находит почту, возвращает true, если нет - false
 */
function isEmailExistsForAuth($con, $name):bool
{
    $emailForCheck = $_POST[$name];
    $emailFromBase = getCurrentUserData($con, $emailForCheck, getQueryIsEmailExists());
    if (empty($emailFromBase)) {
        return false;
    }
    return true;
}

/**
 * Проверяет чтобы поля были заполнены, так же удаляет лишние пробелы из начала и конца строки
 * @param $key . Название ключа в $_POST массиве, по которому будем искать проверяемое поле
 * @return bool Если проверяемое по ключу в $_POST массиве поле пустое, то возвращает false. Иначе, возвращает true
 */
function validateFilled($key):bool
{
    $fieldForCheck = $_POST[$key];
    $fieldForCheck = trim($fieldForCheck);

    if (empty($fieldForCheck)) {
        return false;
    }

    return true;
}

/**
 * Проверяет email на соответствие формату
 * @param $name
 * @return string|null
 * Возвращает сообщение об ошибке если валидация не пройдена, если пройдена возвращает null
 */
function validateEmailFormat($name): null|string
{
    $email = $_POST[$name];
    $validate = filter_var($email, FILTER_VALIDATE_EMAIL);
    if ($validate === false) {
        return 'Поле Email не соответствует формату';
    }
    return null;
}

/**
 * Проверка поля с датой на соответствие формату ГГГГ-ММ-ДД
 * @param $name . Название ключа в $_POST массиве, по которому будем искать дату
 * @return string|null
 * Возвращает null если формат даты верный, либо если дата не была указана при создании задачи
 * Если формат неверный, возвращает сообщение об ошибке
 */
function validateDateFormat($name): string|null
{
    $format = 'Y-m-d';
    $date = $_POST[$name];

    if ($date === '') {
        return null;
    }

    $rightFormatDate = DateTime::createFromFormat($format, $date);
    if ($rightFormatDate === false) {
        return 'Введенная дата не соответствует формату ГГГГ-ММ-ДД';
    }

    return null;
}

/**
 * Проверка даты на соответствие условия. Дата должна быть >= текущей
 * @param $name . Название ключа в $_POST массиве, по которому будем искать дату
 * @return string|null Если дата не соответствует условию, выводит ошибку.
 */
function validateDateRange($name): null|string
{
    $format = 'Y-m-d';
    $date = $_POST[$name];

    if ($date === '') {
        return null;
    }

    $curDate = date($format);
    if (strtotime($date) < strtotime($curDate)) {
        return 'Дата выполнения должна быть больше или равна текущей';
    }

    return null;
}


