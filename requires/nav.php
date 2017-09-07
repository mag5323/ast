<nav id="header" class="navbar" ng-controller="ContactCtrl">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.php"><img src="images/astech.png"></a>
    </div>
    <DIV id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
            <LI><A href="Profile.php">会社紹介</A></LI>
            <LI class="dropdown">
                <A href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    製品紹介<span class="caret"></span>
                </A>
                <ul id="dropdown-products" class="dropdown-menu">
                    <li><a href="LED-lamps.php">LED Lamps</a></li>
                    <li><a href="SMD-LED.php">SMD LED</a></li>
                    <li><a href="LED-light.php">LED 照明</a></li>
                    <li><a href="LED-bulb.php">LED 電球</a></li>
                </ul>
            </LI>                                                                                     <LI><a href="#" ng-click="open()">お問い合わせ</a></LI>
        </ul>
    </DIV>
</nav>

<script type="text/ng-template" id="contactModalContent.html">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="dialog" ng-click="cancel($event)">
            <span aria-hidden="true">&times;</span>
            <span class="sr-only">Close</span>
        </button>
        <h3 class="modal-title" id="modal-title">Contact Us</h3>
    </div>
    <form name="contact-form" ng-submit="submit()">
        <div class="modal-body" id="modal-body">
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" ng-model="contact.first_name" required="required" />
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="lastname" ng-model="contact.last_name" required="required" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="job_title">Job Title</label>
                        <input type="text" class="form-control" name="job_title" ng-model="contact.job_title" id="job_title"/ >
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="company">Company</label>
                        <input type="text" class="form-control" id="company" name="company" ng-model="contact.company" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" ng-model="contact.email" required="required" />
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="phone">Telephone</label>
                        <input type="text" class="form-control" id="phone" name="phone" ng-model="contact.phone" required="required"  />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <label for="comment">Questions or Comments</label>
                        <textarea class="form-control" id="comment" name="comment" rows="6" ng-model="contact.comment" required="required"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" class="btn btn-primary" ng-value="submitVal" ng-disabled="submitting"/>
        </div>
    </form>
</script>
