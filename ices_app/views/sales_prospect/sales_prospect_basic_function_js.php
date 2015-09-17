<script>
    
    var sales_prospect_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var sales_prospect_ajax_url = null;
    var sales_prospect_index_url = null;
    var sales_prospect_view_url = null;
    var sales_prospect_window_scroll = null;
    var sales_prospect_data_support_url = null;
    var sales_prospect_common_ajax_listener = null;
    
    var sales_prospect_insert_dummy = false;
    
    var sales_prospect_init = function(){
        var parent_pane = sales_prospect_parent_pane;
        sales_prospect_ajax_url = '<?php echo $ajax_url ?>';
        sales_prospect_index_url = '<?php echo $index_url ?>';
        sales_prospect_view_url = '<?php echo $view_url ?>';
        sales_prospect_window_scroll = '<?php echo $window_scroll; ?>';
        sales_prospect_data_support_url = '<?php echo $data_support_url; ?>';
        sales_prospect_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        sales_prospect_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var sales_prospect_routing={
        set:function(method,section){
            var lparent_pane = sales_prospect_parent_pane;
            
            $(lparent_pane).find('[routing_section="init"]').hide();
            $(lparent_pane).find('[routing_section="product"]').hide();
            
            if($.inArray(method,['add','view']) !== -1){
                var prev_section = '';
                var next_section = '';
                sales_prospect_methods.btn_controller_reset();                
                switch(section){                    
                    case 'init':
                        $(lparent_pane).find('[routing_section="init"]').show();
                        prev_section = '';
                        next_section = 'product';                        
                        sales_prospect_init_section_methods.btn_controller_set();                        
                        break;
                    case 'product':
                        $(lparent_pane).find('[routing_section="product"]').show();
                        prev_section = 'init';
                        next_section = '';
                        sales_prospect_product_section_methods.btn_controller_set();
                        break;
                }
                
            }
        }
    }
    
    var sales_prospect_methods = {
        status_label_get:function(){
            var parent_pane = sales_prospect_parent_pane;
            return $($(parent_pane).find('#sales_prospect_sales_prospect_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#sales_prospect_sales_prospect_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#sales_prospect_sales_prospect_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lsales_prospect_id = $('#sales_prospect_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'sales_prospect_current_status/',{data:lsales_prospect_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = sales_prospect_parent_pane;    
            $(lparent_pane).find('#sales_prospect_sales_pos').hide();
            $(lparent_pane).find('#sales_prospect_mail').hide();
            $(lparent_pane).find('#sales_prospect_btn_print').hide();
            
        },
        disable_all:function(){
            var lparent_pane = sales_prospect_parent_pane;            

        },
        reset_all:function(){
            var lparent_pane = sales_prospect_parent_pane;
            sales_prospect_summary_methods.reset_all();
            sales_prospect_init_section_methods.reset_all();
            sales_prospect_product_section_methods.reset_all();
            
        },
        btn_controller_reset:function(){
            var lparent_pane = sales_prospect_parent_pane;
            $(lparent_pane).find('#sales_prospect_btn_back').hide();
            $(lparent_pane).find('#sales_prospect_btn_prev').hide();
            $(lparent_pane).find('#sales_prospect_btn_prev').prop('disabled',true);            
            $(lparent_pane).find('#sales_prospect_btn_prev').off();
            $(lparent_pane).find('#sales_prospect_btn_next').hide();
            $(lparent_pane).find('#sales_prospect_btn_next').prop('disabled',true);
            $(lparent_pane).find('#sales_prospect_btn_next').off();            
            $(lparent_pane).find('#sales_prospect_submit').hide();
            $(lparent_pane).find('#sales_prospect_submit').prop('disabled',true);
            
            
        },
        security_set:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = '';
            
        },
        submit:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lajax_url = sales_prospect_index_url;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.sales_prospect = {};
                    json_data.sales_prospect.sales_inquiry_by_id=$(lparent_pane).find('#sales_prospect_sales_inquiry_by').select2('val');
                    json_data.sales_prospect.customer_id=$(lparent_pane).find('#sales_prospect_customer').select2('val');
                    json_data.sales_prospect.price_list_id=$(lparent_pane).find('#sales_prospect_price_list').select2('val');
                    json_data.sales_prospect.expedition_id=$(lparent_pane).find('#sales_prospect_expedition').select2('val');
                    json_data.sales_prospect.is_delivery = $(lparent_pane).find('#sales_prospect_delivery_checkbox').is(':checked');
                    json_data.sales_prospect.discount = $(lparent_pane)
                        .find('#sales_prospect_product_discount').val().replace(/[,]/g,'');
                    json_data.sales_prospect.delivery_cost_estimation = $(lparent_pane)
                        .find('#sales_prospect_delivery_cost_estimation').val().replace(/[,]/g,'');
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
                    
                    $.each($(lparent_pane).find('#sales_prospect_product_table tbody>tr'),function(idx, row){
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
                    
                    lajax_url +='sales_prospect_add';
                    break;
            }
            
            var result = null;
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#sales_prospect_id').val(result.trans_id);
                if(sales_prospect_view_url !==''){
                    var url = sales_prospect_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    sales_prospect_after_submit();
                }
            }

        
        }
        
    };
    
    var sales_prospect_bind_event = function(){
        var lparent_pane = sales_prospect_parent_pane;      
        
        $(lparent_pane).find('#sales_prospect_btn_print').off();
        $(lparent_pane).find('#sales_prospect_btn_print').on('click',function(){
            var lpos_id = $(lparent_pane).find('#sales_prospect_id').val();
            var lis_delivery = $(lparent_pane).find('#sales_prospect_delivery_checkbox').prop('checked');
            var lmodule = lis_delivery? 'delivery':'intake';
            modal_print.init();
            modal_print.menu.add('PROFORMA INVOICE',sales_prospect_index_url+'sales_prospect_print/sales_prospect/'+lpos_id);
            modal_print.show();
            
        });
        
        
        APP_COMPONENT.button.mail.set(
            $(lparent_pane).find('#sales_prospect_mail'),
            {
                mail_to_get:function(){return $('#sales_prospect_customer_detail_email').text()},
                subject:'<?php echo Lang::get('Performa Invoice'); ?>',
                message:<?php echo json_encode($mail_message); ?>,
                ajax_url:sales_prospect_index_url+'sales_prospect_mail/sales_prospect',
                json_data_get:function(){
                    return {
                        sales_prospect_id:$('#sales_prospect_id').val(),                
                        mail_to:$('#modal_mail_mail_to').val(),
                        subject:$('#modal_mail_subject').val(),
                        message:$('#modal_mail_message').val(),
                    }
                },
            }
        );
        
        
        $(lparent_pane).find('#sales_prospect_submit').on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');            
            var lparent_pane = sales_prospect_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                sales_prospect_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });

            $(sales_prospect_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $(function () {
            sales_prospect_relocate = function(){
                APP_COMPONENT.sticky_relocate($('#sales_prospect_div_right')[0],'25%',110,15,15);
            }
            $(window).scroll(sales_prospect_relocate);
        });
                
        $(lparent_pane).find('#sales_prospect_cancel').on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');            
            
            var lparent_pane = sales_prospect_parent_pane;
            modal_confirmation_cancel_module_prefix_id = "sales_prospect";
            modal_confirmation_cancel_primary_data_key = "sales_prospect";
            modal_confirmation_cancel_module_status_field = "sales_prospect_status";
            modal_confirmation_cancel_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            var lsales_prospect_id = $(lparent_pane).find('#sales_prospect_id').val();
            modal_confirmation_cancel_ajax_url = sales_prospect_index_url+'sales_prospect_canceled/'
                    +lsales_prospect_id;
            modal_confirmation_cancel_view_url = sales_prospect_index_url+'view/';
            
            $('#modal_confirmation_cancel').modal('show');
            
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });

        
        $(lparent_pane).find('#sales_prospect_sales_pos').off();
        $(lparent_pane).find('#sales_prospect_sales_pos').on('click',function(e){
            e.preventDefault();
            var lsales_prospect_id = $(lparent_pane).find('#sales_prospect_id').val();
            var lsales_prospect_code = $(lparent_pane).find('#sales_prospect_code').val();
            var lsales_pos_id = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'sales_pos_get/'
                ,{sales_prospect_id:lsales_prospect_id}
            ).response;
            
            if(lsales_pos_id === ''){
                var lform = ''
                    +'<form method="post" action="<?php echo get_instance()->config->base_url(); ?>sales_pos/add">'
                    +'<input name="component[0][id]" value="sales_pos_reference_id">'    
                    +'<input name="component[0][val][id]" value="'+lsales_prospect_id+'">'    
                    +'<input name="component[0][val][text]" value="'+lsales_prospect_code+'">'    
                    +'<input name="component[0][type]" value="select2">'
                    +'<input name="component[1][id]" value="sales_pos_reference_type">'    
                    +'<input name="component[1][val]" value="sales_prospect">'    
                    +'<input name="component[1][type]" value="input">'
                    +'</form>'
                    +'';
                $(lform).submit();
            }
            else{
                window.location = '<?php echo get_instance()->config->base_url(); ?>sales_pos/view/'+lsales_pos_id;
            }
                
        });

        sales_prospect_init_section_bind_events();
        sales_prospect_product_section_bind_events();
        
    }
    
    var sales_prospect_components_prepare = function(){        

        var sales_prospect_data_set = function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            sales_prospect_methods.reset_all();
            switch(lmethod){
                case 'add':
                    var my_timer = function(){
                        $('#sales_prospect_datetime').text((new Date()).format('F d, Y H:i:s'));
                    };
                    window.setInterval(my_timer,1000);

                    sales_prospect_routing.set('add','init');
                    var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>'+'store/data_support/default_store_get/');
                    var ldefault_store = lresult.response;
                    $(lparent_pane).find('#sales_prospect_store').select2('data',
                        {id:ldefault_store.id,text:ldefault_store.name}
                    );
                    $(lparent_pane).find('#sales_pos_expedition').select2('data',null);
                    ldefault_status = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'default_status_get/');
                    $(lparent_pane).find('#sales_prospect_sales_prospect_status')
                            .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
                    var lsales_prospect_status_list = [
                        {id:ldefault_status.val,text:ldefault_status.label}//,
                    ]
                    $(lparent_pane).find('#sales_prospect_sales_prospect_status').select2({data:lsales_prospect_status_list});
                    
                    lsales_inquiry_by = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'sales_inquiry_by_get/').response;
                    var lsales_inquiry_by_list = []
                    $.each(lsales_inquiry_by, function(lidx, lrow){
                        if(lrow.text ==='VISIT'){
                            $(lparent_pane).find('#sales_prospect_sales_inquiry_by')
                            .select2('data',{id:lrow.id,text:lrow.text}).change();
                        }
                        lsales_inquiry_by_list.push({id:lrow.id,text:lrow.text});
                    });
                    $(lparent_pane).find('#sales_prospect_sales_inquiry_by').select2({data:lsales_inquiry_by_list});
                    
                    
                    if(sales_prospect_insert_dummy){
                        <?php /*
                        $(lparent_pane).find('#sales_prospect_customer').select2('data',{id:'55',text:'Customer 1 '}).change();
                        $(lparent_pane).find('#sales_prospect_price_list').select2('data',{id:'30',text:'Pemadam FOB'}).change();
                        
                        $(lparent_pane).find('#sales_prospect_btn_next').click();
                        
                        var lproducts = [];
                        
                        lproducts.push({id:1,code:'TK-DCP/CAP1',qty:10});
                        lproducts.push({id:2,code:'TK-DCP/CAP2',qty:5});
                        lproducts.push({id:3,code:'TK-DCP/CAP3',qty:'8'});
                        lproducts.push({id:21,code:'CCT/CART6',qty:3});
                        lproducts.push({id:22,code:'CCT/CART9',qty:1});
                        lproducts.push({id:'112',code:'DCP/MAPAS-BOX',qty:100});
                        lproducts.push({id:'17',code:'DCP/MAP-BAG',qty:50});
                        lproducts.push({id:'114',code:'P/SP',qty:3});
                        lproducts.push({id:'43',code:'SP-DCP/V',qty:10});

                        $('#sales_prospect_delivery_checkbox').iCheck('check');
                    
                        for(i = 0; i<lproducts.length;i++){
                            $($(lparent_pane).find('#sales_prospect_product_table>tbody>tr')[i])
                                    .find('[col_name="product"]').find('[original]')
                                    .select2('data',{id:lproducts[i].id
                                        ,code:lproducts[i].code})
                                    .change();
                            $($(lparent_pane).find('#sales_prospect_product_table>tbody>tr')
                                    .find('[col_name="qty"]').find('input')[i])
                                    .val(lproducts[i].qty).blur();
                            $($(lparent_pane).find('#sales_prospect_product_table>tbody>tr')
                                    .find('[col_name="action"]').find('button')[i])
                                    .click();
                        }
                        $('#sales_prospect_product_table>tbody>tr').last().find('[col_name="product"]')
                                .find('input[original]').select2('close');
                        
                        $(lparent_pane).find('#sales_prospect_expedition').select2('data',{id:'4',text:'<strong>EXPE/4</strong> Herona'}).change();
                        
                        */ ?>
                    }
                    
                    break;
                case 'view':
                    
                    var lsales_prospect_id = $(lparent_pane).find('#sales_prospect_id').val();
                    
                    var lajax_url = sales_prospect_data_support_url+'sales_prospect_get';
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{sales_prospect_id:lsales_prospect_id});
                    var lresponse = result.response;
                    var lcustomer = lresponse.customer;                    
                    var lsales_prospect = lresponse.sales_prospect;
                    var lsales_info = lresponse.sales_info;
                    
                    $(lparent_pane).find('#sales_prospect_sales_inquiry_by').select2('data',{
                        id:lsales_info.sales_inquiry_by_id,
                        text:lsales_info.sales_inquiry_by_text
                    });
                    
                    
                    $(lparent_pane).find('#sales_prospect_customer').select2('data',{
                        id:lcustomer.id,
                        text:lcustomer.customer_text
                    });
                    $(lparent_pane).find('#sales_prospect_customer_detail_code')[0].innerHTML = '<a href = "<?php echo get_instance()->config->base_url().'customer/view/'; ?>'+lcustomer.id+'" target="_blank"><strong>'+lcustomer.code+'</strong></a>';
                    $(lparent_pane).find('#sales_prospect_customer_detail_name').text(lcustomer.name);
                    $(lparent_pane).find('#sales_prospect_customer_detail_phone').text(lcustomer.phone);
                    $(lparent_pane).find('#sales_prospect_customer_detail_bb_pin').text(lcustomer.bb_pin);
                    $(lparent_pane).find('#sales_prospect_customer_detail_email').text(lcustomer.email);
                    $(lparent_pane).find('#sales_prospect_customer_detail_is_sales_receipt_outstanding')
                            .text(lcustomer.is_sales_receipt_outstanding);
                    
                    var lprice_list = lresponse.price_list;
                    $(lparent_pane).find('#sales_prospect_price_list').select2('data',{
                        id:lprice_list.id,
                        text:lprice_list.price_list_text
                    });
                    
                    var lexpedition = lresponse.expedition;
                    $(lparent_pane).find('#sales_prospect_expedition').select2('data',{
                        id:lexpedition.id,
                        text:lexpedition.expedition_text
                    });
                    
                    if(lresponse.is_delivery === '1'){
                        $(lparent_pane).find('#sales_prospect_delivery_checkbox').iCheck('check');
                    }
                    else{
                        $(lparent_pane).find('#sales_prospect_delivery_checkbox').iCheck('uncheck');
                    }
                    
                    var ltbody = $(lparent_pane).find('#sales_prospect_product_table tbody')[0];
                    $(ltbody).empty();
                    $.each(lresponse.product,function(idx, lproduct){
                        fast_draw = APP_COMPONENT.table_fast_draw;
                        var lrow = document.createElement('tr');  
                        var row_num = $(lparent_pane).find('#sales_prospect_product_table').find('tbody').children().length;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:lproduct.product_img,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:lproduct.product_id,type:'span',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:lproduct.product_text,type:'div'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'total_stock',col_style:'vertical-align:middle;text-align:right;',val:lproduct.total_stock,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'mult_qty',col_style:'vertical-align:middle;text-align:right',val:lproduct.mult_qty,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'qty',col_style:'vertical-align:middle;text-align:right',val:lproduct.qty,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:lproduct.unit_id,type:'span',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:lproduct.unit_name,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'expedition_weight_qty',col_style:'vertical-align:middle;text-align:right',val:lproduct.expedition_weight,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'vertical-align:middle;text-align:right',val:lproduct.amount,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'subtotal',col_style:'vertical-align:middle;text-align:right',val:lproduct.subtotal,type:'span'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'vertical-align:middle',val:'',type:'span'});
                        
                        ltbody.appendChild(lrow);
                    });
                    
                    $(lparent_pane).find('#sales_prospect_expedition_weight_total').text(lresponse.weight_total);
                    
                    $(lparent_pane).find('#sales_prospect_product_discount').val(lsales_prospect.discount);                    
                    if(parseFloat(lsales_prospect.discount.replace(/[^0-9.]/g,'')) === 0){
                        $(lparent_pane).find('#sales_prospect_product_discount').closest('tr').hide();
                    }
                    
                    
                    $(lparent_pane).find('#sales_prospect_code').val(lsales_prospect.code);
                    $(lparent_pane).find('#sales_prospect_sales_prospect_status').select2(
                       'data',{
                            id:lsales_prospect.sales_prospect_status,
                            text:lsales_prospect.sales_prospect_status_name
                        }
                    );
                    
                    $(lparent_pane).find('#sales_prospect_cancellation_reason').val(lsales_prospect.cancellation_reason);
                    if(lsales_prospect.sales_prospect_status === 'X'){
                        $(lparent_pane).find('#sales_prospect_cancellation_reason').closest('.form-group').show();                    
                    }
                    
                    $(lparent_pane).find('#sales_prospect_delivery_cost_estimation_text').text(lsales_prospect.delivery_cost_estimation);
                    $(lparent_pane).find('#sales_prospect_product_extra_charge').text(lsales_prospect.extra_charge);
                    $(lparent_pane).find('#sales_prospect_product_total').text(lsales_prospect.total_product);
                    $(lparent_pane).find('#sales_prospect_product_grand_total').text(lsales_prospect.grand_total);
                    
                    

                    ladditional_costs = lresponse.additional_cost;
                    var lgrand_total = $(sales_prospect_parent_pane).find('#sales_prospect_product_grand_total').closest('tr')[0];
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
                    
                    $(lparent_pane).find('#sales_prospect_modal_extra_charge .modal-body')[0].innerHTML = lresponse.extra_charge_msg;
                    $(lparent_pane).find('#sales_prospect_datetime').text(lsales_prospect.sales_prospect_date);
                    $(lparent_pane).find('#sales_prospect_summary_code').text(lsales_prospect.code);
                    $(lparent_pane).find('#sales_prospect_summary_customer').text(lcustomer.name);
                    $(lparent_pane).find('#sales_prospect_summary_price_list').text(lprice_list.price_list_text);
                    $(lparent_pane).find('#sales_prospect_summary_product_grand_total').text(lsales_prospect.grand_total);
                    
                    $(lparent_pane).find('#sales_prospect_btn_print').show();
                    if($(lparent_pane).find('#sales_prospect_sales_prospect_status').select2('val')!= 'X'){
                        $(lparent_pane).find('#sales_prospect_mail').show();
                        
                    }
                    sales_prospect_routing.set('add','init');
                    break;
            }
        }
        
        var sales_prospect_components_enable_disable = function(){
            
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();    
            sales_prospect_methods.disable_all();
            sales_prospect_init_section_methods.enable_disable();
            sales_prospect_product_section_methods.enable_disable();
            
        }
        
        var sales_prospect_components_show_hide = function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            sales_prospect_methods.hide_all();
            sales_prospect_init_section_methods.show_hide();
            sales_prospect_product_section_methods.show_hide();
            $(lparent_pane).find('#sales_prospect_cancel').hide();
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    var lsales_pos_show = false;
                    
                    var lsales_pos_id = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'sales_pos_get/'
                        ,{sales_prospect_id:$(lparent_pane).find('#sales_prospect_id').val()}).response;
                    if(lsales_pos_id !== ''){
                        if(APP_SECURITY.permission_get('sales_pos','view').result                            
                        ){
                            lsales_pos_show = true;
                        }
                    }
                    else{
                        if(APP_SECURITY.permission_get('sales_pos','add').result
                        && sales_prospect_methods.current_status_get() !=='X'){
                            lsales_pos_show  = true;
                        }
                    }
                    
                    if(lsales_pos_show) $(lparent_pane).find('#sales_prospect_sales_pos').show();
                    
                    var lcancel_show = false;
                    if(APP_SECURITY.permission_get('sales_prospect','sales_prospect_canceled').result
                        && sales_prospect_methods.current_status_get() !=='X'
                        && sales_prospect_methods.current_status_get() !=='done'
                    ){
                        lcancel_show = true;                        
                    }
                    
                    if(lcancel_show){
                        $(lparent_pane).find('#sales_prospect_cancel').show();
                    }
                    
                    break;
            }
        }
                
        sales_prospect_components_enable_disable();
        sales_prospect_components_show_hide();
        sales_prospect_data_set();
    }
    
    var sales_prospect_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    

</script>