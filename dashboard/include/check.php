<?php
session_start();

if (empty($_SESSION['login'])) {
    header('Location: /dashboard/login.php');
    exit;
}
