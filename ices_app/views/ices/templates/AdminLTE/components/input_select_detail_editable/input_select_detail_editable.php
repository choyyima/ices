<?php
    $data = array(
        "detail"=>array(
            "rows"=>$detail_rows
            ,"id"=>$detail_id
        ),
        'disable_all'=>$disable_all,
    );
    
    $data = json_decode(json_encode($data));
?>

<div id = "<?php echo $data->detail->id ?>" class="box" style="border-top:none;">
    <ul class="todo-list ui-sortable" style="" >
            
            <li class="">
                <?php foreach($data->detail->rows as $row) { ?>
                
                        
                <?php if($row->type === 'input'){ ?>
                <div style="margin-top:6px">
                    <span>
                        <strong><?php echo $row->label ?></strong>
                        <input id = "<?php echo $row->id ?>" 
                            class="form-control <?php echo $data->disable_all?'disable_all':'' ?>"
                            <?php echo $row->attribute ?> 
                            disable_all_type="common"
                        >
                    </span>
                </div>
                <?php }  
                else if($row->type === 'text') { ?>
                <div>
                    <span>
                        <strong><?php echo $row->label ?>: </strong>
                        <span id = "<?php echo $row->id ?>"></span>
                    </span>
                </div>    
                <?php } ?>
                        
                <?php } ?>
                <div style="margin-top:6px">
                    <?php if($button_new){ ?>
                    <button id ='<?php echo $button_new_id ?>' class="btn btn-primary" style="min-width:70px" 
                            data-toggle='modal' 
                            data-target='#<?php echo $button_new_target ?>'
                    >
                        <i class="fa fa-plus"></i> 
                        New
                    </button>
                    <?php  } ?>
                    <?php if($button_edit){ ?>
                    <button id ='<?php echo $button_edit_id ?>' class="btn btn-primary" style="min-width:70px" 
                            data-toggle='modal' 
                            data-target='#<?php echo $button_edit_target ?>'
                    >
                        <i class="fa fa-pencil-square-o"></i> 
                        Edit
                    </button>
                    <?php } ?>
                </div>
            </li>
            
    </ul>
    
</div>


