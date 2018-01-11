<?php
require 'connection.php';

$db = $GLOBALS['db'];
$sql = "SELECT * FROM info WHERE field_name IN ('story_info', 'story', 'technology', 'design', 'oem_service', 'oem_service2', 'about')";
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
<TITLE>AS-TECH Welcome :: Profile</TITLE>
</head>
<BODY class="fancycat-page-skin-icons fancycat-page-subskin-e " id="page-container">

<DIV class="container-fluid" id="profile-container" ng-app="ui.bootstrap.app">
<?php
    include __DIR__ . '/requires/nav.php';
?>

  <DIV id="contact-banner"> <IMG src="images/Profile-banner.png"> </DIV>
  <DIV class="our-history">
    <H2 class="ac">私たちの歴史</H2>
    <DIV class="row">
      <DIV class="col-sm-6">
        <?php echo $arr['story_info']; ?>
      </DIV>
      <DIV class="col-sm-6">
        <?php echo $arr['story']; ?>
      </DIV>
    </DIV>
  </DIV>
  <div class="contact-banner"></div>
  <DIV class="contact-main profile-main">
    <DIV class="row">
      <DIV class="col-sm-6"><img src="images/LEDTECH.png" height="380"></DIV>
      <DIV class="col-sm-6">
        <DIV class="contact-box">
          <H1 id="except">最先端の技術</H1>
            <?php echo $arr['technology']; ?>
        </DIV>
      </DIV>
    </DIV>
  <div class="contact-banner"></div>
    <DIV class="row">
      <DIV class="col-sm-6">
        <DIV class="contact-box">
          <H1>設計支援</H1>
          <?php echo $arr['design']; ?>
        </DIV>
      </DIV>
      <DIV class="col-sm-6"><img src="images/SERVICE.PNG" height="380"></DIV>
    </DIV>
  <div class="contact-banner"></div>
    <DIV class="row">
      <DIV class="col-sm-6"><img src="images/CUSTOMER.png" height="380"></DIV>
      <DIV class="col-sm-6">
        <DIV class="contact-box">
          <H1> OEMサービス</H1>
            <?php echo $arr['oem_service']; ?>
        </DIV>
      </DIV>
    </DIV>
  <div class="contact-banner"></div>
    <DIV class="row">
      <DIV class="col-sm-6">
        <DIV class="contact-box">
          <H1> ODMサービス</H1>
            <?php echo $arr['oem_service2']; ?>
        </DIV>
      </DIV>
      <DIV class="col-sm-6"> <img src="images/ASSEMBLY.png" height="380"></DIV>
    </DIV>
    <DIV class="profile-mini-banner-05">
      <H1>エー・エス・テックについて</H1>
        <?php echo $arr['about']; ?>
    </DIV>
  </DIV>
  </SECTION>
<?php
    include __DIR__ . '/requires/footer.php';
?>
</DIV>
<?php
    include __DIR__ . '/requires/scripts.php';
?>
</BODY>
</HTML>
