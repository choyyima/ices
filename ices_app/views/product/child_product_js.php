<script>
var child_product_methods = {
    load_modal:function(lrow){
        var lparent_pane = product_parent_pane;
        var lprefix_id = product_component_prefix_id;
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

        product_child_product_method.reset();
        product_child_product_method.head_generate();
        
        
        $.each(ldata.product_unit_child,function(lidx, lrow){
            product_child_product_method.input_row_generate(lrow);
        });
        
        product_child_product_method.input_row_generate({});
        
        $(lparent_pane).find(lprefix_id+'_modal_btn_submit').off();
        $(lparent_pane).find(lprefix_id+'_modal_btn_submit').on('click',function(){
            var ldata = JSON.stringify(product_child_product_method.setting.func_get_data_table());
            var lparent_pane = product_parent_pane;
            var lprefix_id = product_component_prefix_id;
            var lparent_unit_id = $(lparent_pane).find(lprefix_id+'_parent_unit_id').val();;
            var lrow = $(lparent_pane).find(lprefix_id+'_unit_table tbody tr [name="id"]:contains("'+lparent_unit_id+'")').closest('tr');
            $(lrow).find('[name="product_unit_parent_child"] a').attr('data',ldata);
            $(lparent_pane).find(lprefix_id+'_modal_child_product').modal('hide');
            $(lparent_pane).find(lprefix_id+'_modal_btn_submit').off();
        });
        
        $(lparent_pane).find(lprefix_id+'_modal_btn_cancel').off();
        $(lparent_pane).find(lprefix_id+'_modal_btn_cancel').on('click',function(){
            $(lparent_pane).find(lprefix_id+'_modal_child_product').modal('hide');
        });
        
        
        $(lparent_pane).find(lprefix_id+'_modal_child_product').modal('show');
    }
}

