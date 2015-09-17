<script>
var mf_work_process_scrap_product_methods = {
    load_available_scrap_product:function(){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                
        mf_work_process_scrap_product_table_method.reset();
        mf_work_process_scrap_product_table_method.head_generate();
        var ljson_data = {
            mf_work_process_id:$(lparent_pane).find(lprefix_id+'_id').val()
        };
        var lresponse = APP_DATA_TRANSFER.ajaxPOST(mf_work_process_data_support_url+'available_scrap_product_get/',ljson_data).response;
        
        $.each(lresponse, function(lidx, lrow){
            mf_work_process_scrap_product_table_method.input_row_generate(lrow);
        });
        
        
    }
    
}

var mf_work_process_scrap_product_bind_event = function(){
    
    mf_work_process_scrap_product_table_method.setting.func_new_row_validation= function(iopt){
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lresult = {success:1,msg:[]};
        var success = 0;
        var lrow = iopt.tr;
        var lproduct_id = $(lrow).find('[col_name="product_id"] span').text();
        var lunit_id = $(lrow).find('[col_name="unit_id"] span').text();
        var lqty = '';
                
        lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
        
        if(lproduct_id !== '' && lunit_id !== '' && parseFloat(lqty) > parseFloat('0')){
            success = 1;
        }
        
        lresult.success = success;
        return lresult;
    };
    
    mf_work_process_scrap_product_table_method.setting.func_get_data_table = function(){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lresult = [];
        var lmodule_type = mf_work_process_methods.module_type_get();
        
        var ltbody = $(lparent_pane).find(lprefix_id+'_scrap_product_table tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            var ltemp = {};            
            
            var lproduct_type = $(lrow).find('[col_name="product_type"] span').text();
            var lproduct_id = $(lrow).find('[col_name="product_id"] span').text();
            var lunit_id = $(lrow).find('[col_name="unit_id"] span').text();
            var lstock_location = $(lrow).find('[col_name="stock_location_id"] span').text();;
            var lqty = '';
            
            if($(lrow).find('[col_name="qty"] input').length > 0){
                lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                
            }
            else{
                lqty = $(lrow).find('[col_name="qty"] span')[0].innerHTML.replace(/[^0-9.]/g,'');
            }
            
            if(lproduct_id !== '' && lunit_id !== '' 
                && parseFloat(lqty)> parseFloat('0')
            ){
                ltemp = {
                    product_type: lproduct_type,
                    product_id : lproduct_id,
                    unit_id : lunit_id,
                    qty:lqty,
                    stock_location:lstock_location,
                }

                lresult.push(ltemp);
            }
            
        });
        return lresult;
    };
    
    mf_work_process_scrap_product_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        
        <?php // --- Show and Hide phase --- ?>
        var ltable = $(lparent_pane).find(lprefix_id+'_scrap_product_table')[0];
        
        
        if(lmethod === 'add'){        
        
        }
        else if(lmethod === 'view'){
            
        }
        
        
        
        <?php // --- End Of Show and Hide phase --- ?>
        
        if(Object.keys(ldata_row).length === 0){
                        
        }
    }

    mf_work_process_scrap_product_table_method.setting.func_row_transform_comp_on_new_row = function(iopt){
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lrow = iopt.tr;
        var lproduct_data = $(lrow).find('[col_name="product"] input[original]').select2('data');
        var lunit_data = $(lrow).find('[col_name="unit"] input[original]').select2('data');
        var lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
        
    }

    mf_work_process_scrap_product_table_method.setting.func_row_data_assign = function(iopt){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        var sir_exists = $(lparent_pane).find(lprefix_id+'_sir_checkbox').is(':checked');
        if(Object.keys(ldata_row).length > 0){
            var lcurr_status = mf_work_process_data.current_status;
            if(lcurr_status === 'process'){
                $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata_row.product_img;
                $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<span>'+ldata_row.product_id+'</span>';
                $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<span>'+ldata_row.product_type+'</span>';
                $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+ldata_row.product_text+'</span>';
                $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<span>'+ldata_row.unit_id+'</span>';
                $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+ldata_row.unit_text+'</span>';
                $(lrow).find('[col_name="stock_location_id"]')[0].innerHTML  = '<span>'+ldata_row.stock_location+'</span>';
                $(lrow).find('[col_name="stock_location"]')[0].innerHTML  = '<span>'+ldata_row.stock_location_text+'</span>';

                $(lrow).find('[col_name="qty"]')[0].innerHTML = '<input class="form-control" style="text-align:right">';
                var lqty_inpt = $(lrow).find('[col_name="qty"] input')[0];
                APP_COMPONENT.input.numeric(lqty_inpt,{min_val:0,max_val:ldata_row.max_qty});
                $(lqty_inpt).val('').blur();

                $(lrow).find('[col_name="action"]')[0].innerHTML = '<span></span>';
            }
            else if ($.inArray(lcurr_status,['done','X']) !== -1){
                $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata_row.product_img;
                $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<span>'+ldata_row.product_id+'</span>';
                $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<span>'+ldata_row.product_type+'</span>';
                $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+ldata_row.product_text+'</span>';
                $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<span>'+ldata_row.unit_id+'</span>';
                $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+ldata_row.unit_text+'</span>';
                $(lrow).find('[col_name="stock_location_id"]')[0].innerHTML  = '<span>'+ldata_row.stock_location+'</span>';
                $(lrow).find('[col_name="stock_location"]')[0].innerHTML  = '<span>'+ldata_row.stock_location_text+'</span>';

                $(lrow).find('[col_name="qty"]')[0].innerHTML = '<span>'+APP_CONVERTER.thousand_separator(ldata_row.qty)+'</span>';

                $(lrow).find('[col_name="action"]')[0].innerHTML = '<span></span>';
            }
        }
    }
}
</script>