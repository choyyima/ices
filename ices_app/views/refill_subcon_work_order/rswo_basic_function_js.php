<script>
    var rswo_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var rswo_ajax_url = null;
    var rswo_index_url = null;
    var rswo_view_url = null;
    var rswo_window_scroll = null;
    var rswo_data_support_url = null;
    var rswo_common_ajax_listener = null;
    var rswo_component_prefix_id = '';
    
    var rswo_init = function(){
        var parent_pane = rswo_parent_pane;

        rswo_ajax_url = '<?php echo $ajax_url ?>';
        rswo_index_url = '<?php echo $index_url ?>';
        rswo_view_url = '<?php echo $view_url ?>';
        rswo_window_scroll = '<?php echo $window_scroll; ?>';
        rswo_data_support_url = '<?php echo $data_support_url; ?>';
        rswo_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        rswo_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var rswo_after_submit = function(){

    }
    
    var rswo_methods = {
        hide_all:function(){
            var lparent_pane = rswo_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = rswo_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
            
        },
        show_hide: function(){
            var lparent_pane = rswo_parent_pane;
            var lprefix_id = rswo_component_prefix_id;
            var lmethod = $(lparent_pane).find('#rswo_method').val();            
            rswo_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_subcon').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_subcon_work_order_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_subcon_work_order_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    break;
            }
        },        
        enable_disable: function(){
            var lparent_pane = rswo_parent_pane;
            var lmethod = $(lparent_pane).find('#rswo_method').val();  
            var lprefix_id = rswo_component_prefix_id;
            rswo_methods.disable_all();
            
            switch(lmethod){
                case "add":
                    $(lparent_pane).find(lprefix_id+"_store").select2('enable');
                    $(lparent_pane).find(lprefix_id+"_refill_subcon").select2('enable');
                    $(lparent_pane).find(lprefix_id+"_refill_subcon_work_order_date").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = rswo_parent_pane;
            var lprefix_id = rswo_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
                        
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_refill_subcon_work_order_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y H:i'),
                minDate:APP_GENERATOR.CURR_DATETIME('minute',10,'Y-m-d'),
                minTime:APP_GENERATOR.CURR_DATETIME('minute',10,'H:i:s'),
            });
            
            APP_FORM.status.default_status_set('refill_subcon_work_order',
                $(lparent_pane).find(lprefix_id+'_refill_subcon_work_order_status')
            );
    
            rswo_methods.product_table.reset();
            rswo_methods.product_table.row_generate([]);
            
            rswo_methods.expected_product_result_table.reset();
            rswo_methods.expected_product_result_table.row_generate([]);
        },
        submit:function(){
            var parent_pane = rswo_parent_pane;
            var lprefix_id = rswo_component_prefix_id;
            var ajax_url = rswo_index_url;
            var lmethod = $(parent_pane).find(lprefix_id+"_method").val();
            var rswo_id = $(parent_pane).find(lprefix_id+"_id").val();        
            var json_data = {
                ajax_post:true,
                rswo:{},
                message_session:true
            };
            
            switch(lmethod){
                case 'add':
                    json_data.rswo.store_id = $(parent_pane).find(lprefix_id+"_store").select2('val');
                    json_data.rswo.refill_subcon_id = $(parent_pane).find(lprefix_id+"_refill_subcon").select2('val');
                    json_data.rswo.refill_subcon_work_order_date = (new Date($(parent_pane).find(lprefix_id+"_refill_subcon_work_order_date").val())).format('Y-m-d H:i:s');
                    json_data.rswo.notes = $(parent_pane).find(lprefix_id+"_notes").val();
                    json_data.rswo_product = rswo_methods.product_table.get();
                    json_data.rswo_expected_product_result = rswo_methods.expected_product_result_table.get();
                    break;
                case 'view':
                    json_data.rswo.refill_subcon_work_order_status = $(parent_pane).find(lprefix_id+'_refill_subcon_work_order_status').select2('val');
                    json_data.rswo.notes = $(parent_pane).find(lprefix_id+"_notes").val();
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'rswo_add';
                    break;
                case 'view':
                    lajax_method = $(parent_pane).find(lprefix_id+'_refill_subcon_work_order_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+rswo_id;
            var result = null;
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(rswo_parent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(rswo_view_url !==''){
                    var url = rswo_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    rswo_after_submit();
                }
            }
        },
        product_table:{
            get:function(){
                //<editor-fold defaultstate="collapsed">
                var lresult = [];
                var lparent_pane = rswo_parent_pane;
                var lprefix_id = rswo_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                
                $.each($(ltbody).find('tr'),function(lidx, lrow){
                    var lindex = $(lrow).index();
                    var lproduct_reference_type = $(lrow).find('[col_name="product_reference_type"] div').text();
                    var lproduct_reference_id = $(lrow).find('[col_name="product_reference_id"] div').text();
                    var ltemp_row  = {
                        product_reference_type:lproduct_reference_type === ''?null:lproduct_reference_type,
                        product_reference_id:lproduct_reference_id === ''? null: lproduct_reference_id,
                        product_type:$(lrow).find('[col_name="product_type"]').text(),
                        product_id:$(lrow).find('[col_name="product_id"]').text(),
                        unit_id:$(lrow).find('[col_name="unit_id"]').text(),                        
                    };
                    
                    if(lindex<($(ltbody).find('tr').length-1)){
                        ltemp_row.qty=$(lrow).find('[col_name="qty"] span').text().replace(/[^0-9.]/g,''); 
                    }
                    else{
                        ltemp_row.qty=$(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                    }
                    
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
                var lparent_pane = rswo_parent_pane;
                var lprefix_id = rswo_component_prefix_id;
                $(rswo_parent_pane).find(lprefix_id+'_product_table tbody').empty();
                $(lparent_pane).find(lprefix_id+'_product_table [col_name="stock_qty"]').show();
                $(lparent_pane).find(lprefix_id+'_product_table [col_name="movement_outstanding_qty"]').hide();
            },
            load:function(iproduct_arr){
                var lparent_pane = rswo_parent_pane;
                var lprefix_id = rswo_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                
                rswo_methods.product_table.reset();
                $.each(iproduct_arr,function(lidx,lproduct){
                    rswo_methods.product_table.row_generate(lproduct);
                });                
                
                $(lparent_pane).find(lprefix_id+'_product_table [col_name="stock_qty"]').hide();
                $(lparent_pane).find(lprefix_id+'_product_table [col_name="movement_outstanding_qty"]').show();
            },
            row_generate:function(iproduct){
                //<editor-fold defaultstate="collapsed">
                var lparent_pane = rswo_parent_pane;
                var lprefix_id = rswo_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                var lrow = document.createElement('tr');
                var lrow_detail = document.createElement('tr');
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:($(ltbody).children().length)+1,type:'text'});                            
                
                if(lmethod === 'add'){
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_type',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_img',style:'vertical-align:middle',val:'',type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_reference_type',style:'vertical-align:middle',val:'',type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_reference_id',style:'vertical-align:middle',val:'',type:'div',visible:false});
                    var lproduct_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    var lproduct_reference_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_reference',style:'vertical-align:middle',val:'<div><input original></div>',type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    var lunit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    var lqty_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'stock_qty',col_style:'text-align:right',val:'0.00',type:'span'});
                    var lqty_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'qty',col_style:'text-align:right',style:'text-align:right',val:'0',type:'input'});
                    var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    var lnew_row = APP_COMPONENT.new_row();    
                    laction.appendChild(lnew_row);
                }
                else {
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_type',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_img',style:'vertical-align:middle',val:iproduct.product_img,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'vertical-align:middle',val:iproduct.product_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_reference_type',style:'vertical-align:middle',val:iproduct.product_reference_type,type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_reference_id',style:'vertical-align:middle',val:iproduct.product_reference_id,type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_reference',style:'vertical-align:middle',val:iproduct.product_reference_text,type:'div'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit',style:'vertical-align:middle',val:iproduct.unit_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'stock_qty',col_style:'text-align:right',val:' - ',type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'qty',col_style:'text-align:right',val:iproduct.qty,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'text-red',col_name:'movement_outstanding_qty',col_style:'text-align:right',val:iproduct.movement_outstanding_qty,type:'span'});
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
                            var lproduct_reference_data = $(lrow).find('[col_name="product_reference"] input[original]').select2('data');
                            var lunit_data = $(lrow).find('[col_name="unit"] input[original]').select2('data');
                            var lproduct_reference_type = $(lrow).find('[col_name="product_reference_type"] div').text();
                            var lproduct_reference_id = $(lrow).find('[col_name="product_reference_id"] div').text();
                            if((lproduct_data.rswo_product_reference_req === 'yes' && lproduct_reference_type !== '' && lproduct_reference_id !== '')
                               || lproduct_data.rswo_product_reference_req !=='yes' 
                            ){
                                $(lrow).find('[col_name="product"]').empty();
                                $(lrow).find('[col_name="product"]')[0].innerHTML = '<span>'+lproduct_data.text+'</span>';
                                var lproduct_reference_text = lproduct_reference_data === null?'':lproduct_reference_data.text;
                                $(lrow).find('[col_name="product_reference"]').empty();
                                $(lrow).find('[col_name="product_reference"]')[0].innerHTML = '<div>'+lproduct_reference_text+'</div>';
                                $(lrow).find('[col_name="unit"]').empty();
                                $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+lunit_data.text+'</span>';
                                $(lrow).find('[col_name="qty"]')[0].innerHTML = '<span>'+lqty+'</span>';
                                var ltrash = APP_COMPONENT.trash();
                                $(lrow).find('[col_name="action"]').empty();
                                $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                                $(ltbody).append(rswo_methods.product_table.row_generate([]));                        
                            }
                        }                    
                    });
                    
                    APP_COMPONENT.input.numeric($(lqty_td).find('input')[0],{min_val:0,max_val:0});
                    $(lqty_td).find('input').blur();
                
                    $(lproduct_td).find('input[original]').on('change',function(){
                        var lparent_pane = rswo_parent_pane;
                        var lproduct_category_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        var lval = $(this).select2('val');
                        
                        $(lrow).find('[col_name="product_reference"] input[original]').select2('disable');
                        if((lval === '')){
                            $(lrow).remove();
                            rswo_methods.product_table.row_generate([]);
                        }
                        else{
                            var ldata = $(this).select2('data');
                            $(lrow).find('[col_name="product_id"]').text(ldata.product_id);  
                            $(lrow).find('[col_name="product_type"]').text(ldata.product_type);
                            $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata.product_img;
                            $(lrow).find('[col_name="unit"] input[original]').select2({data:ldata.unit});
                            $(lrow).find('[col_name="unit"] input[original]').select2('data',ldata.unit[0]);
                            $(lrow).find('[col_name="unit"] input[original]').change();
                            if(ldata.rswo_product_reference_req === 'yes' || ldata.rswo_product_reference_req === 'both'){
                                $(lrow).find('[col_name="product_reference"] input[original]').select2('enable');
                            }
                        }                        
                    });
                    
                    $(lproduct_reference_td).find('input[original]').on('change',function(){
                        var lparent_pane = rswo_parent_pane;
                        var lproduct_category_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        var lval = $(this).select2('val');
                        
                        
                        if((lval === '')){
                            $(lrow).find('[col_name="product_reference_type"] div')[0].innerHTML = '';
                            $(lrow).find('[col_name="product_reference_id"] div')[0].innerHTML = '';
                        }
                        else{
                            var ldata = $(this).select2('data');
                            $(lrow).find('[col_name="product_reference_type"] div').text(ldata.product_type);
                            $(lrow).find('[col_name="product_reference_id"] div').text(ldata.product_id);
                            
                        }                        
                    });

                    $(lunit_td).find('input[original]').on('change',function(){
                        
                        var lparent_pane = rswo_parent_pane;
                        var lunit_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        $(lrow).find('[col_name="unit_id"]').text(lunit_id); 
                        $(lrow).find('[col_name="stock_qty"] span').text('0.00');
                        $(lrow).find('[col_name="qty"] input').off();
                        APP_COMPONENT.input.numeric($(lrow).find('[col_name="qty"] input')[0],{min_val:'0',max_val:'0'});
                        
                        if(lunit_id !== ''){
                            var ldata = $(this).select2('data');
                            $(lrow).find('[col_name="stock_qty"] span').text(APP_CONVERTER.thousand_separator(ldata.stock_qty));
                            $(lrow).find('[col_name="qty"] input').off();
                            APP_COMPONENT.input.numeric($(lrow).find('[col_name="qty"] input')[0],{min_val:'0',max_val:ldata.stock_qty.toString()});
                        }
                        $(lrow).find('[col_name="qty"] input').val('0');
                        $(lrow).find('[col_name="qty"] input').blur();
                        
                        
                    }); 
                    
                    APP_COMPONENT.input_select.set($(lproduct_td).find('input[original]')[0],{
                        min_input_length:0,ajax_url:'<?php echo $ajax_url.'input_select_product_search/'?>'
                        ,exceptional_data_func:function(){
                            var lresult = [];
                            var lparent_pane = rswo_parent_pane;
                            var lprefix_id = rswo_component_prefix_id;

                            var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                            $.each($(ltbody).find('tr'),function(lidx, lrow){
                                var lproduct_type = $(lrow).find('[col_name="product_type"]').text();
                                var lproduct_id = $(lrow).find('[col_name="product_id"]').text();
                                if(lproduct_type === 'refill_work_order_product'){
                                    lresult.push({id:lproduct_type+'#'+lproduct_id});
                                }
                            });
                            
                            return lresult;
                        }
                    });
                    
                    APP_COMPONENT.input_select.set($(lproduct_reference_td).find('input[original]')[0],{
                        min_input_length:0,ajax_url:'<?php echo $ajax_url.'input_select_rswo_product_reference_search/'?>'
                        ,exceptional_data_func:function(){
                            var lresult = [];
                            var lparent_pane = rswo_parent_pane;
                            var lprefix_id = rswo_component_prefix_id;

                            var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                            $.each($(ltbody).find('tr'),function(lidx, lrow){
                                
                            });
                            
                            return lresult;
                        }
                    });
                    
                    $(lproduct_reference_td).find('input[original]').select2('disable');
                    
                    APP_COMPONENT.input_select.set($(lunit_td).find('input[original]')[0]);
            
                }
                //</editor-fold>
            },
        },
        expected_product_result_table:{
            get:function(){
                //<editor-fold defaultstate="collapsed">
                var lresult = [];
                var lparent_pane = rswo_parent_pane;
                var lprefix_id = rswo_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_expected_product_result_table tbody')[0];
                
                $.each($(ltbody).find('tr'),function(lidx, lrow){
                    var lindex = $(lrow).index();
                    
                    var ltemp_row  = {
                        product_type:$(lrow).find('[col_name="product_type"]').text(),
                        product_id:$(lrow).find('[col_name="product_id"]').text(),
                        unit_id:$(lrow).find('[col_name="unit_id"]').text(),
                    };
                    
                    if(lindex<($(ltbody).find('tr').length-1)){
                        ltemp_row.qty=$(lrow).find('[col_name="qty"] span').text().replace(/[^0-9.]/g,''); 
                    }
                    else{
                        ltemp_row.qty=$(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                    }

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
                var lparent_pane = rswo_parent_pane;
                var lprefix_id = rswo_component_prefix_id;
                $(rswo_parent_pane).find(lprefix_id+'_expected_product_result_table tbody').empty();
                $(lparent_pane).find(lprefix_id+'_expected_product_result_table [col_name="movement_outstanding_qty"]').hide();
            },
            load:function(iproduct_arr){
                var lparent_pane = rswo_parent_pane;
                var lprefix_id = rswo_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                
                rswo_methods.expected_product_result_table.reset();
                $.each(iproduct_arr,function(lidx,lproduct){
                    rswo_methods.expected_product_result_table.row_generate(lproduct);
                });                
                $(lparent_pane).find(lprefix_id+'_expected_product_result_table [col_name="movement_outstanding_qty"]').show();
            },
            row_generate:function(iproduct){
                
                var lparent_pane = rswo_parent_pane;
                var lprefix_id = rswo_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var ltbody = $(lparent_pane).find(lprefix_id+'_expected_product_result_table tbody')[0];
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                var lrow = document.createElement('tr');
                var lrow_detail = document.createElement('tr');
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:($(ltbody).children().length)+1,type:'text'});                            
                
                if(lmethod === 'add'){
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_type',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_img',style:'vertical-align:middle',val:'',type:'text'});
                    var lproduct_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    var lunit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                    var lqty_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'qty',col_style:'text-align:right',style:'text-align:right',val:'0',type:'input'});
                    var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    var lnew_row = APP_COMPONENT.new_row();    
                    laction.appendChild(lnew_row);
                }
                else {
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_type',style:'vertical-align:middle',val:'',type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_img',style:'vertical-align:middle',val:iproduct.product_img,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'vertical-align:middle',val:iproduct.product_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit',style:'vertical-align:middle',val:iproduct.unit_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'qty',col_style:'text-align:right',val:iproduct.qty,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',class:'text-red',col_name:'movement_outstanding_qty',col_style:'text-align:right',val:iproduct.movement_outstanding_qty,type:'span'});
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
                            $(lrow).find('[col_name="qty"]')[0].innerHTML = '<span>'+lqty+'</span>';
                            var ltrash = APP_COMPONENT.trash();
                            $(lrow).find('[col_name="action"]').empty();
                            $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                            $(ltbody).append(rswo_methods.expected_product_result_table.row_generate([]));                        
                            
                        }                    
                    });
                    
                    APP_COMPONENT.input.numeric($(lqty_td).find('input')[0],{min_val:0,reset:true});
                    $(lqty_td).find('input').blur();
                
                    $(lproduct_td).find('input[original]').on('change',function(){
                        var lparent_pane = rswo_parent_pane;
                        var lproduct_category_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        var lval = $(this).select2('val');
                        
                        
                        if((lval === '')){
                            $(lrow).remove();
                            rswo_methods.expected_product_result_table.row_generate([]);
                        }
                        else{
                            var ldata = $(this).select2('data');
                            $(lrow).find('[col_name="product_id"]').text(ldata.product_id);  
                            $(lrow).find('[col_name="product_type"]').text(ldata.product_type);
                            $(lrow).find('[col_name="product_img"]')[0].innerHTML = ldata.product_img;
                            $(lrow).find('[col_name="unit"] input[original]').select2({data:ldata.unit});
                            $(lrow).find('[col_name="unit"] input[original]').select2('data',ldata.unit[0]);
                            $(lrow).find('[col_name="unit"] input[original]').change();
                            
                        }
                        
                    }); 

                    $(lunit_td).find('input[original]').on('change',function(){
                        
                        var lparent_pane = rswo_parent_pane;
                        var lunit_id = $(this).select2('val');
                        var lrow = $(this).closest('tr')[0];
                        $(lrow).find('[col_name="unit_id"]').text(lunit_id); 
                        var lqty_inpt = $(lrow).find('[col_name="qty"] input')[0];
                        
                        APP_COMPONENT.input.numeric(lqty_inpt,{min_val:'0',reset:true});
                        
                        if(lunit_id !== ''){
                            var ldata = $(this).select2('data');
                            var lproduct_type = $(lrow).find('[col_name="product_type"]').text();
                            if(lproduct_type ==='refill_work_order_product'){
                                APP_COMPONENT.input.numeric(lqty_inpt,{min_val:'0',max_val:ldata.stock_qty,reset:true});
                            }
                        }
                        
                    }); 
                    
                    APP_COMPONENT.input_select.set($(lproduct_td).find('input[original]')[0],{
                        min_input_length:0,ajax_url:'<?php echo $ajax_url.'input_select_product_search/'?>'
                        ,exceptional_data_func:function(){
                            var lresult = [];
                            var lparent_pane = rswo_parent_pane;
                            var lprefix_id = rswo_component_prefix_id;

                            var ltbody = $(lparent_pane).find(lprefix_id+'_expected_product_result_table tbody')[0];
                            $.each($(ltbody).find('tr'),function(lidx, lrow){
                                var lproduct_type = $(lrow).find('[col_name="product_type"]').text();
                                var lproduct_id = $(lrow).find('[col_name="product_id"]').text();
                                lresult.push({id:lproduct_type+"#"+lproduct_id});
                            });
                            
                            return lresult;
                        }
                    });
                    APP_COMPONENT.input_select.set($(lunit_td).find('input[original]')[0]);
                    
                }
                
            },
        }
        
    }

    var rswo_bind_event = function(){
        var parent_pane = rswo_parent_pane;
        var lprefix_id = rswo_component_prefix_id;
        
        
        
        $(parent_pane).find('#rswo_submit').off('click');
        $(parent_pane).find('#rswo_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = rswo_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                rswo_methods.submit();
            });
            $(rswo_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        

    }
    
    var rswo_components_prepare= function(){
        
        var method = $(rswo_parent_pane).find("#rswo_method").val();
        
        
        var rswo_data_set = function(){
            var lparent_pane = rswo_parent_pane;
            var lprefix_id = rswo_component_prefix_id;
            switch(method){
                case "add":
                    rswo_methods.reset_all();      
                    break;
                case "view":
                    var rswo_id = $(rswo_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:rswo_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(rswo_data_support_url+"refill_subcon_work_order_get",json_data).response;
                    
                    if(lresponse != []){
                        var lrswo = lresponse.rswo;
                        $(lparent_pane).find(lprefix_id+'_store')
                            .select2('data',{id:lrswo.store_id
                                ,text:lrswo.store_text}).change();
                        $(lparent_pane).find(lprefix_id+"_code").val(lrswo.code);
                        $(lparent_pane).find(lprefix_id+'_refill_subcon_work_order_date').datetimepicker({value:lrswo.refill_subcon_work_order_date});
                        $(lparent_pane).find(lprefix_id+'_refill_subcon')
                            .select2('data',{id:lrswo.refill_subcon_id
                                ,text:lrswo.refill_subcon_text}).change();
                        $(lparent_pane).find(lprefix_id+"_notes").val(lrswo.notes);
                        
                        $(lparent_pane).find(lprefix_id+'_refill_subcon_work_order_status')
                            .select2('data',{id:lrswo.refill_subcon_work_order_status
                                ,text:lrswo.refill_subcon_work_order_status_text}).change();

                        $(lparent_pane).find(lprefix_id+'_refill_subcon_work_order_status')
                            .select2({data:lresponse.refill_subcon_work_order_status_list});
                        
                        rswo_methods.product_table.load(lresponse.rswo_product);
                        rswo_methods.expected_product_result_table.load(lresponse.rswo_expected_product_result);
                    };
                    
                    
                    
                    break;            
            }
        }
    
        rswo_methods.enable_disable();
        rswo_methods.show_hide();
        rswo_data_set();
    }
    
</script>