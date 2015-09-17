<script>
var mf_work_process_expected_result_product_methods = {
    load_modal:function(){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var ldata = JSON.parse($(lparent_pane).find(lprefix_id+'_btn_set_component_product').attr('data'));
        var lmodal = $(lprefix_id+'_modal_expected_result_product')[0];
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        
        $(lmodal).find(' .modal-title strong')[0].innerHTML =
            $(lparent_pane).find(lprefix_id+'_reference').select2('data').text;
        
                
        if(lmethod === 'add'){
            
            mf_work_process_expected_result_product_table_method.reset();
            mf_work_process_expected_result_product_table_method.head_generate();
            
            var ljson_data = {
                reference_id:$(lparent_pane).find(lprefix_id+'_reference').select2('val')
                ,warehouse_id:$(lparent_pane).find(lprefix_id+'_warehouse').select2('val')
            };
            var lresponse = APP_DATA_TRANSFER.ajaxPOST(mf_work_process_data_support_url+'available_expected_result_product_get/',ljson_data).response;
            $.each(lresponse, function(lidx, lrow){
                mf_work_process_expected_result_product_table_method.input_row_generate(lrow);
            });

            $.each(ldata, function(li, lrow){
                var ltrs = $(lmodal).find(lprefix_id+'_expected_result_product_table tbody tr');
                $.each(ltrs, function(li_tr, ltr){
                    if(
                        $(ltr).find('[col_name="reference_type"] span').text() === lrow.reference_type
                        && $(ltr).find('[col_name="reference_id"] span').text() === lrow.reference_id
                    ){
                        if(lmethod === 'add'){
                            $(ltr).find('[col_name="qty"] input').val(lrow.qty);
                            $(ltr).find('[col_name="qty"] input').blur();
                        }
                        else if(lmethod ==='view'){
                            $(ltr).find('[col_name="qty"]')[0].innerHTML = '<span>'+APP_CONVERTER.thousand_separator(lrow.qty)+'</span>';
                        }
                    }
                });
            });
        
        }
        
        APP_COMPONENT.modal.fade_out_another($(lmodal));
        
        $(lmodal).modal('show');
        
        $(lmodal).on('hidden.bs.modal',function(){
            $('.modal').css('z-index','');
        });
        
    }
}

