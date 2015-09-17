<script>
    var product_unit_conversion_sales_real_weight_methods = {
        show:function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            $(lparent_pane).find('#product_unit_conversion_qty_1').closest('.form-group').show();
            $(lparent_pane).find('#product_unit_conversion_qty_2').closest('.form-group').show();
            $(lparent_pane).find('#product_unit_conversion_unit_1').closest('.form-group').show();
            $(lparent_pane).find('#product_unit_conversion_unit_2').closest('.form-group').show();
            $(lparent_pane).find('#product_unit_conversion_status').closest('.form-group').show();
        },
        enable:function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            var lmethod = $(lparent_pane).find('#product_unit_conversion_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#product_unit_conversion_qty_1').prop('disabled',false);
                    $(lparent_pane).find('#product_unit_conversion_qty_2').prop('disabled',false);
                    $(lparent_pane).find('#product_unit_conversion_unit_1').select2('enable');
                    $(lparent_pane).find('#product_unit_conversion_unit_2').select2('enable');
                    $(lparent_pane).find('#product_unit_conversion_expedition').select2('enable');
                    break;
                case 'view':           
                    $(lparent_pane).find('#product_unit_conversion_status').select2('enable');
                    break;
            }
        },
        data_set:function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            var lmethod = $(lparent_pane).find('#product_unit_conversion_method').val();
            switch(lmethod){
                case 'add':
                    product_unit_conversion_methods.Unit_1_set();
                    break;
                case 'view':             
                    var lproduct_unit_conversion_id = $(lparent_pane).find('#product_unit_conversion_id').val();
                    var lajax_url = product_unit_conversion_data_support_url+'product_unit_conversion_sales_real_weight_get';
                    var ljson_data = {product_unit_conversion_id: lproduct_unit_conversion_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
                    var lresponse = lresult.response;
                    $(lparent_pane).find('#product_unit_conversion_qty_1').val(lresponse.qty_1).blur();
                    $(lparent_pane).find('#product_unit_conversion_qty_2').val(lresponse.qty_2).blur();
                    $(lparent_pane).find('#product_unit_conversion_unit_1').select2('data',{id:lresponse.unit_id_1,text:lresponse.unit_name_1});
                    $(lparent_pane).find('#product_unit_conversion_unit_2').select2('data',{id:lresponse.unit_id_2,text:lresponse.unit_name_2});
                    $(lparent_pane).find('#product_unit_conversion_status').select2('data',{id:lresponse.product_unit_conversion_status,text:lresponse.product_unit_conversion_status_text}).change();
                    $(lparent_pane).find('#product_unit_conversion_status').select2({'data':lresponse.product_unit_conversion_status_list});
                    break;
            }
        },
        submit: function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            var lajax_url = product_unit_conversion_index_url;
            var lmethod = $(lparent_pane).find('#product_unit_conversion_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            json_data.product_unit_conversion = {
                type:"sales_real_weight",
                product_id:$(lparent_pane).find('#product_unit_conversion_reference').val(),
                qty_1:$(lparent_pane).find('#product_unit_conversion_qty_1').val(),
                unit_id_1:$(lparent_pane).find('#product_unit_conversion_unit_1').select2('val'),
                qty_2:$(lparent_pane).find('#product_unit_conversion_qty_2').val(),
                unit_id_2:$(lparent_pane).find('#product_unit_conversion_unit_2').select2('val'),
            };
            //lajax_url +='product_unit_conversion_add';
            switch($(lparent_pane).find('#product_unit_conversion_method').val()){
                case 'add':
                    lajax_url +='product_unit_conversion_add';
                    break;
                case 'view':
                    lajax_url +='product_unit_conversion_'+
                        $(lparent_pane).find('#product_unit_conversion_status').select2('val')
                        +'/'+$(lparent_pane).find('#product_unit_conversion_id').val();
                    break;
            }
            result = null;
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            
            if(result !== null){
                if(result.success ===1){
                    $(lparent_pane).find('#product_unit_conversion_id').val(result.trans_id);
                    if(product_unit_conversion_view_url !==''){
                        var url = product_unit_conversion_view_url+result.trans_id;
                        window.location.href=url;
                    }
                    else{
                        product_unit_conversion_after_submit();
                    }
                }
            }
        },
    }
</script>