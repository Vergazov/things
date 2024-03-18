<?php

/**
 * Проверяет чтобы поля были заполнены. Удаляет пробелы из начала и конца строки
 * @param string $key Имя ключа в $_POST массиве, по которому получаем значение проверяемого поля
 * @return bool Если поле не заполнено возвращает false, если заполнено возвращает true
 */
function isFilled(string $key):bool
{
    $fieldForCheck = $_POST[$key];
    $fieldForCheck = trim($fieldForCheck);

    if (empty($fieldForCheck)) {
        return false;
    }

    return true;
}

/**
 * Проверяет почту в базе на дубликат
 * @param mysqli $con Ресурс соединения
 * @param string $name Имя ключа в $_POST массиве, по которому получаем название почты
 * @return bool Если введенная пользователем почта уже существует в базе - возвращает true, если такой почты нет - возвращает false
 */
function isEmailExists(mysqli $con, string $name): bool
{
    $emailFromForm = $_POST[$name];
    $emailFromBase = getCurrentUserData($con, $emailFromForm, getQueryIsEmailExists());
    if (empty($emailFromBase)) {
        return false;
    }
    return true;
}

/**
 * Проверяет email на соответствие формату
 * @param string $key Имя ключа в $_POST массиве, по которому получаем название почты
 * @return bool Если введенная почта некорректна - возвращает false, если корректна - возвращает true
 */
function isEmailValid(string $key): bool
{
    $email = $_POST[$key];
    $validate = filter_var($email, FILTER_VALIDATE_EMAIL);
    if ($validate === false) {
        return false;
    }
    return true;
}

/**
 * Проверяет существует ли такой проект у текущего пользователя. Осуществляется по имени проекта
 * @param mysqli $con Ресурс соединения с БД
 * @param string $key Имя ключа в $_POST массиве, по которому получаем значение названия проекта
 * @param string $currentUserId id текущего пользователя
 * @return bool Если введенный пользователем проект уже есть в базе - возвращает true, если такого проекта нет - возвращает false
 */
function isProjExistsByName(mysqli $con, string $key, string $currentUserId): bool
{
    $projectName = $_POST[$key];
    $project = getCurrentUserData($con, [$projectName, $currentUserId], getQueryIsProjExistsByName());
    if (empty($project)) {
        return false;
    }
    return true;
}

/**
 * Проверяет существует ли такой проект у текущего пользователя. Осуществляется по id проекта
 * @param mysqli $con Ресурс соединения с БД
 * @param string $key Имя ключа в $_POST массиве, по которому получаем значение id проекта.
 * @param string $currentUserId id текущего пользователя
 * @return bool Если введенный пользователем проект уже есть в базе - возвращает true, если такого проекта нет - возвращает false
 */
function isProjExistsById(mysqli $con, string $key, string $currentUserId): bool
{
    $projectId = $_POST[$key];
    $project = getCurrentUserData($con, [$projectId, $currentUserId], getQueryIsProjExists());
    if (empty($project)) {
        return false;
    }
    return true;
}

/**
 * Проверяет существует ли такой проект у текущего пользователя. Осуществляется по id проекта
 * @param mysqli $con Ресурс соединения с БД
 * @param string $projectId id проекта
 * @param string $currentUserId id текущего пользователя
 * @return bool Если проект уже есть в базе - возвращает true, если такого проекта нет - возвращает false
 */
function isProjExistsByIdForFilter(mysqli $con, string $projectId, string $currentUserId): bool
{
    $project = getCurrentUserData($con, [$projectId, $currentUserId], getQueryIsProjExists());
    if (empty($project)) {
        return false;
    }
    return true;
}

/**
 * Проверка поля с датой на соответствие формату ГГГГ-ММ-ДД
 * @param string $key Имя ключа в $_POST массиве, по которому получаем значение даты
 * @return bool|null
 * Если введенная дата не соответствует формату возвращает false.
 * Если соответствует, возвращает true.
 * Если дата не была передана возвращает null.
 */
function isDateFormatValid(string $key): bool|null
{
    $format = 'Y-m-d';
    $date = $_POST[$key];

    if ($date === '') {
        return null;
    }

    $rightFormatDate = DateTime::createFromFormat($format, $date);
    if ($rightFormatDate === false) {
        return false;
    }

    return true;
}

/**
 * Проверка даты на соответствие условия. Дата должна быть >= текущей
 * @param string $key : Имя ключа в $_POST массиве, по которому получаем значение даты
 * @return bool|null
 * Если выбранная дата меньше текущей , возвращает false.
 * Если выбранная дата больше либо равна текущей (то есть проверка пройдена), возвращает true.
 * Если дата не была передана, возвращает null.
 */
function isDateRangeValid(string $key): bool|null
{
    $format = 'Y-m-d';
    $date = $_POST[$key];

    if ($date === '') {
        return null;
    }

    $curDate = date($format);
    if (strtotime($date) < strtotime($curDate)) {
        return false;
    }

    return true;

}


