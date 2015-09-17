<script>

    var stock_transfer_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var stock_transfer_ajax_url = null;
    var stock_transfer_index_url = null;
    var stock_transfer_view_url = null;
    var stock_transfer_window_scroll = null;
    var stock_transfer_data_support_url = null;
    var stock_transfer_common_ajax_listener = null;
    var stock_transfer_component_prefix_id = '';
    
    
    var stock_transfer_data={
        curr_status:'',
        product_condition:[],        
    }
    
    var stock_transfer_insert_dummy = true;

    var stock_transfer_init = function(){
        var parent_pane = stock_transfer_parent_pane;
        stock_transfer_ajax_url = '<?php echo $ajax_url ?>';
        stock_transfer_index_url = '<?php echo $index_url ?>';
        stock_transfer_view_url = '<?php echo $view_url ?>';
        stock_transfer_window_scroll = '<?php echo $window_scroll; ?>';
        stock_transfer_data_support_url = '<?php echo $data_support_url; ?>';
        stock_transfer_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        stock_transfer_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        stock_transfer_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var stock_transfer_methods = {
        hide_all:function(){
            var lparent_pane = stock_transfer_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#stock_transfer_print').hide();
            $(lparent_pane).find('#stock_transfer_submit').hide();
            
        },
        show_hide:function(){
            var lparent_pane = stock_transfer_parent_pane;
            var lprefix_id = stock_transfer_component_prefix_id;
            var lmethod = $(lparent_pane).find('#stock_transfer_method').val();
            stock_transfer_methods.hide_all();
            
            $(lparent_pane).find('#stock_transfer_btn_print').hide();
            switch(lmethod){
                case 'add':    
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_submit').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_stock_transfer_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_requestor_name').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_stock_transfer_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_stock_transfer_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('.form-group').show();                    
                    $(lparent_pane).find(lprefix_id+'_registered_product_table').closest('.form-group').show();
                    break;
            }
            if(lmethod ==='view'){
                $(lparent_pane).find(lprefix_id+'_btn_print').show();
            }
        },
        disable_all:function(){
            var lparent_pane = stock_transfer_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = stock_transfer_parent_pane;
            var lprefix_id = stock_transfer_component_prefix_id;
            var lmethod = $(lparent_pane).find('#stock_transfer_method').val();    
            stock_transfer_methods.disable_all();
            switch(lmethod){
                case 'add':
                    
                    $(lparent_pane).find(lprefix_id+'_store').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_warehouse_from').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_requestor_name').prop('disabled',false);                    
                    $(lparent_pane).find(lprefix_id+'_stock_transfer_date').prop('disabled',false);                    
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#stock_transfer_notes').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = stock_transfer_parent_pane;
            var lprefix_id = stock_transfer_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
                        
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_stock_transfer_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute', 10,'F d, Y H:i'),
                minDate:APP_GENERATOR.CURR_DATETIME(null, null,'Y-m-d'),
                minTime:APP_GENERATOR.CURR_DATETIME('minute', 10,'H:i:s'),
            });
            
            APP_FORM.status.default_status_set('stock_transfer',
                $(lparent_pane).find(lprefix_id+'_stock_transfer_status')
            );
            
            stock_transfer_methods.registered_product_table.reset();
            stock_transfer_methods.registered_product_table.row_generate([]);
            
        },
        submit:function(){
            var lparent_pane = stock_transfer_parent_pane;
            var lprefix_id = stock_transfer_component_prefix_id;
            var lajax_url = stock_transfer_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };
            
            switch(lmethod){
                case 'add':
                    json_data.stock_transfer = {
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        requestor_name:$(lparent_pane).find(lprefix_id+'_requestor_name').val(),
                        stock_transfer_date:$(lparent_pane).find(lprefix_id+'_stock_transfer_date').val(),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                        warehouse_from_id:$(lparent_pane).find(lprefix_id+'_warehouse_from').select2('val'),
                        warehouse_to_id:$(lparent_pane).find(lprefix_id+'_warehouse_to').select2('val'),
                    }
                    json_data.stock_transfer_product = stock_transfer_methods.registered_product_table.get();

                    lajax_url +='stock_transfer_add/';
                    break;
                case 'view':
                    json_data.stock_transfer = {
                        notes: $(lparent_pane).find(lprefix_id+'_notes').val(),
                        stock_transfer_status: $(lparent_pane).find(lprefix_id+'_stock_transfer_status').select2('val'),
                        cancellation_reason: $(lparent_pane).find(lprefix_id+'_stock_transfer_cancellation_reason').val()
                    }
                    var stock_transfer_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_stock_transfer_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+stock_transfer_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(stock_transfer_view_url !==''){
                    var url = stock_transfer_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    stock_transfer_after_submit();
                }
            }
        },
        
        registered_product_table: {
            get:function(){
                //<editor-fold defaultstate="collapsed">
                var lresult = [];
                var lparent_pane = stock_transfer_parent_pane;
                var lprefix_id = stock_transfer_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_registered_product_table tbody')[0];
                
                $.each($(ltbody).find('tr'),function(lidx, lrow){
                    var lindex = $(lrow).index();
                    
                    var ltemp_row  = {
                        
                        product_id:$(lrow).find('[col_name="product_id"]').text(),
                        unit_id:$(lrow).find('[col_name="unit_id"]').text(),
                        qty:$(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,''), 
                    };

                    if(ltemp_row.product_id!=='' &&
                        ltemp_row.unit_id!=='' &&
                        parseFloat(ltemp_row.qty)>parseFloat('0')
                    ){
                        lresult.push(ltemp_row);
                    }
                    
                });
                
                return lresult;
                //</editor-fold>
            },
            reset:function(){
                var lparent_pane = stock_transfer_parent_pane;
                var lprefix_id = stock_transfer_component_prefix_id;
                $(stock_transfer_parent_pane).find(lprefix_id+'_registered_product_table tbody').empty();
                $(lparent_pane).find(lprefix_id+'_registered_product_table [col_name="stock_qty"]').show();
            },
            load:function(iproduct_arr){
                var lparent_pane = stock_transfer_parent_pane;
                var lprefix_id = stock_transfer_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                
                stock_transfer_methods.registered_product_table.reset();
                $.each(iproduct_arr,function(lidx,lproduct){
                    stock_transfer_methods.registered_product_table.row_generate(lproduct);
                });                
                
                $(lparent_pane).find(lprefix_id+'_registered_product_table [col_name="stock_qty"]').hide();
            },
            row_generate:function(iproduct){
                var lparent_pane = stock_transfer_parent_pane;
                var lprefix_id = stock_transfer_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var ltbody = $(lparent_pane).find(lprefix_id+'_registered_product_table tbody')[0];
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                var lrow = document.createElement('tr');
                var lrow_detail = document.createElement('tr');
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:($(ltbody).children().length)+1,type:'text'});                            
                
                if(lmethod === 'add'){
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_id',style:'vertical-align:middle',val:'',type:'span',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_img',style:'vertical-align:middle',val:'',type:'text'});
                    var lproduct_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit_id',style:'vertical-align:middle',val:'',type:'span',visible:false});
                    var lunit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    var lqty_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'stock_qty',col_style:'text-align:right',val:'0.00',type:'span'});
                    var lqty_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'qty',style:'text-align:right',val:'0',type:'input'});
                    var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    var lnew_row = APP_COMPONENT.new_row();    
                    laction.appendChild(lnew_row);
                }
                else {
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_img',style:'vertical-align:middle',val:iproduct.product_img,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'vertical-align:middle',val:iproduct.product_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit',style:'vertical-align:middle',val:iproduct.unit_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'stock_qty',col_style:'text-align:right',val:' - ',type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'qty',col_style:'text-align:right',val:iproduct.qty,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    
                }
                                
                $(ltbody).append(lrow);
                
                if(lmethod === 'add'){
                    
                    $(lnew_row).on('click',function(){
                        var ltbody = $(this).closest('tbody')[0];
                        var lrow = $(this).closest('tr')[0];
                        var lp_id = $(lrow).find('[col_name="product"] input[original]').select2('val');
                        var lunit_id = $(lrow).find('[col_name="unit"] input[original]').select2('val');
                        var lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                        if(lp_id!=='' && lunit_id!=='' && parseFloat(lqty)>parseFloat('0')){
                            var lproduct_data = $(lrow).find('[col_name="product"] input[original]').select2('data');
                            var lunit_data = $(lrow).find('[col_name="unit"] input[original]').select2('data');
                            $(lrow).find('[col_name="product"]').empty();
                            $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+lproduct_data.text+'</span>';
                            $(lrow).find('[col_name="unit"]').empty();
                            $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+lunit_data.text+'</span>';
                            var ltrash = APP_COMPONENT.trash();
                            $(lrow).find('[col_name="action"]').empty();
                            $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                            $(ltbody).append(stock_transfer_methods.registered_product_table.row_generate([]));                        
                        }                    
                    });
                    
                    APP_COMPONENT.input.numeric($(lqty_td).find('input')[0],{min_val:0,max_val:0});
                    $(lqty_td).find('input').blur();
                
                    $(lproduct_td).find('input[original]').on('change',function(){
                        var lparent_pane = stock_transfer_parent_pane;
                        var lproduct_category_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        var lproduct_id = $(this).select2('val');
                        $(lrow).find('[col_name="product_id"]').text(lproduct_id);  
                        
                        if((lproduct_id === '')){
                            $(lrow).remove();
                            stock_transfer_methods.registered_product_table.row_generate([]);
                        }
                        else{
                            var ldata = $(this).select2('data');
                            $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata.product_img;
                            $(lrow).find('[col_name="unit"] input[original]').select2({data:ldata.unit});
                            $(lrow).find('[col_name="unit"] input[original]').select2('data',ldata.unit[0]);
                            $(lrow).find('[col_name="unit"] input[original]').change();
                        }
                        
                    }); 

                    $(lunit_td).find('input[original]').on('change',function(){
                        var lparent_pane = stock_transfer_parent_pane;
                        var lunit_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        $(lrow).find('[col_name="unit_id"]').text(lunit_id); 
                        $(lrow).find('[col_name="stock_qty"] span').text('0.00');
                        $(lrow).find('[col_name="qty"] input').off();
                        APP_COMPONENT.input.numeric($(lrow).find('[col_name="qty"] input')[0],{min_val:'0',max_val:'0'});
                        
                        if(lunit_id !== ''){
                            var ldata = $(this).select2('data');
                            $(lrow).find('[col_name="stock_qty"] span').text(APP_CONVERTER.thousand_separator(ldata.max_qty));
                            
                            $(lrow).find('[col_name="qty"] input').off();
                            APP_COMPONENT.input.numeric($(lrow).find('[col_name="qty"] input')[0],{min_val:'0',max_val:ldata.max_qty.toString()});
                        }
                        $(lrow).find('[col_name="qty"] input').val('0');
                        $(lrow).find('[col_name="qty"] input').blur();
                        
                    }); 
                    
                    APP_COMPONENT.input_select.set($(lproduct_td).find('input[original]')[0],
                        {min_input_length:0,ajax_url:'<?php echo $ajax_url.'input_select_registered_product_search/'?>'},
                        function(){ return {warehouse_id:$(lparent_pane).find(lprefix_id+'_warehouse_from').select2('val')};}
                    );
                    APP_COMPONENT.input_select.set($(lunit_td).find('input[original]')[0]);
            
                }
                
                
            },
            
            
        },
    };
    
    var stock_transfer_bind_event = function(){
        var lparent_pane = stock_transfer_parent_pane;
        var lprefix_id = stock_transfer_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_warehouse_from').on('change',function(){
            stock_transfer_methods.registered_product_table.reset();
            stock_transfer_methods.registered_product_table.row_generate([]);
        });
        
        $(lparent_pane).find(lprefix_id+'_btn_print').off();
        $(lparent_pane).find(lprefix_id+'_btn_print').on('click',function(){
            var lrwo_id = $(lparent_pane).find(lprefix_id+'_id').val();
            modal_print.init();
            modal_print.menu.add('<?php echo Lang::get(array(array('val'=>"Work Order"),array('val'=>'Form'))) ?>','http://localhost/leo/stock_transfer/stock_transfer_print/'+lrwo_id+'/stock_transfer_form');
            modal_print.show();
            
        });
        
        $(lparent_pane).find(lprefix_id+'_submit').off();
        var lparam = {
            window_scroll: stock_transfer_window_scroll,
            parent_pane: stock_transfer_parent_pane,
            module_method: stock_transfer_methods
        };
        
        APP_COMPONENT.button.submit.set(
            $(lparent_pane).find(lprefix_id+'_submit')[0],
            lparam
        );
        
            
        
    }
    
    var stock_transfer_components_prepare = function(){
        

        var stock_transfer_data_set = function(){
            var lparent_pane = stock_transfer_parent_pane;
            var lprefix_id = stock_transfer_component_prefix_id;
            var lmethod = $(lparent_pane).find('#stock_transfer_method').val();
            
            switch(lmethod){
                case 'add':
                    stock_transfer_methods.reset_all();
                    
                    break;
                case 'view':
                    
                    var lstock_transfer_id = $(lparent_pane).find('#stock_transfer_id').val();
                    var lajax_url = stock_transfer_data_support_url+'stock_transfer_get/';
                    var json_data = {data:lstock_transfer_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lstock_transfer = lresponse.stock_transfer;
                    var lstock_transfer_product = lresponse.stock_transfer_product;

                    $(lparent_pane).find('#stock_transfer_store').select2('data',{id:lstock_transfer.store_id
                        ,text:lstock_transfer.store_text});
                    $(lparent_pane).find('#stock_transfer_warehouse_from').select2('data',{id:lstock_transfer.warehouse_from_id
                        ,text:lstock_transfer.warehouse_from_text});
                    $(lparent_pane).find('#stock_transfer_warehouse_to').select2('data',{id:lstock_transfer.warehouse_to_id
                        ,text:lstock_transfer.warehouse_to_text});
                    
                    $(lparent_pane).find('#stock_transfer_code').val(lstock_transfer.code);
                    $(lparent_pane).find('#stock_transfer_stock_transfer_date').datetimepicker({value:lstock_transfer.stock_transfer_date});
                    $(lparent_pane).find('#stock_transfer_requestor_name').val(lstock_transfer.requestor_name);
                    
                    $(lparent_pane).find('#stock_transfer_notes').val(lstock_transfer.notes);
                    
                    $(lparent_pane).find('#stock_transfer_stock_transfer_status')
                        .select2('data',{id:lstock_transfer.stock_transfer_status
                            ,text:lstock_transfer.stock_transfer_status_text}).change();
                    
                    $(lparent_pane).find('#stock_transfer_stock_transfer_status')
                            .select2({data:lresponse.stock_transfer_status_list});
                    
                    $(lparent_pane).find('#stock_transfer_stock_transfer_cancellation_reason').val(lstock_transfer.cancellation_reason);
                                        
                    stock_transfer_methods.registered_product_table.load(lstock_transfer_product);
                    
                    break;
            }
        }
        
        
        stock_transfer_methods.enable_disable();
        stock_transfer_methods.show_hide();
        stock_transfer_data_set();
    }
    
    var stock_transfer_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>