var mf_work_process_expected_result_product_bind_event = function(){
    var lparent_pane = mf_work_process_parent_pane;
    var lprefix_id = mf_work_process_component_prefix_id;
    
    mf_work_process_expected_result_product_table_method.setting.func_new_row_validation= function(iopt){
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lresult = {success:1,msg:[]};
        var success = 0;
        var lrow = iopt.tr;
        
        lresult.success = success;
        return lresult;
    };
    
    mf_work_process_expected_result_product_table_method.setting.func_get_data_table = function(){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lresult = [];
        var lmodule_reference_type = mf_work_process_methods.reference_type_get();
        var lmodal = $(lprefix_id+'_modal_expected_result_product')[0];
        
        var ltbody = $(lmodal).find(' tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            var ltemp = {};            
            var lreference_type = $(lrow).find('[col_name="reference_type"] span').text();
            var lreference_id = $(lrow).find('[col_name="reference_id"] span').text();
            var lproduct_type = $(lrow).find('[col_name="product_type"] span').text();
            var lproduct_id = $(lrow).find('[col_name="product_id"] span').text();
            var lunit_id = $(lrow).find('[col_name="unit_id"] span').text();
            var lbom_id = $(lrow).find('[col_name="bom_id"] span').text();
            var lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');;
            
            
            if(lmodule_reference_type === 'normal'){
                if(lproduct_id !== '' && lunit_id !== '' 
                    && parseFloat(lqty)> parseFloat('0') 
                ){
                    ltemp = {
                        reference_type: lreference_type,
                        reference_id: lreference_id,
                        product_type: lproduct_type,
                        product_id : lproduct_id,
                        unit_id : lunit_id,
                        qty:lqty,
                        bom_id:lbom_id
                    }

                    lresult.push(ltemp);
                }
            }
            else if($.inArray(lmodule_reference_type,['good_stock_transform','bad_stock_transform'])!== -1){
                if(lproduct_id !== '' && lunit_id !== '' 
                    && parseFloat(lqty)> parseFloat('0')
                ){
                    ltemp = {
                        reference_type: lreference_type,
                        reference_id: lreference_id,
                        product_type: lproduct_type,
                        product_id : lproduct_id,
                        unit_id : lunit_id,
                        qty:lqty,
                    }

                    lresult.push(ltemp);
                }
            }
        });
        return lresult;
    };
    
    mf_work_process_expected_result_product_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        var lmodal = $(lprefix_id+'_modal_expected_result_product')[0];
        var ltable = $(lmodal).find(lprefix_id+'_expected_result_product_table');
        
        <?php // --- Show and Hide phase --- ?>
        $(ltable).find('[col_name="bom"]').hide();
        $(ltable).find('[col_name="outstanding_qty"]').hide();
        $(ltable).find('[col_name="max_qty"]').hide();
        $(ltable).find('[col_name="ordered_qty"]').hide();
        if(lmethod === 'add'){
            $(ltable).find('[col_name="outstanding_qty"]').show();
            $(ltable).find('[col_name="max_qty"]').show();
            $(ltable).find('[col_name="ordered_qty"]').show();
            if(lmodule_type === 'normal'){
                $(ltable).find('[col_name="bom"]').show();
            }
        }
        else if(lmethod === 'view'){
            if(lmodule_type === 'normal'){
                $(ltable).find('[col_name="bom"]').show();

            }
        }
        
        
        
        <?php // --- End Of Show and Hide phase --- ?>
        
        if(Object.keys(ldata_row).length === 0){
            
        }
        
        
        
    }

    mf_work_process_expected_result_product_table_method.setting.func_row_transform_comp_on_new_row = function(iopt){
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lrow = iopt.tr;
    }

    mf_work_process_expected_result_product_table_method.setting.func_row_data_assign = function(iopt){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        var lreference_type = mf_work_process_methods.reference_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        

        if(Object.keys(ldata_row).length > 0){
            if(lmethod === 'add'){
                $(lrow).find('[col_name="reference_type"]')[0].innerHTML = '<span>'+ldata_row.reference_type+'</span>';
                $(lrow).find('[col_name="reference_id"]')[0].innerHTML = '<span>'+ldata_row.reference_id+'</span>';
                $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata_row.product_img;
                $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<span>'+ldata_row.product_id+'</span>';
                $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<span>'+ldata_row.product_type+'</span>';
                $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+ldata_row.product_text+'</span>';
                $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<span>'+ldata_row.unit_id+'</span>';
                $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+ldata_row.unit_text+'</span>';
                $(lrow).find('[col_name="ordered_qty"]')[0].innerHTML  = '<span>'+APP_CONVERTER.thousand_separator(ldata_row.ordered_qty)+'</span>';
                $(lrow).find('[col_name="outstanding_qty"]')[0].innerHTML  = '<span>'+APP_CONVERTER.thousand_separator(ldata_row.outstanding_qty)+'</span>';
                $(lrow).find('[col_name="max_qty"]')[0].innerHTML  = '<span>'+APP_CONVERTER.thousand_separator(ldata_row.max_qty)+'</span>';
                APP_COMPONENT.input.numeric($(lrow).find('[col_name="qty"] input')[0],{min_val:0,max_val:ldata_row.max_qty});
                $(lrow).find('[col_name="qty"] input').blur();

                if(lreference_type === 'normal'){
                    $(lrow).find('[col_name="bom_id"]')[0].innerHTML = '<span>'+ldata_row.bom_id+'</span>';
                    $(lrow).find('[col_name="bom"]')[0].innerHTML = '<span>'+ldata_row.bom_text+'</span>';

                }
            }
            else if (lmethod === 'view'){
                $(lrow).find('[col_name="reference_type"]')[0].innerHTML = '<span>'+ldata_row.reference_type+'</span>';
                $(lrow).find('[col_name="reference_id"]')[0].innerHTML = '<span>'+ldata_row.reference_id+'</span>';
                $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata_row.product_img;
                $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<span>'+ldata_row.product_id+'</span>';
                $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<span>'+ldata_row.product_type+'</span>';
                $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+ldata_row.product_text+'</span>';
                $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<span>'+ldata_row.unit_id+'</span>';
                $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+ldata_row.unit_text+'</span>';
                $(lrow).find('[col_name="qty"]')[0].innerHTML = '<span>'+APP_CONVERTER.thousand_separator(ldata_row.qty)+'</span>';
                if(lreference_type === 'normal'){
                    $(lrow).find('[col_name="bom_id"]')[0].innerHTML = '<span>'+ldata_row.bom_id+'</span>';
                    $(lrow).find('[col_name="bom"]')[0].innerHTML = '<span>'+ldata_row.bom_text+'</span>';

                }
            }
        }

    }
    
    $(lprefix_id+'_modal_expected_result_product_btn_submit').off();
    $(lprefix_id+'_modal_expected_result_product_btn_submit').on('click',function(){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lmodal = $(lprefix_id+'_modal_expected_result_product')[0];
        var lbtn_set_component_product = $(lparent_pane).find(lprefix_id+'_btn_set_component_product')[0];
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        
        if(lmethod === 'add'){
            $(lparent_pane).find(lprefix_id+'_sir_checkbox').iCheck('uncheck');

            var ldata = mf_work_process_expected_result_product_table_method.setting.func_get_data_table();
            $(lbtn_set_component_product).attr('data',JSON.stringify(ldata));

            var lajax_url = mf_work_process_data_support_url+'available_component_product_get/';
            var ljson_data = {
                module_type:mf_work_process_methods.reference_type_get(),
                warehouse_id:$(lparent_pane).find(lprefix_id+'_warehouse').select2('val'),
                expected_product:ldata
            };
            var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, ljson_data).response;        
            mf_work_process_component_product_table_method.reset();
            mf_work_process_component_product_table_method.head_generate();
            $.each(lresponse, function(li, lrow){
                mf_work_process_component_product_table_method.input_row_generate(lrow);
            });
        }
        
        $(lmodal).modal('hide');
    });
    
    $(lprefix_id+'_modal_expected_result_product_btn_cancel').off();
    $(lprefix_id+'_modal_expected_result_product_btn_cancel').on('click',function(){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lmodal = $(lprefix_id+'_modal_expected_result_product')[0];
        
        $(lmodal).modal('hide');
    });
}
</script>