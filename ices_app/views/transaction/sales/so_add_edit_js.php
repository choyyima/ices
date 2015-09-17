<script>
    var ajax_search = "<?php echo $ajax_search ?>";
    var index_url = "<?php echo $index_url ?>";
    var so_item_data = {
        data_table:[]
        ,tmplt_row:{
            item_id:'',
            item_name:'',
            price:'0',
            qty:'0',
            unit_id:'',
            unit_name:'',
            sub_total:'',
            item_price_list_id:'',
            price_from:'0',
            price_to:'0',
            discount:'0',
            master:{
                item_price_list:[],
                unit:[]
            }
        }
    };
    
    
    // flow initial function: init, row_add_new,draw
    // flow on event: copy value to data table, do next step, calculate sub total
    // sub total calculation must be at the final step.
    var so_item_methods={
        item_detail_get:function(id){
            var json_data = {data:id};
            response = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'so_item_detail',json_data);
            return response[0];
        },
        item_unit_detail_get:function(id){
            var json_data = {data:id};
            response = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'so_item_unit_detail',json_data);
            return response;
        },
        item_price_list_get:function(customer_id,item_id,unit_id){
            var json_data = {item_id:item_id,customer_id:customer_id,unit_id:unit_id};
            response = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'so_customer_item_price_list',json_data);
            result = [];
            tmplt_row = {item_price_list_id:'',item_price_list_name:'',price_from:'',price_to:''}
            $.each(response,function(key, val){
                new_row = JSON.parse(JSON.stringify(tmplt_row));
                new_row.item_price_list_id = val.item_price_list_id;
                new_row.item_price_list_name = val.item_price_list_name;
                new_row.price_from = val.price_from;
                new_row.price_to = val.price_to;
                result.push(new_row);
            });
            return result;
        },
        init:function(){
            $("#so_item_input_select").select2('enable',false);   
            $("#so_approval_id").select2('enable',false) ;
            so_item_data.data_table = [];
            so_item_methods.draw();
        },
        master_price_list_set:function(idx){    
            customer_id = $("#so_customer_id").select2('val');
            dt_row = so_item_data.data_table[idx];
            
            item_id = dt_row.item_id;
            unit_id = dt_row.unit_id;
            var master_item_price_list = so_item_methods.item_price_list_get(customer_id,item_id,unit_id);
            tmplt_ipl = {item_price_list_id:'',item_price_list_name:'',price_from:'',price_to:''};
            dt_row.price_from = 0;
            dt_row.price_to = 0;
            dt_row.item_price_list_id = 0;
            dt_row.price = 0;
            dt_row.master.item_price_list = [];
            $.each(master_item_price_list, function(ipl_key, ipl_val){
                if(ipl_key == 0){
                    dt_row.price_from = ipl_val.price_from;
                    dt_row.price_to = ipl_val.price_to;
                    dt_row.item_price_list_id = ipl_val.item_price_list_id;
                    dt_row.price = ipl_val.price_from;
                }
                new_row = JSON.parse(JSON.stringify(tmplt_ipl));
                new_row.item_price_list_id = ipl_val.item_price_list_id;
                new_row.item_price_list_name = ipl_val.item_price_list_name;
                new_row.price_from = ipl_val.price_from;
                new_row.price_to = ipl_val.price_to;
                dt_row.master.item_price_list.push(new_row);
            });                

            
        },        
        tmplt_set:function(item_id){
            customer_id = $("#so_customer_id").select2('val');
            item_detail = so_item_methods.item_detail_get(item_id);
            master_item_unit_detail = so_item_methods.item_unit_detail_get(item_id);

            new_row = JSON.parse(JSON.stringify(so_item_data.tmplt_row));
            new_row.item_id = item_detail.item_id;
            new_row.item_name = item_detail.item_name;
            new_row.qty = '0';
            new_row.price = '0';
            new_row.discount = '0';
            
            new_row.unit_name = '';
            new_row.sub_total = '0';   
            new_row.item_price_list_id='';
            new_row.master.item_price_list=[];
            new_row.master.unit = [];            
            $.each(master_item_unit_detail,function(key, unit){
                if(key == 0){new_row.unit_id = unit.unit_id;}
                new_row.master.unit.push({
                   unit_id:unit.unit_id,
                   unit_name:unit.unit_name
                });
            });
            return new_row;
        },
        calculation:{
            sub_total:function(){
                var total = 0;
                $.each(so_item_data.data_table,function(idx, dt_row){
                        dt_row.sub_total = (parseFloat(dt_row.price) - parseFloat(dt_row.discount))* parseFloat(dt_row.qty);
                        $("[idx='"+idx+"']").find('[col_name="sub_total"]')[0].innerHTML = APP_CONVERTER.thousand_separator(dt_row.sub_total);
                        total+=dt_row.sub_total;
                });
                $("#so_total").children(0)[0].innerHTML=APP_CONVERTER.thousand_separator(total);
                discount = parseFloat($("#so_discount").val());
                if(discount>total){
                    discount = total;
                }
                $("#so_discount").val(discount);
                grand_total = total-discount;
                $("#so_grand_total").children(0)[0].innerHTML = APP_CONVERTER.thousand_separator(grand_total);
            }
        },
        event_set:{
            qty:function(component){
                $(component).on('change',function(){
                    idx = $(this).parent().parent().attr('idx');
                    so_item_data.data_table[idx].qty = $(this).val().replace(/[,]/g,'');
                    so_item_methods.calculation.sub_total();
                });
            },
            price:function(component){
                $(component).on('blur',function(){
                    idx = $(this).parent().parent().attr('idx');
                    so_item_data.data_table[idx].price = $(this).val().replace(/[,]/g,'');
                    so_item_methods.calculation.sub_total();
                });
            },
            unit:function(component){
                $(component).on('change',function(){
                    idx = $(this).parent().parent().attr('idx');
                    so_item_data.data_table[idx].unit_id = $(this).val();
                    so_item_methods.master_price_list_set(idx);
                    so_item_methods.draw();
                });
            },
            price_list:function(component){
                $(component).on('change',function(){
                    idx = $(this).parent().parent().attr('idx');
                    so_item_data.data_table[idx].item_price_list_id = $(this).val();
                    ipl_id = so_item_data.data_table[idx].item_price_list_id;
                    $.each(so_item_data.data_table[idx].master.item_price_list, function(ipl_idx,ipl_val){
                        if(ipl_val.item_price_list_id == ipl_id){
                            so_item_data.data_table[idx].price_from = ipl_val.price_from;
                            so_item_data.data_table[idx].price_to = ipl_val.price_to;
                            so_item_data.data_table[idx].price = ipl_val.price_from;
                        }
                    });
                    so_item_methods.draw();
                    so_item_methods.calculation.sub_total();
                });
            },
            remove:function(component){
                $(component).on('click',function(e){
                    idx = $(this).attr('idx');
                    so_item_data.data_table.splice(idx,1);
                    so_item_methods.draw();
                    so_item_methods.calculation.sub_total();
                });
            },
            discount:function(component){
                $(component).on('blur',function(){
                    idx = $(this).parent().parent().attr('idx');
                    price = parseFloat(so_item_data.data_table[idx].price);
                    discount = parseFloat($(this).val().replace(/[,]/g,''));
                    if(discount > price){
                        discount = price;                        
                    }
                    so_item_data.data_table[idx].discount = discount;
                    so_item_methods.draw();
                    so_item_methods.calculation.sub_total();
                });
            }
        },      
        draw:function(){
            tbody = $("#so_item_table").find('tbody')[0];
            $(tbody).empty();
            $.each(so_item_data.data_table,function(dt_idx, dt_row){                
                tr = document.createElement('tr');
                $(tr).attr('idx',dt_idx);
                td_row_num = document.createElement('td');
                td_row_num.innerHTML = dt_idx+1;
                tr.appendChild(td_row_num);
                
                td_item = document.createElement('td');
                td_item.innerHTML = dt_row.item_name;                
                tr.appendChild(td_item);
                
                input_qty = document.createElement('input');
                APP_EVENT.init().component_set(input_qty).type_set('input').numeric_set().render();
                $(input_qty).val(dt_row.qty);
                so_item_methods.event_set.qty(input_qty);
                td_qty = document.createElement('td');
                td_qty.appendChild(input_qty);
                tr.appendChild(td_qty);
                
                select_unit = document.createElement('select');
                $.each(dt_row.master.unit,function(unit_key, unit_val){
                    option_unit = document.createElement('option');
                    $(option_unit).attr('value',unit_val.unit_id);
                    option_unit.innerHTML = unit_val.unit_name;
                    select_unit.appendChild(option_unit);
                    if(unit_val.unit_id == dt_row.unit_id){
                        $(option_unit).attr('selected','selected');
                    }
                });  
                so_item_methods.event_set.unit(select_unit);
                td_unit = document.createElement('td');
                $(td_unit).attr('col_name','unit');
                td_unit.appendChild(select_unit);
                tr.appendChild(td_unit);
                
                select_item_price_list = document.createElement('select');
                $.each(dt_row.master.item_price_list,function(item_price_list_key, item_price_list_val){
                    option_ipl = document.createElement('option');
                    $(option_ipl).attr('value',item_price_list_val.item_price_list_id);
                    option_ipl.innerHTML = item_price_list_val.item_price_list_name;
                    select_item_price_list.appendChild(option_ipl);
                    if(item_price_list_val.item_price_list_id == dt_row.item_price_list_id){
                        $(option_ipl).attr('selected','selected');
                    }
                });
                so_item_methods.event_set.price_list(select_item_price_list);
                td_item_price_list = document.createElement('td');
                td_item_price_list.appendChild(select_item_price_list);
                $(td_item_price_list).attr('col_name','item_price_list');
                tr.appendChild(td_item_price_list);
                
                td_price_range = document.createElement('td');
                td_price_range.innerHTML = APP_CONVERTER.thousand_separator(dt_row.price_from)+' - '+APP_CONVERTER.thousand_separator(dt_row.price_to);                
                $(td_price_range).attr('col_name','price_range');
                tr.appendChild(td_price_range);
                
                input_price = document.createElement('input');
                $(input_price).val(APP_CONVERTER.thousand_separator(dt_row.price));
                input_price_event = APP_EVENT.init().component_set(input_price).type_set('input').numeric_set();
                
                if($("#so_approval_id").select2('val') === ''){
                    input_price_event.min_val_set(dt_row.price_from);
                    input_price_event.max_val_set(dt_row.price_to);
                }
                input_price_event.render();
                so_item_methods.event_set.price(input_price);
                $(input_price).attr('col_name','price');
                td_price = document.createElement('td');
                td_price.appendChild(input_price);
                tr.appendChild(td_price);
                
                input_discount = document.createElement('input');
                so_item_methods.event_set.discount(input_discount);
                $(input_discount).val(APP_CONVERTER.thousand_separator(dt_row.discount));
                APP_EVENT.init().component_set(input_discount).type_set('input').numeric_set().render();
                td_discount = document.createElement('td');
                td_discount.appendChild(input_discount);
                tr.appendChild(td_discount);
                
                td_sub_total = document.createElement('td');
                $(td_sub_total).attr('col_name','sub_total');
                td_sub_total.innerHTML = APP_CONVERTER.thousand_separator(dt_row.sub_total);      
                tr.appendChild(td_sub_total);
                
                td_action = document.createElement('td');
                i_action = document.createElement('i');
                $(i_action).attr('style','color:red;cursor:pointer');
                $(i_action).attr('idx',dt_idx);
                $(i_action).addClass('fa fa-trash-o');                
                so_item_methods.event_set.remove(i_action);
                td_action.appendChild(i_action);
                tr.appendChild(td_action);
                
                tbody.appendChild(tr);     
                $(input_qty).focus();
            });
        },
        row_add_new:function(item_id){            
            var new_row = this.tmplt_set(item_id);
            so_item_data.data_table.push(new_row);            
            so_item_methods.master_price_list_set(so_item_data.data_table.length-1);
            so_item_methods.draw();
            so_item_methods.calculation.sub_total();
        }
    };
    
    $("#so_submit").on('click',function(e){
        e.preventDefault();
        json_data={
            so:{
                customer_id:$("#so_customer_id").select2('val')
                ,date:$("#so_date").val()
                ,sales_order_status:$("#so_status").select2('val')
                ,notes:$("#so_notes").val()
                ,total:$("#so_total").children(0)[0].innerHTML.replace(/[,]/g,'')
                ,discount:$("#so_discount").val().replace(/[,]/g,'')
                ,grand_total:$("#so_grand_total").children(0)[0].innerHTML.replace(/[,]/g,'')
                ,cancellation_reason:$("#so_cancellation_reason").val()
            },
            so_detail:[]
        };
        if($("#so_approval_id").select2('val') !== ''){
            json_data.so.approval_id=$("#so_approval_id").select2('val');
        }
        tmplt_row = {item_id:'', unit_id:''}
        $.each(so_item_data.data_table,function(key,row){
            new_row = JSON.parse(JSON.stringify(tmplt_row));
            new_row.item_id = row.item_id;
            new_row.unit_id = row.unit_id;
            new_row.price = row.price;
            new_row.discount = row.discount;
            new_row.qty = row.qty;
            json_data.so_detail.push(new_row);
        });
        ajax_url = APP_WINDOW.current_url();
        response = APP_DATA_TRANSFER.submit(ajax_url,json_data);
        if(response.success == 1){
            window.location = index_url+"view/"+response.trans_id;
        }    
        window.scrollTo(0,0);
        
        $(this).removeClass('disabled');
    });
    
    $("#so_customer_id").on('change',function(){
        so_item_methods.init();
        $("#so_item_input_select").select2('enable',true);
    });
    
    $("#so_item_input_select").on('change',function(){
        item_id = $(this).select2('val');
        so_item_methods.row_add_new(item_id);
        
    });
    
    $("#so_status").on('change',function(e){
        var so_status = $(this).val();
        if(so_status == 'X'){
            $("#div_so_cancellation_reason").removeClass("hidden");
        }
        else{
            $("#div_so_cancellation_reason").addClass("hidden");
        }
            
    });
    
    
    
    so_item_methods.init();
    //$("#so_customer_id").select2('data',{id:9,text:'CUST1, Customer 1'});
    //$("#so_customer_id").trigger('change');    
    $("#so_approval_id").on('change',function(){
       so_item_methods.draw();
    });
    $("#so_approval_id_checkbox").on('ifChecked',function(){
       $("#so_approval_id").select2('enable',true);
    });
    
    $("#so_approval_id_checkbox").on('ifUnchecked',function(){
       $("#so_approval_id").select2('enable',false) ;
       $("#so_approval_id").select2('data',{id:'',text:''});
       $.each(so_item_data.data_table,function(key, val){
           val.price = val.price_from;
       })
       so_item_methods.draw();
       
    });
    $("#so_discount").on('blur',function(){
        so_item_methods.calculation.sub_total();
    });
    APP_EVENT.init().component_set($("#so_discount")[0]).type_set('input').numeric_set().render();
</script>