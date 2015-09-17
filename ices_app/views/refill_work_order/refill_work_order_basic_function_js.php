<script>

    var refill_work_order_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var refill_work_order_ajax_url = null;
    var refill_work_order_index_url = null;
    var refill_work_order_view_url = null;
    var refill_work_order_window_scroll = null;
    var refill_work_order_data_support_url = null;
    var refill_work_order_common_ajax_listener = null;
    var refill_work_order_component_prefix_id = '';
    
    
    var refill_work_order_data={
        curr_status:'',
        product_condition:[],        
    }
    
    var refill_work_order_insert_dummy = true;

    var refill_work_order_init = function(){
        var parent_pane = refill_work_order_parent_pane;
        refill_work_order_ajax_url = '<?php echo $ajax_url ?>';
        refill_work_order_index_url = '<?php echo $index_url ?>';
        refill_work_order_view_url = '<?php echo $view_url ?>';
        refill_work_order_window_scroll = '<?php echo $window_scroll; ?>';
        refill_work_order_data_support_url = '<?php echo $data_support_url; ?>';
        refill_work_order_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        refill_work_order_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        refill_work_order_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var refill_work_order_methods = {
        hide_all:function(){
            var lparent_pane = refill_work_order_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#refill_work_order_print').hide();
            $(lparent_pane).find('#refill_work_order_submit').hide();
            $(lparent_pane).find('#refill_work_order_btn_customer_new').hide();
            
        },
        show_hide:function(){
            var lparent_pane = refill_work_order_parent_pane;
            var lmethod = $(lparent_pane).find('#refill_work_order_method').val();
            refill_work_order_methods.hide_all();
            
            $(lparent_pane).find('#refill_work_order_btn_print').hide();
            switch(lmethod){
                case 'add':                    
                    $(lparent_pane).find('#refill_work_order_submit').show();
                    $(lparent_pane).find('#refill_work_order_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_refill_work_order_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_customer').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_refill_work_order_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_refill_work_order_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_customer_bank_acc').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_number_of_product').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_notes').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_btn_customer_new').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#refill_work_order_submit').show();
                    $(lparent_pane).find('#refill_work_order_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_refill_work_order_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_customer').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_refill_work_order_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_refill_work_order_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_work_order_customer_bank_acc').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_number_of_product').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_notes').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_btn_print').show();
                    $(lparent_pane).find('#refill_work_order_product_table').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_total_estimated_amount').closest('.form-group').show();
                    $(lparent_pane).find('#refill_work_order_total_deposit_amount').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = refill_work_order_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = refill_work_order_parent_pane;
            var lmethod = $(lparent_pane).find('#refill_work_order_method').val();    
            refill_work_order_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#refill_work_order_type').select2('enable');
                    $(lparent_pane).find('#refill_work_order_store').select2('enable');
                    $(lparent_pane).find('#refill_work_order_customer').select2('enable');
                    $(lparent_pane).find('#refill_work_order_number_of_product').prop('disabled',false);                    
                    $(lparent_pane).find('#refill_work_order_notes').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#refill_work_order_notes').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = refill_work_order_parent_pane;
            var lprefix_id = refill_work_order_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
                        
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_refill_work_order_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            
            $(lparent_pane).find(lprefix_id+'_outstanding_amount').blur();
            $(lparent_pane).find(lprefix_id+'_change_amount').blur();
            
            APP_FORM.status.default_status_set('refill_work_order',
                $(lparent_pane).find(lprefix_id+'_refill_work_order_status')
            );
            
        },
        submit:function(){
            var lparent_pane = refill_work_order_parent_pane;
            var lprefix_id = refill_work_order_component_prefix_id;
            var lajax_url = refill_work_order_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.refill_work_order = {
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        customer_id:$(lparent_pane).find(lprefix_id+'_customer').select2('val'),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    }
                    json_data.refill_work_order_info = {
                        number_of_product:$(lparent_pane).find(lprefix_id+'_number_of_product').val(),
                    }
                    lajax_url +='refill_work_order_add/';
                    break;
                case 'view':
                    json_data.refill_work_order = {
                        notes: $(lparent_pane).find(lprefix_id+'_notes').val(),
                        refill_work_order_status: $(lparent_pane).find(lprefix_id+'_refill_work_order_status').select2('val'),
                        cancellation_reason: $(lparent_pane).find(lprefix_id+'_refill_work_order_cancellation_reason').val()
                    }
                    
                    json_data.refill_work_order_product = refill_work_order_methods.product_table.get();
                    
                    var refill_work_order_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = '';
                    var lselected_status = $(lparent_pane).find(lprefix_id+'_refill_work_order_status').
                        select2('val');
                    
                    if(lselected_status === 'X'){
                        lajax_method = $(lparent_pane).find(lprefix_id+'_refill_work_order_status').
                        select2('data').method;
                    }
                    else if (refill_work_order_data.curr_status  === 'process'){
                        lajax_method = $(lparent_pane).find(lprefix_id+'_refill_work_order_status').
                        select2('data').method;
                    }
                    else if(refill_work_order_data.curr_status === 'initialized'){
                        if(json_data.refill_work_order_product.length > 0){
                            lajax_method = 'refill_work_order_process';
                        }
                        else{
                            lajax_method = 'refill_work_order_initialized';
                        }
                    }
                    
                    
                    lajax_url +=lajax_method+'/'+refill_work_order_id;
                    break;
            }
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(refill_work_order_view_url !==''){
                    var url = refill_work_order_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    refill_work_order_after_submit();
                }
            }
        },
        total_estimated_amount_set:function(){
            var lparent_pane = refill_work_order_parent_pane;
            var lprefix_id = refill_work_order_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var lstatus = $(lparent_pane).find(lprefix_id+'_refill_work_order_status').val();
            var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
            
            var ltotal_estimated_amount = parseFloat('0');
            $.each($(ltbody).find('tr'),function(lidx,ltr){
                var lindex = $(ltr).index();
                    if(lindex%2 === 0){
                    var lestimated_amount = parseFloat('0');
                    if(lmethod ==='view' && lstatus === 'initialized'){
                        lestimated_amount = parseFloat($(ltr).find('[col_name="estimated_amount"] input').val().replace(/[^0-9.]/g,''));
                    }

                    ltotal_estimated_amount+= lestimated_amount;
                }
            });
            
            $(lparent_pane).find(lprefix_id+'_total_estimated_amount').val(ltotal_estimated_amount).blur();
        },
        product_table: {
            get:function(){
                //<editor-fold defaultstate="collapsed">
                var lresult = [];
                var lparent_pane = refill_work_order_parent_pane;
                var lprefix_id = refill_work_order_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                
                $.each($(ltbody).find('tr'),function(lidx, lrow){
                    var lindex = $(lrow).index();
                    if( lindex % 2 === 0){
                        var ltemp_row  = {
                            id:$(lrow).find('[col_name="refill_work_order_product_id"] span').text(),
                            rpc_id:$(lrow).find('[col_name="product_category_id"]').text(),
                            rpm_id:$(lrow).find('[col_name="product_medium_id"]').text(),
                            capacity_unit_id:$(lrow).find('[col_name="capacity_unit_id"]').text(), 
                            capacity:$(lrow).find('[col_name="capacity"] input').val(),
                            estimated_amount:$(lrow).find('[col_name = "estimated_amount"] input').val().replace(/[^0-9.]/g,''),
                            staff_checker:$(lrow).find('[col_name = "staff_checker"] input').val(),
                        };
                        
                        var lrow_info = $(lrow).closest('tbody').find('tr:eq('+(lindex+1)+')')[0];
                        ltemp_row.product_info_merk = $(lrow_info).find('input[field="product_info_merk"]').val();
                        ltemp_row.product_info_type = $(lrow_info).find('input[field="product_info_type"]').val();
                        
                        $.each($(lrow_info).find('[col_name="product_info"] input[type="checkbox"]'), function(lidx, lcheckbox){
                            if($(lcheckbox).is(':checked')){
                                ltemp_row[$(lcheckbox).attr('field')] = '1';
                            }
                        });
                        
                        ltemp_row.product_condition_description = $(lrow_info).find('input[field="product_condition_description"]').val();
                        
                        
                        if(ltemp_row.rpc_id!=='' &&
                            ltemp_row.rpm_id!=='' &&
                            ltemp_row.capacity_unit_id!==''
                        ){
                            lresult.push(ltemp_row);
                        }
                    }
                });
                
                return lresult;
                //</editor-fold>
            },
            reset:function(){
                var lprefix_id = refill_work_order_component_prefix_id;
                $(refill_work_order_parent_pane).find(lprefix_id+'_product_table tbody').empty();
            },
            load:function(iproduct_arr){
                var lparent_pane = refill_work_order_parent_pane;
                var lprefix_id = refill_work_order_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var lcurr_status = refill_work_order_data.curr_status;
                refill_work_order_methods.product_table.reset();
                $.each(iproduct_arr,function(lidx,lproduct){
                    refill_work_order_methods.product_table.row_generate(lproduct);
                });                
                
                if(lcurr_status === 'initialized'){
                    refill_work_order_methods.total_estimated_amount_set();
                }
            },
            row_generate:function(iproduct){
                var lparent_pane = refill_work_order_parent_pane;
                var lprefix_id = refill_work_order_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var lcurr_status = refill_work_order_data.curr_status;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                var lrow = document.createElement('tr');
                var lrow_detail = document.createElement('tr');
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:($(ltbody).children().length/2)+1,type:'text'});                            
                fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'refill_work_order_product_id',style:'vertical-align:middle',val:iproduct.id,type:'span',visible:false});
                var lpmc_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_marking_code',style:'vertical-align:middle',val:iproduct.product_marking_code,type:'span'});
                
                if(lcurr_status === 'initialized'){
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_category_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    var lproduct_category_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_category',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_medium_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    var lproduct_medium_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_medium',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    var lcapacity_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'capacity',style:'text-align:right',val:'',type:'input'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'capacity_unit_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    var lcapacity_unit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'capacity_unit',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    var lestimated_amount_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'estimated_amount',style:'text-align:right',val:iproduct.estimated_amount,type:'input',comp_attr:{disabled:'true'}});
                    var lstaff_checker_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'staff_checker',style:'text-align:left',val:'',type:'input',comp_attr:{placeholder:'ttd Staff'}});
                }
                else {
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_category_id',style:'vertical-align:middle',val:iproduct.refill_product_category_id,type:'text',visible:false});
                    var lproduct_category_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_category',style:'vertical-align:middle',val:iproduct.rpc_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_medium_id',style:'vertical-align:middle',val:iproduct.rpm_id,type:'text',visible:false});
                    var lproduct_medium_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_medium',style:'vertical-align:middle',val:iproduct.rpm_text,type:'span'});
                    var lcapacity_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'capacity',col_style:'text-align:right',val:iproduct.capacity === null?'':iproduct.capacity,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'capacity_unit_id',style:'vertical-align:middle',val:iproduct.capacity_unit_id,type:'text',visible:false});
                    var lcapacity_unit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'capacity_unit',style:'vertical-align:middle',val:iproduct.capacity_unit_text,type:'span'});
                    var lestimated_amount_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'estimated_amount',col_style:'text-align:right',val:iproduct.estimated_amount,type:'span'});
                    var lstaff_checker_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'staff_checker',style:'text-align:left',val:iproduct.staff_checker === null?'':iproduct.staff_checker,type:'span'});
                }
                
                
                $(ltbody).append(lrow);
                
                var lcondition_arr = refill_work_order_data.product_condition;
                
                fast_draw.col_add(lrow_detail,{tag:'td',col_style:'border-top:none'});
                var lproduct_info_td = fast_draw.col_add(lrow_detail,{tag:'td',col_name:'product_info',col_style:'border-top:none',attr:{colspan:'7'}});
                $(lproduct_info_td).append('<div class="box" style="border-top:none;"><ul class="todo-list ui-sortable"></ul></div>');
                var lproduct_info_ul = $(lproduct_info_td).find('ul');
                $(lproduct_info_ul).append($('<li product_info class="" style="border-left:2px solid #00a65a; background-color:#B3EDCD"><div title><span><strong>Product Info</strong> - '+iproduct.refill_work_order_product_status_text+'</span></div><div detail></div></li>'));
                $(lproduct_info_ul).find('[product_info] div[detail]').append($('<div style="margin-top:10px;"></div>'));
                $(lproduct_info_ul).find('[product_info] div[detail]').append($('<div style=""><input style="float:left;width:40%;margin-left:5px;margin-right:5px;color:black" class="form-control" placeholder="Merk" field="product_info_merk"><input style="width:40%;margin-left:5px;margin-right:5px;color:black" class="form-control" placeholder="Type" field="product_info_type"></div>'));
                
                $(lproduct_info_ul).append($('<li product_condition class="" style="border-left:2px solid #00a65a; background-color:#B3EDCD"><div title><span><strong>Product Condition</strong> (centang bila tersedia)</span></div><div detail></div></li>'));
                $(lproduct_info_ul).find('[product_condition] div[detail]').append($('<div style="margin-top:10px;"></div>'));
                $.each(lcondition_arr, function(lidx, lcondition){
                    $(lproduct_info_ul).find('[product_condition] div[detail] div:eq(0)').append($('<span style="margin-left:5px;"><input type="checkbox" field="'+lcondition.val+'" style=";background-color:#fff;width:inherit;"><label>&nbsp'+lcondition.label+'</label></span> '));
                });
                
                $(lproduct_info_ul).find('[product_condition] div[detail]').append($('<div style=""><input style="margin-left:5px;margin-right:5px;color:black" class="form-control" placeholder="Description" field="product_condition_description"></div>'));
                
                $(ltbody).append(lrow_detail);
                $(lrow_detail).find('input[type="checkbox"]').iCheck({checkboxClass: 'icheckbox_minimal'});
                
                $.each(lcondition_arr, function(lidx, lcondition){
                  if(iproduct[lcondition] === '1'){
                      $(lrow_detail).find('input[type="checkbox][field="'+lcondition+'"]').iCheck('check');
                  }
                });
                
                if(lcurr_status !=='initialized'){
                    // Fill up with value
                    $(lrow_detail).find('input[field="product_info_merk"]').prop('disabled',true);
                    $(lrow_detail).find('input[field="product_info_type"]').prop('disabled',true);
                    $(lrow_detail).find('input[field="product_condition_description"]').prop('disabled',true);
                    $(lrow_detail).find('input[type="checkbox"]').prop('disabled',true);
                    
                    $(lrow_detail).find('input[field="product_info_merk"]').val(iproduct.product_info_merk);
                    $(lrow_detail).find('input[field="product_info_type"]').val(iproduct.product_info_merk);
                    $(lrow_detail).find('input[field="product_condition_description"]').val(iproduct.product_condition_description);
                    $.each(lcondition_arr, function(lidx, lcondition){
                        if(iproduct[lcondition.val]==='1'){ 
                            
                            $(lrow_detail).find('input[field="'+lcondition.val+'"]').iCheck('check');
                        }
                    });
                }
                
                if(lcurr_status === 'initialized'){
                    APP_COMPONENT.input.numeric($(lcapacity_td).find('input')[0],{min_val:0});
                    $(lcapacity_td).find('input').blur();
                
                    $(lproduct_category_td).find('input[original]').on('change',function(){
                        var lparent_pane = refill_work_order_parent_pane;
                        var lproduct_category_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        $(lrow).find('[col_name="product_category_id"]').text(lproduct_category_id);

                        $(lrow).find('[col_name="product_medium"] input[original]').select2('data',null);
                        $(lrow).find('[col_name="product_medium"] input[original]').select2({data:[]});

                        $(lrow).find('[col_name="capacity_unit"] input[original]').select2('data',null);
                        $(lrow).find('[col_name="capacity_unit"] input[original]').select2({data:[]});
                        
                        if($(this).select2('val')!==''){
                            var lproduct_medium = $(this).select2('data').product_medium;
                            $(lrow).find('[col_name="product_medium"] input[original]').select2({data:lproduct_medium});
                        }
                        APP_COMPONENT.input.numeric($(lestimated_amount_td).find('input')[0],{min_val:0});
                        $(lestimated_amount_td).find('input').blur();

                        refill_work_order_methods.product_table.estimated_amount_set(lrow);
                    }); 

                    $(lproduct_medium_td).find('input[original]').on('change',function(){

                        var lparent_pane = refill_work_order_parent_pane;
                        var lproduct_medium_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        $(lrow).find('[col_name="product_medium_id"]').text(lproduct_medium_id);

                        $(lrow).find('[col_name="capacity_unit"] input[original]').select2('data',null);
                        $(lrow).find('[col_name="capacity_unit"] input[original]').select2({data:[]});
                        

                        if($(this).select2('val')!==''){
                            var lcapacity_unit = $(this).select2('data').capacity_unit;
                            $(lrow).find('[col_name="capacity_unit"] input[original]').select2({data:lcapacity_unit});
                            $(lrow).find('[col_name="capacity_unit"] input[original]').select2('data',lcapacity_unit[0]).change();
                        }
                        
                        refill_work_order_methods.product_table.estimated_amount_set(lrow);
                    }); 

                    $(lcapacity_unit_td).find('input[original]').on('change',function(){
                        var lparent_pane = refill_work_order_parent_pane;
                        var lcapacity_unit_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        $(lrow).find('[col_name="capacity_unit_id"]').text(lcapacity_unit_id);                        
                        refill_work_order_methods.product_table.estimated_amount_set(lrow);
                    }); 
                    
                    $(lcapacity_td).find('input').on('change',function(){
                        var lparent_pane = refill_work_order_parent_pane;
                        var lrow = $(this).closest('tr')[0];
                        refill_work_order_methods.product_table.estimated_amount_set(lrow);
                    });
                    
                    APP_COMPONENT.input_select.set($(lproduct_category_td).find('input[original]')[0],
                        {min_input_length:0,ajax_url:'<?php echo $ajax_url.'input_select_refill_product_category_search/'?>'});
                    APP_COMPONENT.input_select.set($(lproduct_medium_td).find('input[original]')[0]);
                    APP_COMPONENT.input_select.set($(lcapacity_unit_td).find('input[original]')[0]);
            
                }
                
                
            },
            estimated_amount_set:function(irow){
                $(irow).find('[col_name="estimated_amount"] input').val('0').blur();
                var lparent_pane = refill_work_order_parent_pane;
                var lprefix_id = refill_work_order_component_prefix_id;
                var lajax_url = refill_work_order_data_support_url+'product_price_get/';
                var ljson_data = {
                    customer_id: $(lparent_pane).find(lprefix_id+'_customer').select2('val'),
                    product_category_id:$(irow).find('[col_name="product_category"] input[original]').select2('val'),
                    product_medium_id:$(irow).find('[col_name="product_medium"] input[original]').select2('val'),
                    capacity_unit_id:$(irow).find('[col_name="capacity_unit"] input[original]').select2('val'),
                    capacity:$(irow).find('[col_name="capacity"] input').val(),
                };
                
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data).response;
                
                $(irow).find('[col_name="estimated_amount"] input').val(lresponse).blur();
                
                refill_work_order_methods.total_estimated_amount_set();
            }
            
        },
    };
    
    var refill_work_order_bind_event = function(){
        var lparent_pane = refill_work_order_parent_pane;
        var lprefix_id = refill_work_order_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_btn_print').off();
        $(lparent_pane).find(lprefix_id+'_btn_print').on('click',function(){
            var lrwo_id = $(lparent_pane).find(lprefix_id+'_id').val();
            modal_print.init();
            modal_print.menu.add('<?php echo Lang::get(array(array('val'=>"Work Order"),array('val'=>'Form'))) ?>','<?php echo get_instance()->config->base_url();?>refill_work_order/refill_work_order_print/'+lrwo_id+'/refill_work_order_form');
            modal_print.show();
            
        });
        
        APP_COMPONENT.input.numeric($(lprefix_id+'_total_estimated_amount')[0],{min_val:0});
        APP_COMPONENT.input.numeric($(lprefix_id+'_number_of_product')[0],{min_val:1,data_type:'int'});
        $(lprefix_id+'_number_of_product').blur();
        
        $(lprefix_id+"_btn_customer_new").off();
        $(lprefix_id+"_btn_customer_new").on("click",function(){ 
            var lmodal_parent_pane = $('#modal_customer')[0];
            $(lmodal_parent_pane).find("#customer_method").val("add");
            customer_components_prepare();
            $(lmodal_parent_pane).modal('show');
            customer_after_submit = function(){
                var lcustomer_id = $(lmodal_parent_pane).find("#customer_id").val();
                var lcustomer_name = $(lmodal_parent_pane).find("#customer_name").val();
                $(lprefix_id+"_customer").select2("data",{id:lcustomer_id,text:lcustomer_name}).change();
                $(lmodal_parent_pane).modal('hide');
            }
        });  
        
        
        $(lprefix_id+"_customer").on('change',function(){
            var lcustomer_id = $(this).select2('val');
            $(lprefix_id+'_customer_detail .extra_info').remove();
            if(lcustomer_id !==''){
                var ldata = {customer_id:lcustomer_id};
                var lajax_url = refill_work_order_data_support_url+'customer_detail_get';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ldata).response;
                APP_COMPONENT.reference_detail.extra_info_set($(lprefix_id+'_customer_detail')[0],lresponse.customer_detail);
            }
            
            
        });
        
        $(lparent_pane).find('#refill_work_order_submit').off();
        var lparam = {
            window_scroll: refill_work_order_window_scroll,
            parent_pane: refill_work_order_parent_pane,
            module_method: refill_work_order_methods
        };
        
        APP_COMPONENT.button.submit.set(
            $(lparent_pane).find('#refill_work_order_submit')[0],
            lparam
        );
        
            
        
    }
    
    var refill_work_order_components_prepare = function(){
        

        var refill_work_order_data_set = function(){
            var lparent_pane = refill_work_order_parent_pane;
            var lprefix_id = refill_work_order_component_prefix_id;
            var lmethod = $(lparent_pane).find('#refill_work_order_method').val();
            
            switch(lmethod){
                case 'add':
                    refill_work_order_methods.reset_all();
                    
                    break;
                case 'view':
                    
                    var lrefill_work_order_id = $(lparent_pane).find('#refill_work_order_id').val();
                    var lajax_url = refill_work_order_data_support_url+'refill_work_order_get/';
                    var json_data = {data:lrefill_work_order_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lrefill_work_order = lresponse.refill_work_order;
                    var lrefill_work_order_info = lresponse.refill_work_order_info;
                    var lrefill_work_order_product = lresponse.rwo_product;

                    refill_work_order_data.product_condition = lresponse.product_condition;
                    refill_work_order_data.curr_status = APP_FORM.status.current_status_get('refill_work_order','refill_work_order');

                    $(lparent_pane).find('#refill_work_order_store').select2('data',{id:lrefill_work_order.store_id
                        ,text:lrefill_work_order.store_text});
                    $(lparent_pane).find('#refill_work_order_code').val(lrefill_work_order.code);
                    $(lparent_pane).find('#refill_work_order_refill_work_order_date').datetimepicker({value:lrefill_work_order.refill_work_order_date});
                    $(lparent_pane).find('#refill_work_order_creator').val(lrefill_work_order_info.creator_name);
                    $(lparent_pane).find('#refill_work_order_customer')
                            .select2('data',{id:lrefill_work_order.customer_id
                                ,text:lrefill_work_order.customer_text}).change();
                    
                    $(lparent_pane).find('#refill_work_order_number_of_product')
                            .val(lrefill_work_order_info.number_of_product).blur();
                    
                    $(lparent_pane).find('#refill_work_order_total_estimated_amount').val(lrefill_work_order.total_estimated_amount);
                    $(lparent_pane).find('#refill_work_order_total_deposit_amount').val(lrefill_work_order.total_deposit_amount);
                    
                    $(lparent_pane).find('#refill_work_order_notes').val(lrefill_work_order.notes);
                    
                    $(lparent_pane).find('#refill_work_order_refill_work_order_status')
                        .select2('data',{id:lrefill_work_order.refill_work_order_status
                            ,text:lrefill_work_order.refill_work_order_status_text}).change();
                    
                    $(lparent_pane).find('#refill_work_order_refill_work_order_status')
                            .select2({data:lresponse.refill_work_order_status_list});
                    
                    $(lparent_pane).find('#refill_work_order_refill_work_order_cancellation_reason').val(lrefill_work_order.cancellation_reason);
                                        
                    refill_work_order_methods.product_table.load(lrefill_work_order_product);
                    
                    break;
            }
        }
        
        
        refill_work_order_methods.enable_disable();
        refill_work_order_methods.show_hide();
        refill_work_order_data_set();
    }
    
    var refill_work_order_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>