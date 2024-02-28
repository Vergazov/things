<?php
session_start();
require_once 'functions/db.php';
require_once 'functions/template.php';
require_once 'functions/validators.php';

$_SESSION = [];
header("Location:" . getAbsolutePath('index.php'));
