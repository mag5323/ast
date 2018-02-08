        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/dashboard">Dashboard</a>
            </div>
            <ul class="nav navbar-right top-nav">
                <li><a class="navbar-link" href="#"><i class="fa fa-user"></i> admin </a></li>
            </ul>
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav navbar-sidenav" id="accordion">
                    <li>
                        <a href="/"><i class="fa fa-fw fa-dashboard"></i> WebSite</a>
                    </li>
                    <li>
                        <a href="/dashboard"><i class="fa fa-fw fa-dashboard"></i> News</a>
                    </li>
                    <li class="nav-item" data-toggle="tooltip" data-placement="right" title="会社紹介">
                        <a class="nav-link nav-link-collapse collapsed" data-toggle="collapse" href="#collapseComponents" data-parent="#accordion">
                            <i class="fa fa-fw fa-wrench"></i>
                            <span class="nav-link-text">会社紹介</span>
                        </a>
                        <ul class="sidenav-second-level collapse" id="collapseComponents">
                            <li>
                                <a href="/dashboard/story_info.php"><i class="fa fa-fw fa-dashboard"></i> 私たちの歴史_info</a>
                            </li>
                            <li>
                                <a href="/dashboard/story.php"><i class="fa fa-fw fa-dashboard"></i> 私たちの歴史</a>
                            </li>
                            <li>
                                <a href="/dashboard/technology.php"><i class="fa fa-fw fa-dashboard"></i> 最先端の技術</a>
                            </li>
                            <li>
                                <a href="/dashboard/design.php"><i class="fa fa-fw fa-dashboard"></i> 設計支援</a>
                            </li>
                            <li>
                                <a href="/dashboard/oem_service.php"><i class="fa fa-fw fa-dashboard"></i> OEMサービス</a>
                            </li>
                            <li>
                                <a href="/dashboard/oem_service2.php"><i class="fa fa-fw fa-dashboard"></i> ODMサービス2</a>
                            </li>
                            <li>
                                <a href="/dashboard/about.php"><i class="fa fa-fw fa-dashboard"></i> エー・エス・テックについて</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item" data-toggle="tooltip" data-placement="right" title="製品紹介">
                        <a class="nav-link nav-link-collapse collapsed" data-toggle="collapse" href="#collapseIntro" data-parent="#accordion">
                            <i class="fa fa-fw fa-wrench"></i>
                            <span class="nav-link-text">製品紹介</span>
                        </a>
                        <ul class="sidenav-second-level collapse" id="collapseIntro">
                            <li>
                                <a href="/dashboard/led_bulb.php"><i class="fa fa-fw fa-dashboard"></i> LED 電球</a>
                            </li>
                            <li>
                                <a href="/dashboard/led_light.php"><i class="fa fa-fw fa-dashboard"></i> LED 照明</a>
                            </li>
                            <li>
                                <a href="/dashboard/power_supply.php"><i class="fa fa-fw fa-dashboard"></i> パワーサプライ</a>
                            </li>
                            <li>
                                <a href="/dashboard/pcb_assembly.php"><i class="fa fa-fw fa-dashboard"></i> PCB Assembly</a>
                            </li>
                            <li>
                                <a href="/dashboard/oem_odm.php"><i class="fa fa-fw fa-dashboard"></i> OEM/ODMサービス</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="/dashboard/logout.php"><i class="fa fa-fw fa-sign-out"></i> 登出</a>
                    </li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </nav>
