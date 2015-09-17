<script>

    var receive_product_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var receive_product_ajax_url = null;
    var receive_product_index_url = null;
    var receive_product_view_url = null;
    var receive_product_window_scroll = null;
    var receive_product_data_support_url = null;
    var receive_product_common_ajax_listener = null;
    var receive_product_component_prefix_id = '';
    
    var purchase_invoice_insert_dummy = false;
    

    var receive_product_init = function(){
        var parent_pane = receive_product_parent_pane;
        receive_product_ajax_url = '<?php echo $ajax_url ?>';
        receive_product_index_url = '<?php echo $index_url ?>';
        receive_product_view_url = '<?php echo $view_url ?>';
        receive_product_window_scroll = '<?php echo $window_scroll; ?>';
        receive_product_data_support_url = '<?php echo $data_support_url; ?>';
        receive_product_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        receive_product_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        receive_product_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var receive_product_methods = {
        hide_all:function(){
            var lparent_pane = receive_product_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all');
            $.each(lc_arr, function(c_idx, c){
                $(c).closest('.form-group').attr('style','display:none');
            });
            $(lparent_pane).find('#receive_product_rma_view_table').hide();
            $(lparent_pane).find('#receive_product_rma_add_table').hide();
            $('#receive_product_print').hide();
            
        },
        show_hide:function(){
            var lparent_pane = receive_product_parent_pane;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();
            receive_product_methods.hide_all();
            var ldo_type = $(lparent_pane).find('#receive_product_type').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#receive_product_reference').closest('div [class*="form-group"]').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#receive_product_reference').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = receive_product_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = receive_product_parent_pane;
            var lprefix_id = receive_product_component_prefix_id;
            var lmethod = $(lparent_pane).find('#receive_product_method').val(); 
            var lprefix_id = receive_product_component_prefix_id;
            receive_product_methods.disable_all();
            var ldo_type = $(lparent_pane).find('#receive_product_type').val();
            var lreference_id = $(lparent_pane).find('#receive_product_reference').select2('val');
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#receive_product_reference').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_store').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_receive_product_date').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#receive_product_reference').select2('disable');
                    $(lparent_pane).find('#receive_product_notes').prop('disabled',false);
                    break;
            }
            
            if(lreference_id !== ''){

                
            }
        },
        reset_all:function(){
            var lparent_pane = receive_product_parent_pane;
            var lprefix_id = receive_product_component_prefix_id;
            $(lparent_pane).find('#receive_product_code').val('[AUTO GENERATE]');
            
            var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>'+
                'store/data_support/default_store_get/');
            var ldefault_store = lresult.response;
            $(lparent_pane).find('#receive_product_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'receive_product'}).response;

            $(lparent_pane).find(lprefix_id+'_receive_product_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lreceive_product_status_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            
            $(lparent_pane).find(lprefix_id+'_receive_product_status').
                select2({data:lreceive_product_status_list});
            
            $(lparent_pane).find(lprefix_id+'_receive_product_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y H:i'),
                minDate:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y'),
                minTime:APP_GENERATOR.CURR_DATETIME('minute',10,'H:i'),
            });
            
            receive_product_methods.reference.reset_dependency();
            receive_product_methods.product_table.load([]);
        },
        reference:{
            reset_dependency:function(){
                var lparent_pane = receive_product_parent_pane;
                var lprefix_id = receive_product_component_prefix_id;
                $(lparent_pane).find(lprefix_id+'_warehouse_from').select2('disable');
                $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('disable');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_contact_name').prop('disabled',true);
                $(lparent_pane).find(lprefix_id+'_warehouse_to_address').prop('disabled',true);
                $(lparent_pane).find(lprefix_id+'_warehouse_to_phone').prop('disabled',true);
                $(lparent_pane).find(lprefix_id+'_reference_detail').find('.extra_info').remove();
                $(lparent_pane).find(lprefix_id+'_warehouse_from').select2('data',null);
                $(lparent_pane).find(lprefix_id+'_warehouse_from_detail').find('.extra_info').remove();
                $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('data',null);
                $(lparent_pane).find(lprefix_id+'_warehouse_to').select2({data:[]});
                $(lparent_pane).find(lprefix_id+'_warehouse_to_detail').find('.extra_info').remove();
                receive_product_methods.product_table.reset();
                receive_product_methods.product_table.header_render();
                
            },
            dependency_set:function(){
                var lparent_pane = receive_product_parent_pane;
                var lprefix_id = receive_product_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                
                receive_product_methods.show_hide();
                receive_product_methods.enable_disable();
        
                var ldata = $(lparent_pane).find(lprefix_id+'_reference').select2('data');
                
                $('#receive_product_type').val(ldata.reference_type);
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(receive_product_data_support_url+'reference_dependency_data_get/',
                    {reference_type:ldata.reference_type,reference_id:ldata.id}
                ).response;
                
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail')[0],lresponse.reference_detail,{reset:true});
                
                $(lparent_pane).find(lprefix_id+'_warehouse_from').select2({data:lresponse.warehouse_from});
                $(lparent_pane).find(lprefix_id+'_warehouse_from').select2('data',lresponse.warehouse_from[0]).change();
                
                $(lparent_pane).find(lprefix_id+'_warehouse_to').select2({data:lresponse.warehouse_to});
                
                receive_product_methods.product_table.load(lresponse.product);
            }
        },
        product_table:{
            reset:function(){
                var lparent_pane = receive_product_parent_pane;
                var lprefix_id = receive_product_component_prefix_id;
                $(lparent_pane).find(lprefix_id+'_product_table tbody').empty();
                $(lparent_pane).find(lprefix_id+'_product_table thead').empty();
            },
            header_render:function(){
                var lparent_pane = receive_product_parent_pane;
                var lprefix_id = receive_product_component_prefix_id;
                var lthead = $(lparent_pane).find(lprefix_id+'_product_table thead')[0];
                var lrow = document.createElement('tr');
                var fast_draw = APP_COMPONENT.table_fast_draw;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                var lreference_type = $(lparent_pane).find(lprefix_id+'_type').val();
                
                fast_draw.col_add(lrow,{tag:'th',col_name:'row_num',class:'table-row-num',col_style:'vertical-align:middle',val:'#',type:'text'});
                fast_draw.col_add(lrow,{tag:'th',col_name:'product_type',class:'',col_style:'vertical-align:middle',val:'',type:'text',visible:false});
                fast_draw.col_add(lrow,{tag:'th',col_name:'product_img',class:'product-img',col_style:'vertical-align:middle',val:'',type:'text'});
                fast_draw.col_add(lrow,{tag:'th',col_name:'product',class:'',col_style:'vertical-align:middle;min-width:100px',val:'Product',type:'text'});
                fast_draw.col_add(lrow,{tag:'th',col_name:'unit',class:'',col_style:'vertical-align:middle;width:100px',val:'Unit',type:'text'});
                if(lmethod === 'add'){
                    fast_draw.col_add(lrow,{tag:'th',col_name:'ordered_qty',class:'',col_style:'vertical-align:middle;text-align:right;width:125px',val:'<?php echo Lang::get('Ordered Qty');?>',type:'text'});
                    if(lreference_type === 'refill_subcon_work_order'){
                        fast_draw.col_add(lrow,{tag:'th',col_name:'delivered_qty',class:'',col_style:'vertical-align:middle;text-align:right;width:125px;',val:'<?php echo Lang::get(array('Delivered','Qty'),true,true,true);?>',type:'text'});
                    }
                    fast_draw.col_add(lrow,{tag:'th',col_name:'outstanding_qty',class:'',col_style:'vertical-align:middle;text-align:right;width:125px;',val:'<?php echo Lang::get(array('Unreceived','Qty'),true,true,true);?>',type:'text'});
                }
                fast_draw.col_add(lrow,{tag:'th',col_name:'qty',class:'',col_style:'vertical-align:middle;text-align:right;width:150px',val:'Qty',type:'text'});
                fast_draw.col_add(lrow,{tag:'th',col_name:'action',class:'table-action',style:'vertical-align:middle',val:'',type:'text'});
                $(lthead).append(lrow);
            },
            load:function(iproduct_arr){
                var lparent_pane = receive_product_parent_pane;
                var lprefix_id = receive_product_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                var lref_type = $(lparent_pane).find(lprefix_id+'_type').val();
                
                var fast_draw = APP_COMPONENT.table_fast_draw;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                $.each(iproduct_arr, function(lidx, lproduct){
                    var lrow = document.createElement('tr');
                    var lqty_max = 0;
                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',class:'',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'reference_type',style:'vertical-align:middle',val:lproduct.reference_type,type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'reference_id',style:'vertical-align:middle',val:lproduct.reference_id,type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_type',class:'',style:'vertical-align:middle',val:lproduct.product_type,type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',class:'product-img',style:'vertical-align:middle',val:lproduct.product_img,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',class:'',col_style:'vertical-align:middle;display:none',val:lproduct.product_id,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product',class:'',style:'vertical-align:middle',val:lproduct.product_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'unit',class:'',style:'vertical-align:middle',val:lproduct.unit_code,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',class:'',col_style:'vertical-align:middle;display:none',val:lproduct.unit_id,type:'text'});
                    
                    if(lmethod === 'add'){
                        fast_draw.col_add(lrow,{tag:'td',col_name:'ordered_qty',class:'',col_style:'vertical-align:middle;text-align:right',val:APP_CONVERTER.thousand_separator(lproduct.ordered_qty),type:'span'});
                        if(lref_type === 'refill_subcon_work_order'){
                            fast_draw.col_add(lrow,{tag:'td',col_name:'delivered_qty',class:'',col_style:'vertical-align:middle;text-align:right',val:APP_CONVERTER.thousand_separator(lproduct.delivered_qty),type:'span'});
                        }
                        fast_draw.col_add(lrow,{tag:'td',col_name:'outstanding_qty',class:'text-red',col_style:'vertical-align:middle;text-align:right;font-weight:bold',val:APP_CONVERTER.thousand_separator(lproduct.outstanding_qty),type:'span'});
                    }
                    else if (lmethod === 'view'){
                        
                    }
                    
                    if(lmethod === 'add'){
                        fast_draw.col_add(lrow,{tag:'td',col_name:'qty',class:'form-control',col_style:'vertical-align:middle;text-align:right',style:'text-align:right;',val:'',type:'input'});
                    }
                    else if(lmethod === 'view'){
                        fast_draw.col_add(lrow,{tag:'td',col_name:'qty',class:'',col_style:'vertical-align:middle;text-align:right',style:'text-align:right;',val:APP_CONVERTER.thousand_separator(lproduct.qty),type:'span'});
                    }
                    fast_draw.col_add(lrow,{tag:'td',col_name:'action',class:'',style:'vertical-align:middle',val:'',type:'span'});
                    $(ltbody).append(lrow);
                    
                    if(lmethod === 'add'){
                        var lqty_input = $(lrow).find('[col_name="qty"] input');
                        APP_COMPONENT.input.numeric($(lqty_input),{min_val:0,max_val:lproduct.max_available_qty});
                        $(lqty_input).blur();
                    }
                    
                });
            },
            data_get:function(){
                var lparent_pane = receive_product_parent_pane;
                var lprefix_id = receive_product_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                var lresult = [];
                $.each($(ltbody).find('tr'),function(lidx, lrow){
                    var lproduct_reference_type = $(lrow).find('[col_name="reference_type"]').text();
                    var lproduct_reference_id = $(lrow).find('[col_name="reference_id"]').text();
                    var lqty = APP_CONVERTER._float($(lrow).find('[col_name="qty"] input').val());
                    var lproduct_id = $(lrow).find('[col_name="product_id"]').text();
                    var lunit_id = $(lrow).find('[col_name="unit_id"]').text();
                    var lproduct_type = $(lrow).find('[col_name="product_type"]').text();
                    if(parseFloat(lqty)>parseFloat('0')){
                        lresult.push({
                            reference_type: lproduct_reference_type,
                            reference_id: lproduct_reference_id,
                            product_type: lproduct_type,
                            product_id:lproduct_id,
                            unit_id:lunit_id,
                            qty:lqty
                        });
                    }
                });
                return lresult;
            },
        },
        submit:function(){
            var lparent_pane = receive_product_parent_pane;
            var lprefix_id = receive_product_component_prefix_id;
            var lajax_url = receive_product_index_url;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.reference_id = $(lparent_pane).find(lprefix_id+'_reference').select2('val');
                    json_data.receive_product = {
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        receive_product_date:$(lparent_pane).find(lprefix_id+'_receive_product_date').val(),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                        receive_product_type:$(lparent_pane).find(lprefix_id+'_type').val(),
                    };
                    json_data.warehouse_from ={
                        warehouse_id: $(lparent_pane).find(lprefix_id+'_warehouse_from').select2('val')
                    };
                    json_data.warehouse_to ={
                        warehouse_id: $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('val'),
                    }; 
                    json_data.product=[];
                    json_data.product = receive_product_methods.product_table.data_get();
                    lajax_url +='receive_product_add/';
                    break;
                case 'view':
                    json_data.receive_product = {
                        receive_product_status:$(lparent_pane).find(lprefix_id+'_receive_product_status').select2('val'),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                        cancellation_reason:$(lparent_pane).find(lprefix_id+'_receive_product_cancellation_reason').val()
                    };
                    var receive_product_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_receive_product_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+receive_product_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            
            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(receive_product_view_url !==''){
                    var url = receive_product_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    receive_product_after_submit();
                }
            }
        }
    };
    
    var receive_product_bind_event = function(){
        var lparent_pane = receive_product_parent_pane;
        var lprefix_id = receive_product_component_prefix_id;
        $(lparent_pane).find(lprefix_id+"_reference")
        .on('change', function(){            
            var lparent_pane = receive_product_parent_pane;
            
            
            var ldata = $(this).select2('data');
            var lreference_type = (ldata !== null?ldata.reference_type:'');
            $(lparent_pane).find(lprefix_id+'_type').val(lreference_type);
            
            receive_product_methods.reference.reset_dependency();            
            
            if(lreference_type!==''){
                receive_product_methods.reference.dependency_set();
            }            
        });
        
        $(lparent_pane).find('#receive_product_warehouse_from').on('change',function(){
            var lparent_pane = receive_product_parent_pane;
            var lprefix_id = receive_product_component_prefix_id;
            
            if($(this).select2('val')!==''){
                var ldata = $(this).select2('data');
                $(lparent_pane).find(lprefix_id+'_warehouse_from_detail .extra_info').remove();
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_warehouse_from_detail'),ldata.warehouse_detail);
            }
        });
        
        $(lparent_pane).find('#receive_product_warehouse_to').on('change',function(){
            var lparent_pane = receive_product_parent_pane;
            var lprefix_id = receive_product_component_prefix_id;
            if($(this).select2('val')!==''){
                var lajax_url = receive_product_data_support_url+'warehouse_to_detail_get/';
                var ljson_data = {warehouse_id:$(this).select2('val')};
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, ljson_data).response;
                var lwarehouse_detail = lresponse.warehouse_detail;
                var ldata = $(this).select2('data');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_detail .extra_info').remove();
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_warehouse_to_detail'),lwarehouse_detail);
            }
        });
        
        $(lparent_pane).find('#receive_product_submit').off();        
        $(lparent_pane).find('#receive_product_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = receive_product_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                receive_product_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
                
            
            $(receive_product_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    var receive_product_components_prepare = function(){
        

        var receive_product_data_set = function(){
            var lparent_pane = receive_product_parent_pane;
            var lprefix_id = receive_product_component_prefix_id;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();
            
            switch(lmethod){
                case 'add':
                    receive_product_methods.reset_all();
                    if(purchase_invoice_insert_dummy){
                        $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('data',{id: "1", text: "Warehouse 1"}).change();
                        $(lparent_pane).find(lprefix_id+'_reference').select2('search',' ');
                        setTimeout(function(){
                            var lref = APP_COMPONENT.input_select.dropdown_get($(lparent_pane).find(lprefix_id+'_reference')[0])[0];
                            $(lprefix_id+'_reference').select2('data',lref).change();
                            $(lprefix_id+'_reference').select2('close');
                            $(lprefix_id+'_warehouse_from').select2('data',{id: "8", text: "<strong >WS</strong> Supplier Warehouse", warehouse_detail: [{id: "type",label: "Type: ",val: "Non Business Operation Site"}]}).change();
                            var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody')[0];
                            $(ltbody).find('tr:eq(0) [col_name="qty"] input').val('25').blur();
                            $(ltbody).find('tr:eq(1) [col_name="qty"] input').val('150').blur();
                            $(ltbody).find('tr:eq(2) [col_name="qty"] input').val('10000').blur();
                            
                            
                            
                        },2000);
                    }
                    break;
                case 'view':
                    var lreceive_product_id = $(lparent_pane).find('#receive_product_id').val();                    
                    var lajax_url = receive_product_data_support_url+'receive_product_get';
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{data:lreceive_product_id});
                    var lresponse = result.response;
                    var lreceive_product = lresponse.receive_product;
                    var lreference_type = lreceive_product.receive_product_type;
                    
                    $(lparent_pane).find(lprefix_id+'_store').select2('data',{id:lreceive_product.store_id
                        ,text:lreceive_product.store_text});
                    $(lparent_pane).find(lprefix_id+'_code').val(lreceive_product.code);
                    
                    $(lparent_pane).find(lprefix_id+'_reference').select2('data',lresponse.reference);
                    $(lparent_pane).find(lprefix_id+'_reference_detail .extra_info').remove();
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail')[0],lresponse.reference_detail);
                    
                    $(lparent_pane).find(lprefix_id+'_receive_product_date').val(lreceive_product.receive_product_date);
                    
                    $(lparent_pane).find(lprefix_id+'_warehouse_from').select2('data',lresponse.warehouse_from);
                    $(lparent_pane).find(lprefix_id+'_warehouse_from_detail .extra_info').remove();
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_warehouse_from_detail')[0],lresponse.warehouse_from_detail);
                    
                    $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('data',lresponse.warehouse_to);
                    $(lparent_pane).find(lprefix_id+'_warehouse_to_detail .extra_info').remove();
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_warehouse_to_detail')[0],lresponse.warehouse_to_detail);
                    
                    $(lparent_pane).find(lprefix_id+'_notes').val(lreceive_product.notes);
                    $(lparent_pane).find(lprefix_id+'_receive_product_cancellation_reason').val(lreceive_product.cancellation_reason);
                    
                    $(lparent_pane).find('#receive_product_receive_product_status')
                            .select2('data',{id:lreceive_product.receive_product_status
                                ,text:lreceive_product.receive_product_status_text}).change();
                    
                    $(lparent_pane).find('#receive_product_receive_product_status')
                            .select2({data:lresponse.receive_product_status_list});
                    
                    receive_product_methods.product_table.reset();
                    receive_product_methods.product_table.header_render();
                    receive_product_methods.product_table.load(lresponse.receive_product_product);
                    
                    
                    break;
            }
        }
        
        
        receive_product_methods.enable_disable();
        receive_product_methods.show_hide();
        receive_product_data_set();
    }
    
    var receive_product_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>