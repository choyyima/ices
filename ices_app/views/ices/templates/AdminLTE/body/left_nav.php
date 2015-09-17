<?php 
    $data = array(
        "user_id"=>$user_info['user_id']
        ,"name"=>$user_info['name']
        ,"role"=>$user_info['role']
        ,'menu_item'=>$menu_item
        ,'base_url'=>$base_url
        ,'lib_root'=>$lib_root    
    );
?>

<section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
        <div class="pull-left image">
            <img src="<?php echo $data['lib_root'] ?>img/avatar3.png"  class="img-circle" alt="User Image" />
        </div>
        <div class="pull-left info" style ="max-width:150px">
            <p>Hello, <?php echo $data['name'] ?></p>

            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
    </div>
    <!-- search form -->
    <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
            <input id="sidebar_search" type="text" class="form-control" placeholder="Search..."/>
            <span class="input-group-btn">
                <button type='submit' name='seach' id='sidebar_search_btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
            </span>
        </div>
    </form>
    <!-- /.search form -->
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" >
    <?php
        foreach($data['menu_item'] as $key=>$val){
            $child_exists = false;
            if(isset($val['child'])) if(count($val['child'])>0) $child_exists = true;
            if($child_exists) echo '<li class="treeview">'."\n";
            else echo '<li>'."\n";
            echo '<a href="'.$val['ref'].'">'."\n";
            echo '<i class="'.$val['properties']['class'].'"></i>'."\n";
            echo '<span>'.$key.'</span>'."\n";
            if(isset($val['is_new'])) if($val['is_new'])echo '<small class="badge pull-right bg-green">new</small>';
            if($child_exists) echo '<i class="fa fa-angle-left pull-right"></i>'."\n";
            echo '</a>'."\n";

            if($child_exists){
                echo '<ul class="treeview-menu">';
                foreach($val['child'] as $key2=>$val2){
                    if(!isset($val2['child'])){
                        echo '<li class="">';
                        echo '<a href="'.$val2['ref'].'"><i class="fa fa-angle-double-right" style="float:left"></i>'
                            .'<div style="margin-left:20px">'.$key2.'</div></a>';
                        //echo '<ul class="treeview-menu">';
                        //echo '<li><a href="test"><i class="fa fa-angle-double-right"></i>TEST</a></li>';
                        //echo '</ul>';
                        echo '</li>';
                    }
                    else{
                        echo '<li class="treeview">';
                        echo '<a href="'.$val2['ref'].'"><i class="fa fa-angle-double-right"></i>'.$key2.'<i class="fa fa-angle-left pull-right"></i></a>';
                        echo '<ul class="treeview-menu">';
                        foreach($val2['child'] as $key3=>$val3){
                            if(!isset($val3['child'])){
                                echo '<li class="">';
                                echo '<a href="'.$val3['ref'].'"><i class="fa fa-angle-double-right" style="float:left"></i>'
                                        .'<div style="margin-left:20px">'.$key3.'</div></a>';
                                //echo '<ul class="treeview-menu">';
                                //echo '<li><a href="test"><i class="fa fa-angle-double-right"></i>TEST</a></li>';
                                //echo '</ul>';
                                echo '</li>';
                            }
                            else{

                            }
                        }
                        echo '</ul>';
                        echo '</li>';
                    }
                }
                echo '</ul>';
            }   
            echo '</li>',"\n";
        }

    ?>
    </ul>
</section>