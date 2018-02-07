<?php
require 'connection.php';

$db = $GLOBALS['db'];
$raw = $db->prepare("SELECT id, subject, context, created_at FROM news WHERE deleted_at IS NULL AND id = ?");
$raw->execute(array($_GET['id']));
$detail = $raw->fetchAll();

?><!DOCTYPE HTML>
<HTML>
<head>
<style>
#container ol, #container ul {
    list-style: none;
}
#container table {
    border-collapse: collapse;
    border-spacing: 0;
}
#container hr {
    margin-top: 20px;
    margin-bottom: 20px;
    border: 0;
    border-top: 1px solid #eee;
}
#container p, #container span {
    font-size: 1.3em;
}
</style>
<?php
    include __DIR__ . '/requires/header.php';
?>
<TITLE>AS-TECH Welcome :: ニュース</TITLE>
</head>
<BODY class="fancycat-page-skin-icons fancycat-page-subskin-e " id="page-container">

<DIV class="container-fluid" ng-app="ui.bootstrap.app">
<?php
    include __DIR__ . '/requires/nav.php';
?>
<DIV class="featured clearfix">
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1" id="container">
            <div class="page-header">
                <h3>
                    <?php
                        echo empty($detail) ? 'News not found.' : $detail[0]['subject'] .  ' <small>' . date('Y.n.j', $detail[0]['created_at']) . '</small>';
                    ?>
                </h3>
            </div>
            <?php
                printf('<iframe src="data:text/html;charset=utf-8,%s"><p>Sorry, this browser does not support iframes.</p></iframe>', htmlentities($detail[0]['context']));
            ?>
        </div>
    </div>
</DIV>
<?php
    include __DIR__ . '/requires/footer.php';
?>
</DIV>
<?php
    include __DIR__ . '/requires/scripts.php';
?>
</BODY>
</HTML>
