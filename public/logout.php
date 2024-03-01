<?php
session_start();
require_once '../functions/template.php';
$_SESSION = [];
header("Location:" . getAbsolutePath('index.php'));
