<?php
    $data = array(
        "content_header_major"=>$content_header_major
        ,"content_header_major_icon"=>$content_header_major_icon
        ,"content_header_minor"=>$content_header_minor
        ,"breadcrumb"=>array("name"=>$breadcrumb['name'],"href"=>$breadcrumb['href'])
        ,"content"=>$content
        ,"msg"=>$msg
        ,"base_url"=>$base_url
    );
    $data = json_decode(json_encode($data));
    
    
?>


<section class="content-header" style="z-index:190;">
    <h1>
        <?php if (is_string($data->content_header_major_icon)){ ?>
            <i class="<?php echo $data->content_header_major_icon ?>"></i>
        <?php } 
            else if (is_array($data->content_header_major_icon)){
                foreach($data->content_header_major_icon as $idx=>$icon){
        ?>
            <i class="<?php echo $icon ?>"></i> 
        <?php
                }
            }
        ?>
        &nbsp<?php echo $data->content_header_major;?>
        <small><?php echo $data->content_header_minor?> </small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo get_instance()->config->base_url();?>">ICES</a></li>
        <li><a href="<?php echo $data->base_url.'dashboard';?>">Home</a></li>
        <?php if(strlen($data->breadcrumb->name)>0){?>
        <li class="active"><a href="<?php echo $data->breadcrumb->href?>"><?php echo $data->breadcrumb->name; ?></a></li>
        <?php }?>
    </ol>
</section>

<!-- Main content -->
<section class="content" style="">
    <?php if( in_array(strtolower($data->msg->type),array('danger','error'))) { ?>
    <div class="alert alert-danger alert-dismissable" >
        <i class="fa fa-ban"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <b><?php echo strtoupper($data->msg->type)?>! </b><br/>
        <?php 
            
            foreach($data->msg->msg as $msg_item){    
                echo "<li>".$msg_item."<br/></li>";
            }        
        ?>
    </div>
    <?php } ?>
    <?php if(in_array($data->msg->type,array('info','success'))) { ?>
        
    <div class="alert alert-info alert-dismissable" >
        <i class="fa fa-check"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <b><?php 
            if($data->msg->type ==='info') echo "Information!"; 
            else if($data->msg->type ==='success') echo "Success!"; 
        ?>
        </b><br/>
        <?php 
            if(isset($data->msg->msg)){
                foreach($data->msg->msg as $msg_item){   
                    echo "<li>".$msg_item."<br/></li>";
                }        
            }
        ?>
    </div>
    <?php } ?>
    <?php echo $data->content;?>    
</section><!-- /.content -->


