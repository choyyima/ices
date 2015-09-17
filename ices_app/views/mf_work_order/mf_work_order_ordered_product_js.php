<script>
var mf_work_order_ordered_product_methods = {
    
}

var mf_work_order_ordered_product_bind_event = function(){
    
    mf_work_order_ordered_product_table_method.setting.func_new_row_validation= function(iopt){
        var lmodule_type = mf_work_order_methods.module_type_get();
        var lresult = {success:1,msg:[]};
        var success = 0;
        var lrow = iopt.tr;
        var lproduct_id = $(lrow).find('[col_name="product_id"] span').text();
        var lunit_id = $(lrow).find('[col_name="unit_id"] span').text();
        var lbom_id = '';
        var lqty = '';
        
        if(lmodule_type === 'normal'){
            lbom_id = $(lrow).find('[col_name="bom_id"] span').text();
        }
        
        lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
        
        if(lmodule_type === 'normal'){
            if(lproduct_id !== '' && lunit_id !== '' && parseFloat(lqty) > parseFloat('0') && lbom_id !== ''){
                success = 1;
            }
        }
        else {
            if(lproduct_id !== '' && lunit_id !== '' && parseFloat(lqty) > parseFloat('0')){
                success = 1;
            }
        }
        
        lresult.success = success;
        return lresult;
    };
    
    mf_work_order_ordered_product_table_method.setting.func_get_data_table = function(){
        var lparent_pane = mf_work_order_parent_pane;
        var lprefix_id = mf_work_order_component_prefix_id;
        var lresult = [];
        var lmodule_type = mf_work_order_methods.module_type_get();
        
        var ltbody = $(lparent_pane).find(lprefix_id+'_ordered_product_table tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            var ltemp = {};            
            
            var lproduct_type = $(lrow).find('[col_name="product_type"] span').text();
            var lproduct_id = $(lrow).find('[col_name="product_id"] span').text();
            var lunit_id = $(lrow).find('[col_name="unit_id"] span').text();
            var lqty = '';
            
            if(lmodule_type === 'normal'){
                var lbom_id = $(lrow).find('[col_name="bom_id"] span').text();
            }
            
            if(lidx < ($(ltbody).find('tr').length - 1)){
                lqty = $(lrow).find('[col_name="qty"] span')[0].innerHTML.replace(/[^0-9.]/g,'');
                
            }
            else{
                lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
            }
            
            if(lmodule_type === 'normal'){
                if(lproduct_id !== '' && lunit_id !== '' 
                    && parseFloat(lqty)> parseFloat('0') && lbom_id !== ''
                ){
                    ltemp = {
                        product_type: lproduct_type,
                        product_id : lproduct_id,
                        unit_id : lunit_id,
                        qty:lqty,
                        bom_id:lbom_id
                    }

                    lresult.push(ltemp);
                }
            }
            else if($.inArray(lmodule_type,['good_stock_transform','bad_stock_transform'])!== -1){
                if(lproduct_id !== '' && lunit_id !== '' 
                    && parseFloat(lqty)> parseFloat('0')
                ){
                    ltemp = {
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
    
    mf_work_order_ordered_product_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = mf_work_order_parent_pane;
        var lprefix_id = mf_work_order_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        var lmodule_type = mf_work_order_methods.module_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        
        <?php // --- Show and Hide phase --- ?>
        $(lparent_pane).find(lprefix_id+'_ordered_product_table [col_name="outstanding_qty"]').hide();
        $(lparent_pane).find(lprefix_id+'_ordered_product_table [col_name="bom"]').hide();
        $(lparent_pane).find(lprefix_id+'_ordered_product_table [col_name="action"]').hide();
        if(lmethod === 'add'){
            $(lparent_pane).find(lprefix_id+'_ordered_product_table [col_name="action"]').show();
            if(lmodule_type === 'normal'){
                $(lparent_pane).find(lprefix_id+'_ordered_product_table [col_name="bom"]').show();

            }
        }
        else if(lmethod === 'view'){
            $(lparent_pane).find(lprefix_id+'_ordered_product_table [col_name="outstanding_qty"]').show();
            if(lmodule_type === 'normal'){
                $(lparent_pane).find(lprefix_id+'_ordered_product_table [col_name="bom"]').show();

            }
        }
        
        
        
        <?php // --- End Of Show and Hide phase --- ?>
        
        if(Object.keys(ldata_row).length === 0){
            var lproduct_inpt = $(lrow).find('[col_name="product"] input[original]')[0];
            var lunit_inpt = $(lrow).find('[col_name="unit"] input[original]')[0];
            var lqty = $(lrow).find('[col_name="qty"] input')[0];
            var lbom_inpt = null;
            
            if(lmodule_type === 'normal'){
                lbom_inpt = $(lrow).find('[col_name="bom"] input[original]')[0];
            }
            

            APP_COMPONENT.input_select.set(lproduct_inpt,{
                min_input_length:0
                ,ajax_url:mf_work_order_ajax_url+'input_select_ordered_product_search/'
                ,exceptional_data_func:function(){
                    var lparent_pane = mf_work_order_parent_pane;
                    var lprefix_id = mf_work_order_component_prefix_id;
                    var lresult = [];

                    $.each($(lparent_pane).find(lprefix_id+'_ordered_product_table tbody tr ')
                        ,function(lidx, lrow){
                            lresult.push({id:$(lrow).find('[col_name="product_id"] span').text()});
                    });
                    
                    return lresult;
                }
                
            },
            function(){
                var lmodule_type = mf_work_order_methods.module_type_get();
                var lresult = {mf_work_order_type:lmodule_type};
                return lresult;
            });       
    
            $(lproduct_inpt).on('change',function(){
                var lid = $(this).select2('val');
                var lrow = $(this).closest('tr');
                $(lrow).find('[col_name="product_id"] span').text(lid);
                var lunit_inpt = $(lrow).find('[col_name="unit"] input[original]')[0];
                
                
                $(lunit_inpt).select2('data',null);
                $(lunit_inpt).select2({data:[]});
                $(lunit_inpt).change();
                $(lrow).find('[col_name="product_img"]')[0].innerHTML ='<?php echo Product_Engine::img_get(null);?>';
                $(lrow).find('[col_name="product_type"] span')[0].innerHTML ='';

                if(lid!== ''){
                    var ldata = $(this).select2('data');
                    $(lrow).find('[col_name="product_type"] span')[0].innerHTML =ldata.product_type;
                    $(lunit_inpt).select2({data:ldata.unit});
                    if(Object.keys(ldata.unit).length > 0){
                        $(lunit_inpt).select2('data',ldata.unit[0]).change();
                    }
                    $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata.product_img;
                }

            });


            APP_COMPONENT.input_select.set(lunit_inpt,{
                min_input_length:0
                ,allow_clear:false
            });

            $(lunit_inpt).on('change',function(){
                var lid = $(this).select2('val');
                var lrow = $(this).closest('tr');
                $(lrow).find('[col_name="unit_id"] span').text(lid);
                var lmodule_type = mf_work_order_methods.module_type_get();
                
                if(lmodule_type === 'normal'){
                    var lbom_inpt = $(lrow).find('[col_name="bom"] input[original]')[0];
                    $(lbom_inpt).select2('data',null);
                    $(lbom_inpt).select2({data:[]});
                    $(lbom_inpt).change();
                }
                
                if(lid !== ''){
                    var ldata = $(this).select2('data');
                    if(lmodule_type === 'normal'){
                        if(Object.keys(ldata.bom).length > 0){
                            $(lbom_inpt).select2({data:ldata.bom});
                            $(lbom_inpt).select2('data',ldata.bom[0]).change();
                        }
                    }
                }
            });

            APP_COMPONENT.input.numeric(lqty,{min_val:'0'});
            $(lqty).val('1');
            $(lqty).blur();
            
            if(lmodule_type === 'normal'){
                APP_COMPONENT.input_select.set(lbom_inpt,{
                    min_input_length:0
                    ,allow_clear:false
                });
                
                $(lbom_inpt).on('change',function(){
                    var lid = $(this).select2('val');
                    var lrow = $(this).closest('tr');
                    $(lrow).find('[col_name="bom_id"] span').text(lid);
                    var lmodule_type = mf_work_order_methods.module_type_get();
                });
            }
        }
    }

    mf_work_order_ordered_product_table_method.setting.func_row_transform_comp_on_new_row = function(iopt){
        var lmodule_type = mf_work_order_methods.module_type_get();
        var lrow = iopt.tr;
        var lproduct_data = $(lrow).find('[col_name="product"] input[original]').select2('data');
        var lunit_data = $(lrow).find('[col_name="unit"] input[original]').select2('data');
        var lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
        
        if(lmodule_type === 'normal'){
            var lbom_data = $(lrow).find('[col_name="bom"] input[original]').select2('data');
        }
        $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+lproduct_data.text+'</span>';
        $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+lunit_data.text+'</span>';
        $(lrow).find('[col_name="qty"]')[0].innerHTML = '<span>'+lqty+'</span>';
        
        if(lmodule_type === 'normal'){
            $(lrow).find('[col_name="bom"]')[0].innerHTML = '<span>'+lbom_data.text+'</span>';
        }
    }

    mf_work_order_ordered_product_table_method.setting.func_row_data_assign = function(iopt){
        var lparent_pane = mf_work_order_parent_pane;
        var lprefix_id = mf_work_order_component_prefix_id;
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        var lmodule_type = mf_work_order_methods.module_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        if(Object.keys(ldata_row).length > 0){
            $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata_row.product_img;
            $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<span>'+ldata_row.product_id+'</span>';
            $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<span>'+ldata_row.product_type+'</span>';
            $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+ldata_row.product_text+'</span>';
            $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<span>'+ldata_row.unit_id+'</span>';
            $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+ldata_row.unit_text+'</span>';
            $(lrow).find('[col_name="qty"]')[0].innerHTML  = '<span>'+APP_CONVERTER.thousand_separator(ldata_row.qty)+'</span>';
            $(lrow).find('[col_name="outstanding_qty"]')[0].innerHTML  = '<span style="color:red">'+APP_CONVERTER.thousand_separator(ldata_row.outstanding_qty)+'</span>';
            
            if(lmodule_type === 'normal'){
                $(lrow).find('[col_name="bom_id"]')[0].innerHTML = '<span>'+ldata_row.bom_id+'</span>';
                $(lrow).find('[col_name="bom"]')[0].innerHTML = '<span>'+ldata_row.bom_text+'</span>';
            
            }
        }
    }
}
</script>