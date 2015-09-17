<script>
var delivery_mop_mixed_methods={
        hide_show:function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();

            switch(lmethod){
                case  'add':    
                case 'view':
                    $(lparent_pane).find('#delivery_mop_mixed_amount').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_mop_mixed_product_table').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        enable_disable: function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#delivery_mop_mixed_amount').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#delivery_mop_mixed_amount').prop('disabled',false);
                    break;
                    
            }
        },
        init_data:function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#delivery_mop_mixed_amount').val('0').blur();
                    delivery_mop_mixed_methods.product_load();
                    delivery_mop_mixed_methods.security_set();
                    break;
                case 'view':          
                    var ldelivery_mop_id = $(lparent_pane).find('#delivery_mop_id').val();
                    var lajax_url = delivery_mop_data_support_url+'delivery_mop/mixed/delivery_mop_mixed_get';
                    var json_data = {delivery_mop_id:ldelivery_mop_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                    var lresponse = lresult.response;

                    $(lparent_pane).find('#delivery_mop_mixed_amount').val(lresponse.amount).blur();
                    delivery_mop_mixed_methods.product_load();
                    delivery_mop_mixed_methods.security_set();
                    break;
            }
            
        },
        security_set:function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lsubmit_show = false;  
            
            var lstatus_label = '';
            switch($(lparent_pane).find('#delivery_mop_method').val()){
                case 'add':
                    lstatus_label = 'delivery_mop_mixed_add';
                    break
                case 'view':
                    lstatus_label = 'delivery_mop_mixed_update';
                    break;
            }
                        
            if(APP_SECURITY.permission_get('product_price_list',lstatus_label).result){
                lsubmit_show = true;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#delivery_mop_btn_submit').show();
                
            }
            else{
                $(lparent_pane).find('#delivery_mop_btn_submit').hide();
                
            }    
        },
        product_load:function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lajax_url = delivery_mop_data_support_url+'delivery_mop/mixed/product_get';
            var ljson_data = {product_price_list_id:$(lparent_pane).find('#delivery_mop_reference').val()};
            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
            var lresponse = lresult.response;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            
            fast_draw = APP_COMPONENT.table_fast_draw;
            var ltbody = $(lparent_pane).find('#delivery_mop_mixed_product_table>tbody')[0];
            $(ltbody).empty();
            $.each(lresponse, function(key, product){
                var lrow = document.createElement('tr');
                var lcheckbox_td = fast_draw.col_add(lrow,{tag:'td',col_name:'is_selected',style:'',val:'<input type="checkbox">',type:'text'});
                var lproduct_id_td = fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'',val:product.product_id,type:'text',visible:false});
                var lproduct_name_td = fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'',val:product.product_name,type:'text'});
                ltbody.appendChild(lrow);
                $(lcheckbox_td).find('input').iCheck({
                    checkboxClass: 'icheckbox_minimal'
                });
            });
            if(lmethod === 'view'){
                
                var lajax_url = delivery_mop_data_support_url+'delivery_mop/mixed/delivery_mop_mixed_product_get';
                var ljson_data = {delivery_mop_id:$(lparent_pane).find('#delivery_mop_id').val()};
                var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
                var lresponse = lresult.response;
                $.each(lresponse,function(key, product){
                    $(ltbody).find('tr').filter(function(){
                        if($(this).find('[col_name="product_id"]').text() === product.product_id){
                            return this
                        }
                    }).iCheck('check');
                });
                
            }
            
        },
        submit: function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lajax_url = delivery_mop_index_url;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            json_data.delivery_mop = {
                product_price_list_id:$(lparent_pane).find('#delivery_mop_reference').val(),
                code:$(lparent_pane).find('#delivery_mop_code').val(),         
            };

            json_data.delivery_mop_mixed = {
                amount:$(lparent_pane).find('#delivery_mop_mixed_amount').val().replace(/[,]/g,''),
            };

            json_data.delivery_mop_mixed_product=[];
            var ltbody = $(lparent_pane).find('#delivery_mop_mixed_product_table>tbody')[0];
            var lselected_product = $(ltbody).find('tr').filter(
                function(){ 
                    if ($(this).find('[col_name="is_selected"]').find('input').is(':checked')) 
                        return this; 
                }
            );
            
            $.each(lselected_product,function(key, val){
                var lproduct_id = $(val).find('[col_name="product_id"]').text();
                json_data.delivery_mop_mixed_product.push({
                    product_id:lproduct_id,
                });
                
            });
            
            
            switch(lmethod){
                case 'add':                    
                    lajax_url +='delivery_mop_mixed_add';
                    break;
                case 'view':
                    var delivery_mop_id = $(lparent_pane).find('#delivery_mop_id').val();
                    lajax_url +='delivery_mop_mixed_update/'+delivery_mop_id;
                    break;
            }
            result = null;
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            
            
            if(result !== null){
                if(result.success ===1){
                    $(lparent_pane).find('#delivery_mop_id').val(result.trans_id);
                    if(delivery_mop_view_url !==''){
                        var url = delivery_mop_view_url+result.trans_id;
                        window.location.href=url;
                    }
                    else{
                        delivery_mop_after_submit();
                    }
                }
            }
        },
        
    }

</script>