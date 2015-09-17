<?php $lib_root = $this->config->base_url()."libraries/"; 

?>    

<html>
    <head>
        <meta charset="UTF-8">
        <title>No Permission</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="<?php echo $lib_root ?>css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $lib_root ?>css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $lib_root ?>css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $lib_root ?>css/AdminLTE/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $lib_root ?>css/AdminLTE/AdminLTE_ext.css" rel="stylesheet" type="text/css" />
    </head>
    <body class="bg-black">
        <div class="form-box" id="" style=''>
            <div class="body bg-gray" >
                <div class="form-group" style="text-align:center">
                    You don't have enough permission !!!
                    <br/>
                    <a style="color:blue"href="<?php echo get_instance()->config->base_url();?>"> Home </a>
                </div>
            </div>
        </div>
    </body>
    <additional>
        <script src="<?php echo $lib_root ?>js/jquery.min.js"></script>
        <script src="<?php echo $lib_root ?>js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo $lib_root ?>js/AdminLTE/app.js" type="text/javascript"></script>
    </additional>
</html>
