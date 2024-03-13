<?php
session_start();

require_once 'vendor/autoload.php';
require_once 'functions/db.php';
require_once 'functions/template.php';
require_once 'functions/validators.php';

$config = require('config.php');

$con = dbConnection($config);

$titleName = 'Дела в порядке';