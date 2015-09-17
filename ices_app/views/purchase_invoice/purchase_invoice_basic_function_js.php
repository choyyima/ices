<script>

    var purchase_invoice_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var purchase_invoice_ajax_url = null;
    var purchase_invoice_index_url = null;
    var purchase_invoice_view_url = null;
    var purchase_invoice_window_scroll = null;
    var purchase_invoice_data_support_url = null;
    var purchase_invoice_common_ajax_listener = null;
    var purchase_invoice_component_prefix_id = '';
    
    var purchase_invoice_insert_dummy = false;

    var purchase_invoice_init = function(){
        
        var parent_pane = purchase_invoice_parent_pane;
        purchase_invoice_ajax_url = '<?php echo $ajax_url ?>';
        purchase_invoice_index_url = '<?php echo $index_url ?>';
        purchase_invoice_view_url = '<?php echo $view_url ?>';
        purchase_invoice_window_scroll = '<?php echo $window_scroll; ?>';
        purchase_invoice_data_support_url = '<?php echo $data_support_url; ?>';
        purchase_invoice_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        purchase_invoice_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        purchase_invoice_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var purchase_invoice_methods = {
        hide_all:function(){
            var lparent_pane = purchase_invoice_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#purchase_invoice_print').hide();
            $(lparent_pane).find('#purchase_invoice_submit').hide();
            $(lparent_pane).find('#purchase_invoice_product_table [col_name="movement_outstanding_qty"]').hide();
        },
        show_hide:function(){
            var lparent_pane = purchase_invoice_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_invoice_method').val();
            purchase_invoice_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#purchase_invoice_submit').show();
                    $(lparent_pane).find('#purchase_invoice_type').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_invoice_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_reference_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_supplier').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_invoice_product_arrival_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_purchase_invoice_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_purchase_invoice_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_notes').closest('.form-group').show();
                    
                    break;
                case 'view':
                    $(lparent_pane).find('#purchase_invoice_submit').show();
                    $(lparent_pane).find('#purchase_invoice_type').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_invoice_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_reference_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_supplier').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_invoice_product_arrival_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_purchase_invoice_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_purchase_invoice_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_invoice_notes').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_invoice_product_table [col_name="movement_outstanding_qty"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = purchase_invoice_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = purchase_invoice_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_invoice_method').val();    
            purchase_invoice_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#purchase_invoice_type').select2('enable');
                    $(lparent_pane).find('#purchase_invoice_store').select2('enable');
                    $(lparent_pane).find('#purchase_invoice_supplier').select2('enable');
                    $(lparent_pane).find('#purchase_invoice_product_arrival_date').prop('disabled',false);
                    $(lparent_pane).find('#purchase_invoice_notes').prop('disabled',false);
                    $(lparent_pane).find('#purchase_invoice_reference_code').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#purchase_invoice_notes').prop('disabled',false);
                    break;
            }
        },
        expense_table:{
            reset:function(){
                var lparent_pane = purchase_invoice_parent_pane;
                var lprefix_id = purchase_invoice_component_prefix_id;
                $(lparent_pane).find(lprefix_id+'_expense_table tbody').empty();
                $(lparent_pane).find(lprefix_id+'_expense_total').text('0.00');
                var lrow = purchase_invoice_methods.expense_table.input_row_generate();
                $(lparent_pane).find(lprefix_id+'_expense_table tbody').append(lrow);
            },
            total_calculate:function(){
                var lparent_pane = purchase_invoice_parent_pane;
                var lprefix_id = purchase_invoice_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                var ltr_arr = $(lparent_pane).find(lprefix_id+'_expense_table tbody tr');
                var ltotal = parseFloat('0');
                $.each(ltr_arr,function(lidx, ltr){
                    switch(lmethod){
                        case 'add':
                            ltotal+=parseFloat($(ltr).find('[col_name="amount"] input').val().replace(/[^0-9.]/g,''));
                            break;
                        case 'view':
                            ltotal+=parseFloat($(ltr).find('[col_name="amount"] span').text().replace(/[^0-9.]/g,''));
                            break;
                    }
                });
                $(lparent_pane).find(lprefix_id+'_expense_total').text(APP_CONVERTER.thousand_separator(ltotal));
            },
            input_row_generate:function(){
                var lrow = document.createElement('tr');
                var lparent_pane = purchase_invoice_parent_pane;
                var fast_draw = APP_COMPONENT.table_fast_draw;
                var lprefix_id = purchase_invoice_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_expense_table tbody')[0];
                var row_num = $(ltbody).children().length;
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                var ldescription_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'description',style:'vertical-align:middle;text-align:left;font-size:12px',val:'',type:'input'});
                var lamount_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'amount',style:'vertical-align:middle;text-align:right;font-size:12px',val:'0.00',type:'input'});
                var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                var lnew_row = APP_COMPONENT.new_row();    
                laction.appendChild(lnew_row);
                
                var lamount_input = $(lamount_td).find('input')[0];
                APP_COMPONENT.input.numeric(lamount_input,{min_val:0});
                
                $(lnew_row).on('click',function(){
                    var lrow = $(this).closest('tr');                    
                    var ldescription = $(lrow).find('[col_name="description"] input').val().replace(/[ ]/g,'');
                    var lamount = $(lrow).find('[col_name="amount"] input').val().replace(/[^0-9,]/g,'');
                    if(parseFloat(lamount)>0 && ldescription !==''){
                        $(lrow).find('[col_name="description"]').find('input').prop('disabled',true);
                        $(lrow).find('[col_name="amount"]').find('input').prop('disabled',true);
                        var ltrash = APP_COMPONENT.trash();
                        $(lrow).find('[col_name="action"]').empty();
                        $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                        $(ltrash).on('click',function(){
                            purchase_invoice_methods.expense_table.total_calculate()();
                            
                        })
                        var ltbody = $(purchase_invoice_parent_pane).find(lprefix_id+'_expense_table').find('tbody')[0];
                        var linput_row = purchase_invoice_methods.expense_table.input_row_generate();
                        ltbody.appendChild(linput_row);
                        $(linput_row).find('[col_name="product"]').find('input').select2('open');
                    }
                    
                    window.scrollTo(0,document.body.scrollHeight);
                    
                });
                
                $(lamount_input).on('blur',function(){
                    var lrow = $(this).closest('tr')[0];
                    purchase_invoice_methods.expense_table.total_calculate();
                });

                
                return lrow;
            },
            data_get:function(){
                var lresult = [];
                var lparent_pane = purchase_invoice_parent_pane;
                var lprefix_id = purchase_invoice_component_prefix_id;
                
                var ltbody = $(lparent_pane).find(lprefix_id+'_expense_table tbody')[0];
                var ltr_arr = $(ltbody).find('tr');
                $.each(ltr_arr, function(lidx,ltr){
                    var ldescription = $(ltr).find('[col_name="description"] input').val();
                    var lamount = $(ltr).find('[col_name="amount"] input').val().replace(/[^0-9.]/g,'');
                    if(ldescription.replace(/[ ]/g,'') !== '' && parseFloat(lamount)>parseFloat('0')){
                        lresult.push({description:ldescription, amount: lamount});
                    }
                });
                
                return lresult;
            },
        },
        product_table:{
            reset:function(){
                var lparent_pane = purchase_invoice_parent_pane;
                var lprefix_id = purchase_invoice_component_prefix_id;
                $(lparent_pane).find(lprefix_id+'_product_table tbody').empty();
                $(lparent_pane).find(lprefix_id+'_product_total').text('0.00');
                var lrow = purchase_invoice_methods.product_table.input_row_generate();
                $(lparent_pane).find(lprefix_id+'_product_table tbody').append(lrow);
            },
            total_calculate:function(){
                var lparent_pane = purchase_invoice_parent_pane;
                var lprefix_id = purchase_invoice_component_prefix_id;
                var ltr_arr = $(lparent_pane).find(lprefix_id+'_product_table tbody tr');
                var ltotal = parseFloat('0');
                $.each(ltr_arr,function(lidx, ltr){
                    ltotal+=parseFloat($(ltr).find('[col_name="subtotal"] span').text().replace(/[^0-9.]/g,''));
                });
                $(lparent_pane).find(lprefix_id+'_product_total').text(APP_CONVERTER.thousand_separator(ltotal));
            },
            subtotal_calculate:function(lrow){
                var lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                var lamount = $(lrow).find('[col_name="amount"] input').val().replace(/[^0-9.]/g,'');
                var lsubtotal = parseFloat(lqty) * parseFloat(lamount);
                $(lrow).find('[col_name="subtotal"] span').text(APP_CONVERTER.thousand_separator(lsubtotal.toString()));
            },
            input_row_generate:function(){
            // <editor-fold defaultstate="collapsed">
                var lrow = document.createElement('tr');
                var lparent_pane = purchase_invoice_parent_pane;
                var fast_draw = APP_COMPONENT.table_fast_draw;
                var lprefix_id = purchase_invoice_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                var row_num = $(ltbody).children().length;
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:'',type:'text'});
                var lproduct_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'vertical-align:middle',val:'<div><input original class="pos-product-search"> </div>',type:'text'});
                var lqty_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'qty',style:'vertical-align:middle;text-align:right;font-size:12px',val:'0.00',type:'input'});
                var lunit_td = fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:'<div><input original class="pos-unit-search"> </div>',type:'text'});
                var lamount_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'amount',style:'vertical-align:middle;text-align:right;font-size:12px',val:'0.00',type:'input'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'subtotal',col_style:'vertical-align:middle;text-align:right',val:'<span>0.00</span>',type:'text'});
                var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                var lnew_row = APP_COMPONENT.new_row();    
                laction.appendChild(lnew_row);
                
                purchase_invoice_product_timeout = null;
                $(lproduct_td).find('input').select2({                    
                    minimumInputLength:1
                    ,placeholder: 'Search Product'
                    ,allowClear:true
                    ,query:function(query){
                        window.clearTimeout(purchase_invoice_product_timeout);
                        purchase_invoice_product_timeout = window.setTimeout(function(){    
                            var lparent_pane = purchase_invoice_parent_pane;
                            var typed_word = query.term.toLowerCase();
                            if(typed_word.replace(' ','') == '') typed_word = '';
                            if(typed_word[0] == ' '){typed_word=typed_word.substr(1,typed_word.length-1);}
                            var data={results:[]};
                            var lrows = $(purchase_invoice_parent_pane).find(lprefix_id+'_product_table>tbody>tr');
                            var excluded_product = [];
                            $.each(lrows,function(key, val){
                                excluded_product.push($(val).find('[col_name="product"] input[original]').select2('val'));
                            });
                            
                            var json_data = {data:typed_word,excluded_product:excluded_product}; 
                            var url = "<?php echo get_instance()->config->base_url().'purchase_invoice/ajax_search/input_select_product_search/' ?>";            
                            var lresponse = APP_DATA_TRANSFER.ajaxPOST(url,json_data).response;
                            for (var i = 0; i < lresponse.length;i++ ){
                                data.results.push(lresponse[i]);
                            }
                            query.callback(data);
                        },200);
                    }
                });  

                $(lunit_td).find('input').select2({data:[],placeholder:''});
                
                var lqty_input = $(lqty_td).find('input')[0];
                APP_COMPONENT.input.numeric(lqty_input,{min_val:0});
                
                var lamount_input = $(lamount_td).find('input')[0];
                APP_COMPONENT.input.numeric(lamount_input,{min_val:0});
                
                $(lnew_row).on('click',function(){
                    var lrow = $(this).closest('tr');
                    var lsubtotal = $(lrow).find('[col_name="subtotal"]').text().replace(/[^0-9.]/g,'');
                    var lproduct_id = $(lrow).find('[col_name="product"]').find('input').select2('val');
                    var lunit_id = $(lrow).find('[col_name="unit"]').find('input').select2('val');
                    var lqty = $(lrow).find('[col_name="qty"]').find('input').val().replace(/[^0-9.]/g,'');
                    if(lproduct_id !=='' && lunit_id !== '' && parseFloat(lqty)>0
                    ){
                        $(lrow).find('[col_name="product"]').find('input').select2('disable');
                        $(lrow).find('[col_name="unit"]').find('input').select2('disable');
                        $(lrow).find('[col_name="qty"]').find('input').prop('disabled',true);
                        $(lrow).find('[col_name="amount"]').find('input').prop('disabled',true);
                        var ltrash = APP_COMPONENT.trash();
                        $(lrow).find('[col_name="action"]').empty();
                        $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                        $(ltrash).on('click',function(){
                            purchase_invoice_methods.product_table.total_calculate()();
                            
                        })
                        var ltbody = $(purchase_invoice_parent_pane).find(lprefix_id+'_product_table').find('tbody')[0];
                        var linput_row = purchase_invoice_methods.product_table.input_row_generate();
                        ltbody.appendChild(linput_row);
                        $(linput_row).find('[col_name="product"]').find('input').select2('open');
                    }
                    
                    window.scrollTo(0,$('#purchase_invoice_expense_table').offset().top -100);
                    
                });
                
                $(lamount_input).on('blur',function(){
                    var lrow = $(this).closest('tr')[0];
                    purchase_invoice_methods.product_table.subtotal_calculate(lrow);
                    purchase_invoice_methods.product_table.total_calculate();
                });
                
                $(lqty_input).on('blur',function(){
                    var lrow = $(this).closest('tr')[0];
                    purchase_invoice_methods.product_table.subtotal_calculate(lrow);
                    purchase_invoice_methods.product_table.total_calculate();
                });
                
                $(lproduct_td).find('input[original]').on('change',function(){
                    var lparent_pane = purchase_invoice_parent_pane;                    
                    var lprefix_id = purchase_invoice_component_prefix_id
                    var lproduct_id = $(this).select2('val');
                    var lrow = $(this).closest('tr')[0];
                    $(lrow).find('[col_name="unit"] input[original]').select2('data',null);
                    $(lrow).find('[col_name="unit"] input[original]').select2({data:[],placeholder:''});
                    if(lproduct_id!==''){
                        var ldata = $(this).select2('data');
                        $(lrow).find('[col_name="unit"] input[original]').select2({data:ldata.unit});
                        if(ldata.unit !== null){
                            $(lrow).find('[col_name="unit"] input[original]').select2('data',ldata.unit[0]);
                        }
                    }
                });
                return lrow;
            // </editor-fold>
            },
            data_get:function(){
                var lresult = [];
                var lparent_pane = purchase_invoice_parent_pane;
                var lprefix_id = purchase_invoice_component_prefix_id;
                
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                var ltr_arr = $(ltbody).find('tr');
                $.each(ltr_arr, function(lidx, ltr){
                    lproduct_id = $(ltr).find('[col_name="product"] input[original]').select2('val');
                    lunit_id = $(ltr).find('[col_name="unit"] input[original]').select2('val');
                    lqty = $(ltr).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                    lamount = $(ltr).find('[col_name="amount"] input').val().replace(/[^0-9.]/g,'');
                    if(lproduct_id!=='' && lunit_id!== '' && parseFloat(lqty)>0){
                        lresult.push({product_id:lproduct_id, unit_id:lunit_id,qty:lqty,amount:lamount});
                    }
                });
                
                return lresult;
            },
        },
        reset_all:function(){// <editor-fold defaultstate="collapsed">
            var lparent_pane = purchase_invoice_parent_pane;
            var lprefix_id = purchase_invoice_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'purchase_invoice'}).response;

            $(lparent_pane).find(lprefix_id+'_purchase_invoice_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            
            $(lparent_pane).find(lprefix_id+'_purchase_invoice_status').
                select2({data:lstatus_list});
            
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_purchase_invoice_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            
            $(lparent_pane).find(lprefix_id+'_product_arrival_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('hour', 1,'F d, Y H:i')
            });
            purchase_invoice_methods.product_table.reset();
            purchase_invoice_methods.expense_table.reset();
        // </editor-fold>
        },
        submit:function(){
            var lparent_pane = purchase_invoice_parent_pane;
            var lprefix_id = purchase_invoice_component_prefix_id;
            var lajax_url = purchase_invoice_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.purchase_invoice = {
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        supplier_id:$(lparent_pane).find(lprefix_id+'_supplier').select2('val'),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    
                    }
                    
                    json_data.info = {
                        product_arrival_date:$(lparent_pane).find(lprefix_id+'_product_arrival_date').val(),
                        reference_code:$(lparent_pane).find(lprefix_id+'_reference_code').val()
                    }
                    
                    json_data.product = purchase_invoice_methods.product_table.data_get();
                    json_data.expense = purchase_invoice_methods.expense_table.data_get(),
                    lajax_url +='purchase_invoice_add/';
                    break;
                    
                case 'view':
                    json_data.purchase_invoice = {
                        notes: $(lparent_pane).find(lprefix_id+'_notes').val(),
                        purchase_invoice_status: $(lparent_pane).find(lprefix_id+'_purchase_invoice_status').select2('val'),
                        cancellation_reason: $(lparent_pane).find(lprefix_id+'_purchase_invoice_cancellation_reason').val()
                    }
                    
                    var purchase_invoice_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_purchase_invoice_status').
                        select2('data').method;
                    lajax_url +=lajax_method+'/'+purchase_invoice_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(purchase_invoice_view_url !==''){
                    var url = purchase_invoice_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    purchase_invoice_after_submit();
                }
            }
        }
    };
    
    var purchase_invoice_bind_event = function(){
        var lparent_pane = purchase_invoice_parent_pane;
        var lprefix_id = purchase_invoice_component_prefix_id;
        
        
        $(lparent_pane).find('#purchase_invoice_submit').off();        
        $(lparent_pane).find('#purchase_invoice_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = purchase_invoice_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            modal_confirmation_cancel_primary_data_key = "sales_prospect";
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                purchase_invoice_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            
            $(purchase_invoice_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var purchase_invoice_components_prepare = function(){
        

        var purchase_invoice_data_set = function(){
            var lparent_pane = purchase_invoice_parent_pane;
            var lprefix_id = purchase_invoice_component_prefix_id;
            var lmethod = $(lparent_pane).find('#purchase_invoice_method').val();
            
            switch(lmethod){
                case 'add':
                    purchase_invoice_methods.reset_all();
                    if(purchase_invoice_insert_dummy){
                        $(lprefix_id+'_supplier').select2('data',{id: "32", text: "<strong >SUP1</strong> Supplier 1 08111111111 "});
                        
                        var ltbody = $(lprefix_id+'_product_table tbody')[0];
                        
                        var lproduct_arr = [
                            {
                                product:{id: "21", name: "Cartridge CART 6 KG Bertekanan", code: "CCT/CART6", unit: [{id: "1",text: "PCS"}], text: "<strong >CCT/CART6</strong>"},
                                qty:'100',
                                amount:'100000'
                            },
                            {
                                product:{id: "22", name: "Cartridge CART 9 KG Bertekanan", code: "CCT/CART9", unit: [{id: "1",text: "PCS"}], text: "<strong >CCT/CART6</strong>"},
                                qty:'320',
                                amount:'20000'
                            },
                            {
                                product:{id: "23", name: " Thermatic THERM 6 KG Kosong", code: "CCT/THERM6", unit: [{id: "1",text: "PCS"}], text: "<strong >CCT/CART6</strong>"},
                                qty:'10000',
                                amount:'20000'
                            },
                        ];
                        
                        
                        $.each(lproduct_arr, function(lidx, lproduct){
                            lrow = $(ltbody).find('tr').last();                            
                            $(lrow).find('[col_name="product"] input[original]').select2('data',lproduct.product).change();
                            $(lrow).find('[col_name="qty"] input').val(lproduct.qty).blur();
                            $(lrow).find('[col_name="amount"] input').val(lproduct.amount).blur();
                            $(lrow).find('[col_name="action"] button').click();
                            $(ltbody).find('tr').last().find('[col_name="product"] input[original]').select2('close');
                        });
                        
                        var ldescription_arr = [
                            {description:'Description 1', amount:'100000'},
                            {description:'Description 2', amount:'230000'},
                        ];
                        
                        var ltbody = $(lprefix_id+'_expense_table tbody')[0];
                        
                        $.each(ldescription_arr, function(lidx, ldescription){
                            lrow = $(ltbody).find('tr').last();
                            $(lrow).find('[col_name="description"] input').val(ldescription.description).blur();
                            $(lrow).find('[col_name="amount"] input').val(ldescription.amount).blur();
                            $(lrow).find('[col_name="action"] button').click();
                        });
                        
                    }
                    break;
                case 'view':
                    
                    var lpurchase_invoice_id = $(lparent_pane).find('#purchase_invoice_id').val();
                    var lajax_url = purchase_invoice_data_support_url+'purchase_invoice_get/';
                    var json_data = {data:lpurchase_invoice_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lpurchase_invoice = lresponse.purchase_invoice;
                    var lproduct_arr = lresponse.product;
                    var lexpense_arr = lresponse.expense;
                    var linfo = lresponse.info;
                    
                    $(lparent_pane).find('#purchase_invoice_store').select2('data',{id:lpurchase_invoice.store_id
                        ,text:lpurchase_invoice.store_text});
                    $(lparent_pane).find('#purchase_invoice_code').val(lpurchase_invoice.code);
                    $(lparent_pane).find('#purchase_invoice_reference_code').val(linfo.reference_code);
                    $(lparent_pane).find('#purchase_invoice_product_arrival_date').datetimepicker({value:linfo.product_arrival_date});
                    $(lparent_pane).find('#purchase_invoice_purchase_invoice_date').datetimepicker({value:lpurchase_invoice.purchase_invoice_date});
                    $(lparent_pane).find('#purchase_invoice_purchase_invoice_cancellation_reason').val(lpurchase_invoice.cancellation_reason);

                    $(lparent_pane).find('#purchase_invoice_purchase_invoice_status')
                            .select2('data',{id:lpurchase_invoice.purchase_invoice_status
                                ,text:lpurchase_invoice.purchase_invoice_status_text}).change();
                    
                    $(lparent_pane).find('#purchase_invoice_supplier')
                            .select2('data',{id:lpurchase_invoice.supplier_id
                                ,text:lpurchase_invoice.supplier_text}).change();
                    
                    $(lparent_pane).find('#purchase_invoice_purchase_invoice_status')
                            .select2({data:lresponse.purchase_invoice_status_list});
                    
                    $(lparent_pane).find('#purchase_invoice_notes').val(lpurchase_invoice.notes);
                    
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                    $(ltbody).empty();
                    $.each(lproduct_arr,function(lidx,lproduct){
                        var lrow = document.createElement('tr');
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:lproduct.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:lproduct.product_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:lproduct.product_text,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'qty',col_style:'vertical-align:middle;text-align:right',val:lproduct.qty,type:'span'});
                        var lm_outstanding_qty = fast_draw.col_add(lrow,{tag:'td',col_name:'movement_outstanding_qty',col_style:'vertical-align:middle;text-align:right;',val:lproduct.movement_outstanding_qty,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit',col_style:'vertical-align:middle;text-align:left;',val:lproduct.unit_text,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'vertical-align:middle;text-align:right;',val:lproduct.amount,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'subtotal',col_style:'vertical-align:middle;text-align:right;',val:lproduct.subtotal,type:'span'});
                        $(ltbody).append(lrow);
                        
                        if(parseFloat($(lm_outstanding_qty).find('span').text().replace(/[^0-9.]/g,''))>parseFloat('0')){
                            $(lrow).find('[col_name="movement_outstanding_qty"]').css('color','red');
                        }
                    });
                    purchase_invoice_methods.product_table.total_calculate();
                    
                    
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var ltbody = $(lparent_pane).find(lprefix_id+'_expense_table tbody')[0];
                    $(ltbody).empty();
                    $.each(lexpense_arr,function(lidx,lexpense){
                        var lrow = document.createElement('tr');
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'description',style:'vertical-align:middle',val:lexpense.description,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'vertical-align:middle;text-align:right;',val:lexpense.amount,type:'span'});
                        $(ltbody).append(lrow);
                    });
                    purchase_invoice_methods.expense_table.total_calculate();
                    
                    break;
            }
        }
        
        
        purchase_invoice_methods.enable_disable();
        purchase_invoice_methods.show_hide();
        purchase_invoice_data_set();
    }
    
    var purchase_invoice_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>