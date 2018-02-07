<?php
require 'connection.php';

$db = $GLOBALS['db'];
$sql = "SELECT YEAR(FROM_UNIXTIME(created_at)) AS year FROM news WHERE deleted_at IS NULL GROUP BY year DESC";
$rs = $db->query($sql);
$years = $rs->fetchAll();

$raw = $db->prepare("SELECT id, subject, created_at FROM news WHERE deleted_at IS NULL AND YEAR(FROM_UNIXTIME(created_at)) = ?");
$y = $_GET['y'] ?: $years[0]['year'];
$raw->execute(array($y));
$newsByYear = $raw->fetchAll();

?><!DOCTYPE HTML>
<HTML>
<head>
<?php
    include __DIR__ . '/requires/header.php';
?>
<TITLE>AS-TECH Welcome :: ニュース</TITLE>
<style>
    #news-container li {
        font-size: 1.2em;
        font-family: monospace;
        padding: 5px;
    }
    #sidebar {
        padding: 0px 20px;
    }
    #sidebar li {
        border-bottom: 1px solid #bbb;
    }
    #sidebar p {
        font-size: 1.4em;
        border-bottom: 2px solid #bbb;
    }
    #news-container li .date {
        margin-right: 60px;
        font-weight: bold;
    }
    #news-container li {
        border-top: 1px dashed #bbb;
    }
    #news-container p {
        font-size: 1.9em;
        border-bottom: 2px solid #bbb;
        margin-bottom: 15px;
    }
    #news-container li a { color: #337ab7; }
    #news-container li a:link {color: #337ab7; }
    #news-container li a:visited {color: #337ab7; }
</style>
</head>
<BODY class="fancycat-page-skin-icons fancycat-page-subskin-e " id="page-container">

<DIV class="container-fluid" ng-app="ui.bootstrap.app">
<?php
    include __DIR__ . '/requires/nav.php';
?>
<DIV class="featured clearfix">
    <div class="col-sm-3">
        <ul id="sidebar">
            <p>アーカイブス</p>
            <?php
            foreach ($years as $year) {
                echo '<li><a href="news.php?y=' . $year['year'] . '">' . $year['year'] . '</a></li>';
            }
            ?>
        </ul>
    </div>
    <div class="col-sm-9" style="border-left: 1px solid #bbb">
        <ul id="news-container">
            <p>プレスリリース</p>
            <?php
            foreach ($newsByYear as $news) {
                $str = '<span class="date">' . date('Y.n.j', $news['created_at']) . '</span>';
                $str .= '<a href="detail.php?id=' . $news['id'] . '">' . $news['subject'] . '</a>';
                echo implode(array('<li>', $str, '</li>'));
            }
            ?>
        </ul>
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
