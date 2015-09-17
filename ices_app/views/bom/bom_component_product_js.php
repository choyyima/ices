<script>
var bom_component_product_methods = {
    
}

var bom_component_product_bind_event = function(){
    bom_component_product_table_method.setting.func_new_row_validation= function(iopt){
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
    
    bom_component_product_table_method.setting.func_get_data_table = function(){
        var lparent_pane = bom_parent_pane;
        var lprefix_id = bom_component_prefix_id;
        var lresult = [];
        
        var ltbody = $(lparent_pane).find(lprefix_id+'_component_product_table tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            var ltemp = {};            
            
            var lproduct_type = $(lrow).find('[col_name="product_type"] span').text();
            var lproduct_id = $(lrow).find('[col_name="product_id"] span').text();
            var lunit_id = $(lrow).find('[col_name="unit_id"] span').text();
            var lqty = '';
            if(lidx < ($(ltbody).find('tr').length - 1)){
                lqty = $(lrow).find('[col_name="qty"] span')[0].innerHTML.replace(/[^0-9.]/g,'');
            }
            else{
                lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
            }
            
            if(lproduct_id !== '' && lunit_id !== '' && parseFloat(lqty)> parseFloat('0')
            ){
                ltemp = {
                    product_type: lproduct_type,
                    product_id : lproduct_id,
                    unit_id : lunit_id,
                    qty:lqty
                }
                
                lresult.push(ltemp);
            }
        });
        return lresult;
    };
    
    bom_component_product_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = bom_parent_pane;
        var lprefix_id = bom_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        
        if(Object.keys(ldata_row).length === 0){
            var lproduct_inpt = $(lrow).find('[col_name="product"] input[original]')[0];
            var lunit_inpt = $(lrow).find('[col_name="unit"] input[original]')[0];
            var lqty = $(lrow).find('[col_name="qty"] input')[0];

            APP_COMPONENT.input_select.set(lproduct_inpt,{
                min_input_length:0
                ,ajax_url:bom_ajax_url+'input_select_bom_component_product_search/'
                ,exceptional_data_func:function(){
                    var lparent_pane = bom_parent_pane;
                    var lprefix_id = bom_component_prefix_id;
                    var lresult = [];

                    $.each($(lparent_pane).find(lprefix_id+'_result_product_table tbody tr ')
                        ,function(lidx, lrow){
                            lresult.push({id:$(lrow).find('[col_name="product_id"] span').text()});
                    });
                    $.each($(lparent_pane).find(lprefix_id+'_component_product_table tbody tr ')
                        ,function(lidx, lrow){
                            lresult.push({id:$(lrow).find('[col_name="product_id"] span').text()});
                    });
                    return lresult;
                }
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
            });

            APP_COMPONENT.input.numeric(lqty,{min_val:'0'});
            $(lqty).val('1');
            $(lqty).blur();
        }
    }
    
    bom_component_product_table_method.setting.func_row_transform_comp_on_new_row = function(iopt){
        var lrow = iopt.tr;
        var lproduct_data = $(lrow).find('[col_name="product"] input[original]').select2('data');
        var lunit_data = $(lrow).find('[col_name="unit"] input[original]').select2('data');
        var lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
        $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+lproduct_data.text+'</span>';
        $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+lunit_data.text+'</span>';
        $(lrow).find('[col_name="qty"]')[0].innerHTML = '<span>'+lqty+'</span>';
    }

    bom_component_product_table_method.setting.func_row_data_assign = function(iopt){
       var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        if(Object.keys(ldata_row).length > 0){
            $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<span>'+ldata_row.product_type+'</span>';
            $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<span>'+ldata_row.product_id+'</span>';
            $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+ldata_row.product_text+'</span>';
            $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<span>'+ldata_row.unit_id+'</span>';
            $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+ldata_row.unit_text+'</span>';
            $(lrow).find('[col_name="qty"]')[0].innerHTML = '<span>'+APP_CONVERTER.thousand_separator(ldata_row.qty)+'</span>';
            bom_component_product_table_method.components.trash_set(iopt);
        }
    }
}
</script>