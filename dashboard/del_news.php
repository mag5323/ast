<?php
require 'include/check.php';
require __DIR__ . '/../connection.php';

$db = $GLOBALS['db'];
$id = intval($_GET['id']);

if (!empty($id)) {
    try {
        $sql = "UPDATE news SET deleted_at = ? WHERE id = ?";
        $sth = $db->prepare($sql);
        $sth->execute([time(), $id]);
    } catch (Exception $e) {
        //
    }
}

header('Location: /dashboard/index.php');;
exit;
