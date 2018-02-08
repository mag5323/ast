<?php
    require 'include/check.php';
    require __DIR__ . '/../connection.php';

    $db = $GLOBALS['db'];

    // POST
    if (!empty($_POST['context'])) {
        header('Content-Type: application/json');
        try {
            $sql = "UPDATE info SET context = ? WHERE field_name = 'led_lamps'";
            $rs = $db->prepare($sql);
            $rs->execute([$_POST['context']]);

            echo json_encode(['status' => 200]);
        } catch (Exception $e) {
            echo json_encode(['status' => 500]);
        }
        exit;
    }

    $id = intval($_GET['id']);
    $sql = "SELECT * FROM info WHERE field_name = ?";
    $sth = $db->prepare($sql);
    $sth->execute(['led_lamps']);
    $raws = $sth->fetchAll();
    if (empty($raws)) {
        header('Location: /dashboard/index.php');
        exit;
    }

    $raw = $raws[0];

?><!DOCTYPE html>
<html>
<?php include 'include/header.php'; ?>
<body>
    <div id="wrapper">
        <!-- Navigation -->
<?php include 'include/nav.php'; ?>
        <div id="page-wrapper">
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="row">
                    <div class="col-md-12">
                        <h1 class="page-header">
                            製品紹介 LED Lamps
                        </h1>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row">
                    <div class="col-md-12">
                        <textarea id="editor" name="editor" data-id="<?php echo $raw['id']; ?>"><?php echo $raw['context']; ?></textarea>
                    </div><!-- end .col-md-12-->
                </div><!-- end .row -->
                <div class="row">
                    <div class="col-md-12">
                        <div id="message" class="form-group text-center"></div>
                    </div><!-- end .col-md-12 -->
                </div><!-- end .row-->
                <hr>
                <div class="row">
                    <div class="col-md-12 text-center"><button class="btn btn-primary submit">Submit</button></div><!-- end .col-md-12-->
                </div><!-- end .row -->
            </div><!-- end .container-fluid -->
            <script>
                $(function() {
                    var editorObj = CKEDITOR.replace('editor');
                    CKFinder.setupCKEditor();

                    $('.submit').click(function(e) {
                        e.preventDefault();

                        $.post('/dashboard/led_lamps.php', {context: editorObj.getData()}).
                            done(function (data) {
                                if (data.status === 200) {
                                    window.location = '/dashboard/led_lamps.php';
                                } else {
                                    $('#message').html('<div class="alert alert-danger">Post Faild</div>');
                                }
                            });
                    });
                });
            </script>
        </div><!-- /#page-wrapper -->
    </div><!-- /#wrapper -->
</body>
</html>

