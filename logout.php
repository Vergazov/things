<?php
session_start();
require_once 'functions/helpers.php';

$_SESSION = [];
header("Location:" . getAbsolutePath('index.php'));
