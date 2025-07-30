<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../includes/functions.php';

session_start();
session_unset();
session_destroy();

header("Location: /gaming_hub/");
exit();
?>