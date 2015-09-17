<?php 
    $data['input_selector_id']=$input_selector_id;
    $data['raw_data_id'] = 'raw_data_'.$id;
    $data['format_id'] = 'format_'.$id;
    $data['ajax_url'] = $ajax_url;
    
    $data['raw_data'] = $raw_data;
    $data['min_length'] = $min_length;
    $data['value']=$value;
    $data['table'] = array(
        "columns"=>$table_columns
        ,'column_key'=>$table_column_key    
        ,"id"=>$table_id
        ,'ajax_url'=>$tbl_ajax_url
        ,'allow_duplicate_id'=>$table_allow_duplicate_id
        ,'selected_value'=> $selected_value
    );
    $data['max_selected_item'] = $max_selected_item;
    
    
    $data = json_decode(json_encode($data));

?>
<script>

    var check_duplicate_<?php echo $data->table->id ?>=function(tbody_id, value){
        var tbody = $("#"+tbody_id)[0];
        var rows = tbody.children;
        var duplicate = false;
        var col_key = "<?php echo $data->table->column_key; ?>";
        $.each(rows,function(key,val){
            row = val;
            var cols = row.children;
            $.each(cols,function(cols_key,cols_val){
                var col = cols_val;                
                var attr_val = col.getAttribute("name");                
                if(attr_val == col_key && col.innerHTML == value){
                    duplicate = true;
                }
            });
        });
        return duplicate;
    }
    
    after_delete_row_<?php echo $data->table->id ?> = function(){
        
    }
    
    var delete_row_<?php echo $data->table->id?> = function(tbody_id, row_id){
            $("#"+row_id).remove();
            
            var children = $("#"+tbody_id)[0].children;
            var row_num = 0;
            $.each(children,function(key, val){
                var row = val;
                var cols = row.children;
                $.each(cols,function(col_key,col_val){
                   if(col_val.getAttribute('name') == 'row_num'){
                       row_num+=1;
                       col_val.innerHTML = row_num;
                   } 
                });
                
            });
        }
    
    $("#<?php echo $data->input_selector_id; ?>").on("change",function(e){
        var tbody = $("#<?php echo 'tbody_'.$data->table->id;?>")[0];
        var curr_row = tbody.children.length;
        var columns = <?php echo json_encode($data->table->columns); ?>;
        var allow_duplicate_id = <?php echo $data->table->allow_duplicate_id?'true':'false'; ?>;
        var max_selected_item = <?php echo $data->max_selected_item; ?>;
        if(max_selected_item>$(tbody).find('tr').length || max_selected_item === 0){
            var json_data = {data:$(this).val()};
            var url = "<?php echo $data->table->ajax_url ?>";
            var result = APP_DATA_TRANSFER.ajaxPOST(url,json_data);
            var response = null;
            if(typeof result.response !== 'undefined')
                response = result.response;
            else
                response = result;
            $.each(response,function(key,val){
                var tbody_id = "<?php echo 'tbody_'.$data->table->id;?>";
                curr_row +=1;
                var row = val;
                var data_row =[];
                var row_id = APP_GENERATOR.UNIQUEID().toString(); 
                if("<?php echo $data->table->column_key ?>" in val){
                    var duplicate=check_duplicate_<?php echo $data->table->id ?>(tbody_id, val.<?php echo $data->table->column_key ?>);
                    if(allow_duplicate_id) duplicate = false;
                    if(!duplicate){
                        data_row.push({name:'row_num',value:curr_row,type:'text',filter:''});
                        $.each(columns,function(col_key,col_val){
                            var temp_col = {
                                name:col_val.name,value:col_val.value,type:col_val.type,filter:col_val.filter
                            };
                            $.each(row, function(row_key,row_val){
                               if(col_val.name == row_key){
                                   temp_col.value = row_val;
                               }
                            });
                            data_row.push(temp_col);
                        });

                        var temp_row = document.createElement("tr");
                        temp_row.setAttribute("id",row_id);
                        // generate key row
                        var key_row = document.createElement("td");
                        key_row.setAttribute("class","hidden");
                        key_row.setAttribute("name","<?php echo $data->table->column_key?>");
                        key_row.innerHTML = val.<?php echo $data->table->column_key?>;
                        // end of key row
                        temp_row.appendChild(key_row);
                        for(i = 0;i<data_row.length;i++){
                            var temp_cell = document.createElement("td");
                            if(data_row[i].type === 'text')
                                temp_cell.innerHTML = data_row[i].value;
                            else if(data_row[i].type === 'input'){
                                var linput = document.createElement('input');
                                $(linput).addClass('form-control');
                                $(linput).val(data_row[i].value);
                                if(data_row[i].filter === 'numeric'){
                                    APP_EVENT.init().component_set(linput).type_set('input').numeric_set().min_val_set(0).render();
                                }
                                temp_cell.appendChild(linput);
                            }
                            temp_cell.setAttribute("name",data_row[i].name);
                            temp_row.appendChild(temp_cell);
                        }

                        var action_list = document.createElement("td");
                        var btn_delete = document.createElement("button");
                        btn_delete.setAttribute("ref_id",row_id);
                        btn_delete.setAttribute("class","btn btn-primary btn-sm");
                        btn_delete.onclick = function(event){
                            var lrow = $(this).closest('tr');
                            var ldeleted_unit_id = $(lrow).find('[name="id"]').text();
                            event.preventDefault();
                            delete_row_<?php echo $data->table->id ?>("<?php echo 'tbody_'.$data->table->id ?>",row_id);
                            var lopt = {deleted_unit_id: ldeleted_unit_id};
                            after_delete_row_<?php echo $data->table->id ?>(lopt);
                        }
                        btn_delete.innerHTML = '<i class="fa fa-trash-o"></i>';
                        action_list.appendChild(btn_delete);
                        temp_row.appendChild(action_list);
                        tbody.appendChild(temp_row); 
                    }

                }

            });
        }
        
        
        $("#<?php echo $data->input_selector_id?>").select2('data',null);        
    ;});

    var get_dt_<?php echo $data->table->id ?>=function(){
        var table_id = "<?php echo $data->table->id?>";
        var tbody_id = "tbody_"+table_id;
        var result = [];
        var rows = $("#"+tbody_id)[0].children;
        var col_key = "<?php echo $data->table->column_key ?>";
        $.each(rows,function(key,val){
            var cols = val.children;
            var row_temp = {};
            $.each(cols,function(cols_key,cols_val){
                var col = cols_val;
                if(col.hasAttribute("name")){
                    var forbidden_name = ["row_num"];
                    if(forbidden_name.indexOf(col.getAttribute("name")) == -1){
                        var temp_key = col.getAttribute("name");
                        var temp_val = '';
                        if($(col).find('input').length > 0){
                            temp_val = $($(col).find('input')[0]).val();
                        }
                        else{
                            temp_val = col.innerHTML;
                        }
                        row_temp[temp_key]=temp_val;
                    }
                }
            });
            
            result.push(row_temp);
        });
        
        return result;        
    }
    
    //$("#input_select").select2('data',{id:'2',text:'TEST'});        
    <?php foreach($data->table->selected_value as $val){ ?>
    $("#<?php echo $data->input_selector_id ?>").val('<?php echo $val; ?>').change();
    <?php } ?>

</script>