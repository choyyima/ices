<script>
    var rcrf_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var rcrf_ajax_url = null;
    var rcrf_index_url = null;
    var rcrf_view_url = null;
    var rcrf_window_scroll = null;
    var rcrf_data_support_url = null;
    var rcrf_common_ajax_listener = null;
    var rcrf_component_prefix_id = '';
    
    var rcrf_init = function(){
        var parent_pane = rcrf_parent_pane;

        rcrf_ajax_url = '<?php echo $ajax_url ?>';
        rcrf_index_url = '<?php echo $index_url ?>';
        rcrf_view_url = '<?php echo $view_url ?>';
        rcrf_window_scroll = '<?php echo $window_scroll; ?>';
        rcrf_data_support_url = '<?php echo $data_support_url; ?>';
        rcrf_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        rcrf_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var rcrf_after_submit = function(){

    }
    
    var rcrf_data = {
        product_condition:<?php echo json_encode($product_condition); ?>
    }
    
    var rcrf_methods = {
        hide_all:function(){
            var lparent_pane = rcrf_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = rcrf_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
            
        },
        show_hide: function(){
            var lparent_pane = rcrf_parent_pane;
            var lprefix_id = rcrf_component_prefix_id;
            var lmethod = $(lparent_pane).find('#rcrf_method').val();            
            rcrf_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_checker').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_checking_result_form_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_checking_result_form_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    break;
            }
        },        
        enable_disable: function(){
            var lparent_pane = rcrf_parent_pane;
            var lmethod = $(lparent_pane).find('#rcrf_method').val();  
            var lprefix_id = rcrf_component_prefix_id;
            rcrf_methods.disable_all();
            
            switch(lmethod){
                case "add":
                    $(lparent_pane).find(lprefix_id+"_store").select2('enable');
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_checker").prop("disabled",false);
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = rcrf_parent_pane;
            var lprefix_id = rcrf_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            
            APP_FORM.store.store_set($(lparent_pane).find(lprefix_id+'_store'));
                
            $(lparent_pane).find(lprefix_id+'_refill_checking_result_form_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute','','F d, Y H:i'),
            });
            
            APP_FORM.status.default_status_set('refill_checking_result_form',
                $(lparent_pane).find(lprefix_id+'_refill_checking_result_form_status')
            );
            
            rcrf_methods.product_table.reset();
            rcrf_methods.product_table.row_generate({});
            
    
        },
        submit:function(){
            var parent_pane = rcrf_parent_pane;
            var lprefix_id = rcrf_component_prefix_id;
            var ajax_url = rcrf_index_url;
            var lmethod = $(parent_pane).find(lprefix_id+"_method").val();
            var rcrf_id = $(parent_pane).find(lprefix_id+"_id").val();        
            var json_data = {
                ajax_post:true,
                rcrf:{},
                message_session:true
            };
            
            switch(lmethod){
                case 'add':
                    json_data.rcrf.store_id = $(parent_pane).find(lprefix_id+"_store").select2('val');
                    json_data.rcrf.refill_checking_result_form_date = (new Date($(parent_pane).find(lprefix_id+"_refill_checking_result_form_date").val())).format('Y-m-d H:i:s');
                    json_data.rcrf.notes = $(parent_pane).find(lprefix_id+"_notes").val();
                    json_data.rcrf.checker = $(parent_pane).find(lprefix_id+'_checker').val();
                    json_data.rcrf_product = rcrf_methods.product_table.get();
                    
                    break;
                case 'view':
                    json_data.rcrf.refill_checking_result_form_status = $(parent_pane).find(lprefix_id+'_refill_checking_result_form_status').select2('val');
                    json_data.rcrf.notes = $(parent_pane).find(lprefix_id+"_notes").val();
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'rcrf_add';
                    break;
                case 'view':
                    lajax_method = $(parent_pane).find(lprefix_id+'_refill_checking_result_form_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+rcrf_id;
            var result = null;
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(rcrf_parent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(rcrf_view_url !==''){
                    var url = rcrf_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    rcrf_after_submit();
                }
            }
        },
        product_table:{
            get:function(){
                var lparent_pane = rcrf_parent_pane;
                var lprefix_id = rcrf_component_prefix_id;
                var lresult = [];
                var lrows = $(lparent_pane).find(lprefix_id+'_product_table>tbody>tr');
                $.each(lrows,function(lidx, lrow){
                    if($(lrow).index()%2 === 0){
                        var lproduct = {
                            reference_type:$(lrow).find('[col_name="reference_type"] div')[0].innerHTML,
                            reference_id:$(lrow).find('[col_name="reference_id"] div')[0].innerHTML,
                            product_type:$(lrow).find('[col_name="product_type"] div')[0].innerHTML,
                            product_id:$(lrow).find('[col_name="product_id"] div')[0].innerHTML,
                            product_condition:'',
                            notes:'',
                            product_recondition_cost:[]
                        };
                        
                        var lproduct_detail = rcrf_methods.product_detail.get($(lrow).next());
                        
                        lproduct.product_recondition_cost = lproduct_detail.product_recondition_cost;
                        lproduct.product_condition = lproduct_detail.product_condition;
                        lproduct.notes = lproduct_detail.notes;
                        lproduct.product_sparepart_cost = lproduct_detail.product_sparepart_cost;
                        
                        if(lproduct.product_id!==''){
                            lresult.push(lproduct);
                        }
                    }
                });
                return lresult;
            },
            reset:function(){
                var lprefix_id = rcrf_component_prefix_id;
                $(rcrf_parent_pane).find(lprefix_id+'_product_table tbody').empty();
            },
            load:function(iproduct_arr){
                var lparent_pane = rcrf_parent_pane;
                var lprefix_id = rcrf_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var lcurr_status = rcrf_data.curr_status;
                rcrf_methods.product_table.reset();
                $.each(iproduct_arr,function(lidx,lproduct){
                    rcrf_methods.product_table.row_generate(lproduct);
                });                
            },
            row_generate:function(iproduct){
                var lparent_pane = rcrf_parent_pane;
                var lprefix_id = rcrf_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                var lrow = document.createElement('tr');
                var lrow_detail = document.createElement('tr');
                
                //draw product row
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:($(ltbody).children().length/2)+1,type:'text'});                            
                if(lmethod === 'add'){
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'reference_type',style:'vertical-align:middle',val:'',type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'reference_id',style:'vertical-align:middle',val:'',type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_type',style:'vertical-align:middle',val:'',type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_id',style:'vertical-align:middle',val:'',type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_marking_code',style:'vertical-align:middle',val:'<div><input original></div>',type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_info',style:'vertical-align:middle',val:'',type:'div'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'estimated_amount',style:'text-align:right',val:'',type:'div'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'subtotal',style:'text-align:right',val:'',type:'div'});
                    
                }
                else {
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'reference_type',style:'vertical-align:middle',val:iproduct.reference_type,type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'reference_id',style:'vertical-align:middle',val:iproduct.reference_id,type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_type',style:'vertical-align:middle',val:iproduct.product_type,type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_id',style:'vertical-align:middle',val:iproduct.product_id,type:'div',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_marking_code',style:'vertical-align:middle',val:iproduct.product_marking_code,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_info',style:'vertical-align:middle',val:iproduct.product_info,type:'div'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'estimated_amount',style:'text-align:right',val:APP_CONVERTER.thousand_separator(iproduct.estimated_amount),type:'div'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'subtotal',style:'text-align:right',val:APP_CONVERTER.thousand_separator(iproduct.amount),type:'div'});
                    var lproduct_detail = {
                        product_condition:iproduct.product_condition,
                        product_condition_text:iproduct.product_condition_text,
                        product_recondition_cost:iproduct.rcrf_product_recondition_cost,
                        product_sparepart_cost:iproduct.rcrf_product_sparepart_cost,
                    };
                    rcrf_methods.product_detail.reset(lrow_detail);
                    rcrf_methods.product_detail.generate(lrow_detail,lproduct_detail);
                }
                
                var laction_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                
                if(lmethod === 'add'){
                    $(laction_td).append(APP_COMPONENT.new_row());
                }
                
                $(ltbody).append(lrow);
                //end of draw product row                
                
                $(ltbody).append(lrow_detail);
                
                
                if(lmethod === 'add'){
                    var lpmc_inpt = $(lrow).find('[col_name="product_marking_code"] input[original]');
                    APP_COMPONENT.input_select.set(lpmc_inpt,{
                        min_input_length:0
                        ,ajax_url:rcrf_ajax_url+'input_select_product_marking_code_search/'
                        ,exceptional_data_func:function(){
                            var lparent_pane = rcrf_parent_pane;
                            var lprefix_id = rcrf_component_prefix_id;
                            var lresult = [];

                            $.each($(lparent_pane).find(lprefix_id+'_product_table tbody>tr ')
                                ,function(lidx, lrow){
                                    lresult.push({id:$(lrow).find('[col_name="product_id"] div').text()});
                            });
                            return lresult;
                        }
                    });
                    
                    $(lpmc_inpt).on('change',function(){
                        var lval = $(this).select2('val');
                        var lrow = $(this).closest('tr');
                        var lproduct_row = $(this).closest('tr');
                        var lproduct_detail_row = $(this).closest('tr').next();
                        
                        $(this).closest('tr').find('[col_name="product_id"] div')[0].innerHTML = lval;
                        
                        if(lval!== ''){
                            var ldata = $(this).select2('data');
                            $(this).closest('tr').find('[col_name="product_type"] div')[0].innerHTML = ldata.product_type;
                            $(this).closest('tr').find('[col_name="reference_type"] div')[0].innerHTML = ldata.product_reference_type;
                            $(this).closest('tr').find('[col_name="reference_id"] div')[0].innerHTML = ldata.product_reference_id;
                            $(lrow).find('[col_name="product_info"] div')[0].innerHTML = ldata.product_info;
                            $(lrow).find('[col_name="estimated_amount"] div')[0].innerHTML = APP_CONVERTER.thousand_separator(ldata.estimated_amount);
                            $(this).closest('tr').find('[col_name="subtotal"] div')[0].innerHTML = APP_CONVERTER.thousand_separator('0');;
                            
                            var lresponse = APP_DATA_TRANSFER.ajaxPOST(rcrf_data_support_url+'input_select_product_marking_code_dependency_get/',
                                {product_id:lval,product_type:ldata.product_type}
                            ).response;
                            
                            lproduct_detail_data = {
                                product_sparepart_cost:lresponse.product_sparepart_cost
                            };
                            rcrf_methods.product_detail.reset(lproduct_detail_row);
                            rcrf_methods.product_detail.generate(lproduct_detail_row,lproduct_detail_data);
                            rcrf_methods.product_table.subtotal_calculate(lrow);
                        }
                        else{
                            var ltbody = $(this).closest('tbody');
                            $(lproduct_row).remove();
                            $(lproduct_detail_row).remove();
                            var lnew_row = rcrf_methods.product_table.row_generate({});
                            $(ltbody).append(lnew_row);
                        }
                    });
                    
                    $(lrow).find('[col_name="action"] button').on('click',function(){
                        var ltable = $(this).closest('table');
                        var ltr = $(this).closest('tr');
                        var lproduct_detail_row =  $(this).closest('tr').next();
                        var lproduct_id = $(ltr).find('[col_name="product_id"] div')[0].innerHTML;
                        var lproduct_recondition_cost_table = $(lproduct_detail_row).find('[product_recondition_cost] table');
                        var lproduct_recondition_cost = rcrf_methods.product_recondition_cost_table.get(lproduct_recondition_cost_table);
                        var lvalid = true;
                        
                        if(lproduct_id === '') lvalid = false;
                        if(lvalid){
                            rcrf_methods.product_detail.disable_all_input($(lproduct_detail_row));
                            var lproduct_marking_code_data = $(ltr).find('[col_name="product_marking_code"] input[original]').select2('data');
                            $(ltr).find('[col_name="product_marking_code"]')[0].innerHTML = '<div>'+lproduct_marking_code_data.product_marking_code+'</div>';
                            $(ltr).find('[col_name="action"]').empty();
                            $(ltr).find('[col_name="action"]').append(APP_COMPONENT.trash());
                            
                            $(ltr).find('[col_name="action"] button').off('click');
                            $(ltr).find('[col_name="action"] button').on('click',function(lidx, lrow){
                                var ltbody = $(ltr).closest('tbody')[0];
                                var lproduct_row = $(this).closest('tr');
                                var lproduct_detail_row = $(this).closest('tr').next();
                                $(lproduct_row).remove();
                                $(lproduct_detail_row).remove();
                                for(var i = 0; i<$(ltbody).children().length;i++){
                                    if(i%2 === 0){
                                        var row_num = (i%2)+1;
                                        $($(ltbody).children()[i]).find('[col_name="row_num"]').text(row_num);
                                    }
                                }
                            });
                            var lrow_new = rcrf_methods.product_table.row_generate({});
                            $(ltr).append(lrow_new);
                            
                        }
                    });
                    
                }
                else{
                    
                }
            },
            subtotal_calculate:function(irow){
                var lsubtotal_amount = APP_CONVERTER._float('0');
                var lrows = $(irow).next().find('[product_recondition_cost] table tbody tr');
                $.each(lrows,function(lidx, lrow){
                    var lamount = APP_CONVERTER._float('0');
                    if($(lrow).index() !== lrows.length - 1){
                        lamount = APP_CONVERTER._float($(lrow).find('[col_name="amount"] div')[0].innerHTML);
                    }
                    else{
                        lamount = APP_CONVERTER._float($(lrow).find('[col_name="amount"] input').val());
                    }
                    if(APP_CONVERTER._float(lamount) > APP_CONVERTER._float('0')
                        
                    )
                    lsubtotal_amount+= APP_CONVERTER._float(lamount);
                });
                
                var lrows = $(irow).next().find('[product_sparepart_cost] table tbody tr');
                $.each(lrows,function(lidx, lrow){
                    var lamount = APP_CONVERTER._float('0');
                    if($(lrow).find('[col_name="amount"] div').length === 1){
                        lamount = APP_CONVERTER._float($(lrow).find('[col_name="amount"] div')[0].innerHTML);
                    }
                    else{
                        lamount = APP_CONVERTER._float($(lrow).find('[col_name="amount"] input').val());
                    }
                    lsubtotal_amount+= APP_CONVERTER._float(lamount);
                });
                
                $(irow).find('[col_name="subtotal"] div')[0].innerHTML = APP_CONVERTER.thousand_separator(lsubtotal_amount);
            }
        },
        product_detail:{
            disable_all_input:function(irow){
                $(irow).find('[product_condition] input[original]').select2('disable');
                $(irow).find('[product_notes] textarea').prop('disabled',true);
                
                var lrow = $(irow).find('[product_recondition_cost] table tbody tr').last();
                $(lrow).find('[col_name="action"] button').click();
                $(irow).find('[product_recondition_cost] table tbody tr').last().remove();
                $(irow).find('[product_recondition_cost] table tbody tr [col_name="action"]').empty();
                
                $.each($(irow).find('[product_sparepart_cost] table tbody tr'),function(lidx, lrow){
                    var lcol = $(lrow).find('[col_name="amount"]');
                    var lamount = APP_CONVERTER._float($(lcol).find('input').val());
                    $(lcol)[0].innerHTML = '<div>'+APP_CONVERTER.thousand_separator(lamount)+'</div>';
                });
                        
            },
            get:function(irow){
                var lproduct_recondition_cost_table = $(irow).find('[product_recondition_cost] table');
                var lproduct_sparepart_cost_table = $(irow).find('[product_sparepart_cost] table');
                var lresult = {
                    product_condition:$(irow).find('[product_condition] input[original]').select2('val'),
                    product_recondition_cost:rcrf_methods.product_recondition_cost_table.get(lproduct_recondition_cost_table),
                    product_sparepart_cost:rcrf_methods.product_sparepart_cost_table.get(lproduct_sparepart_cost_table),
                    notes:$(irow).find('[product_notes] textarea').val(),
                };
                
                return lresult;
            },
            reset:function(irow){
                $(irow).empty();
                
            },
            generate:function(irow, idata){
                var lparent_pane = rcrf_parent_pane;
                var lprefix_id = rcrf_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                //drawing product detail
                fast_draw.col_add(irow,{tag:'td',col_style:'border-top:none'});
                var lproduct_detail_td = fast_draw.col_add(irow,{tag:'td',col_name:'product_detail',col_style:'border-top:none',attr:{colspan:'5'}});
                $(lproduct_detail_td).append('<div class="box" style="border-top:none;"><ul class="todo-list ui-sortable"></ul></div>');
                var lproduct_detail_ul = $(lproduct_detail_td).find('ul');
                var lul_style = 'border-left:2px solid #00a65a';
                $(lproduct_detail_ul).append($('<li product_condition class="" style="'+lul_style+'"><div title><span><strong>Product Condition</strong></span></div><div detail></div></li>'));
                $(lproduct_detail_ul).find('[product_condition] div[detail]').append($('<div style="margin-top:10px;"></div>'));
                $(lproduct_detail_ul).find('[product_condition] div[detail]').append($('<div style=""><input style="" class="" original></div>'));
                
                $(lproduct_detail_ul).append($('<li product_recondition_cost class="" style="'+lul_style+'"><div title><span><strong>Product Recondition Cost</strong></span></div><div detail></div></li>'));
                $(lproduct_detail_ul).find('[product_recondition_cost] div[detail]').append($('<div style="margin-top:10px;"></div>'));
                $(lproduct_detail_ul).find('[product_recondition_cost] div[detail]').append($('<div style=""><table class="table fixed-table" style="background-color:transparent"></div>'));
                var lproduct_recondition_cost_table = $(lproduct_detail_ul).find('[product_recondition_cost] [detail] table');
                rcrf_methods.product_recondition_cost_table.reset($(lproduct_recondition_cost_table));
                
                $(lproduct_detail_ul).append($('<li product_sparepart_cost class="" style="'+lul_style+'"><div title><span><strong>Product Sparepart Cost</strong></span></div><div detail></div></li>'));
                $(lproduct_detail_ul).find('[product_sparepart_cost] div[detail]').append($('<div style="margin-top:10px;"></div>'));
                $(lproduct_detail_ul).find('[product_sparepart_cost] div[detail]').append($('<div style=""><table class="table fixed-table" style="background-color:transparent"></div>'));
                var lproduct_sparepart_cost_table = $(lproduct_detail_ul).find('[product_sparepart_cost] [detail] table');
                rcrf_methods.product_sparepart_cost_table.reset($(lproduct_sparepart_cost_table));
                
                if(lmethod === 'add'){
                    rcrf_methods.product_recondition_cost_table.row_generate($(lproduct_recondition_cost_table),{});
                    rcrf_methods.product_sparepart_cost_table.load($(lproduct_sparepart_cost_table),idata.product_sparepart_cost);
                }
                else{
                    $.each(idata.product_recondition_cost,function(lprc_idx,lprc_row){
                        rcrf_methods.product_recondition_cost_table.row_generate($(lproduct_recondition_cost_table),lprc_row);
                    });
                    
                    rcrf_methods.product_sparepart_cost_table.load($(lproduct_sparepart_cost_table),idata.product_sparepart_cost);
                    
                }
                $(lproduct_detail_ul).append($('<li product_notes class="" style="'+lul_style+'"><div title><span><strong>Notes</strong></span></div><div detail></div></li>'));
                $(lproduct_detail_ul).find('[product_notes] div[detail]').append($('<div style="margin-top:10px;"></div>'));
                $(lproduct_detail_ul).find('[product_notes] div[detail]').append($('<div style=""><textarea class="form-control" style="" rows=3/></div>'));
                // end of drawing product detail
                
                
                //bind product detail component 
                var lpc_inpt = $(irow).find('[product_condition] input[original]');
                var lnotes_textarea = $(irow).find('[product_notes] textarea');
                if(lmethod === 'add'){                    
                    $(lpc_inpt).select2({data:rcrf_data.product_condition});
                    $(lpc_inpt).select2('data',rcrf_data.product_condition[0]);
                }
                else{
                    var lproduct_condition = [{id:idata.product_condition,text:idata.product_condition_text}];
                    $(lpc_inpt).select2({data:lproduct_condition});
                    $(lpc_inpt).select2('data',lproduct_condition[0]);
                    
                    $(lnotes_textarea).val(idata.notes);
                    $(lnotes_textarea).prop('disabled',true);
                    
                    
                }
                //end of bind product detail component
                
                
            }
        },
        product_recondition_cost_table:{
            get:function(itable){
                var lresult = [];
                var lrows = $(itable).find('tbody tr');
                $.each(lrows,function(lidx, lrow){
                    var lproduct_recondition_name = '';
                    var lamount = '';
                    var lvalid = true;
                    
                    if($(lrow).find('[col_name="product_recondition_name"] div').length>0){
                        lproduct_recondition_name = $(lrow).find('[col_name="product_recondition_name"] div')[0].innerHTML;
                        lamount = APP_CONVERTER._float($(lrow).find('[col_name="amount"] div')[0].innerHTML);
                    }
                    else{
                        lproduct_recondition_name = $(lrow).find('[col_name="product_recondition_name"] input').val();
                        lamount = APP_CONVERTER._float($(lrow).find('[col_name="amount"] input').val());
                    }
                    
                    if(lproduct_recondition_name.replace(/[ ]/g,'') === '' && 
                        APP_CONVERTER._float(lamount) <= APP_CONVERTER._float('0')
                    ) lvalid = false;

                    if(lvalid){
                        lresult.push({
                            product_recondition_name:lproduct_recondition_name,
                            amount:lamount
                        });
                    }
                    
                });
                return lresult;
            },
            reset:function(itable){
                $(itable).append($('<thead><tr></tr></thead>'));
                $(itable).append($('<tbody></tbody>'));
                $(itable).find('thead tr').append('<th col_name="row_num" class="table-row-num">#</th>');
                $(itable).find('thead tr').append('<th col_name="product_recondition_name">Recondition</th>');
                $(itable).find('thead tr').append('<th col_name="amount" style="width:250px;text-align:right">Amount</th>');
                $(itable).find('thead tr').append('<th col_name="action" class="table-action"></th>');
                
            },
            row_generate:function(itable,iproduct){
                var lparent_pane = rcrf_parent_pane;
                var lprefix_id = rcrf_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var ltbody = $(itable).find('tbody')[0];
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                var lrow = document.createElement('tr');
                
                //draw product recondition row
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:($(ltbody).children().length)+1,type:'text'});                            
                if(lmethod === 'add'){
                    fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'product_recondition_name',style:'',val:'',type:'input'});
                    fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'amount',style:'text-align:right',val:'',type:'input'});
                }
                else {
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_recondition_name',style:'',val:iproduct.product_recondition_name,type:'div'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'amount',style:'text-align:right',val:APP_CONVERTER.thousand_separator(iproduct.amount),type:'div'});
                    
                }
                var laction_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                
                if(lmethod === 'add'){
                    $(laction_td).append(APP_COMPONENT.new_row());
                }
                //end of draw product recondition row
                
                $(ltbody).append(lrow);
                
                //bind product recondition component
                if(lmethod === 'add'){
                    var lamount_inpt = $(lrow).find('[col_name="amount"] input');
                    APP_COMPONENT.input.numeric(lamount_inpt,{min_val:0});
                    
                    $(lrow).find('[col_name="amount"] input').on('change',function(){
                        rcrf_methods.product_table.subtotal_calculate($(this).closest('[col_name="product_detail"]').closest('tr').prev());
                    });
                    
                    $(lrow).find('[col_name="action"] button').on('click',function(){
                        var ltable = $(this).closest('table');
                        var ltr = $(this).closest('tr');
                        var lproduct_detail = $(ltr).closest('[col_name="product_detail"]');
                        var lproduct_recondition_name = $(ltr).find('[col_name="product_recondition_name"] input').val();
                        var lamount = APP_CONVERTER._float($(ltr).find('[col_name="amount"] input').val());
                        var lvalid = true;
                        
                        if(lproduct_recondition_name.replace(/[ ]/g,'') !== ''
                            || APP_CONVERTER._float(lamount) > APP_CONVERTER._float('0')
                        ){
                            $(ltr).find('[col_name="product_recondition_name"]')[0].innerHTML = '<div>'+lproduct_recondition_name+'</div>';
                            $(ltr).find('[col_name="amount"]')[0].innerHTML = '<div style="text-align:right">'+APP_CONVERTER.thousand_separator(lamount)+'</div>';
                            $(ltr).find('[col_name="action"]').empty();
                            $(ltr).find('[col_name="action"]').append(APP_COMPONENT.trash());
                            
                            $(ltr).find('[col_name="action"] button').on('click',function(){
                                var lrow = $(lproduct_detail).closest('tr').prev();
                                console.log(lproduct_detail);
                                rcrf_methods.product_table.subtotal_calculate($(lrow));
                            });
                            
                            var lrow_new = rcrf_methods.product_recondition_cost_table.row_generate(ltable,{});
                            $(ltr).append(lrow_new);
                        }
                        
                    });
                    
                    
                }
                //end of bind product recondition component
                
            }
        },
        product_sparepart_cost_table:{
            get:function(itable){
                var lresult = [];
                var lrows = $(itable).find('tbody tr');
                $.each(lrows,function(lidx, lrow){
                    var lproduct_reference_type = $(lrow).find('[col_name="reference_type"] div').text();
                    var lproduct_reference_id = $(lrow).find('[col_name="reference_id"] div').text();
                    var lproduct_type = $(lrow).find('[col_name="product_type"] div').text();
                    var lproduct_id = $(lrow).find('[col_name="product_id"] div').text();
                    var lunit_id = $(lrow).find('[col_name="unit_id"] div').text();
                    var lqty = APP_CONVERTER._float($(lrow).find('[col_name="qty"] div').text());
                    var lamount = APP_CONVERTER._float('0');
                    
                    var lvalid = true;
                    
                    if($(lrow).find('[col_name="amount"] div').length>0){
                        lamount = APP_CONVERTER._float($(lrow).find('[col_name="amount"] div')[0].innerHTML);
                    }
                    else{
                        lamount = APP_CONVERTER._float($(lrow).find('[col_name="amount"] input').val());
                    }
                    
                    if(lvalid){
                        lresult.push({
                            reference_type:lproduct_reference_type,
                            reference_id:lproduct_reference_id,
                            product_type:lproduct_type,
                            product_id:lproduct_id,
                            unit_id:lunit_id,
                            qty:lqty,
                            amount:lamount,
                        });
                    }
                    
                });
                return lresult;
            },
            reset:function(itable){
                $(itable).append($('<thead><tr></tr></thead>'));
                $(itable).append($('<tbody></tbody>'));
                $(itable).find('thead tr').append('<th col_name="row_num" class="table-row-num">#</th>');
                $(itable).find('thead tr').append('<th col_name="product">Product</th>');
                $(itable).find('thead tr').append('<th col_name="unit" style="width:75px">Unit</th>');
                $(itable).find('thead tr').append('<th col_name="qty" style="width:75px;text-align:right">Qty</th>');
                $(itable).find('thead tr').append('<th col_name="amount" style="width:250px;text-align:right">Amount</th>');
                $(itable).find('thead tr').append('<th col_name="action" class="table-action"></th>');
                
            },
            load:function(itable,iproduct_arr){
                var lparent_pane = rcrf_parent_pane;
                var lprefix_id = rcrf_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
                var ltbody = $(itable).find('tbody')[0];
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                $.each(iproduct_arr, function(lidx, lproduct){
                    var lrow = document.createElement('tr');
                    //data adjustment
                    if(lmethod === 'add'){
                        lproduct.qty = lproduct.sent_qty;
                    }
                    else{
                        
                    }
                    //end of data adjustment
                    
                    //draw product additional row
                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:($(ltbody).children().length)+1,type:'text'});
                    if(lmethod === 'add'){
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'reference_type',style:'',val:lproduct.reference_type,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'reference_id',style:'',val:lproduct.reference_id,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_type',style:'',val:lproduct.product_type,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_id',style:'',val:lproduct.product_id,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'',val:lproduct.product_text,type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit_id',style:'',val:lproduct.unit_id,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit',style:'',val:lproduct.unit_text,type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'qty',col_style:'text-align:right',val:APP_CONVERTER.thousand_separator(lproduct.qty),type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'amount',style:'text-align:right',col_style:'text-align:right',val:APP_CONVERTER.thousand_separator(lproduct.amount),type:'div'});
                    }
                    else {
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'reference_type',style:'',val:lproduct.reference_type,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'reference_id',style:'',val:lproduct.reference_id,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_type',style:'',val:lproduct.product_type,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_id',style:'',val:lproduct.product_id,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',col_style:'',val:lproduct.product_text,type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit_id',style:'',val:lproduct.unit_id,type:'div',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'unit',style:'',val:lproduct.unit_text,type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'qty',col_style:'text-align:right',val:APP_CONVERTER.thousand_separator(lproduct.qty),type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'amount',style:'text-align:right',col_style:'text-align:right',val:APP_CONVERTER.thousand_separator(lproduct.amount),type:'div'});

                    }
                    
                    $(ltbody).append(lrow);
                    
                    //bind product recondition component
                    if(lmethod === 'add'){
                        var lamount_inpt = $(lrow).find('[col_name="amount"] input');
                        APP_COMPONENT.input.numeric(lamount_inpt,{min_val:0});

                        $(lrow).find('[col_name="amount"] input').on('change',function(){
                            rcrf_methods.product_table.subtotal_calculate($(this).closest('[col_name="product_detail"]').closest('tr').prev());
                        });

                    }
                    //end of bind product recondition component
                    
                });
                
                
            }
        },
    }

    var rcrf_bind_event = function(){
        var parent_pane = rcrf_parent_pane;
        var lprefix_id = rcrf_component_prefix_id;
        
        $(parent_pane).find(lprefix_id+'_submit').off('click');
        APP_COMPONENT.button.submit.set($(parent_pane).find(lprefix_id+'_submit'),{
            parent_pane:parent_pane,
            module_method:rcrf_methods
        });
    }
    
    var rcrf_components_prepare= function(){
        
        var method = $(rcrf_parent_pane).find("#rcrf_method").val();
        
        
        var rcrf_data_set = function(){
            var lparent_pane = rcrf_parent_pane;
            var lprefix_id = rcrf_component_prefix_id;
            switch(method){
                case "add":
                    rcrf_methods.reset_all();      
                    break;
                case "view":
                    var rcrf_id = $(rcrf_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:rcrf_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(rcrf_data_support_url+"refill_checking_result_form_get",json_data).response;
                    
                    if(lresponse != []){
                        var lrcrf = lresponse.rcrf;
                        $(lparent_pane).find(lprefix_id+'_store')
                            .select2('data',{id:lrcrf.store_id
                                ,text:lrcrf.store_text}).change();
                        $(lparent_pane).find(lprefix_id+"_code").val(lrcrf.code);
                        $(lparent_pane).find(lprefix_id+'_refill_checking_result_form_date').datetimepicker({value:lrcrf.refill_checking_result_form_date});
                        $(lparent_pane).find(lprefix_id+"_notes").val(lrcrf.notes);
                        $(lparent_pane).find(lprefix_id+"_checker").val(lrcrf.checker);
                        $(lparent_pane).find(lprefix_id+'_refill_checking_result_form_status')
                            .select2('data',{id:lrcrf.refill_checking_result_form_status
                                ,text:lrcrf.refill_checking_result_form_status_text}).change();
                        $(lparent_pane).find(lprefix_id+'_refill_checking_result_form_status')
                            .select2({data:lresponse.refill_checking_result_form_status_list});
                        $(lparent_pane).find(lprefix_id+'_refill_checking_result_form_cancellation_reason').val(lrcrf.cancellation_reason);
                        rcrf_methods.product_table.load(lresponse.rcrf_product);
                        
                    };
                    
                    
                    
                    break;            
            }
        }
    
        rcrf_methods.enable_disable();
        rcrf_methods.show_hide();
        rcrf_data_set();
    }
    
</script>