<?php $lib_root = $this->config->base_url()."libraries/"; 
    $img_link_style = 'float:left;width:25px;height:25px';
    $ices_base_url = $this->config->base_url().'ices/';
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Integrated Civil Engineering System</title>
<link rel="shortcut icon" href="icon/favicon.ico" type="image/x-icon" />
<link href="<?php echo $lib_root ?>css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $lib_root ?>css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $lib_root; ?>css/ices/style.css" rel="stylesheet" type="text/css" />

</head>
<body>
    <section>
    <div class="container_12" id="content">
        <div style="position:absolute;top:0px;right:0px;height:50px;">
            <div class="navbar-right" >
                <ul style="margin-top:25px;margin-right:25px;">
                    <li class="dropdown user user-menu" style="display:none">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color:#ffffff;font-size:16px">
                            <i class="glyphicon glyphicon-user"></i>
                            <span fullname></span><i class="caret"></i>
                        </a>
                        <ul class="dropdown-menu" style="width:280px;padding: 1px 0 0 0;;
                        border-top-width: 0;">
                            <li class="" style="max-height:150px;padding: 10px;
                            background: #6aa3c0;
                            text-align: center;">
                                <img style="border-radius: 50%;width: 90px;
                                border: 8px solid;border-color: rgba(255, 255, 255, 0.2);"src="http://localhost/leo/libraries/img/avatar.png" class="" alt="User Image">
                                    <p style="color: rgba(255, 255, 255, 1);
                                    font-size: 17px;
                                    text-shadow: 2px 2px 3px #333333;
                                    margin-top: 10px;" fullname>                                                              
                                    </p>
                            </li>
                            <li class="" style="background-color: #f9f9f9;
                            padding: 10px;height:50px">                            
                                <div class="pull-left">
                                    <a href="" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="<?php echo $ices_base_url.'sign_in/sign_out'; ?>" class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <h1 style="font-family: Adobe Caslon Pro;font-weight:normal;">Integrated Civil Engineering System (ICES)</h1>
        <h3>fast, precise, reliable </h3>
        <div class="main">
                <div class="mcontent" style="height:320px">
                        <div class="box" >
                            <div><a open_app app_name="civil_project" href=""><img src="<?php echo $lib_root.'img/ices/civil_project.png' ?>" style="<?php echo $img_link_style; ?>"/></a></div>
                            <h2><a style="margin-left:5px" open_app app_name="civil_project" href="<?php echo $this->config->base_url().'project/'; ?>">Civil Project</a></h2>
                                <p>Civil Project System<br/>Support : 081-133-08009<br/>
                                </p>
                        </div>
                        <div class="box">
                            <div><a open_app app_name="accounting" href=""><img src="<?php echo $lib_root.'img/ices/accounting.png' ?>" style="<?php echo $img_link_style; ?>"/></a></div>
                            <h2><a style="margin-left:5px" open_app app_name="accounting" href="">Accounting</a></h2>
                                <p>Accounting System<br/>Support : 081-133-08009<br/>
                                </p>
                        </div>
                        <div class="box" >
                            <h2><a style="margin-left:5px" open_app app_name="analysis" href="">Analysis</a></h2>
                                <p>Analysis System<br/>Support : 081-133-08009<br/>
                                </p>
                        </div>
                        <div class="box" id="phone_book">
                            <div><a href="" open_app app_name="phone_book" ><img src="<?php echo $lib_root.'img/ices/phone_book.png' ?>" style="<?php echo $img_link_style; ?>"/></a></div>
                            <h2><a style="margin-left:5px" href="" open_app app_name="phone_book">Phone Book</a></h2>
                                <p>Phone Book System<br/>Support : 081-133-08009<br/>
                                </p>
                        </div>
                        <div class="box" id="ices_system">
                            <div><a href="" open_app app_name="ices" ><img src="<?php echo $lib_root.'img/ices/ices_system.png' ?>" style="<?php echo $img_link_style; ?>"/></a></div>
                            <h2 ><a style="margin-left:5px" href="" open_app app_name="ices">ICES System</a></h2>
                                <p>ICES System<br/>Support : 081-133-08009<br/>
                                </p>
                        </div>
                </div>
        </div>
    </div>

    <div class="modal fade" id="modal_sign_in" tabindex="" role="dialog" aria-hidden="false" style="display: none;overflow-y:auto">
        <div class="modal-dialog" style="width:404px;
                        ">
        <div class="modal-content" style="">


        <div class="modal-body" style="background-color:#6aa3c0;padding:1px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
            border-bottom-left-radius: 4px;"
        >
            <div class="" id="login-box" style='width:400px;margin:0 auto 0 auto'>
                <div class="" style="border-top-left-radius: 4px;
                        border-top-right-radius: 4px;
                        border-bottom-right-radius: 0;
                        border-bottom-left-radius: 0;

                        background: #3d9970;
                        box-shadow: inset 0px -3px 0px rgba(0, 0, 0, 0.2);
                        padding: 20px 10px;
                        text-align: center;
                        font-size: 26px;
                        font-weight: 300;
                        color: #fff;
                        background-color:#6aa3c0">
                    SIGN IN
                </div>
                <form action="" method="post">
                    <div class="" 
                         style="padding: 10px 20px;
                            background: #fff;
                            color: #444;background-color: #eaeaec !important;"
                    >
                        <div class="form-group">
                            <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user fa-lg "></i>
                            </span>
                            <input type="text" name="username" class="form-control" placeholder="User ID"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-key fa-lg"></i>
                            </span>
                            <input type="password" name="password" class="form-control" placeholder="Password"/>
                            </div>
                        </div>          
                        <div class="form-group">
                            <strong id="login_msg" style="color:#f56954 " 
                                    class=""></strong>
                        </div>
                    </div>
                    <div class="" style="border-top-left-radius: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 4px;
        border-bottom-left-radius: 4px;padding: 10px 20px;
        background: #fff;
        color: #444;">                                                               
                        <button type="submit" class="btn btn-primary btn-block" style="
                                margin-bottom: 10px;background-color: #6aa3c0;
        border-color: #6aa3c0;">Let me in</button>  
                    </div>
                </form>            
            </div>
        </div>

        </div>
        </div>
    </div>
        </section>
    <div>
    <div class="container_12">

    <p style="text-align:center;color:#2a6888">PT. Aryana Cakasana - 2015</p>
    </div>
    </div>
    </body>
    

<script type="text/javascript" src="<?php echo $lib_root; ?>js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $lib_root; ?>js/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $lib_root; ?>js/jquery.actual.min.js"></script>
<script src="<?php echo $lib_root ?>js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/ices/ices.js" type="text/javascript"></script>

</html>
