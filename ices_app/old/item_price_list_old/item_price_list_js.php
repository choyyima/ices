<script>
    
    var item_price_list = {
        data:{
            price_list:[]
            ,template_price_list:{
                item:{id:"",name:""}
                ,unit:[]
            }
            ,template_unit:{
                id:""
                ,name:""
                ,price_from:"0"
                ,price_to:"0"
            }
            
        },
        check_duplicate:function(data){
            for(var i = 0;i<this.data.price_list.length;i++){
                if(this.data.price_list[i].item.id == data.item_id)
                    return true;
            }
            return false;
        },                
        valid:function(data){
    
            var result = false
            if(data.length>0){
                if(!this.check_duplicate(data[0])){
                    result = true;
                }
            }
            return result;
        },
        append:function(selected_item){
            if(this.valid(selected_item) ){
                var new_price_list = JSON.parse(JSON.stringify(this.data.template_price_list));
                new_price_list.item.id = selected_item[0].item_id;
                new_price_list.item.name = selected_item[0].item_name;
                for(var i = 0;i<selected_item.length;i++){
                    var new_unit = JSON.parse(JSON.stringify(this.data.template_unit));
                    new_unit.id = selected_item[i].unit_id;
                    new_unit.name = selected_item[i].unit_name;
                    new_price_list.unit.push(new_unit);
                }
                this.data.price_list.push(new_price_list);                
            }
        },
        remove:function(id){
            this.data.price_list.splice(id,1);
            this.draw();
        },
        event_set:function(obj,binded_data,key){
            $(obj).on('keyup',function(e){                
                APP_FILTER.numeric_only(e,$(this));
                binded_data[key] = $(this).val();
            });
            
            $(obj).on('blur',function(e){
                $(this).val(APP_CONVERTER.thousand_separator($(this).val()));
                $(this).val($(this).val()==''?'0':$(this).val());
            });

            $(obj).on('focus',function(e){
                $(this).val($(this).val().replace(/[,]/g,''));
            });            
        },
        table_generate:function(data){
            table = document.createElement('table');
            table.setAttribute('class','table');
            table.setAttribute('style','margin-bottom:0px');
            var thead = document.createElement('thead');
            var thead_row = document.createElement('tr');
            thead_row.innerHTML='<th>Unit</th><th>Price Range - From</th><th>Price Range - To</th>';
            thead.appendChild(thead_row);

            var tbody = document.createElement('tbody');
            for(var i = 0;i<data.unit.length;i++){
                var data_temp = data.unit[i];
                var row = document.createElement('tr');
                
                var input_from = document.createElement('input');
                input_from.setAttribute('style','width:50%');
                this.event_set(input_from,data_temp,'price_from');
                $(input_from).val(data_temp.price_from);

                var input_to = document.createElement('input');
                input_to.setAttribute('style','width:50%')
                this.event_set(input_to,data_temp,'price_to');
                $(input_to).val(data_temp.price_to);
                
                var input_from_col = document.createElement('td');
                var input_to_col = document.createElement('td');
                row.innerHTML='<td>'+data.unit[i].name+'</td>';
                input_from_col.appendChild(input_from);
                input_to_col.appendChild(input_to);
                row.appendChild(input_from_col);
                row.appendChild(input_to_col);
                tbody.appendChild(row);
            }           
            
            table.appendChild(thead);
            table.appendChild(tbody);
            
            return table;
        },                
        draw:function(){
            var root_element = $("#price_list_view")[0];
            var root = this;
            $("#price_list_view").empty();
            $.each(this.data.price_list,function(key, val){
                var id = APP_GENERATOR.UNIQUEID();
                var li = document.createElement('li');
                li.setAttribute('id',id);
                
                var icon = document.createElement('span');
                icon.setAttribute('class','handle');
                icon.innerHTML='<i class="fa fa-ellipsis-v"></i> <i class="fa fa-ellipsis-v" ></i>';
                
                var title = document.createElement('span');
                title.setAttribute('class','text');
                title.innerHTML='<strong>'+val.item.name+'</strong>';
                
                var tools = document.createElement('div');
                tools.setAttribute('class','tools');
                tools.innerHTML = '<i id="btn_'+id+'" class="fa fa-trash-o" "></i>';
                
                var table = root.table_generate(val);

                var tbl_form_group = document.createElement('div');
                tbl_form_group.setAttribute('class','form-group');
                tbl_form_group.setAttribute('style','margin-bottom:0px');
                tbl_form_group.appendChild(table);    
                    
                li.appendChild(icon);
                li.appendChild(title);
                li.appendChild(tools);
                li.appendChild(tbl_form_group);
                
                root_element.appendChild(li);
                
                $("#btn_"+id).on('click',function(){
                    item_price_list.remove(key);
                });
            });            
        }
    }
    
    $("#item_selector").on('change',function(){
        var json_data = {data:$(this).val()};
        var url = "<?php echo $ajax_url ?>unit_item";
        var result = APP_DATA_TRANSFER.ajaxPOST(url,json_data);
        item_price_list.append(result);
        item_price_list.draw();
        $(this).select2('data',{id:'',text:''});

    });
    
    $("#item_price_list_submit").on('click',function(e){
        e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');
        var ajax_url = APP_WINDOW.current_url();
        var json_data = {
            item_price_list_detail: item_price_list.data.price_list
            ,item_price_list:{
                code:$("#item_price_list_code").val(),
                name:$("#item_price_list_name").val(),
                notes:$("#item_price_list_notes").val()            
            }
        };
        var index_url = "<?php echo $index_url ?>"
        APP_DATA_TRANSFER.submit(ajax_url,json_data);
        setTimeout(function(){btn.removeClass('disabled')},2000);
    });
    
    <?php 
        if(count($item_price_list_detail)>0){             
    ?>
            item_price_list.data.price_list = <?php echo json_encode($item_price_list_detail); ?>;
            item_price_list.draw();
    <?php 
        } 
    ?>
</script>