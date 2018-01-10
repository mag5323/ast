<?php
    require 'include/check.php';
    require __DIR__ . '/../connection.php';

    if (!empty($_POST['subject']) && !empty($_POST['context'])) {

        header('Content-Type: application/json');
        $db = $GLOBALS['db'];
        try {
            $sql = "INSERT INTO news VALUES(null, ?, ?, ". time() . ", " . time() . ", null)";
            $rs = $db->prepare($sql);
            $rs->execute([$_POST['subject'], $_POST['context']]);

            echo json_encode(['status' => 200]);
        } catch (Exception $e) {
            echo json_encode(['status' => 500]);
        }
        exit;
    }
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
                            Post News
                        </h1>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row form-horizontal">
                    <label class="col-md-1 control-label">Subject</label>
                    <div class="col-md-10">
                        <input class="form-control subject">
                    </div><!-- end .col-md-9 -->
                </div<!-- end .row -->
                <br><br><br>
                <div class="row">
                    <div class="col-md-12">
                        <textarea id="editor"></textarea>
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
                    var editorObj;

                    ClassicEditor
                        .create(document.querySelector('#editor'))
                        .then(editor => {
                            editorObj = editor;
                        })
                        .catch( error => {
                            console.error( error );
                        });

                    $('.submit').click(function(e) {
                        e.preventDefault();

                        $.post('/dashboard/create_news.php', {subject: $('.subject').val(), context: editorObj.getData()}).
                            done(function (data) {
                                if (data.status === 200) {
                                    window.location = '/dashboard';
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

