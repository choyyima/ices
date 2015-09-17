<script>
    
    var sales_pos_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var sales_pos_ajax_url = null;
    var sales_pos_index_url = null;
    var sales_pos_view_url = null;
    var sales_pos_window_scroll = null;
    var sales_pos_data_support_url = null;
    var sales_pos_common_ajax_listener = null;
    
    var sales_pos_insert_dummy = false;
    var sales_pos_insert_dummy_delivery = true;
    
    var sales_pos_init = function(){
        var parent_pane = sales_pos_parent_pane;
        sales_pos_ajax_url = '<?php echo $ajax_url ?>';
        sales_pos_index_url = '<?php echo $index_url ?>';
        sales_pos_view_url = '<?php echo $view_url ?>';
        sales_pos_window_scroll = '<?php echo $window_scroll; ?>';
        sales_pos_data_support_url = '<?php echo $data_support_url; ?>';
        sales_pos_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        sales_pos_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var sales_pos_routing={
        set:function(method,section){
            var lparent_pane = sales_pos_parent_pane;
            $(lparent_pane).find('[routing_section="init"]').hide();
            $(lparent_pane).find('[routing_section="product"]').hide();
            $(lparent_pane).find('[routing_section="payment"]').hide();
            $(lparent_pane).find('[routing_section="movement"]').hide();
            $(lparent_pane).find('[routing_section="cd_cb"]').hide();
            if($.inArray(method,['add','view']) !== -1){
                sales_pos_methods.btn_controller_reset();                
                switch(section){
                    
                    case 'init':
                        $(lparent_pane).find('[routing_section="init"]').show();
                        sales_pos_init_section_methods.btn_controller_set();                        
                        break;
                    case 'product':
                        sales_pos_product_section_methods.reset_all();
                        $(lparent_pane).find('[routing_section="product"]').show();
                        sales_pos_product_section_methods.btn_controller_set();
                        break;
                    case 'payment':
                        sales_pos_payment_section_methods.reset_all();
                        $(lparent_pane).find('[routing_section="payment"]').show();
                        sales_pos_payment_section_methods.btn_controller_set();
                        break;
                    
                    case 'movement':
                        sales_pos_movement_section_methods.reset_all();
                        $(lparent_pane).find('[routing_section="movement"]').show();
                        sales_pos_movement_section_methods.show_hide_routing();
                        sales_pos_movement_section_methods.btn_controller_set();
                        break;
                        
                    case 'cd_cb':
                        sales_pos_cd_cb_section_methods.reset_all();
                        $(lparent_pane).find('[routing_section="cd_cb"]').show();
                        sales_pos_cd_cb_section_methods.show_hide_routing();
                        sales_pos_cd_cb_section_methods.btn_controller_set();
                        break;
                }
                
            }
        }
    }
    
    var sales_pos_methods = {
        status_label_get:function(){
            var parent_pane = sales_pos_parent_pane;
            return $($(parent_pane).find('#sales_pos_sales_pos_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#sales_pos_sales_pos_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#sales_pos_sales_pos_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lsales_pos_id = $('#sales_pos_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_pos_data_support_url+'sales_pos_current_status/',{data:lsales_pos_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = sales_pos_parent_pane;    
            $(lparent_pane).find('#sales_pos_btn_sales_pos_add').hide();
            $(lparent_pane).find('#sales_pos_btn_print').hide();
            $(lparent_pane).find('#sales_pos_new_dof').hide();
            $(lparent_pane).find('#sales_pos_new_intake_final').hide();
            $(lparent_pane).find('#sales_pos_mail').hide();
        },
        disable_all:function(){
            var lparent_pane = sales_pos_parent_pane;            
        },
        reset_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            sales_pos_summary_methods.reset_all();
            sales_pos_init_section_methods.reset_all();
            sales_pos_product_section_methods.reset_all();
            sales_pos_payment_section_methods.reset_all();
            sales_pos_movement_section_methods.reset_all();
            sales_pos_cd_cb_section_methods.reset_all();
            //sales_pos_product_section_methods.reset_all();
            
        },
        approval:{
            reset:function(){
                $(sales_pos_parent_pane).find('#sales_pos_approval').select2('data',null);
                
            }
        },
        btn_controller_reset:function(){
            var lparent_pane = sales_pos_parent_pane;
            $(lparent_pane).find('#sales_pos_btn_back').hide();
            $(lparent_pane).find('#sales_pos_btn_prev').hide();
            $(lparent_pane).find('#sales_pos_btn_prev').prop('disabled',true);            
            $(lparent_pane).find('#sales_pos_btn_prev').off();
            $(lparent_pane).find('#sales_pos_btn_prev').on('click',function(){$(this).blur();});;
            
            $(lparent_pane).find('#sales_pos_btn_next').hide();
            $(lparent_pane).find('#sales_pos_btn_next').prop('disabled',true);
            $(lparent_pane).find('#sales_pos_btn_next').off(); 
            $(lparent_pane).find('#sales_pos_btn_next').on('click',function(){$(this).blur();});;
            
            $(lparent_pane).find('#sales_pos_submit').hide();
            $(lparent_pane).find('#sales_pos_submit').prop('disabled',true);
        },
        submit:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lajax_url = sales_pos_index_url;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.sales_pos = {};
                    json_data.sales_pos.sales_inquiry_by_id=$(lparent_pane).find('#sales_pos_sales_inquiry_by').select2('val');
                    json_data.sales_pos.reference_id = $(lparent_pane).find('#sales_pos_reference_id').val();
                    json_data.sales_pos.reference_type = $(lparent_pane).find('#sales_pos_reference_type').val();
                    json_data.sales_pos.store_id = $(lparent_pane).find('#sales_pos_store').select2('val');
                    json_data.sales_pos.customer_id=$(lparent_pane).find('#sales_pos_customer').select2('val');
                    json_data.sales_pos.price_list_id=$(lparent_pane).find('#sales_pos_price_list').select2('val');
                    json_data.sales_pos.approval_id = $(lparent_pane).find('#sales_pos_approval').select2('val');
                    json_data.sales_pos.is_delivery = $(lparent_pane).find('#sales_pos_delivery_checkbox').is(':checked');
                    json_data.sales_pos.expedition_id=$(lparent_pane).find('#sales_pos_expedition').select2('val');
                    json_data.sales_pos.discount = $(lparent_pane)
                        .find('#sales_pos_product_discount').val().replace(/[,]/g,'');
                    json_data.sales_pos.extra_charge = $(lparent_pane)
                            .find('#sales_pos_product_extra_charge').text().replace(/[,]/g,'');
                    json_data.sales_pos.delivery_cost_estimation = $(lparent_pane)
                        .find('#sales_pos_delivery_cost_estimation').val().replace(/[,]/g,'');
                    json_data.additional_cost = [];
                    
                    $.each($(lparent_pane).find('[additional_cost_row]'),function(key, lrow){
                        var lamount = $(lrow).find('[col_name="additional_cost_amount"] input')
                                    .val().replace(/[^0-9.]/g,'');
                        var ldescription = $(lrow).find('[col_name="additional_cost_description"] input')
                                    .val();
                        json_data.additional_cost.push({
                            description:ldescription,
                            amount:lamount
                        })
                    });
                    
                    json_data.product = [];
                    
                    $.each($(lparent_pane).find('#sales_pos_product_table tbody>tr'),function(idx, row){
                        var lqty = '0';
                        if($(row).find('[col_name="qty"] input').length>0){
                            lqty = $(row).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                        }
                        else{
                            lqty = $(row).find('[col_name="qty"] span').text().replace(/[^0-9.]/g,'');
                        }
                        if(parseFloat(lqty)>0){
                            var ltemp_product = {
                                product_id:$(row).find('[col_name="product_id"] span').text(),
                                unit_id:$(row).find('[col_name="unit_id"] span').text(),
                                qty:lqty,
                                amount:'0'
                            };
                            if($(row).find('[col_name="amount"] input').length>0){
                                ltemp_product.amount = $(row).find('[col_name="amount"] input').val().replace(/[^0-9.]/g,'');
                            }
                            else{
                                ltemp_product.amount = $(row).find('[col_name="amount"] span').text().replace(/[^0-9.]/g,'');
                            }
                            json_data.product.push(ltemp_product);
                        }
                    });
                    
                    json_data.customer_deposit = [];
                    
                    $.each($(lparent_pane).find('#sales_pos_customer_deposit_table tbody>tr'),function(idx, row){
                        json_data.customer_deposit.push({
                            customer_deposit_id:$(row).find('[col_name="customer_deposit_id"]')
                                    .text(),
                            allocated_amount:$(row).find('[col_name="allocated_amount"] span')
                                    .text().replace(/[,]/g,''),
                        });                            
                    });
                    
                    json_data.receipt = [];
                    
                    $.each($(lparent_pane).find('#sales_pos_payment_table tbody>tr'),function(idx, row){
                        var lamount = $(row).find('[col_name="amount"] input').val().replace(/[,]/g,'');
                        if(parseFloat(lamount)>0){
                            json_data.receipt.push({
                                payment_type_id:$(row).find('[col_name="payment_type"] input[original]')
                                        .select2('val'),
                                customer_bank_acc:$(row).find('[col_name="customer_bank_acc"] input')
                                        .val(),
                                bos_bank_account_id:$(row).find('[col_name="bos_bank_account_id"] div')[0].innerHTML,
                                amount:$(row).find('[col_name="amount"] input')
                                        .val().replace(/[,]/g,''),
                                allocated_amount:$(row).find('[col_name="allocated_amount"] input')
                                        .val().replace(/[,]/g,''),
                            });
                            
                        }
                    });
                    
                    json_data.receipt_change = $(lparent_pane).find('#sales_pos_summary_change_amount').text().replace(/[^0-9.]/g,'');
                    
                    json_data.final_movement = [];
                    
                    var final_movement_get = function(module){
                        $.each($(lparent_pane).find('#sales_pos_movement_'+module+'_table tbody>tr'),function(idx, row){
                            var lmovement = JSON.parse($(row).find('[col_name="movement_data"]').text(),null,null);
                            json_data.final_movement.push({
                                final_movement_date:$(row).find('[col_name="'+module+'_date"] input').val(),
                                movement:lmovement
                            });
                        });
                    }
                    
                    final_movement_get(sales_pos_movement_props.module);
                    
                    lajax_url +='sales_pos_add';
                    break;
                case 'view':
                    break;
                    
            }
            
            var result = null;
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#sales_pos_id').val(result.trans_id);
                if(sales_pos_view_url !==''){
                    var url = sales_pos_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    sales_pos_after_submit();
                }
            }

        
        }
        
    };
    
    var sales_pos_bind_event = function(){
        var lparent_pane = sales_pos_parent_pane;
        $(lparent_pane).find('#sales_pos_btn_print').off();
        $(lparent_pane).find('#sales_pos_btn_print').on('click',function(){
            var lpos_id = $(lparent_pane).find('#sales_pos_id').val();
            var lis_delivery = $(lparent_pane).find('#sales_pos_delivery_checkbox').prop('checked');
            var lmodule = lis_delivery? 'delivery':'intake';
            modal_print.init();
            modal_print.menu.add('Invoice',sales_pos_index_url+'sales_pos_print/'+lpos_id+'/invoice');
            modal_print.menu.add('Payment',sales_pos_index_url+'sales_pos_print/'+lpos_id+'/payment');

            var ltd_arr = $(lparent_pane).find('#sales_pos_movement_'+lmodule+'_table td[col_name="code"]');
            
            $.each(ltd_arr,function(lidx, ltd){
                var lmodule_name = lis_delivery?'delivery_order':'intake';
                var lf_movement_id =  $(ltd).closest('tr').find('td[col_name="id"]').text();
                modal_print.menu.add($(ltd).find('a strong').text(),
                    sales_pos_index_url+'sales_pos_print/'+lpos_id+'/movement/'+lf_movement_id);
            });
                
            modal_print.show();
            
        });
        
        APP_COMPONENT.button.mail.set(
            $(lparent_pane).find('#sales_pos_mail'),
            {
                mail_to_get:function(){return $('#sales_pos_customer_detail_email').text()},
                subject:'<?php echo Lang::get('Performa Invoice'); ?>',
                message:<?php echo json_encode($mail_message); ?>,
                ajax_url:sales_pos_index_url+'sales_pos_mail/sales_pos',
                json_data_get:function(){
                    return {
                        sales_pos_id:$('#sales_pos_id').val(),                
                        mail_to:$('#modal_mail_mail_to').val(),
                        subject:$('#modal_mail_subject').val(),
                        message:$('#modal_mail_message').val(),
                    }
                },
            }
        );
        
        $(lparent_pane).find('#sales_pos_submit').on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');            
            var lparent_pane = sales_pos_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                sales_pos_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });


            $(sales_pos_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $(function () {
            sales_pos_relocate = function(){
                APP_COMPONENT.sticky_relocate($('#sales_pos_div_right')[0],'25%',110,15,15);
            }
            
            $(window).scroll(sales_pos_relocate);
            $('.navbar-btn').on('click',sales_pos_relocate);
            
        });
        

        sales_pos_init_section_bind_events();
        sales_pos_product_section_bind_events();
        sales_pos_movement_section_bind_events();
        
    }
    
    var sales_pos_components_prepare = function(){        

        var sales_pos_data_set = function(){
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            sales_pos_methods.reset_all();
            switch(lmethod){
                case 'add':
                    var my_timer = function(){
                        $('#sales_pos_time').text((new Date()).format('H:i:s'));
                    };
                    window.setInterval(my_timer,1000);
                    
                    sales_pos_routing.set('add','init');
                    var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>'+'store/data_support/default_store_get/');
                    var ldefault_store = lresult.response;
                    $(lparent_pane).find('#sales_pos_store').select2('data',
                        {id:ldefault_store.id,text:ldefault_store.name}
                    );
                    
                    $(lparent_pane).find('#sales_pos_customer').select2('data',null).change();
                    
                    ldefault_status = APP_DATA_TRANSFER.ajaxPOST(sales_pos_data_support_url+'default_status_get/');
                    $(lparent_pane).find('#sales_pos_sales_pos_status')
                            .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
                    var lsales_pos_status_list = [
                        {id:ldefault_status.val,text:ldefault_status.label}//,
                    ]
                    $(lparent_pane).find('#sales_pos_sales_pos_status').select2({data:lsales_pos_status_list});
                    
                    lsales_inquiry_by = APP_DATA_TRANSFER.ajaxPOST(sales_pos_data_support_url+'sales_inquiry_by_get/').response;
                    var lsales_inquiry_by_list = []
                    $.each(lsales_inquiry_by, function(lidx, lrow){
                        if(lrow.text ==='VISIT'){
                            $(lparent_pane).find('#sales_pos_sales_inquiry_by')
                            .select2('data',{id:lrow.id,text:lrow.text}).change();
                        }
                        lsales_inquiry_by_list.push({id:lrow.id,text:lrow.text});
                    });
                    $(lparent_pane).find('#sales_pos_sales_inquiry_by').select2({data:lsales_inquiry_by_list});
                    
                    
                    break;
                case 'edit':
                case 'view':
                    var lsales_pos_id = $(lparent_pane).find('#sales_pos_id').val();
                    var lajax_url = sales_pos_data_support_url+'sales_pos_get';
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{sales_pos_id:lsales_pos_id});
                    
                    var lresponse = result.response;
                    var lcustomer = lresponse.customer;
                    var lsales_pos = lresponse.sales_pos;
                    var lsales_pos_info = lresponse.sales_pos_info;
                                        
                    if(lsales_pos_info.reference_type !== null){
                        $(lparent_pane).find('#sales_pos_reference_id').select2('data',{id:lsales_pos_info.reference_id,text:lsales_pos_info.reference_text});
                        APP_COMPONENT.reference_detail.extra_info_set(
                            $(lparent_pane).find('#sales_pos_reference_id_detail'),
                            lresponse.reference_detail,
                            {reset:true}
                        );
                        $(lparent_pane).find('#sales_pos_reference_id').closest('.form-group').show();
                    }
                    
                    $(lparent_pane).find('#sales_pos_store').select2('data',{
                        id:lsales_pos.store_id,
                        text:lsales_pos.store_text
                    });
                    
                    $(lparent_pane).find('#sales_pos_code').val(lsales_pos.code);
                    $(lparent_pane).find('#sales_pos_sales_pos_status').select2(
                       'data',{
                            id:lsales_pos.sales_invoice_status,
                            text:lsales_pos.sales_invoice_status_name
                        }
                    );
                    
                    $(lparent_pane).find('#sales_pos_sales_inquiry_by').select2('data',{
                        id:lsales_pos_info.sales_inquiry_by_id,
                        text:lsales_pos_info.sales_inquiry_by_text
                    });
                    
                    $(lparent_pane).find('#sales_pos_customer').select2('data',{
                        id:lcustomer.id,
                        text:lcustomer.customer_text
                    });
                    $(lparent_pane).find('#sales_pos_customer_detail_code')[0].innerHTML = '<a href = "<?php echo get_instance()->config->base_url().'customer/view/'; ?>'+lcustomer.id+'" target="_blank"><strong>'+lcustomer.code+'</strong></a>';
                    $(lparent_pane).find('#sales_pos_customer_detail_name').text(lcustomer.name);
                    $(lparent_pane).find('#sales_pos_customer_detail_phone').text(lcustomer.phone);
                    $(lparent_pane).find('#sales_pos_customer_detail_bb_pin').text(lcustomer.bb_pin);
                    $(lparent_pane).find('#sales_pos_customer_detail_email').text(lcustomer.email);
                    $(lparent_pane).find('#sales_pos_customer_detail_is_sales_receipt_outstanding')
                            .text(lcustomer.is_sales_receipt_outstanding);
                    
                    var lprice_list = lresponse.price_list;
                    $(lparent_pane).find('#sales_pos_price_list').select2('data',{
                        id:lprice_list.id,
                        text:lprice_list.price_list_text
                    });
                    
                    $(lparent_pane).find('#sales_pos_approval').select2('data',{
                        id:lsales_pos_info.approval_id,
                        text:lsales_pos_info.approval_text
                    });

                    $(lparent_pane).find('#sales_pos_expedition').select2('data',{
                        id:lsales_pos_info.expedition_id,
                        text:lsales_pos_info.expedition_text
                    });
                    
                    if(lresponse.is_delivery === '1'){
                        $(lparent_pane).find('#sales_pos_delivery_checkbox').iCheck('check');
                    }
                    else{
                        $(lparent_pane).find('#sales_pos_delivery_checkbox').iCheck('uncheck');
                    }
                    
                    $(lparent_pane).find('[col_name="total_stock"]').hide();
                    $(lparent_pane).find('[col_name="movement_outstanding_qty"]').show();
                    var ltbody = $(lparent_pane).find('#sales_pos_product_table tbody')[0];
                    $(ltbody).empty();
                    
                    $.each(lresponse.product,function(idx, lproduct){
                        fast_draw = APP_COMPONENT.table_fast_draw;
                        var lrow = document.createElement('tr');  
                        var row_num = $(lparent_pane).find('#sales_pos_product_table').find('tbody').children().length;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:lproduct.product_img,type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:lproduct.product_id,type:'span',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:lproduct.product_text,type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'total_stock',col_style:'vertical-align:middle;text-align:right;display:none',val:'',type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'mult_qty',col_style:'vertical-align:middle;text-align:right',val:lproduct.mult_qty,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'qty',col_style:'vertical-align:middle;text-align:right',val:lproduct.qty,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'movement_outstanding_qty',col_style:'vertical-align:middle;text-align:right;',val:lproduct.movement_outstanding_qty,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:lproduct.unit_id,type:'span',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:lproduct.unit_name,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'expedition_weight_qty',col_style:'vertical-align:middle;text-align:right',val:lproduct.expedition_weight,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'vertical-align:middle;text-align:right',val:lproduct.amount,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'subtotal',col_style:'vertical-align:middle;text-align:right',val:lproduct.subtotal,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'vertical-align:middle',val:'',type:'span'});
                        
                        ltbody.appendChild(lrow);
                        
                        
                        var lm_outstanding_qty = $(lrow).find('[col_name="movement_outstanding_qty"] span').text().replace(/[^0-9]/g,'');
                        
                        if(parseFloat(lm_outstanding_qty)>parseFloat('0')){
                            $(lrow).find('[col_name="movement_outstanding_qty"]').css('color','red');
                        }
                    });
                    
                    
                    $(lparent_pane).find('#sales_pos_expedition_weight_total').text(lresponse.weight_total);
                    
                    $(lparent_pane).find('#sales_pos_product_discount').val(lsales_pos.discount);                    
                    if(parseFloat(lsales_pos.discount.replace(/[^0-9.]/g,'')) === 0){
                        $(lparent_pane).find('#sales_pos_product_discount').closest('tr').hide();
                    }
                    
                    $(lparent_pane).find('#sales_pos_cancellation_reason').val(lsales_pos.cancellation_reason);
                    if(lsales_pos.sales_pos_status === 'X'){
                        $(lparent_pane).find('#sales_pos_cancellation_reason').closest('.form-group').show();                    
                    }
                    
                    $(lparent_pane).find('#sales_pos_delivery_cost_estimation_text').text(lsales_pos.delivery_cost_estimation);
                    $(lparent_pane).find('#sales_pos_product_extra_charge').text(lsales_pos.extra_charge);
                    $(lparent_pane).find('#sales_pos_product_total').text(lsales_pos.total_product);
                    $(lparent_pane).find('#sales_pos_product_grand_total').text(lsales_pos.grand_total);
                    
                    

                    ladditional_costs = lresponse.additional_cost;
                    var lgrand_total = $(sales_pos_parent_pane).find('#sales_pos_product_grand_total').closest('tr')[0];
                    $.each(ladditional_costs,function(idx,ladditional_cost){
                        fast_draw = APP_COMPONENT.table_fast_draw;
                        var lrow = document.createElement('tr'); 
                        $(lrow).attr('additional_cost_row','');
                        fast_draw.col_add(lrow,{tag:'td',attr:{colspan:'7'},class:'',col_name:'',style:'',val:'',type:'text'});                
                        fast_draw.col_add(lrow,{tag:'td',attr:{colspan:'2'},class:'',col_name:'additional_cost_description',col_style:'vertical-align:middle;text-align:right',val:'<strong>'+ladditional_cost.description+'</strong>',type:'text'});                
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'additional_cost_amount',col_style:'vertical-align:middle;text-align:right',val:'<strong>'+ladditional_cost.amount+'</strong>',type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'',style:'',val:'',type:'text'});                
                        $(lgrand_total).before(lrow);
                        
                    });
                    
                    $(lparent_pane).find('#sales_pos_modal_extra_charge .modal-body')[0].innerHTML = lresponse.extra_charge_msg;
                    $(lparent_pane).find('#sales_pos_datetime').text(lsales_pos.sales_pos_date);
                    $(lparent_pane).find('#sales_pos_summary_code').text(lsales_pos.code);
                    $(lparent_pane).find('#sales_pos_summary_customer').text(lcustomer.name);
                    $(lparent_pane).find('#sales_pos_summary_price_list').text(lprice_list.price_list_text);
                    $(lparent_pane).find('#sales_pos_summary_product_grand_total').text(lsales_pos.grand_total);
                    
                    sales_pos_payment_props.reset_all = false;
                    var lpayment_total = parseFloat('0.00');
                    lcustomer_deposit = lresponse.customer_deposit;
                    var ltbody = $(sales_pos_parent_pane).find('#sales_pos_customer_deposit_table tbody')[0];
                    sales_pos_payment_section_methods.table.customer_deposit.empty();                    
                    var lallocated_amount_total = parseFloat('0');
                    $.each(lcustomer_deposit, function(lidx, customer_deposit){
                        fast_draw = APP_COMPONENT.table_fast_draw;
                        var lrow = document.createElement('tr'); 
                        var row_num = $(lparent_pane).find('#sales_pos_customer_deposit_table').find('tbody').children().length;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_deposit_id',col_style:'vertical-align:middle;display:none',val:customer_deposit.id,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_deposit_code',style:'vertical-align:middle;',val:customer_deposit.code,type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_deposit_date',style:'vertical-align:middle',val:'<span>'+customer_deposit.customer_deposit_date+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'vertical-align:middle;text-align:right',val:'<span>'+customer_deposit.amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'allocated_amount',col_style:'vertical-align:middle;text-align:right',val:'<span>'+customer_deposit.allocated_amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'',val:'',type:'text'});
                        ltbody.appendChild(lrow);
                        lallocated_amount_total+=parseFloat(customer_deposit.allocated_amount.replace(/[^0-9.]/g,''));
                    });
                    $(lparent_pane).find('#sales_pos_customer_deposit_allocated_amount_total')
                            .text(APP_CONVERTER.thousand_separator(lallocated_amount_total));
                    lpayment_total += lallocated_amount_total;
                    
                    lpayment = lresponse.payment;
                    var ltbody = $(sales_pos_parent_pane).find('#sales_pos_payment_table tbody')[0];
                    sales_pos_payment_section_methods.table.payment.empty();                    
                    var lallocated_amount_total = parseFloat('0');
                    var lamount_total = parseFloat('0');
                    $.each(lpayment, function(lidx, payment){
                        fast_draw = APP_COMPONENT.table_fast_draw;
                        var lrow = document.createElement('tr');  
                        var row_num = $(sales_pos_parent_pane).find('#sales_pos_payment_table').find('tbody').children().length;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'code',style:'vertical-align:middle',val:'<a href="<?php echo get_instance()->config->base_url().'sales_receipt/view/'; ?>'+payment.id+'" target="_blank"><strong>'+payment.code+'</strong></a>',type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'payment_type',style:'vertical-align:middle',val:'<span>'+payment.payment_type_code+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'receipt_date',style:'vertical-align:middle',val:'<span>'+payment.receipt_date+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'customer_bank_acc',style:'vertical-align:middle;text-align:left',val:'<span>'+APP_CONVERTER._str(payment.customer_bank_acc)+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'bos_bank_account',style:'vertical-align:middle;text-align:left',val:'<span>'+APP_CONVERTER._str(payment.bos_bank_account_text)+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'amount',col_style:'vertical-align:middle;text-align:right;',val:'<span>'+payment.amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'allocated_amount',col_style:'vertical-align:middle;text-align:right',val:'<span>'+payment.allocated_amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'',val:'',type:'text'});
                        ltbody.appendChild(lrow);
                        lallocated_amount_total+=parseFloat(payment.allocated_amount.replace(/[^0-9.]/g,''));
                        lamount_total+=parseFloat(payment.amount.replace(/[^0-9.]/g,''));
                    });
                    $(lparent_pane).find('#sales_pos_payment_total')
                            .text(APP_CONVERTER.thousand_separator(lamount_total));
                    
                    
                    $(lparent_pane).find('#sales_pos_payment_allocated_amount_total')
                            .text(APP_CONVERTER.thousand_separator(lallocated_amount_total));
                    lpayment_total += lallocated_amount_total;
                    
                    $(lparent_pane).find('#sales_pos_summary_change_amount').text(lresponse.change);
                    lpayment_total += parseFloat(lresponse.change.replace(/[^0-9.]/g,''));
                    
                    $(lparent_pane).find('#sales_pos_summary_payment_grand_total').text(
                        APP_CONVERTER.thousand_separator(lpayment_total,5)
                    );
                    
                    var loutstanding_amount = parseFloat(lsales_pos.outstanding_amount.toString().replace(/[^0-9.]/g,''));
                    if(loutstanding_amount < parseFloat('0')) loutstanding_amount = APP_CONVERTER.thousand_separator('0');
                    $(lparent_pane).find('#sales_pos_summary_outstanding_amount').text(
                        APP_CONVERTER.thousand_separator(loutstanding_amount,5)
                    );
                    
                    
                    var lis_delivery = lresponse.is_delivery === '1'?true:false;
                    var lmodule_name = '';
                    if(lis_delivery) lmodule_name = 'delivery';
                    else lmodule_name = 'intake';
                    sales_pos_movement_props.module = lmodule_name ;                    
                    
                    sales_pos_movement_props.reset_all = false;
                    var ltbody = $(lparent_pane).find('#sales_pos_movement_'+lmodule_name+'_table tbody')[0];
                    var lf_mov = lresponse.final_movement;
                    $(ltbody).empty();
                    $.each(lf_mov, function(lidx, f_mov){
                        var lrow = sales_pos_movement_section_methods.table.input_row_generate(lmodule_name);
                        ltbody.appendChild(lrow);
                        $(lrow).find('[col_name="id"]').text(f_mov.id);
                        $(lrow).find('[col_name="code"] strong')[0].innerHTML = '<a href="<?php echo get_instance()->config->base_url(); ?>'+(lmodule_name === 'delivery'?'delivery_order_final/view/':'intake_final/view/')+f_mov.id+'" target="_blank"><strong>'+f_mov.code+'</strong></a>';
                        $(lrow).find('[col_name="'+lmodule_name+'_date"] input').val(f_mov.movement_date);
                        $(lrow).find('[col_name="'+lmodule_name+'_date"] input').prop('disabled',true);
                        $(lrow).find('[col_name="movement_data"]')[0].innerHTML=JSON.stringify(f_mov.movement);                        
                        $(lrow).find('[col_name="movement_status"] span')[0].innerHTML = f_mov.movement_status;
                        $(lrow).find('[method]').attr('method','view');                        
                        $(lrow).find('[col_name="action"]').empty();
                    });
                    
                    
                    sales_pos_cd_cb_props.reset_all = false;
                    var ltbody = $(lparent_pane).find('#sales_pos_cd_cb_customer_bill_table tbody')[0];
                    $(ltbody).empty();
                    var ldofc_customer_bill = lresponse.dofc_customer_bill;
                    fast_draw = APP_COMPONENT.table_fast_draw;
                    $.each(ldofc_customer_bill, function(cb_idx, cb){
                        var lrow = document.createElement('tr');  
                        var row_num = $(ltbody).children().length;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'code',style:'vertical-align:middle',val:'<span>'+cb.code+'</span>',type:'text'});                
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_bill_date',style:'vertical-align:middle',val:'<span>'+cb.customer_bill_date+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_bill_status',style:'vertical-align:middle',val:'<span>'+cb.customer_bill_status_text+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'amount',col_style:'vertical-align:middle;text-align:right;',val:'<span>'+cb.amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'outstanding_amount',col_style:'vertical-align:middle;text-align:right',val:'<span>'+cb.outstanding_amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'',val:'',type:'text'});
                        ltbody.appendChild(lrow);
                    });
                    
                    var ltbody = $(lparent_pane).find('#sales_pos_cd_cb_customer_deposit_table tbody')[0];
                    $(ltbody).empty();
                    var ldofc_customer_deposit = lresponse.dofc_customer_deposit;
                    fast_draw = APP_COMPONENT.table_fast_draw;
                    $.each(ldofc_customer_deposit, function(cb_idx, cb){
                        var lrow = document.createElement('tr');  
                        var row_num = $(ltbody).children().length;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'code',style:'vertical-align:middle',val:'<span>'+cb.code+'</span>',type:'text'});                
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_deposit_date',style:'vertical-align:middle',val:'<span>'+cb.customer_deposit_date+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_deposit_status',style:'vertical-align:middle',val:'<span>'+cb.customer_deposit_status_text+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'amount',col_style:'vertical-align:middle;text-align:right;',val:'<span>'+cb.amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'outstanding_amount',col_style:'vertical-align:middle;text-align:right',val:'<span>'+cb.outstanding_amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'',val:'',type:'text'});
                        ltbody.appendChild(lrow);
                    });
                    
                    sales_pos_routing.set('add','init');
                    
                    break;
            }
        }
        
        var sales_pos_components_enable_disable = function(){
            
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();    
            sales_pos_methods.disable_all();
            sales_pos_init_section_methods.enable_disable();
            sales_pos_product_section_methods.enable_disable();
            sales_pos_payment_section_methods.enable_disable();
            sales_pos_movement_section_methods.enable_disable();

            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    
                    break;
            }
            
        }
        
        var sales_pos_components_show_hide = function(){
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            sales_pos_methods.hide_all();
            sales_pos_init_section_methods.show_hide();
            sales_pos_product_section_methods.show_hide();
            sales_pos_payment_section_methods.show_hide();
            sales_pos_movement_section_methods.show_hide();
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    var lsales_pos_show = false;
                    $(lparent_pane).find('#sales_pos_btn_sales_pos_add').show();
                    $(lparent_pane).find('#sales_pos_btn_print').show();
                    $(lparent_pane).find('#sales_pos_mail').show();
                    break;
            }
        }
                
        sales_pos_components_enable_disable();
        sales_pos_components_show_hide();
        sales_pos_data_set();
    }
    
    var sales_pos_after_submit = function(){
        //function that will be executed after submit 
    }
   
</script>