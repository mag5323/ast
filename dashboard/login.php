<?php
require __DIR__ . '/../connection.php';

session_start();
$_SESSION['message'] = '';
if (!empty($_POST['account'])) {

    $db = $GLOBALS['db'];
    $rs = $db->query("SELECT * FROM admin");
    $accountInfo = ($rs->fetchAll())[0];

    if ($accountInfo['account'] === $_POST['account'] && $accountInfo['password'] === md5($_POST['password'])) {
        $_SESSION['login'] = true;

        header('Location: /dashboard');
        exit;
    }

    $_SESSION['message'] = '<div class="alert alert-danger">Login Faild</div>';
}

?><!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <title>Dashboard</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/font-awesome.min.css" rel="stylesheet">
        <link href="css/login.css" rel="stylesheet">
        <script src="js/jquery.min.js"></script>
    </head>
    <body>
        <div id="doc">
            <div class="login container">
                <div class="login-wrapper">
                    <div class="login-title">
                        <h1>Dashboard</h1>
                    </div><!-- end .login-title -->
                    <div class="login-form">
                        <div class="login-header">
                            <i class="fa fa-user"></i>
                        </div>
                        <div class="login-body">
                            <form method="post">
                                <div class="form-group">
                                    <input id="account" name="account" class="form-control" placeholder="Account">
                                </div>
                                <div class="form-group">
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                                </div>
                                <button id="submit" class="btn btn-info">Login</button>
                            </form>
                        </div>
                    </div><!-- end .login-form -->
                    <div class="row">
                        <div class="col-md-12">
                            <div id="message" class="form-group text-center"><?php echo $_SESSION['message']; ?></div>
                        </div><!-- end .col-md-12 -->
                    </div><!-- end .row-->
                </div><!-- end .login-wrapper -->
            </div><!-- end .login -->
        </div><!-- end #doc -->
    </body>
</html>
