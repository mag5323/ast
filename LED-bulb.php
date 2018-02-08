<?php
require 'connection.php';

$db = $GLOBALS['db'];
$sql = "SELECT * FROM info WHERE field_name IN ('led_bulb')";
$rs = $db->query($sql);
$raws = $rs->fetchAll();

$arr = [];
foreach ($raws as $raw) {
    $arr[$raw['field_name']] = $raw['context'];
}

?><!DOCTYPE HTML>
<HTML>
<head>
<?php
    include __DIR__ . '/requires/header.php';
?>
<TITLE>AS-TECH Welcome :: LED 電球</TITLE>
</head>
<BODY class="fancycat-page-skin-icons fancycat-page-subskin-e " id="page-container">

<DIV class="container-fluid" ng-app="ui.bootstrap.app">
<?php
    include __DIR__ . '/requires/nav.php';                                                 ?>

  <DIV class="featured clearfix">
      <H2 class="ac">製品紹介<br><small>LED 電球</small></H2>
      <div class="col-sm-10 col-sm-offset-1">
      <?php
          printf('<iframe src="data:text/html;charset=utf-8,%s"><p>Sorry, this browser does not support iframes.</p></iframe>', htmlentities($arr['led_bulb']));
      ?>
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
