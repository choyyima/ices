<?php 
    $data = array(
        "user_id"=>$user_info['user_id']
        ,"name"=>$user_info['name']
        ,"role"=>$user_info['role']
    );
?>
<?php $is_strech  = false; if(!$menu_properties['collapsed']) $is_strech = true;?>
<body class="skin-blue">
    <?php echo $top_nav ?>
    <div id="div_wrapper" class="wrapper row-offcanvas row-offcanvas-left "> 
        <aside class="left-side sidebar-offcanvas <?php if($is_strech) echo 'collapse-left'; ?>">                
            <!-- sidebar: style can be found in sidebar.less -->
            <?php echo $left_nav ?>
            <!-- /.sidebar -->
        </aside>
        <aside class="right-side <?php if ($is_strech) echo 'strech'; ?>" id="app_container" >                
            <!-- Content Header (Page header) -->
            <?php echo $content ?> 
        </aside>
    </div>
</body>
