<?php
    require 'include/check.php';
    require __DIR__ . '/../connection.php';

    $db = $GLOBALS['db'];

    // POST
    if (!empty(intval($_POST['id'])) && !empty($_POST['subject']) && !empty($_POST['context'])) {
        header('Content-Type: application/json');
        try {
            $sql = "UPDATE news SET subject = ?, context = ? WHERE id = ?";
            $rs = $db->prepare($sql);
            $rs->execute([$_POST['subject'], $_POST['context'], intval($_POST['id'])]);

            echo json_encode(['status' => 200]);
        } catch (Exception $e) {
            echo json_encode(['status' => 500]);
        }
        exit;
    }

    $id = intval($_GET['id']);
    $sql = "SELECT * FROM news WHERE id = ?";
    $sth = $db->prepare($sql);
    $sth->execute([$id]);
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
                            News
                        </h1>
                    </div>
                </div>
                <!-- /.row -->

                <div class="row form-horizontal">
                    <label class="col-md-1 control-label">Subject</label>
                    <div class="col-md-10">
                        <input class="form-control subject" value="<?php echo $raw['subject']; ?>">
                    </div><!-- end .col-md-9 -->
                </div<!-- end .row -->
                <br><br><br>
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
                    var editorObj;
                    CKFinder.setupCKEditor();
                    editorObj = CKEDITOR.replace('editor', {
                        toolbarGroups: [
							{name: 'basicstyles', groups: ['basicstyles']},
							{name: 'links', groups: ['links']},
							{name: 'paragraph', groups: ['list', 'blocks']},
							{name: 'document', groups: ['mode']},
							{name: 'insert', groups: ['insert']},
							{name: 'styles', groups: ['styles']},
                        ],
                        language: 'ja',
                    });


                    $('.submit').click(function(e) {
                        e.preventDefault();

                        var id = $('#editor').data('id');
                        $.post('/dashboard/news.php', {id: id, subject: $('.subject').val(), context: editorObj.getData()}).
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

