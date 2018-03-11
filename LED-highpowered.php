<?php
require 'connection.php';

$db = $GLOBALS['db'];
$sql = "SELECT * FROM info WHERE field_name IN ('led_highpowered')";
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
<TITLE>AS-TECH Welcome :: ハイパワーLED</TITLE>
</head>
<BODY class="fancycat-page-skin-icons fancycat-page-subskin-e " id="page-container">

<DIV class="container-fluid" ng-app="ui.bootstrap.app">
<?php
    include __DIR__ . '/requires/nav.php';
?>

  <DIV class="featured clearfix high-power">
    <H2 class="ac">製品紹介<br><small>ハイパワーLED</small></H2>
    <div class="col-sm-10 col-sm-offset-1">
        <iframe id="frame" data-content="<?php echo htmlentities($arr['led_highpowered']); ?>"></iframe>
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
