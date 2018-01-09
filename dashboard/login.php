<!DOCTYPE HTML>
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
                                    <input id="account" class="form-control" placeholder="Account">
                                </div>
                                <div class="form-group">
                                    <input type="password" id="password" class="form-control" placeholder="Password">
                                </div>
                                <button id="submit" class="btn btn-info">Login</button>
                            </form>
                        </div>
                    </div><!-- end .login-form -->
                    <div class="row">
                        <div class="col-md-12">
                            <div id="message" class="form-group text-center"></div>
                        </div><!-- end .col-md-12 -->
                    </div><!-- end .row-->
                </div><!-- end .login-wrapper -->
            </div><!-- end .login -->
        </div><!-- end #doc -->
    </body>
</html>
