<script>

    var delivery_order_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var delivery_order_ajax_url = null;
    var delivery_order_index_url = null;
    var delivery_order_view_url = null;
    var delivery_order_window_scroll = null;
    var delivery_order_data_support_url = null;
    var delivery_order_common_ajax_listener = null;
    var delivery_order_component_prefix_id = '';
    

    var delivery_order_init = function(){
        var parent_pane = delivery_order_parent_pane;
        delivery_order_ajax_url = '<?php echo $ajax_url ?>';
        delivery_order_index_url = '<?php echo $index_url ?>';
        delivery_order_view_url = '<?php echo $view_url ?>';
        delivery_order_window_scroll = '<?php echo $window_scroll; ?>';
        delivery_order_data_support_url = '<?php echo $data_support_url; ?>';
        delivery_order_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        delivery_order_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        delivery_order_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var delivery_order_methods = {
        hide_all:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all');
            $.each(lc_arr, function(c_idx, c){
                $(c).closest('.form-group').attr('style','display:none');
            });
            $(lparent_pane).find('#delivery_order_rma_view_table').hide();
            $(lparent_pane).find('#delivery_order_rma_add_table').hide();
            $('#delivery_order_print').hide();
            
        },
        show_hide:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lprefix_id = delivery_order_component_prefix_id;
            var lmethod = $(lparent_pane).find('#delivery_order_method').val();
            delivery_order_methods.hide_all();
            var ldo_type = $(lparent_pane).find('#delivery_order_type').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#delivery_order_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_print').hide();
                    $(lparent_pane).find(lprefix_id+'_delivery_order_status').closest('div [class*="form-group"]').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#delivery_order_reference').closest('div [class*="form-group"]').show();                    
                    $(lparent_pane).find(lprefix_id+'_delivery_order_status').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = delivery_order_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lprefix_id = delivery_order_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();    
            delivery_order_methods.disable_all();
            var ldo_type = $(lparent_pane).find(lprefix_id+'_type').val();
            var lreference_id = $(lparent_pane).find(lprefix_id+'_reference').select2('val');
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find(lprefix_id+'_reference').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_delivery_order_date').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_reference').select2('disable');
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    break;
            }
            
            if(lreference_id !== ''){
                $(lparent_pane).find(lprefix_id+'_warehouse_from').select2('enable');
                $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('enable');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_contact_name').prop('disabled',false);
                $(lparent_pane).find(lprefix_id+'_warehouse_to_address').prop('disabled',false);
                $(lparent_pane).find(lprefix_id+'_warehouse_to_phone').prop('disabled',false);
            }
        },
        reset_all:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lprefix_id = delivery_order_component_prefix_id;
            
            var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>'+
                'store/data_support/default_store_get/');
            var ldefault_store = lresult.response;
            $(lparent_pane).find('#delivery_order_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find('#delivery_order_code').val('[AUTO GENERATE]');            
            $(lparent_pane).find('#delivery_order_warehouse_from').select2('data',null);
            $(lparent_pane).find('#delivery_order_warehouse_to').select2('data',null);
            
            $(lparent_pane).find(lprefix_id+'_delivery_order_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y H:i'),
            });
    
            APP_FORM.status.default_status_set('delivery_order',
                $(lparent_pane).find(lprefix_id+'_delivery_order_status')
            );
    
            delivery_order_methods.table.product.reset();
            
        },
        table:{
            product:{
                reset:function(){
                    var lparent_pane = delivery_order_parent_pane;
                    $(lparent_pane).find('#delivery_order_product_table tbody').empty();
                    $(lparent_pane).find('#delivery_order_product_table thead [col_type="additonal"]').remove();
                },
                get:function(){
                    var lparent_pane = delivery_order_parent_pane;
                    var lprefix_id = delivery_order_component_prefix_id;
                    var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                    var lref_type = $(lparent_pane).find(lprefix_id+'_type').val();
                    var ltbody = $(lparent_pane).find(lprefix_id+'_product_table tbody');
                    
                    var lresult = [];
                    $.each($(ltbody).find('tr'), function(lidx, lrow){
                        var lproduct_reference_type = $(lrow).find('[col_name="reference_type"]').text();
                        var lproduct_reference_id = $(lrow).find('[col_name="reference_id"]').text();
                        var lproduct_id = $(lrow).find('[col_name="product_id"]').text();
                        var lunit_id = $(lrow).find('[col_name="unit_id"]').text();
                        var lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
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
                header_generate:function(){
                    var lparent_pane = delivery_order_parent_pane;
                    var lprefix_id = delivery_order_component_prefix_id;
                    var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                    var lref_type = $(lparent_pane).find(lprefix_id+'_type').val();
                    
                    var lthead = $(lparent_pane).find('#delivery_order_product_table thead')[0];
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    
                    if(lmethod ==='add'){
                        var lth = $('<th col_name="ordered_qty" col_type="additonal" style="width:125px;text-align:right" ><?php echo Lang::get('Ordered Qty');?> </th>');
                        $(lthead).find('[col_name="unit_name"]').after(lth);
                        var lth = $('<th col_name="outstanding_qty" col_type="additonal" style="width:125px;text-align:right" ><?php echo Lang::get(array('Undelivered','Qty'),true,true,true);?> </th>');
                        $(lthead).find('[col_name="ordered_qty"]').after(lth);
                        var lth = $('<th col_name="stock_qty" col_type="additonal" style="width:125px;text-align:right" >Stock Qty</th>');
                        $(lthead).find('[col_name="outstanding_qty"]').after(lth);
                    }
                    
                },
                load:function(iproduct_arr){
                    var lparent_pane = delivery_order_parent_pane;
                    var lprefix_id = delivery_order_component_prefix_id;
                    var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                    var lref_type = $(lparent_pane).find(lprefix_id+'_type').val();
                    
                    var ltbody = $(lparent_pane).find('#delivery_order_product_table tbody')[0];
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    $.each(iproduct_arr, function (lidx, lproduct){
                        var lrow = document.createElement('tr');
                        var row_num = lidx;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'reference_type',style:'vertical-align:middle',val:lproduct.reference_type,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'reference_id',style:'vertical-align:middle',val:lproduct.reference_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_type',style:'vertical-align:middle',val:lproduct.product_type,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:lproduct.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:lproduct.product_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:lproduct.product_text,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:lproduct.unit_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:lproduct.unit_text,type:'text'});
                        
                        if(lmethod ==='add'){
                            fast_draw.col_add(lrow,{tag:'td',col_name:'ordered_qty',col_style:'vertical-align:middle;text-align:right',val:APP_CONVERTER.thousand_separator(lproduct.ordered_qty),type:'span'});
                            fast_draw.col_add(lrow,{tag:'td',col_name:'outstanding_qty',col_style:'vertical-align:middle;text-align:right;font-weight:bold',class:'text-red',val:APP_CONVERTER.thousand_separator(lproduct.outstanding_qty),type:'span'});
                            fast_draw.col_add(lrow,{tag:'td',col_name:'stock_qty',col_style:'vertical-align:middle;text-align:right',val:APP_CONVERTER.thousand_separator(lproduct.stock_qty),type:'span'});
                        }
                        if(lmethod === 'add'){
                            var lqty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:middle;',val:'<input class="form-control" style="text-align:right">',type:'text'});
                        }
                        else if (lmethod ==='view'){
                            fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:middle',val:APP_CONVERTER.thousand_separator(lproduct.qty),type:'text',col_style:"text-align:right"});
                        }
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'vertical-align:middle',val:'',type:'text',style:""});
                        ltbody.appendChild(lrow);
                        
                        if(lmethod === 'add'){
                            var lmax_qty = lproduct.stock_qty.replace(/[^0-9.]/g,'');
                            if(parseFloat(lmax_qty) > parseFloat(lproduct.movement_outstanding_qty.replace(/[^0-9.]/g,''))) 
                                lmax_qty = lproduct.movement_outstanding_qty.replace(/[^0-9.]/g,'');
                            var lqty_input = $(lrow).find('[col_name="qty"] input');
                            APP_COMPONENT.input.numeric($(lqty_input),{min_val:0,max_val:lmax_qty});
                            $(lqty_input).blur();
                        }
                    });
                }
            }
        },
        submit:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lajax_url = delivery_order_index_url;
            var lmethod = $(lparent_pane).find('#delivery_order_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.reference = {
                        reference_id:$(lparent_pane).find('#delivery_order_reference').select2('val'),
                    };
                    json_data.delivery_order = {
                        delivery_order_type:$(lparent_pane).find('#delivery_order_type').val(),
                        store_id:$(lparent_pane).find('#delivery_order_store').select2('val'),
                        delivery_order_date:$(lparent_pane).find('#delivery_order_delivery_order_date').val(),
                        notes:$(lparent_pane).find('#delivery_order_notes').val()                                            
                    };
                    json_data.warehouse_from ={
                        warehouse_id: $(lparent_pane).find('#delivery_order_warehouse_from').select2('val')
                    };
                    json_data.warehouse_to ={
                        warehouse_id: $(lparent_pane).find('#delivery_order_warehouse_to').select2('val'),
                        contact_name: $(lparent_pane).find('#delivery_order_warehouse_to_contact_name').val(),
                        address: $(lparent_pane).find('#delivery_order_warehouse_to_address').val(),
                        phone: $(lparent_pane).find('#delivery_order_warehouse_to_phone').val()
                    }; 
                    
                    json_data.delivery_order_product=delivery_order_methods.table.product.get();
                    
                    lajax_url +='delivery_order_add/';
                    break;
                case 'view':
                    json_data.delivery_order = {
                        delivery_order_status:$(lparent_pane).find('#delivery_order_delivery_order_status').select2('val'),
                        notes:$(lparent_pane).find('#delivery_order_notes').val(),
                        cancellation_reason:$(lparent_pane).find('#delivery_order_delivery_order_cancellation_reason').val()
                    };
                    var delivery_order_id = $(lparent_pane).find('#delivery_order_id').val();
                    var lajax_method = $(lparent_pane).find('#delivery_order_delivery_order_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+delivery_order_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#delivery_order_id').val(result.trans_id);
                if(delivery_order_view_url !==''){
                    var url = delivery_order_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    delivery_order_after_submit();
                }
            }
        },
        warehouse_to:{
            reset_dependency:function(){
                var lparent_pane = delivery_order_parent_pane;
                var lprefix_id = delivery_order_component_prefix_id;
                
                $(lparent_pane).find(lprefix_id+'_warehouse_to_code')
                        .text('');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_name')
                        .text('');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_type')
                        .text('');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_contact_name')
                        .val('');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_address')
                        .val('');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_phone')
                        .val('');
            }
        },
        reference:{
            reset_dependency:function(){
                var lparent_pane = delivery_order_parent_pane;
                var lprefix_id = delivery_order_component_prefix_id;
                $(lparent_pane).find(lprefix_id+'_type').val('');
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
                delivery_order_methods.warehouse_to.reset_dependency();
                delivery_order_methods.table.product.reset();
            
            },
            dependency_set:function(){
                var lparent_pane = delivery_order_parent_pane;
                var lprefix_id = delivery_order_component_prefix_id;
                var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
                
                delivery_order_methods.show_hide();
                delivery_order_methods.enable_disable();
                
        
                var ldata = $(lparent_pane).find(lprefix_id+'_reference').select2('data');
                
                $('#delivery_order_type').val(ldata.reference_type);
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(delivery_order_data_support_url+'reference_dependency_data_get/',
                    {reference_type:ldata.reference_type,reference_id:ldata.id}
                ).response;
                
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail')[0],lresponse.reference_detail,{reset:true});
                
                $(lparent_pane).find(lprefix_id+'_warehouse_to').select2({data:lresponse.warehouse_to});
                $(lparent_pane).find(lprefix_id+'_warehouse_to').select2('data',lresponse.warehouse_to[0]).change();

            }
        }
    };
    
    var delivery_order_bind_event = function(){
        var lparent_pane = delivery_order_parent_pane;
        var lprefix_id = delivery_order_component_prefix_id;
        
        <?php  ?>        
        $(lparent_pane).find(lprefix_id+'_print').off();
        $(lparent_pane).find(lprefix_id+'_print').on('click',function(){
            var ldo_id = $(lparent_pane).find(lprefix_id+'_id').val();
            modal_print.init();
            modal_print.menu.add('<?php echo Lang::get(array(array('val'=>"Delivery Order"))) ?>',delivery_order_index_url+'delivery_order_print/'+ldo_id+'/delivery_order_form');
            modal_print.show();
            
        });      
        <?php  ?>
        
        $(lparent_pane).find(lprefix_id+"_reference")
        .on('change', function(){
            
            var lparent_pane = delivery_order_parent_pane;
            var lprefix_id = delivery_order_component_prefix_id;
            
            var lmethod = $(this).find(lprefix_id+'_method').val();
            var ldo_type = '';
            
            delivery_order_methods.reference.reset_dependency();            
            
            
            if($(this).select2('val')!== ''){
                delivery_order_methods.reference.dependency_set();
            }
            
        });
        
        $(lparent_pane).find('#delivery_order_warehouse_from').on('change',function(){
            var lparent_pane =  delivery_order_parent_pane;
            var lprefix_id = delivery_order_component_prefix_id;
            
            delivery_order_methods.table.product.reset();
            
            if($(this).select2('val')!== ''){
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(delivery_order_data_support_url+'product_list_get/',{
                    reference_id:$(lparent_pane).find(lprefix_id+'_reference').select2('val'),
                    reference_type:$(lparent_pane).find(lprefix_id+'_type').val(),
                    warehouse_id:$(lparent_pane).find(lprefix_id+'_warehouse_from').select2('val'),
                }).response;

                delivery_order_methods.table.product.reset();
                delivery_order_methods.table.product.header_generate();
                delivery_order_methods.table.product.load(lresponse);
            }
        });
        
        $(lparent_pane).find('#delivery_order_warehouse_to').on('change',function(){
            var lparent_pane =  delivery_order_parent_pane;
            var lprefix_id = delivery_order_component_prefix_id;
            
            if($(this).select2('val')!==''){
                
                var ldata = $(this).select2('data');
                $(lparent_pane).find(lprefix_id+'_warehouse_to_code')
                        .text(ldata.code);
                $(lparent_pane).find(lprefix_id+'_warehouse_to_name')
                        .text(ldata.name);
                $(lparent_pane).find(lprefix_id+'_warehouse_to_type')[0]
                        .innerHTML=ldata.warehouse_type_text;
                $(lparent_pane).find(lprefix_id+'_warehouse_to_contact_name')
                        .val(ldata.contact_name);
                $(lparent_pane).find(lprefix_id+'_warehouse_to_address')
                        .val(ldata.address);
                $(lparent_pane).find(lprefix_id+'_warehouse_to_phone')
                        .val(ldata.phone);
            }
        });
        
        
        $(lparent_pane).find('#delivery_order_submit').off();        
        $(lparent_pane).find('#delivery_order_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = delivery_order_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                delivery_order_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
                
            
            $(delivery_order_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    var delivery_order_components_prepare = function(){
        

        var delivery_order_data_set = function(){
            var lparent_pane = delivery_order_parent_pane;
            var lprefix_id = delivery_order_component_prefix_id;
            var lmethod = $(lparent_pane).find('#delivery_order_method').val();
            
            switch(lmethod){
                case 'add':
                    delivery_order_methods.reset_all();
                    break;
                case 'view':
                    var ldelivery_order_id = $(lparent_pane).find('#delivery_order_id').val();                    
                    var lajax_url = delivery_order_data_support_url+'delivery_order_get';
                    var json_data = {data:ldelivery_order_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var ldelivery_order = lresponse.delivery_order;
                    var lproduct = lresponse.product;
                    
                    $(lparent_pane).find('#delivery_order_reference').select2(
                        'data',lresponse.reference);                    
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#delivery_order_reference_detail')[0],lresponse.reference_detail,{reset:true});
                                       
                    $(lparent_pane).find('#delivery_order_store').select2('data',{id:ldelivery_order.store_id
                        ,text:ldelivery_order.store_text});
                    $(lparent_pane).find('#delivery_order_type').val(ldelivery_order.delivery_order_type);
                    $(lparent_pane).find('#delivery_order_code').val(ldelivery_order.code);
                    $(lparent_pane).find('#delivery_order_warehouse_from').
                        select2('data',{id:ldelivery_order.warehouse_from_id,
                            text:ldelivery_order.warehouse_from_text}
                        );
                    $(lparent_pane).find('#delivery_order_warehouse_to')
                        .select2('data',{id:ldelivery_order.warehouse_to_id,
                            text:ldelivery_order.warehouse_to_text}
                        );
                
                    $(lparent_pane).find('#delivery_order_warehouse_to_code')
                            .text(ldelivery_order.warehouse_to_code);
                    $(lparent_pane).find('#delivery_order_warehouse_to_name')
                            .text(ldelivery_order.warehouse_to_name);
                    $(lparent_pane).find('#delivery_order_warehouse_to_type')
                            .text(ldelivery_order.warehouse_to_type_name);
                    $(lparent_pane).find('#delivery_order_warehouse_to_contact_name')
                            .val(ldelivery_order.warehouse_to_contact_name);
                    $(lparent_pane).find('#delivery_order_warehouse_to_address')
                            .val(ldelivery_order.warehouse_to_address);
                    $(lparent_pane).find('#delivery_order_warehouse_to_phone')
                            .val(ldelivery_order.warehouse_to_phone);
                    
                    $(lparent_pane).find('#delivery_order_delivery_order_date').datetimepicker({value:ldelivery_order.delivery_order_date});
                    $(lparent_pane).find('#delivery_order_notes').val(ldelivery_order.notes);
                    $(lparent_pane).find('#delivery_order_delivery_order_cancellation_reason').val(ldelivery_order.cancellation_reason);
                    
                    
                    $(lparent_pane).find('#delivery_order_delivery_order_status')
                            .select2('data',{id:ldelivery_order.delivery_order_status
                                ,text:ldelivery_order.delivery_order_status_text}).change();
                    
                    $(lparent_pane).find('#delivery_order_delivery_order_status')
                            .select2({data:lresponse.delivery_order_status_list});
                    
                    delivery_order_methods.table.product.reset();
                    delivery_order_methods.table.product.load(lproduct);
                    
                    switch(ldelivery_order.delivery_order_type){
                        case'rma':
                            delivery_order_rma_methods.rma_info_set();
                            delivery_order_rma_methods.load_product_table();
                            break;
                    }
                    $(lparent_pane).find(lprefix_id+'_print').show();
                    
                    break;
            }
        }
        
        
        delivery_order_methods.enable_disable();
        delivery_order_methods.show_hide();
        delivery_order_data_set();
    }
    
    var delivery_order_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>