<?php
    require 'include/check.php';
    require __DIR__ . '/../connection.php';

    $db = $GLOBALS['db'];
    $page = (empty(intval($_GET['page']))) ? (1) : (intval($_GET['page']));
    $limit = 30;
    $seq = ($page - 1) * $limit;
    $rs = $db->query("SELECT id FROM news");
    $count = $rs->rowCount();
    $totalPage = ($count % $limit > 0) ? ($count / $limit + 1) : ($count / $limit);

    $sql = "SELECT id, subject, created_at FROM news WHERE deleted_at is NULL ORDER BY created_at DESC LIMIT $seq, $limit";
    $sth = $db->prepare($sql);
    $sth->execute();

    $raws = $sth->fetchAll();
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

                <div class="row">
                    <div class="col-md-12">
                        <a class="btn btn-info" href="create_news.php">Create</a><hr>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="col-md-2">Date</th>
                                        <th class="col-md-8">Subject</th>
                                        <th class="col-md-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    foreach ($raws as $raw) {
                                        echo '<tr>';
                                        echo "<td>" . date('Y-m-d', $raw['created_at']) . "</td>";
                                        echo '<td><a href="news.php?id=' . $raw['id'] . '">' . htmlspecialchars($raw['subject']) . "</a></td>";
                                        echo '<td class="text-center"><a class="btn btn-danger del" data-id="' . $raw['id'] . '" href="#/">Delete</a></td>';
                                        echo '</tr>';
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div><!-- end .table-responsive -->
                    </div><!-- end .col-md-12-->
                </div><!-- end .row -->
                <nav aria-label="Page navigation example">
                    <ul class="pagination">
                        <?php
                            for ($i = 1; $i <= $totalPage; $i++) {
                                echo '<li class="page-item"><a class="page-link" href="/dashboard/index.php?page=' . $i . '">' . $i . '</a></li>';
                            }
                        ?>
                    </ul>
                </nav>
            </div><!-- end .container-fluid -->
        </div><!-- /#page-wrapper -->
    </div><!-- /#wrapper -->
    <script>
        $(function() {
            $('.del').click(function(e) {
                if (confirm('Delete this post ?')) {
                    var id = $(this).data('id');
                    window.location = '/dashboard/del_news.php?id=' + id;
                }
            });
        });
    </script>
</body>
</html>
