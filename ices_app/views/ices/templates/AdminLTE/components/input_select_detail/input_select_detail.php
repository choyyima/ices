<?php
    $data = array(
        "detail"=>array(
            "rows"=>$detail_rows
            ,"id"=>$detail_id
        )
    );
    
    $data = json_decode(json_encode($data));
?>

<div id = "<?php echo $data->detail->id ?>" class="box" style="border-top:none;">
    <ul class="todo-list ui-sortable" style="" >
            
            <li class="">
                <?php foreach($data->detail->rows as $row) { ?>
                <div>
                    <span>
                        <strong><?php echo $row->label ?>:</strong>
                        <span id = "<?php echo $data->detail->id.'_'.$row->name ?>"></span>
                    </span>
                </div>
                <?php } ?>
                <?php if($button_new || $button_edit){ ?>
                <div style="margin-top:6px">
                    <?php if($button_new){ ?>
                    <button id ='<?php echo $button_new_id ?>' class="<?php echo $button_new_class ?>" style="min-width:70px" 
                            data-toggle='modal' 
                            data-target='#<?php echo $button_new_target ?>'
                    >
                        <i class="fa fa-plus"></i> 
                        New
                    </button>
                    <?php  } ?>
                    <?php if($button_edit){ ?>
                    <button id ='<?php echo $button_edit_id ?>' class="<?php echo $button_edit_class ?>" style="min-width:70px" 
                            data-toggle='modal' 
                            data-target='#<?php echo $button_edit_target ?>'
                    >
                        <i class="fa fa-pencil-square-o"></i> 
                        Edit
                    </button>
                    <?php } ?>
                </div>
                <?php } ?>
            </li>
            
    </ul>
    
</div>


