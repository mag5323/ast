<!DOCTYPE HTML>
<HTML>
<head>
<?php
    include __DIR__ . '/requires/header.php';
?>
<TITLE>AS-TECH Welcome :: Power Supply</TITLE>
</head>
<BODY class="fancycat-page-skin-icons fancycat-page-subskin-e " id="page-container">

<DIV class="container-fluid" ng-app="ui.bootstrap.app">
<?php
    include __DIR__ . '/requires/nav.php';
?>
  <DIV class="featured clearfix">
      <H2 class="ac">製品紹介<br><small>パワーサプライ</small></H2>
    <div class="col-sm-4 col-sm-offset-2">
        <div class="well">
            <img src="files/power-supply/IP65.png" class="center-block" height="75">
            <h5 class="text-center">調節可能　100W / 120W / 150W / 185W</h5>
        </div>
        <div class="list-group">
            <a href="files/power-supply/Datasheet_lne-12v100w調光.pdf" class="list-group-item" target="_blank">
                <span class="glyphicon glyphicon-chevron-right"></span>12V　100V　データシート
            </a>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="well">
            <img src="files/power-supply/IP67.png" class="center-block" height="75">
            <h5 class="text-center">調光可能　100W / 120W / 150W / 185W</h5>
        </div>
        <div class="list-group">
            <a href="files/power-supply/Datasheet_lne-12v100w調節可能.pdf" class="list-group-item" target="_blank">
                <span class="glyphicon glyphicon-chevron-right"></span>12V　100V　データシート
            </a>
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
