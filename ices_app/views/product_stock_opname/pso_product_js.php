<script>
var pso_product_methods = {
    load_product:function(iproduct){
        var lparent_pane = pso_parent_pane;
        var lprefix_id = pso_component_prefix_id;
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                
        pso_product_table_method.reset();
        pso_product_table_method.head_generate();
        
        $.each(iproduct, function(lidx, lrow){
            pso_product_table_method.input_row_generate(lrow);
        });
        
        if(lmethod ==='add'){
            pso_product_table_method.input_row_generate({});
        }
    }
}

var pso_product_bind_event = function(){
    var lparent_pane = pso_parent_pane;
    var lprefix_id = pso_component_prefix_id;
    
    pso_product_table_method.setting.func_new_row_validation= function(iopt){
        var lresult = {success:1,msg:[]};
        var success = 0;
        var lrow = iopt.tr;
        
        var lproduct_id = $(lrow).find('[col_name="product_id"] div').text();
        var lunit_id = $(lrow).find('[col_name="unit_id"] div').text();
        
        if(lproduct_id !== '' && lunit_id !== ''){
            success = 1;
        }
        
        lresult.success = success;
        return lresult;
    };
    
    pso_product_table_method.setting.func_get_data_table = function(){
        var lparent_pane = pso_parent_pane;
        var lprefix_id = pso_component_prefix_id;
        var lresult = [];

        
        var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            var ltemp = {};            
            var lproduct_type = $(lrow).find('[col_name="product_type"] div').text();
            var lproduct_id = $(lrow).find('[col_name="product_id"] div').text();
            var lunit_id = $(lrow).find('[col_name="unit_id"] div').text();
            
            
            if(lproduct_id !== '' && lunit_id !== ''){        
                var linput_row = ($(lrow).index() == ($(ltbody).find('tr').length -1))?true:false;
                var lcol_name_qty_list = ['outstanding_qty','floor_1_qty','floor_2_qty','floor_3_qty','floor_4_qty','stock_bad_qty'];
                                
                ltemp = {
                    product_type: lproduct_type,
                    product_id : lproduct_id,
                    unit_id : lunit_id,
                }
                
                
                $.each(lcol_name_qty_list,function(lidx,lcol_name){
                    if(linput_row){
                        ltemp[lcol_name] = APP_CONVERTER._float($(lrow).find('[col_name="'+lcol_name+'"] input').val());
                    }
                    else{
                        ltemp[lcol_name] = APP_CONVERTER._float($(lrow).find('[col_name="'+lcol_name+'"] div')[0].innerHTML);
                    }
                });
                
                
                lresult.push(ltemp);
            }
            
        });
                
        return lresult;
    };
    
    pso_product_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = pso_parent_pane;
        var lprefix_id = pso_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        
        <?php // --- Show and Hide phase --- ?>
        var ltable = $(lparent_pane).find(lprefix_id+'_product_table')[0];
        if(lmethod === 'add'){
            $(lrow).closest('table').find('thead [col_name="outstanding_qty_old"]').hide();
            $(lrow).closest('table').find('thead [col_name="total_qty_old"]').hide();
        }
        else if(lmethod === 'view'){
            $(lrow).closest('table').find('thead [col_name="outstanding_qty_old"]').show();
            $(lrow).find('[col_name="outstanding_qty_old"]').show();
            $(lrow).closest('table').find('thead [col_name="total_qty_old"]').show();
            $(lrow).find('[col_name="total_qty_old"]').show();
            $(lrow).closest('table').find('thead [col_name="stock_bad_qty_old"]').show();
            $(lrow).find('[col_name="stock_bad_qty_old"]').show();
            
        }
        <?php // --- End Of Show and Hide phase --- ?>
        
        if(lmethod === 'add'){
            var lproduct_inpt = $(lrow).find('[col_name="product"] input[original]')[0];
            var lunit_inpt = $(lrow).find('[col_name="unit"] input[original]')[0];
            
            
            APP_COMPONENT.input_select.set(lproduct_inpt,{
                min_input_length:1
                ,ajax_url:pso_ajax_url+'input_select_product_search/'
                ,exceptional_data_func:function(){
                    var lparent_pane = pso_parent_pane;
                    var lprefix_id = pso_component_prefix_id;
                    var lresult = [];
                    
                    $.each($(lparent_pane).find(lprefix_id+'_product_table tbody tr ')
                        ,function(lidx, lrow){
                            lresult.push({id:$(lrow).find('[col_name="product_id"] div').text()});
                    });
                    
                    return lresult;
                }
            },
            function(){
                var lparent_pane = pso_parent_pane;
                var lprefix_id = pso_component_prefix_id;
                var lresult = {};                    
                return lresult;
            });       
            
            $(lproduct_inpt).on('change',function(){
                var lid = $(this).select2('val');
                var lrow = $(this).closest('tr');
                $(lrow).find('[col_name="product_id"] div').text(lid);
                var lunit_inpt = $(lrow).find('[col_name="unit"] input[original]')[0];
                
                
                $(lunit_inpt).select2('data',null);
                $(lunit_inpt).select2({data:[]});
                $(lunit_inpt).change();
                $(lrow).find('[col_name="product_type"] div')[0].innerHTML ='';

                if(lid!== ''){
                    var ldata = $(this).select2('data');
                    $(lrow).find('[col_name="product_type"] div')[0].innerHTML =ldata.product_type;
                    $(lunit_inpt).select2({data:ldata.unit});
                    if(Object.keys(ldata.unit).length > 0){
                        $(lunit_inpt).select2('data',ldata.unit[0]).change();
                    }
                }

            });


            APP_COMPONENT.input_select.set(lunit_inpt,{
                min_input_length:0
                ,allow_clear:false
            });

            $(lunit_inpt).on('change',function(){
                var lid = $(this).select2('val');
                var lrow = $(this).closest('tr');
                $(lrow).find('[col_name="unit_id"] div').text(lid);
                if(lid !== ''){
                    var ldata = $(this).select2('data');
                }
            });
            var lnumeric_style = 'text-align:right;font-size:10px;';
            $(lrow).find('[col_name="outstanding_qty"] input').attr('style',lnumeric_style);
            $(lrow).find('[col_name="floor_1_qty"] input').attr('style',lnumeric_style);
            $(lrow).find('[col_name="floor_2_qty"] input').attr('style',lnumeric_style);
            $(lrow).find('[col_name="floor_3_qty"] input').attr('style',lnumeric_style);
            $(lrow).find('[col_name="floor_4_qty"] input').attr('style',lnumeric_style);
            $(lrow).find('[col_name="stock_bad_qty"] input').attr('style',lnumeric_style);
            
            
            APP_COMPONENT.input.numeric($(lrow).find('[col_name="outstanding_qty"] input'),{min_val:0,reset:true});
            APP_COMPONENT.input.numeric($(lrow).find('[col_name="floor_1_qty"] input'),{min_val:0,reset:true});
            APP_COMPONENT.input.numeric($(lrow).find('[col_name="floor_2_qty"] input'),{min_val:0,reset:true});
            APP_COMPONENT.input.numeric($(lrow).find('[col_name="floor_3_qty"] input'),{min_val:0,reset:true});
            APP_COMPONENT.input.numeric($(lrow).find('[col_name="floor_4_qty"] input'),{min_val:0,reset:true});
            APP_COMPONENT.input.numeric($(lrow).find('[col_name="stock_bad_qty"] input'),{min_val:0,reset:true});
            
            var lcalculate_total_qty = function(irow){
                var ltotal = APP_CONVERTER._float('0');
                var lfloor_1 = APP_CONVERTER._float($(irow).find('[col_name="floor_1_qty"] input').val());
                var lfloor_2 = APP_CONVERTER._float($(irow).find('[col_name="floor_2_qty"] input').val());
                var lfloor_3 = APP_CONVERTER._float($(irow).find('[col_name="floor_3_qty"] input').val());
                var lfloor_4 = APP_CONVERTER._float($(irow).find('[col_name="floor_4_qty"] input').val());
                
                var ltotal = lfloor_1+lfloor_2+lfloor_3+lfloor_4;
                $(irow).find('[col_name="total_qty"] div')[0].innerHTML = APP_CONVERTER.thousand_separator(ltotal);
            }
            
            $(lrow).find('[col_name^="floor_"] input').on('change',function(){
                lcalculate_total_qty($(this).closest('tr'));
            });
            
        }
        
        
        
    }

    pso_product_table_method.setting.func_row_transform_comp_on_new_row = function(iopt){
        var lrow = iopt.tr;
        var lproduct_data = $(lrow).find('[col_name="product"] input[original]').select2('data');
        var lunit_data = $(lrow).find('[col_name="unit"] input[original]').select2('data');
        
        $(lrow).find('[col_name="product"]')[0].innerHTML = '<div>'+lproduct_data.text+'</div>';
        $(lrow).find('[col_name="unit"]')[0].innerHTML = '<div>'+lunit_data.text+'</div>';
        $.each($(lrow).find('td>input'),function(){
            var lval = $(this).val();
            var lcol_name = $(this).closest('td').attr('col_name');
            if($.inArray(lcol_name,['outstanding_qty','floor_1_qty','floor_2_qty','floor_3_qty','floor_4_qty','stock_bad_qty'])!== -1){
                var lval = APP_CONVERTER._float($(this).val());
                $(this).closest('td')[0].innerHTML = '<div>'+APP_CONVERTER.thousand_separator(lval)+'</div>';
            }
        });
        
    }

    pso_product_table_method.setting.func_row_data_assign = function(iopt){
        var lparent_pane = pso_parent_pane;
        var lprefix_id = pso_component_prefix_id;
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        

        if(lmethod === 'view'){
            var lcurr_status = pso_data.product_stock_opname_status_curr;
            var diff_qty_get = function(idiff){
                var lresult = '';
                var div_style = 'font-size:10px;';
                if(APP_CONVERTER._float(idiff)!== APP_CONVERTER._float('0')){
                    if(parseFloat(idiff)> parseFloat('0')){
                        lresult = '<div class="fa fa-arrow-up text-green" style="'+div_style+'">'+APP_CONVERTER.thousand_separator(idiff)+'</div> ';
                    }
                    else{
                        lresult = '<div class="fa fa-arrow-down text-red" style="'+div_style+'">'+APP_CONVERTER.thousand_separator(Math.abs(parseFloat(idiff)))+'</div> ';
                    }
                }
                return lresult;
            };

            var qty_style = 'font-size:12px';

            $(lrow).find('[col_name="product_id"]')[0].innerHTML = '<div>'+ldata_row.product_id+'</div>';
            $(lrow).find('[col_name="product_type"]')[0].innerHTML = '<div>'+ldata_row.product_type+'</div>';
            $(lrow).find('[col_name="product"]')[0].innerHTML = '<div>'+ldata_row.product_text+'</div>';
            $(lrow).find('[col_name="unit_id"]')[0].innerHTML = '<div>'+ldata_row.unit_id+'</div>';
            $(lrow).find('[col_name="unit"]')[0].innerHTML = '<div>'+ldata_row.unit_text+'</div>';
            $(lrow).find('[col_name="outstanding_qty_old"]')[0].innerHTML = '<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.outstanding_qty_old)+'</div>';
            $(lrow).find('[col_name="outstanding_qty"]')[0].innerHTML = diff_qty_get(ldata_row.outstanding_qty_diff)+'<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.outstanding_qty)+'</div>';
            $(lrow).find('[col_name="floor_1_qty"]')[0].innerHTML = '<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.ssa_floor_1_qty)+'</div>';
            $(lrow).find('[col_name="floor_2_qty"]')[0].innerHTML = '<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.ssa_floor_2_qty)+'</div>';
            $(lrow).find('[col_name="floor_3_qty"]')[0].innerHTML = '<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.ssa_floor_3_qty)+'</div>';
            $(lrow).find('[col_name="floor_4_qty"]')[0].innerHTML = '<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.ssa_floor_4_qty)+'</div>';
            $(lrow).find('[col_name="total_qty_old"]')[0].innerHTML = '<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.total_qty_old)+'</div>';
            $(lrow).find('[col_name="total_qty"]')[0].innerHTML = diff_qty_get(ldata_row.total_qty_diff)+'<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.total_qty)+'</div>';
            $(lrow).find('[col_name="stock_bad_qty_old"]')[0].innerHTML = '<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.stock_bad_qty_old)+'</div>';
            $(lrow).find('[col_name="stock_bad_qty"]')[0].innerHTML = diff_qty_get(ldata_row.stock_bad_qty_diff)+'<div style="'+qty_style+'">'+APP_CONVERTER.thousand_separator(ldata_row.stock_bad_qty)+'</div>';


            $(lrow).find('[col_name="action"]')[0].innerHTML = '<div></div>';
        }

    }
    
}
</script>