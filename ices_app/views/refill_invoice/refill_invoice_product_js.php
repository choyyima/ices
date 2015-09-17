<script>
var refill_invoice_product_methods = {
    load_product:function(iproduct){
        var lparent_pane = refill_invoice_parent_pane;
        var lprefix_id = refill_invoice_component_prefix_id;

        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                
        refill_invoice_product_table_method.reset();
        refill_invoice_product_table_method.head_generate();
        
        $.each(iproduct, function(lidx, lrow){
            refill_invoice_product_table_method.input_row_generate(lrow);
        });
        
    }
}

var refill_invoice_product_bind_event = function(){
    var lparent_pane = refill_invoice_parent_pane;
    var lprefix_id = refill_invoice_component_prefix_id;
    
    refill_invoice_product_table_method.setting.func_new_row_validation= function(iopt){
        var lmodule_type = refill_invoice_methods.module_type_get();
        var lresult = {success:1,msg:[]};
        var success = 0;
        var lrow = iopt.tr;
        
        lresult.success = success;
        return lresult;
    };
    
    refill_invoice_product_table_method.setting.func_get_data_table = function(){
        var lparent_pane = refill_invoice_parent_pane;
        var lprefix_id = refill_invoice_component_prefix_id;
        var lresult = [];
        var lreference_type = $(lparent_pane).find(lprefix_id+'_type').val();
        
        var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            
            
        });
        return lresult;
    };
    
    refill_invoice_product_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = refill_invoice_parent_pane;
        var lprefix_id = refill_invoice_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        var lmodule_type = refill_invoice_methods.module_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        
        <?php // --- Show and Hide phase --- ?>
        var ltable = $(lparent_pane).find(lprefix_id+'_product_table')[0];
        if(lmethod === 'add'){
            
        }
        else if(lmethod === 'view'){
            
        }
        
        
        
        <?php // --- End Of Show and Hide phase --- ?>
        
        if(Object.keys(ldata_row).length === 0){
            
        }
        
    }

    refill_invoice_product_table_method.setting.func_row_transform_comp_on_new_row = function(iopt){
        var lmodule_type = refill_invoice_methods.module_type_get();
        var lrow = iopt.tr;
        
    }

    refill_invoice_product_table_method.setting.func_row_data_assign = function(iopt){
        var lparent_pane = refill_invoice_parent_pane;
        var lprefix_id = refill_invoice_component_prefix_id;
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        var lreference_type = refill_invoice_methods.reference_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        
        switch(lmethod){
            case 'add':
            case 'view':
                if(Object.keys(ldata_row).length > 0){
                    var lproduct_recondition = $('<ul style="padding-left:25px"></ul>');
                    var lproduct_sparepart = $('<ul style="padding-left:25px"></ul>');
                    
                    $.each(ldata_row.product_recondition_cost,function(lpr_idx,lpr_row){
                        $(lproduct_recondition).append($('<li>'+lpr_row.product_recondition_name+' - '+APP_CONVERTER.thousand_separator(lpr_row.amount)+'</li>'));
                    })
                    
                    $.each(ldata_row.product_sparepart_cost,function(lpr_idx,lpr_row){
                        $(lproduct_sparepart).append(
                            $('<li>'+lpr_row.product_text
                                +' '+APP_CONVERTER.thousand_separator(lpr_row.qty)+' '+lpr_row.unit_text+' - '+APP_CONVERTER.thousand_separator(lpr_row.amount)+'</li>')
                        );
                    })
                    
                    var lcurr_status = refill_invoice_data.current_status;
                    $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<div>'+ldata_row.product_type+'</div>';
                    $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<div>'+ldata_row.product_id+'</div>';
                    $(lrow).find('[col_name="product"]')[0].innerHTML = '<div>'+ldata_row.product_text+'</div>';
                    $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<div>'+ldata_row.unit_id+'</div>';
                    $(lrow).find('[col_name="unit"]')[0].innerHTML = '<div>'+ldata_row.unit_text+'</div>';
                    $(lrow).find('[col_name="qty"]')[0].innerHTML = '<div>'+APP_CONVERTER.thousand_separator(ldata_row.qty)+'</div>';
                    $(lrow).find('[col_name="amount"]')[0].innerHTML = '<div>'+APP_CONVERTER.thousand_separator(ldata_row.amount)+'</div>';
                    $(lrow).find('[col_name="product_recondition"]')[0].innerHTML = '<div>'+$(lproduct_recondition).prop('outerHTML')+'</div>';
                    $(lrow).find('[col_name="product_sparepart"]')[0].innerHTML = '<div>'+$(lproduct_sparepart).prop('outerHTML')+'</div>';
                    $(lrow).find('[col_name="action"]')[0].innerHTML = '<div></div>';
                }
                break;
            
        }
        

    }
    
}
</script>