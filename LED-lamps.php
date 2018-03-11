<?php
require 'connection.php';

$db = $GLOBALS['db'];
$sql = "SELECT * FROM info WHERE field_name IN ('led_lamps')";
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
<TITLE>AS-TECH Welcome :: LED Lamps</TITLE>
<style>
.table-bordered > thead > tr > th {
    text-align: center;
    font-size:  1.1em;
}

td > strong {
    font-size: 1.1em;
}
</style>
</head>
<BODY class="fancycat-page-skin-icons fancycat-page-subskin-e " id="page-container">

<div class="container-fluid" ng-app="ui.bootstrap.app">
<?php
    include __DIR__ . '/requires/nav.php';
?>

  <DIV class="featured clearfix">
      <H2 class="ac">製品紹介<br><small>LED Lamps</small></H2>
      <iframe id="frame" data-content="<?php echo htmlentities($arr['led_lamps']); ?>"></iframe>
  </DIV>
<?php
	include __DIR__ . '/requires/footer.php';
?>
</div>
<?php
	include __DIR__ . '/requires/scripts.php';
?>
</BODY>
</HTML>