var child_product_bind_event = function(){
    product_child_product_method.setting.func_new_row_validation= function(iopt){
        var lresult = {success:1,msg:[]};
        var success = 0;
        var lrow = iopt.tr;
        var lchild_product_id = $(lrow).find('[col_name="child_product_id"] span').text();
        var lchild_unit_id = $(lrow).find('[col_name="child_unit_id"] span').text();
        
        if(lchild_product_id !== '' && lchild_unit_id !== ''){
            success = 1;
        }
        lresult.success = success;
        return lresult;
    },
    
    product_child_product_method.setting.func_get_data_table = function(){
        var lparent_pane = product_parent_pane;
        var lprefix_id = product_component_prefix_id;
        var lparent_product_id = $(lparent_pane).find(lprefix_id+'_parent_product_id').val();
        var lparent_unit_id = $(lparent_pane).find(lprefix_id+'_parent_unit_id').val();
        var lparent_qty = $(lparent_pane).find(lprefix_id+'_parent_qty').val().replace(/[^0-9.]/g,'');
        var lresult = {parent_qty:lparent_qty,product_unit_child:[]};
        
        var ltbody = $(lparent_pane).find(lprefix_id+'_child_product tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            var ltemp = {};            
            
            var lchild_product_id = $(lrow).find('[col_name="child_product_id"] span').text();
            var lchild_product_text = '';
            if(lidx < ($(ltbody).find('tr').length - 1)){
                lchild_product_text = $(lrow).find('[col_name="child_product"] span')[0].innerHTML;
            }
            else{
                var lval = $(lrow).find('[col_name="child_product"] input[original]').select2('val');
                if(lval!=='') lchild_product_text = $(lrow).find('[col_name="child_product"] input[original]').select2('data').text;
            }
            
            var lchild_unit_id = $(lrow).find('[col_name="child_unit_id"] span').text();
            var lchild_unit_text = '';
            if(lidx < ($(ltbody).find('tr').length - 1)){
                lchild_unit_text = $(lrow).find('[col_name="child_unit"] span')[0].innerHTML;
            }
            else{
                var lval = $(lrow).find('[col_name="child_unit"] input[original]').select2('val');
                if(lval!=='') lchild_unit_text = $(lrow).find('[col_name="child_unit"] input[original]').select2('data').text;
            }
            
            var lchild_qty = '';
            if(lidx < ($(ltbody).find('tr').length - 1)){
                lchild_qty = $(lrow).find('[col_name="child_qty"] span')[0].innerHTML.replace(/[^0-9.]/g,'');
            }
            else{
                lchild_qty = $(lrow).find('[col_name="child_qty"] input').val().replace(/[^0-9.]/g,'');
            }
            
            if(lparent_unit_id !== '' && lchild_product_id !== '' 
                && lchild_unit_id !== '' && parseFloat(lchild_qty)> parseFloat('0')
            ){
                ltemp = {
                    product_id : lchild_product_id,
                    product_text: lchild_product_text,
                    unit_id : lchild_unit_id,
                    unit_text: lchild_unit_text,
                    qty:lchild_qty
                }
                
                lresult.product_unit_child.push(ltemp);
            }
        });
        return lresult;
    };
    
    product_child_product_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = product_parent_pane;
        var lprefix_id = product_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        
        if(Object.keys(ldata_row).length === 0){
            var lchild_prod_inpt = $(lrow).find('[col_name="child_product"] input[original]')[0];
            var lchild_unit_inpt = $(lrow).find('[col_name="child_unit"] input[original]')[0];
            var lchild_qty_inpt = $(lrow).find('[col_name="child_qty"] input')[0];
            
            APP_COMPONENT.input_select.set(lchild_prod_inpt,{
                min_input_length:0
                ,ajax_url:product_ajax_url+'input_select_child_product_search/'
                ,exceptional_data_func:function(){
                    var lparent_pane = product_parent_pane;
                    var lprefix_id = product_component_prefix_id;
                    var lresult = [];
                    var lparent_product_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    lresult.push({id:lparent_product_id});
                    $.each($(lparent_pane).find(lprefix_id+'_child_product tbody tr ')
                        ,function(lidx, lrow){
                            lresult.push({id:$(lrow).find('[col_name="child_product_id"] span').text()});
                    });
                    return lresult;
                }
            });            
            $(lchild_prod_inpt).on('change',function(){
                var lid = $(this).select2('val');
                var lrow = $(this).closest('tr');
                $(lrow).find('[col_name="child_product_id"] span').text(lid);
                var lchild_unit_inpt = $(lrow).find('[col_name="child_unit"] input[original]')[0];
                $(lchild_unit_inpt).select2({data:[]});
                if(lid!== ''){
                    var ldata = $(this).select2('data');                    
                    $(lchild_unit_inpt).select2({data:ldata.unit});
                    if(Object.keys(ldata.unit).length > 0){
                        $(lchild_unit_inpt).select2('data',ldata.unit[0]).change();
                    }
                }
                
            });
                 

            APP_COMPONENT.input_select.set(lchild_unit_inpt,{
                min_input_length:0
            });
            
            $(lchild_unit_inpt).on('change',function(){
                var lid = $(this).select2('val');
                var lrow = $(this).closest('tr');
                $(lrow).find('[col_name="child_unit_id"] span').text(lid);
            });
            
            APP_COMPONENT.input.numeric(lchild_qty_inpt,{min_val:'0'});
            $(lchild_qty_inpt).val('1');
            $(lchild_qty_inpt).blur();
        }
    }
    
    product_child_product_method.setting.func_row_transform_comp_on_new_row = function(iopt){
        var lrow = iopt.tr;
        var lchild_product_data = $(lrow).find('[col_name="child_product"] input[original]').select2('data');
        var lchild_unit_data = $(lrow).find('[col_name="child_unit"] input[original]').select2('data');
        var lchild_qty = $(lrow).find('[col_name="child_qty"] input').val().replace(/[^0-9.]/g,'');
        $(lrow).find('[col_name="child_product"]')[0].innerHTML = '<span>'+lchild_product_data.text+'</span>';
        $(lrow).find('[col_name="child_unit"]')[0].innerHTML = '<span>'+lchild_unit_data.text+'</span>';
        $(lrow).find('[col_name="child_qty"]')[0].innerHTML = '<span>'+lchild_qty+'</span>';
    }
    
    product_child_product_method.setting.func_row_data_assign = function(iopt){
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        if(Object.keys(ldata_row).length > 0){
            $(lrow).find('[col_name="child_product_id"]')[0].innerHTML = '<span>'+ldata_row.product_id+'</span>';
            $(lrow).find('[col_name="child_product"]')[0].innerHTML = '<span>'+ldata_row.product_text+'</span>';
            $(lrow).find('[col_name="child_unit_id"]')[0].innerHTML = '<span>'+ldata_row.unit_id+'</span>';
            $(lrow).find('[col_name="child_unit"]')[0].innerHTML = '<span>'+ldata_row.unit_text+'</span>';
            $(lrow).find('[col_name="child_qty"]')[0].innerHTML = '<span>'+APP_CONVERTER.thousand_separator(ldata_row.qty)+'</span>';
            product_child_product_method.components.trash_set(iopt);
        }
    }
}
</script>