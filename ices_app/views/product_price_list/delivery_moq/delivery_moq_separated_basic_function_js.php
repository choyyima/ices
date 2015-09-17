<script>
var delivery_moq_separated_methods={
        hide_show:function(){
            var lparent_pane = delivery_moq_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_moq_method').val();

            switch(lmethod){
                case  'add':    
                case 'view':
                    $(lparent_pane).find('#delivery_moq_separated_product_table').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        enable_disable: function(){
            var lparent_pane = delivery_moq_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_moq_method').val();
            switch(lmethod){
                case 'add':
                case 'view':
                    break;
                    
            }
        },
        init_data:function(){
            var lparent_pane = delivery_moq_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_moq_method').val();
            switch(lmethod){
                case 'add':
                    delivery_moq_separated_methods.product_load();
                    delivery_moq_separated_methods.security_set();
                    break;
                case 'view':          
                    delivery_moq_separated_methods.product_load();
                    delivery_moq_separated_methods.security_set();
                    break;
            }
            
        },
        security_set:function(){
            var lparent_pane = delivery_moq_parent_pane;
            var lsubmit_show = false;  
            
            var lstatus_label = '';
            switch($(lparent_pane).find('#delivery_moq_method').val()){
                case 'add':
                    lstatus_label = 'delivery_moq_separated_add';
                    break
                case 'view':
                    lstatus_label = 'delivery_moq_separated_update';
                    break;
            }
                        
            if(APP_SECURITY.permission_get('product_price_list',lstatus_label).result){
                lsubmit_show = true;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#delivery_moq_btn_submit').show();
                
            }
            else{
                $(lparent_pane).find('#delivery_moq_btn_submit').hide();
                
            }    
        },
        product_load:function(){
            var lparent_pane = delivery_moq_parent_pane;
            var lajax_url = delivery_moq_data_support_url+'delivery_moq/separated/product_get';
            var ljson_data = {product_price_list_id:$(lparent_pane).find('#delivery_moq_reference').val()};
            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
            var lresponse = lresult.response;
            var lmethod = $(lparent_pane).find('#delivery_moq_method').val();
            
            fast_draw = APP_COMPONENT.table_fast_draw;
            var ltbody = $(lparent_pane).find('#delivery_moq_separated_product_table>tbody')[0];
            $(ltbody).empty();
            var lrow_num = 1;
            var lunit_list = APP_DATA_TRANSFER.ajaxPOST(delivery_moq_data_support_url+'delivery_moq/separated/unit_list_get').response;
            $.each(lresponse, function(key, product){
                var lrow = document.createElement('tr');
                var lrownum_td = fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_num,type:'text'});
                //var lcheckbox_td = fast_draw.col_add(lrow,{tag:'td',col_name:'is_selected',style:'',val:'<input type="checkbox">',type:'text'});
                var lproduct_id_td = fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'',val:product.product_id,type:'text',visible:false});
                var lproduct_name_td = fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'',val:product.product_name,type:'text'});
                
                
                
                fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'',val:product.unit_id,type:'text',visible:false});
                fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:product.unit_name,type:'text'});
                
                var lqty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'',val:'',type:'input',class:'form-control'});
                var lqty_input = $(lqty_td).find('input')[0];
                APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set().min_val_set(0).render();
                
                var lunit_measurement = fast_draw.col_add(lrow,{tag:'td',col_name:'unit_measurement',style:'',val:'<input original>',type:'text'});
                $(lunit_measurement).find('input').select2({placeholder:'',data:lunit_list});
                $.each(lunit_list,function(li,lrow){
                    if(lrow.id === product.unit_id){
                        $(lunit_measurement).find('input').select2('data',lrow);
                    }
                })
                
                
                ltbody.appendChild(lrow);
                $(lqty_input).blur();
                lrow_num+=1;
                
            });
            if(lmethod === 'view'){
                
                var lajax_url = delivery_moq_data_support_url+'delivery_moq/separated/delivery_moq_separated_get';
                var ljson_data = {delivery_moq_id:$(lparent_pane).find('#delivery_moq_id').val()};
                var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
                var lresponse = lresult.response;
                $.each(lresponse,function(key, product){
                    $(ltbody).find('tr').filter(function(){
                        if($(this).find('[col_name="product_id"]').text().toString() === product.product_id.toString()
                            && $(this).find('[col_name="unit_id"]').text().toString() === product.unit_id.toString()
                        ){
                            $(this).find('[col_name="qty"]').find('input').val(product.qty).blur();
                            $.each(lunit_list,function(li, lrow){
                                if(lrow.id === product.unit_id_measurement){
                                    $(this).find('[col_name="unit_measurement"]').find('input[original]')
                                        .select2('data',lrow);
                                }
                            });
                            
                        }
                        
                    });
                });
                
            }
            
        },
        submit: function(){
            var lparent_pane = delivery_moq_parent_pane;
            var lajax_url = delivery_moq_index_url;
            var lmethod = $(lparent_pane).find('#delivery_moq_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            json_data.delivery_moq = {
                product_price_list_id:$(lparent_pane).find('#delivery_moq_reference').val(),
                code:$(lparent_pane).find('#delivery_moq_code').val(),    
            };

            json_data.delivery_moq_separated = [];

            var ltbody = $(lparent_pane).find('#delivery_moq_separated_product_table>tbody')[0];
            var ltrs = $(ltbody).find('tr');
            $.each(ltrs, function(key, val){
                var lqty = $(val).find('[col_name="qty"]').find('input').val().toString().replace(/[^0-9.]/g,'');
                if (parseFloat(lqty)>0){
                    var lproduct_id = $(val).find('[col_name="product_id"]').text();
                    var lunit_id=$(val).find('[col_name="unit_id"]').text();
                    var lunit_id_measurement = $(val).find('input[original]').select2('val');
                    json_data.delivery_moq_separated.push({
                        product_id:lproduct_id,
                        unit_id:lunit_id,
                        qty:lqty.replace(/[,]/g,''),
                        unit_id_measurement: lunit_id_measurement
                    });
                }
            });
            
            
            
            switch(lmethod){
                case 'add':                    
                    lajax_url +='delivery_moq_separated_add';
                    break;
                case 'view':
                    var delivery_moq_id = $(lparent_pane).find('#delivery_moq_id').val();
                    lajax_url +='delivery_moq_separated_update/'+delivery_moq_id;
                    break;
            }
            result = null;
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            
            if(result !== null){
                if(result.success ===1){
                    $(lparent_pane).find('#delivery_moq_id').val(result.trans_id);
                    if(delivery_moq_view_url !==''){
                        var url = delivery_moq_view_url+result.trans_id;
                        window.location.href=url;
                    }
                    else{
                        delivery_moq_after_submit();
                    }
                }
            }
        },
        
    }

</script>