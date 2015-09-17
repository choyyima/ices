<?php 
    $data['role'] = $user_info['role'];
?>
<header class="header" >
    <a href="" class="logo">
        <!-- Add the class icon to your logo image or logo icon to add the margining -->
        <?php echo $data['role']?>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <!-- Messages: style can be found in dropdown.less-->
                <li class="dropdown messages-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-envelope"></i>
                        <span class="label label-success" id="message_nav_number"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header" id="message_nav_header"></li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu" id="message_nav_body">
                                
                                                               
                            </ul>
                        </li>
                        <li class="footer"><a href="<?php echo ICES_Engine::$app['app_base_dir'].'app_message/' ?>">See All Messages</a></li>
                    </ul>
                </li>
                <!-- Notifications: style can be found in dropdown.less -->
                <li id="notification" class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-warning"></i>
                        <span class="label label-warning" id="notification_number"></span>
                    </a>
                    <ul class="dropdown-menu" >
                        <li class="header" id="notification_header"></li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <ul class="menu" id="notification_body">
                                
                            </ul>
                        </li>
                        <?php /*<li class="footer"><a href="#">View all</a></li> */ ?>
                    </ul>
                </li>
                <!-- Tasks: style can be found in dropdown.less -->
                
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu" >
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo User_Info::get()['name'] ?><i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header bg-light-blue" style="max-height:150px">
                            <img src="<?php echo $lib_root ?>img/avatar3.png" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo User_Info::get()['name'] ?>                                
                            </p>
                        </li>
                        <!-- Menu Body -->
                        
                        <li class="user-body">
                            
                            <div class="col-xs-12 text-center">
                                <a href="#"><?php echo User_Info::get()['role'] ?></a>
                            </div>
                            
                            
                             
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            
                            <div class="pull-left">
                                <a href="<?php echo $base_url.'u_profile/'?>" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="<?php echo $base_url?>sign_in/sign_out" class="btn btn-default btn-flat">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
    </nav>
</header>

