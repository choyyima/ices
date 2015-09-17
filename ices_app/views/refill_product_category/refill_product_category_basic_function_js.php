<script>

    var refill_product_category_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var refill_product_category_ajax_url = null;
    var refill_product_category_index_url = null;
    var refill_product_category_view_url = null;
    var refill_product_category_window_scroll = null;
    var refill_product_category_data_support_url = null;
    var refill_product_category_common_ajax_listener = null;
    var refill_product_category_component_prefix_id = '';
    
    var refill_product_category_insert_dummy = true;

    var refill_product_category_init = function(){
        var parent_pane = refill_product_category_parent_pane;
        refill_product_category_ajax_url = '<?php echo $ajax_url ?>';
        refill_product_category_index_url = '<?php echo $index_url ?>';
        refill_product_category_view_url = '<?php echo $view_url ?>';
        refill_product_category_window_scroll = '<?php echo $window_scroll; ?>';
        refill_product_category_data_support_url = '<?php echo $data_support_url; ?>';
        refill_product_category_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        refill_product_category_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
                
        
    }
    
    var refill_product_category_methods = {
        hide_all:function(){
            var lparent_pane = refill_product_category_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#refill_product_category_print').hide();
            $(lparent_pane).find('#refill_product_category_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = refill_product_category_parent_pane;
            var lmethod = $(lparent_pane).find('#refill_product_category_method').val();
            refill_product_category_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    
                case 'view':
                    $(lparent_pane).find('#refill_product_category_submit').show();
                    $(lparent_pane).find('#refill_product_category_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_product_category_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_product_category_refill_product_category_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_product_category_notes').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = refill_product_category_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = refill_product_category_parent_pane;
            var lmethod = $(lparent_pane).find('#refill_product_category_method').val();    
            refill_product_category_methods.disable_all();
            switch(lmethod){
                case 'add':
                   
                    break;
                case 'view':
                   
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = refill_product_category_parent_pane;
            var lprefix_id = refill_product_category_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('');
            $(lparent_pane).find(lprefix_id+'_name').val('');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'refill_product_category'}).response;
            
            refill_product_category_methods.product_medium_unit.reset();
            $(lparent_pane).find(lprefix_id+'_refill_product_category_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
        },
        product_medium_unit:{
            input_row_generate:function(){
                var lparent_pane = refill_product_category_parent_pane;
                var lprefix_id = refill_product_category_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody')[0];
                
                var lrow = document.createElement('tr');
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'text'});                            
                var lproduct_medium_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_medium',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'product_medium_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                var lcapacity_unit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'capacity_unit',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'capacity_unit_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                var lnew_row = APP_COMPONENT.new_row();    
                laction.appendChild(lnew_row);
        
                $(ltbody).append(lrow);
                
                $(lproduct_medium_td).find('input[original]').on('change',function(){
                    var lparent_pane = refill_product_category_parent_pane;
                    var lproduct_medium_id = $(this).select2('val');
                    var lrow = $(this).closest('tr')[0];
                    $(lrow).find('[col_name="product_medium_id"]').text(lproduct_medium_id);
                }); 
                
                $(lcapacity_unit_td).find('input[original]').on('change',function(){
                    var lparent_pane = refill_product_category_parent_pane;
                    var lcapacity_unit_id = $(this).select2('val');
                    var lrow = $(this).closest('tr')[0];
                    $(lrow).find('[col_name="capacity_unit_id"]').text(lcapacity_unit_id)
                    
                }); 
                                
                APP_COMPONENT.input_select.set($(lproduct_medium_td).find('input[original]')[0],
                    {min_input_length:0,ajax_url:'<?php echo $ajax_url.'input_select_refill_product_medium_search/'?>'});
                APP_COMPONENT.input_select.set($(lcapacity_unit_td).find('input[original]')[0],
                    {min_input_length:0,ajax_url:'<?php echo $ajax_url.'input_select_unit_search/'?>'});
            
                $(lnew_row).on('click',function(){
                    var ltbody = $(this).closest('tbody')[0];
                    var lrow = $(this).closest('tr')[0];
                    var lpm_id = $(lrow).find('[col_name="product_medium"] input[original]').select2('val');
                    var lcapacity_unit_id = $(lrow).find('[col_name="capacity_unit"] input[original]').select2('val');
                    if(lpm_id!=='' && lcapacity_unit_id!=='' ){
                        var lpm_data = $(lrow).find('[col_name="product_medium"] input[original]').select2('data');
                        var lcapacity_unit_data = $(lrow).find('[col_name="capacity_unit"] input[original]').select2('data');
                        $(lrow).find('[col_name="product_medium"]').empty();
                        $(lrow).find('[col_name="product_medium"]')[0].innerHTML = '<span>'+lpm_data.text+'</span>';
                        $(lrow).find('[col_name="capacity_unit"]').empty();
                        $(lrow).find('[col_name="capacity_unit"]')[0].innerHTML = '<span>'+lcapacity_unit_data.text+'</span>';
                        var ltrash = APP_COMPONENT.trash();
                        $(lrow).find('[col_name="action"]').empty();
                        $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                        $(ltbody).append(refill_product_category_methods.product_medium_unit.input_row_generate());                        
                    }                    
                });
            },
            reset:function(){
                var lparent_pane = refill_product_category_parent_pane;
                var lprefix_id = refill_product_category_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody')[0];
                $(ltbody).empty();
                refill_product_category_methods.product_medium_unit.input_row_generate();
            },
            load:function(idata_arr){
                var lparent_pane = refill_product_category_parent_pane;
                var lprefix_id = refill_product_category_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody')[0];
                $(ltbody).empty();
                fast_draw = APP_COMPONENT.table_fast_draw;
                $.each(idata_arr, function(ldix, ldata){
                    var lrow = document.createElement('tr');                    
                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'text'});                            
                    var lproduct_medium_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_medium',style:'vertical-align:middle',val:ldata.product_medium_text,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_medium_id',style:'vertical-align:middle',val:ldata.product_medium_id,type:'text',visible:false});
                    var lcapacity_unit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'capacity_unit',style:'vertical-align:middle',val:ldata.capacity_unit_text,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'capacity_unit_id',style:'vertical-align:middle',val:ldata.capacity_unit_id,type:'text',visible:false});
                    var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    var ltrash = APP_COMPONENT.trash();
                    $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                    $(ltbody).append(lrow);
                });
            },
            get:function(){
                var lresult = [];
                var lparent_pane = refill_product_category_parent_pane;
                var lprefix_id = refill_product_category_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody')[0];
                $.each($(ltbody).find('tr'),function(lidx, lrow){
                    var lpm_id = $(lrow).find('[col_name="product_medium_id"]').text();
                    var lcapacity_unit_id = $(lrow).find('[col_name="capacity_unit_id"]').text();
                    
                    if(lpm_id !== '' && lcapacity_unit_id !== ''){
                        lresult.push({refill_product_medium_id:lpm_id,capacity_unit_id:lcapacity_unit_id});
                    }
                });
                return lresult;
            }
        },        
        submit:function(){
            var lparent_pane = refill_product_category_parent_pane;
            var lprefix_id = refill_product_category_component_prefix_id;
            var lajax_url = refill_product_category_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.refill_product_category = {
                        code:$(lparent_pane).find(lprefix_id+'_code').val(),
                        name:$(lparent_pane).find(lprefix_id+'_name').val(),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    };
                    
                    json_data.rpc_rpmu = refill_product_category_methods.product_medium_unit.get();
                    
                    lajax_url +='refill_product_category_add/';
                    break;
                case 'view':
                    json_data.refill_product_category = {
                        code:$(lparent_pane).find(lprefix_id+'_code').val(),
                        name:$(lparent_pane).find(lprefix_id+'_name').val(),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    };
                    json_data.rpc_rpm_cu = refill_product_category_methods.product_medium_unit.get();
                    
                    var refill_product_category_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_refill_product_category_status').
                        select2('data').method;
                    lajax_url +=lajax_method+'/'+refill_product_category_id;
                    break;
            }
            console.log(json_data);
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(refill_product_category_view_url !==''){
                    var url = refill_product_category_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    refill_product_category_after_submit();
                }
            }
        }
    };
    
    var refill_product_category_bind_event = function(){
        var lparent_pane = refill_product_category_parent_pane;
        var lprefix_id = refill_product_category_component_prefix_id;
                
        $(lparent_pane).find('#refill_product_category_submit').off();
        var lparam = {
            window_scroll: refill_product_category_window_scroll,
            parent_pane: refill_product_category_parent_pane,
            module_method: refill_product_category_methods
        };
        
        APP_COMPONENT.button.submit.set(
            $(lparent_pane).find('#refill_product_category_submit')[0],
            lparam
        );
        
            
        
    }
    
    var refill_product_category_components_prepare = function(){
        

        var refill_product_category_data_set = function(){
            var lparent_pane = refill_product_category_parent_pane;
            var lprefix_id = refill_product_category_component_prefix_id;
            var lmethod = $(lparent_pane).find('#refill_product_category_method').val();
            
            switch(lmethod){
                case 'add':
                    refill_product_category_methods.reset_all();
                    if(refill_product_category_insert_dummy){
                        
                    }
                    break;
                case 'view':
                    
                    var lrefill_product_category_id = $(lparent_pane).find('#refill_product_category_id').val();
                    var lajax_url = refill_product_category_data_support_url+'refill_product_category_get/';
                    var json_data = {data:lrefill_product_category_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lrefill_product_category = lresponse.refill_product_category;

                    $(lparent_pane).find('#refill_product_category_code').val(lrefill_product_category.code);
                    $(lparent_pane).find('#refill_product_category_name').val(lrefill_product_category.name);
                    $(lparent_pane).find('#refill_product_category_notes').val(lrefill_product_category.notes);
                    
                    refill_product_category_methods.product_medium_unit.load(lresponse.refill_product_medium_unit);
                    refill_product_category_methods.product_medium_unit.input_row_generate();
                    
                    $(lparent_pane).find('#refill_product_category_refill_product_category_status')
                        .select2('data',{id:lrefill_product_category.refill_product_category_status
                            ,text:lrefill_product_category.refill_product_category_status_text}).change();
                    
                    $(lparent_pane).find('#refill_product_category_refill_product_category_status')
                            .select2({data:lresponse.refill_product_category_status_list});
                    
                    
                    
                    
                    break;
            }
        }
        
        
        refill_product_category_methods.enable_disable();
        refill_product_category_methods.show_hide();
        refill_product_category_data_set();
    }
    
    var refill_product_category_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>