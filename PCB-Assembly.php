<?php
require 'connection.php';

$db = $GLOBALS['db'];
$sql = "SELECT * FROM info WHERE field_name IN ('pcb_assembly')";
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
<TITLE>AS-TECH Welcome :: PCB Assembly</TITLE>
</head>
<BODY class="fancycat-page-skin-icons fancycat-page-subskin-e " id="page-container">

<DIV class="container-fluid" ng-app="ui.bootstrap.app">
<?php
    include __DIR__ . '/requires/nav.php';                                                ?>

  <DIV class="featured clearfix">
      <H2 class="ac">製品紹介<br><small>PCB Assembly</small></H2>
    <div class="col-sm-3 col-sm-offset-1">
        <img src="files/PCB_Assembly_picture.png" class="img-responsive">
    </div>
    <div class="col-sm-7">
        <div class="assembly">
			<?php echo $arr['pcb_assembly']; ?>
        </div>
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
