<?php
$dsn = "mysql:host=localhost;dbname=pdo;";
$db = new PDO($dsn,"db_account","db_passwd",array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
