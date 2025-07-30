<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();
session_unset();
session_destroy();

header("Location: index.php");
exit();
?>