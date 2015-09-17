<script>
var bom_result_product_methods = {
    load_result_product:function(lrow){
        var lparent_pane = bom_parent_pane;
        var lprefix_id = bom_component_prefix_id;
        var lproduct_text = $(lparent_pane).find(lprefix_id+'_name').val();
        var lproduct_id = $(lparent_pane).find(lprefix_id+'_id').val();
        var lunit_text = $(lrow).find('[name="code"]').text();
        var lunit_id = $(lrow).find('[name="id"]').text();
        var ldata = JSON.parse($(lrow).find('[name="product_unit_parent_child"] a').attr('data'));
        var lparent_qty = typeof ldata.parent_qty ==='undefined'?'1':ldata.parent_qty;
        
        $(lparent_pane).find(lprefix_id+'_parent_product_id').val(lproduct_id);
        $(lparent_pane).find(lprefix_id+'_parent_product').val(lproduct_text);
        $(lparent_pane).find(lprefix_id+'_parent_unit_id').val(lunit_id);
        $(lparent_pane).find(lprefix_id+'_parent_unit').val(lunit_text);
        $(lparent_pane).find(lprefix_id+'_parent_qty').val(lparent_qty).blur();
        
        bom_result_product_table_method.reset();
        bom_result_product_table_method.head_generate();

        $.each(ldata.product_unit_child,function(lidx, lrow){
            bom_result_product_table_method.input_row_generate(lrow);
        });
        bom_result_product_table_method.input_row_generate({});
        
        $(lparent_pane).find(lprefix_id+'_modal_btn_submit').off();
        $(lparent_pane).find(lprefix_id+'_modal_btn_submit').on('click',function(){
            var ldata = JSON.stringify(bom_result_product_table_method.setting.func_get_data_table());
            var lparent_pane = bom_parent_pane;
            var lprefix_id = bom_component_prefix_id;
            var lparent_unit_id = $(lparent_pane).find(lprefix_id+'_parent_unit_id').val();;
            var lrow = $(lparent_pane).find(lprefix_id+'_unit_table tbody tr [name="id"]:contains("'+lparent_unit_id+'")').closest('tr');
            $(lrow).find('[name="product_unit_parent_child"] a').attr('data',ldata);
            $(lparent_pane).find(lprefix_id+'_modal_bom_result_product').modal('hide');
            $(lparent_pane).find(lprefix_id+'_modal_btn_submit').off();
        });
        
        $(lparent_pane).find(lprefix_id+'_modal_btn_cancel').off();
        $(lparent_pane).find(lprefix_id+'_modal_btn_cancel').on('click',function(){
            $(lparent_pane).find(lprefix_id+'_modal_bom_result_product').modal('hide');
        });
        
        
        $(lparent_pane).find(lprefix_id+'_modal_bom_result_product').modal('show');
    }
}

var bom_result_product_bind_event = function(){
    
    bom_result_product_table_method.setting.func_get_data_table = function(){
        var lparent_pane = bom_parent_pane;
        var lprefix_id = bom_component_prefix_id;
        var lresult = {};
        
        var ltbody = $(lparent_pane).find(lprefix_id+'_result_product_table tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            var ltemp = {};            
            
            var lproduct_type = $(lrow).find('[col_name="product_type"] span').text();
            var lproduct_id = $(lrow).find('[col_name="product_id"] span').text();
            var lunit_id = $(lrow).find('[col_name="unit_id"] span').text();            
            var lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
            ltemp = {product_id:lproduct_id,unit_id:lunit_id,qty:lqty,product_type:lproduct_type};
            lresult = ltemp;
            
        });
        return lresult;
    };
    
    bom_result_product_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = bom_parent_pane;
        var lprefix_id = bom_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        

        var lproduct_inpt = $(lrow).find('[col_name="product"] input[original]')[0];
        var lunit_inpt = $(lrow).find('[col_name="unit"] input[original]')[0];
        var lqty = $(lrow).find('[col_name="qty"] input')[0];

        APP_COMPONENT.input_select.set(lproduct_inpt,{
            min_input_length:0
            ,ajax_url:bom_ajax_url+'input_select_bom_result_product_search/'
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

    bom_result_product_table_method.setting.func_row_data_assign = function(iopt){
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        if(Object.keys(ldata_row).length > 0){
            $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata_row.product_img;
            $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<span>'+ldata_row.product_id+'</span>';
            $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<span>'+ldata_row.product_type+'</span>';
            $(lrow).find('[col_name="product"] input[original]').select2('data',{id:ldata_row.product_id,text:ldata_row.product_text});
            $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<span>'+ldata_row.unit_id+'</span>';
            $(lrow).find('[col_name="unit"] input[original]').select2('data',{id:ldata_row.unit_id,text:ldata_row.unit_text});
            $(lrow).find('[col_name="qty"] input').val(ldata_row.qty);
            $(lrow).find('[col_name="qty"] input').blur();
        }
    }
}
</script>