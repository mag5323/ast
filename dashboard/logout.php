<?php
require __DIR__ . '/../connection.php';

session_start();
session_destroy();

header('Location: /dashboard/login.php');
exit;
