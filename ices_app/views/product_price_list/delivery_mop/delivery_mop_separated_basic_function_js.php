<script>
var delivery_mop_separated_methods={
        hide_show:function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();

            switch(lmethod){
                case  'add':    
                case 'view':
                    $(lparent_pane).find('#delivery_mop_separated_product_table').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        enable_disable: function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            switch(lmethod){
                case 'add':
                case 'view':
                    break;
                    
            }
        },
        init_data:function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            switch(lmethod){
                case 'add':
                    delivery_mop_separated_methods.product_load();
                    delivery_mop_separated_methods.security_set();
                    break;
                case 'view':          
                    delivery_mop_separated_methods.product_load();
                    delivery_mop_separated_methods.security_set();
                    break;
            }
            
        },
        security_set:function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lsubmit_show = false;  
            
            var lstatus_label = '';
            switch($(lparent_pane).find('#delivery_mop_method').val()){
                case 'add':
                    lstatus_label = 'delivery_mop_separated_add';
                    break
                case 'view':
                    lstatus_label = 'delivery_mop_separated_update';
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
            var lajax_url = delivery_mop_data_support_url+'delivery_mop/separated/product_get';
            var ljson_data = {product_price_list_id:$(lparent_pane).find('#delivery_mop_reference').val()};
            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
            var lresponse = lresult.response;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            
            fast_draw = APP_COMPONENT.table_fast_draw;
            var ltbody = $(lparent_pane).find('#delivery_mop_separated_product_table>tbody')[0];
            $(ltbody).empty();
            var lrow_num = 1;
            $.each(lresponse, function(key, product){
                var lrow = document.createElement('tr');
                var lrownum_td = fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_num,type:'text'});
                //var lcheckbox_td = fast_draw.col_add(lrow,{tag:'td',col_name:'is_selected',style:'',val:'<input type="checkbox">',type:'text'});
                var lproduct_id_td = fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'',val:product.product_id,type:'text',visible:false});
                var lproduct_name_td = fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'',val:product.product_name,type:'text'});
                
                var lunit_id_td = fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'',val:product.unit_id,type:'text',visible:false});
                var lunit_name_td = fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:product.unit_name,type:'text'});

                var lamount_td = fast_draw.col_add(lrow,{tag:'td',col_name:'amount',style:'',val:'',type:'input'});
                var lamount_input = $(lamount_td).find('input')[0];
                APP_EVENT.init().component_set(lamount_input).type_set('input').numeric_set().min_val_set(0).render();
    
                ltbody.appendChild(lrow);
                $(lamount_input).blur();
                lrow_num+=1;
                
            });
            if(lmethod === 'view'){
                
                var lajax_url = delivery_mop_data_support_url+'delivery_mop/separated/delivery_mop_separated_get';
                var ljson_data = {delivery_mop_id:$(lparent_pane).find('#delivery_mop_id').val()};
                var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
                var lresponse = lresult.response;
                $.each(lresponse,function(key, product){
                    $(ltbody).find('tr').filter(function(){
                        if($(this).find('[col_name="product_id"]').text().toString() === product.product_id.toString()
                            && $(this).find('[col_name="unit_id"]').text().toString() === product.unit_id.toString()
                        ){
                            $(this).find('[col_name="amount"]').find('input').val(product.amount).blur();
                        }
                    });
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

            json_data.delivery_mop_separated = [];

            var ltbody = $(lparent_pane).find('#delivery_mop_separated_product_table>tbody')[0];
            var ltrs = $(ltbody).find('tr');
            $.each(ltrs, function(key, val){
                var lamount = $(val).find('[col_name="amount"]').find('input').val().toString().replace(/[^0-9.]/g,'');
                if (parseFloat(lamount)>0){
                    var lproduct_id = $(val).find('[col_name="product_id"]').text();
                    var lunit_id=$(val).find('[col_name="unit_id"]').text();
                    json_data.delivery_mop_separated.push({
                        product_id:lproduct_id,
                        unit_id:lunit_id,
                        amount:lamount.replace(/[,]/g,'')
                    });
                }
            });
            
            
            
            switch(lmethod){
                case 'add':                    
                    lajax_url +='delivery_mop_separated_add';
                    break;
                case 'view':
                    var delivery_mop_id = $(lparent_pane).find('#delivery_mop_id').val();
                    lajax_url +='delivery_mop_separated_update/'+delivery_mop_id;
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