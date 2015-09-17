<?php 
    $properties = json_decode(json_encode($properties));
?>

<div class="form-group <?php echo $properties->input_select_properties->hide_all?'hide_all':'' ?>">
    <input type='checkbox' id = '<?php echo $properties->input_select_properties->id; ?>_checkbox'>
    <label><?php echo Lang::get('System Investigation Report'); ?></label>
    <div class="input-group " style="margin-bottom:6px;display:none">
        <span class="input-group-addon">
            <?php echo APP_ICON::html_get(APP_ICON::sir());?>
        </span>
        <div>
        <input 
            id = '<?php echo $properties->input_select_properties->id; ?>'            
            class=""
            type="hidden"
            disable_all_type='<?php echo $properties->input_select_properties->disable_all_type; ?>'
        >
        </div>
    </div>
    <div id = '<?php echo $properties->input_select_properties->id ?>_detail' class="box " style="border-top:none;display:none">
        <ul class="todo-list ui-sortable" style="" >
                <li class="">
                </li>
        </ul>

    </div>
</div>
