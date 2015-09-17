<script>

    var dofc_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var dofc_ajax_url = null;
    var dofc_index_url = null;
    var dofc_view_url = null;
    var dofc_window_scroll = null;
    var dofc_data_support_url = null;
    var dofc_common_ajax_listener = null;
    
    var dofc_insert_dummy = false;

    var dofc_init = function(){
        var parent_pane = dofc_parent_pane;
        dofc_ajax_url = '<?php echo $ajax_url ?>';
        dofc_index_url = '<?php echo $index_url ?>';
        dofc_view_url = '<?php echo $view_url ?>';
        dofc_window_scroll = '<?php echo $window_scroll; ?>';
        dofc_data_support_url = '<?php echo $data_support_url; ?>';
        dofc_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        dofc_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var dofc_methods = {
        hide_all:function(){
            var lparent_pane = dofc_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#dofc_print').hide();
            
        },
        show_hide:function(){
            var lparent_pane = dofc_parent_pane;
            var lmethod = $(lparent_pane).find('#dofc_method').val();
            var ldofc_type = $(lparent_pane).find('#dofc_type').val();
            dofc_methods.hide_all();
            
            $(lparent_pane).find('#dofc_reference').closest('div [class*="form-group"]').show();
            
            switch(lmethod){
                case 'add':                    
                    $(lparent_pane).find('#dofc_print').hide();
                    if(ldofc_type!==''){
                        $(lparent_pane).find('#dofc_submit').show();
                        $(lparent_pane).find('#dofc_code').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#dofc_store').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#dofc_delivery_order_final_confirmation_date').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#dofc_delivery_order_final_confirmation_status').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#dofc_delivery_cost').closest('.form-group').show();
                        $(lparent_pane).find('#dofc_driver_name').closest('.form-group').show();
                        $(lparent_pane).find('#dofc_driver_assistant_name').closest('.form-group').show();
                        $(lparent_pane).find('#dofc_receipt_number').closest('.form-group').show();
                        $(lparent_pane).find('#dofc_expedition_name').closest('.form-group').show();
                        $(lparent_pane).find('#dofc_additional_cost_table').closest('.form-group').show();
                        $(lparent_pane).find('#dofc_notes').closest('.form-group').show();
                        $(lparent_pane).find('#dofc_receiver_name').closest('.form-group').show();
                        switch(ldofc_type){
                            case 'sales_invoice':
                                    
                                    break;
                        }
                    }
                    break;
                case 'view':
                    $(lparent_pane).find('#dofc_print').show();
                    $(lparent_pane).find('#dofc_submit').show();
                    $(lparent_pane).find('#dofc_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dofc_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dofc_delivery_order_final_confirmation_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dofc_delivery_order_final_confirmation_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dofc_delivery_cost').closest('.form-group').show();
                    $(lparent_pane).find('#dofc_driver_name').closest('.form-group').show();
                    $(lparent_pane).find('#dofc_driver_assistant_name').closest('.form-group').show();
                    $(lparent_pane).find('#dofc_receipt_number').closest('.form-group').show();
                    $(lparent_pane).find('#dofc_expedition_name').closest('.form-group').show();
                    $(lparent_pane).find('#dofc_additional_cost_table').closest('.form-group').show();
                    $(lparent_pane).find('#dofc_notes').closest('.form-group').show();
                    $(lparent_pane).find('#dofc_receiver_name').closest('.form-group').show();

                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = dofc_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = dofc_parent_pane;
            var lmethod = $(lparent_pane).find('#dofc_method').val();    
            var ldofc_type = $(lparent_pane).find('#dofc_type').val();
            dofc_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#dofc_reference').select2('enable');
                    $(lparent_pane).find('#dofc_store').select2('enable');
                    $(lparent_pane).find('#dofc_delivery_order_final_confirmation_date').prop('disabled',false);
                    $(lparent_pane).find('#dofc_notes').prop('disabled',false);
                    $(lparent_pane).find('#dofc_delivery_cost').prop('disabled',false);
                    $(lparent_pane).find('#dofc_driver_name').prop('disabled',false);
                    $(lparent_pane).find('#dofc_driver_assistant_name').prop('disabled',false);
                    $(lparent_pane).find('#dofc_receipt_number').prop('disabled',false);
                    $(lparent_pane).find('#dofc_expedition_name').prop('disabled',false);
                    $(lparent_pane).find('#dofc_notes').prop('disabled',false);
                    $(lparent_pane).find('#dofc_receiver_name').prop('disabled',false);

                    switch(ldofc_type){
                        case 'sales_invoice':
                            break;
                    }
                    break;
                case 'view':
                    $(lparent_pane).find('#dofc_reference').select2('disable');
                    $(lparent_pane).find('#dofc_notes').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = dofc_parent_pane;
            $(lparent_pane).find('#dofc_code').val('[AUTO GENERATE]');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'dofc'}).response;

            $(lparent_pane).find('#dofc_delivery_order_final_confirmation_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var ldofc_status_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            
            $(lparent_pane).find('#dofc_delivery_order_final_confirmation_status').
                select2({data:ldofc_status_list});
            
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find('#dofc_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find('#dofc_delivery_order_final_confirmation_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            
            dofc_methods.table.additional_cost.reset();
            
        },
        table:{
            additional_cost:{
                input_row_add:function(ldata){
                    var lparent_pane = dofc_parent_pane;
                    var lmethod = $(lparent_pane).find('#dofc_method').val();
                    var ltbody = $(lparent_pane).find('#dofc_additional_cost_table tbody')[0];
                    var lrow = document.createElement('tr');
                    fast_draw = APP_COMPONENT.table_fast_draw;
                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'description',style:'vertical-align:middle;',val:'',type:'input',class:'form-control'});
                    var lamount_td = fast_draw.col_add(lrow,{tag:'td',col_name:'amount',style:'vertical-align:middle',val:'',type:'text',class:''});
                    lamount_td.innerHTML = '<input class="form-control" style="text-align:right">';
                    laction_td = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    $(laction_td).append(APP_COMPONENT.new_row());
                    $(ltbody).append(lrow);
                    
                    APP_COMPONENT.input.numeric($(lamount_td).find('input'),{min_val:0});
                    $(lamount_td).find('input').blur();
                    
                    $(laction_td).find('button').on('click',function(){
                        var lparent_pane = dofc_parent_pane;
                        var ltr = $(this).closest('tr');
                        var lamount = $(ltr).find('[col_name="amount"] input').val().replace(/[^0-9.]/g,'');
                        var ldescription = $(ltr).find('[col_name="description"] input').val().replace(/[ ]/g,'');
                        var lvalid = true;
                        
                        
                        
                        $(ltr).find('[col_name="description"] input').popover_danger('destroy','','');
                        if(ldescription===''){
                            $(ltr).find('[col_name="description"] input').popover_danger('init','','Empty');
                            lvalid = false;
                        }
                        
                        $(ltr).find('[col_name="amount"] input').popover_danger('destroy','','');
                        if(parseFloat(lamount)<=0){
                            $(ltr).find('[col_name="amount"] input').popover_danger('init','','Zero');
                            lvalid = false;
                        }
                        if(lvalid){
                            $(ltr).find('[col_name="description"] input').prop('disabled',true);
                            $(ltr).find('[col_name="amount"] input').prop('disabled',true);
                            
                            $(ltr).find('[col_name="action"]').empty();
                            $(ltr).find('[col_name="action"]').append(APP_COMPONENT.trash());
                            dofc_methods.table.additional_cost.input_row_add();
                        }
                    });
                    
                },
                reset:function(){
                    var lparent_pane = dofc_parent_pane;
                    var ltbody = $(lparent_pane).find('tbody')[0];
                    $(ltbody).empty();
                },
                load:function(ldata){
                    if(typeof ldata ==='undefined') ldata = [];
                    
                    dofc_methods.table.additional_cost.reset();
                    dofc_methods.table.additional_cost.input_row_add(ldata);
                }
            }
        },
        submit:function(){
            var lparent_pane = dofc_parent_pane;
            var lajax_url = dofc_index_url;
            var lmethod = $(lparent_pane).find('#dofc_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.reference = {id:$(lparent_pane).find('#dofc_reference').select2('val')};
                    json_data.dofc = {
                        dofc_type:$(lparent_pane).find('#dofc_type').val(),
                        store_id:$(lparent_pane).find('#dofc_store').select2('val'),
                        dofc_date:$(lparent_pane).find('#dofc_delivery_order_final_confirmation_date').val(),
                        receipt_number:$(lparent_pane).find('#dofc_receipt_number').val(),
                        expedition_name:$(lparent_pane).find('#dofc_expedition_name').val(),
                        driver_name:$(lparent_pane).find('#dofc_driver_name').val(),
                        driver_assistant_name:$(lparent_pane).find('#dofc_driver_assistant_name').val(),
                        delivery_cost:$(lparent_pane).find('#dofc_delivery_cost').val().replace(/[^0-9.]/g,''),
                        receiver_name:$(lparent_pane).find('#dofc_receiver_name').val(),
                    };
                    json_data.additional_cost = [];
                    $.each($(lparent_pane).find('#dofc_additional_cost_table tbody tr'),
                    function(lrow_idx,lrow){
                        json_data.additional_cost.push({
                            description:$(lrow).find('[col_name="description"] input').val(),
                            amount:$(lrow).find('[col_name="amount"] input').val().replace(/[^0-9.]/g,''),
                        });
                    });
                    lajax_url +='dofc_add/';
                    break;
                case 'view':
                    json_data.dofc = {
                        delivery_order_final_confirmation_status:$(lparent_pane).find('#dofc_delivery_order_final_confirmation_status').select2('val'),
                        notes:$(lparent_pane).find('#dofc_notes').val(),
                        cancellation_reason:$(lparent_pane).find('#dofc_delivery_order_final_confirmation_cancellation_reason').val()
                    };
                    var dofc_id = $(lparent_pane).find('#dofc_id').val();
                    var lajax_method = $(lparent_pane).find('#dofc_delivery_order_final_confirmation_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+dofc_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#dofc_id').val(result.trans_id);
                if(dofc_view_url !==''){
                    var url = dofc_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    dofc_after_submit();
                }
            }
        },
        reference:{
            reset_dependency:function(){
                var lparent_pane = dofc_parent_pane;
                $(lparent_pane).find('#dofc_receipt_number').val('');
                $(lparent_pane).find('#dofc_receiver_name').val('');
                $(lparent_pane).find('#dofc_expedition_name').val('');
                $(lparent_pane).find('#dofc_driver_name').val('');
                $(lparent_pane).find('#dofc_driver_assistant_name').val('');
                $(lparent_pane).find('#dofc_delivery_cost').val('0').change();
            }
        }
    };
    
    var dofc_bind_event = function(){
        var lparent_pane = dofc_parent_pane;
        
        $(lparent_pane).find('#dofc_print').off();
        $(lparent_pane).find('#dofc_print').on('click',function(){
            var ldofc_id = $(lparent_pane).find('#dofc_id').val();
            modal_print.init();
            modal_print.menu.add('<?php echo Lang::get('Delivery Order Final Confirmation') ?>',dofc_index_url+'dofc_print/dofc/'+ldofc_id);
            modal_print.show();
            
        });
        
        APP_COMPONENT.button.mail.set(
            $(lparent_pane).find('#dofc_mail'),
            {
                mail_to_get:function(){return $('#dofc_reference').select2('data').mail_to;},
                subject:'<?php echo Lang::get('Delivery Order Final Confirmation'); ?>',
                message:<?php echo json_encode($mail_message)  ?>,
                ajax_url:dofc_index_url+'dofc_mail/dofc',
                json_data_get:function(){
                    return {
                        dofc_id:$('#dofc_id').val(),                
                        mail_to:$('#modal_mail_mail_to').val(),
                        subject:$('#modal_mail_subject').val(),
                        message:$('#modal_mail_message').val(),
                    }
                },
            }
        );
        
        
        $(lparent_pane).find('#dofc_delivery_cost').off();
        APP_COMPONENT.input.numeric($(lparent_pane).find('#dofc_delivery_cost'),{min_val:'0'});
        $(lparent_pane).find('#dofc_delivery_cost').blur();

        
        $(lparent_pane).find("#dofc_reference")
        .on('change', function(){
            var lparent_pane = dofc_parent_pane;
            var lmethod = $(lparent_pane).find('#dofc_method').val();
            var ldofc_type = '';
            var lref_data = $(this).select2('data');            
            if(lref_data === null) lref_data = {id:'',text:'',reference_type:'',reference_type_name:''}
            
            $('#dofc_type').val(lref_data.reference_type);            
            ldofc_type = $(lparent_pane).find('#dofc_type').val();
            
            dofc_methods.show_hide();//important for reference switching
            dofc_methods.enable_disable();//important for reference switching
            dofc_methods.reference.reset_dependency();
            
            $('#dofc_reference_detail').find('.extra_info').remove();
            
            
            
            if(lmethod === 'add'){
                dofc_methods.table.additional_cost.load();
                if(ldofc_type!==''){
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(dofc_data_support_url+'/reference_dependency_get',{ref_id:lref_data.id,ref_type:ldofc_type}).response;
                    var lref_detail = lresponse.reference_detail;
                    
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#dofc_reference_detail')[0],lref_detail);
                    $(lparent_pane).find('#dofc_expedition_name').val(lresponse.expedition.name);
                    
                    switch(ldofc_type){
                        case 'sales_invoice':
                            
                            break;
                    }
                }
                
            }
            
        });
        
        $(lparent_pane).find('#dofc_submit').off();        
        $(lparent_pane).find('#dofc_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = dofc_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                dofc_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            
            $(dofc_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var dofc_components_prepare = function(){
        

        var dofc_data_set = function(){
            var lparent_pane = dofc_parent_pane;
            var lmethod = $(lparent_pane).find('#dofc_method').val();
            
            switch(lmethod){
                case 'add':
                    dofc_methods.reset_all();
                    if(dofc_insert_dummy){
                        $(lparent_pane).find('#dofc_reference').select2('search',' ');
                        setTimeout(function(){
                            var lref = APP_COMPONENT.input_select.dropdown_get($(lparent_pane).find('#dofc_reference')[0])[0];
                            $('#dofc_reference').select2('data',lref).change();
                            $('#dofc_reference').select2('close');
                            $('#dofc_receipt_number').val('Receipt Number 123');
                            $('#dofc_receiver_name').val('Receiver Name 123');
                            $('#dofc_expedition_name').val('Expedition Name 123');
                            $('#dofc_driver_name').val('Driver Name 123');
                            $('#dofc_driver_assistant_name').val('Driver Assistant Name 123');
                            $('#dofc_delivery_cost').val('5200000').blur();
                        },2000);
                        
                        //$(lparent_pane).find('#dofc_reference').select2('select');
                        
                    }
                    break;
                case 'view':
                    var ldofc_id = $(lparent_pane).find('#dofc_id').val();                    
                    var lajax_url = dofc_data_support_url+'dofc_init_get';
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{data:ldofc_id}).response;
                    var lreference_type = lresponse.reference.reference_type;
                    var lref_detail = lresponse.reference_detail;
                    
                    $(lparent_pane).find('#dofc_reference').select2(
                        'data',lresponse.reference);
                    $(lparent_pane).find('#dofc_type').val(lresponse.reference_type);
                    
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#dofc_reference_detail')[0],lref_detail);
                    
                    var lajax_url = dofc_data_support_url+'dofc_get/';
                    var json_data = {data:ldofc_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var ldofc = lresponse.dofc;
                    var ldofc_additional_cost = lresponse.dofc_additional_cost;
                    
                    $(lparent_pane).find('#dofc_store').select2('data',{id:ldofc.store_id
                        ,text:ldofc.store_text});
                    $(lparent_pane).find('#dofc_code').val(ldofc.code);
                    $(lparent_pane).find('#dofc_receipt_number').val(ldofc.receipt_number);
                    $(lparent_pane).find('#dofc_expedition_name').val(ldofc.expedition_name);
                    $(lparent_pane).find('#dofc_driver_name').val(ldofc.driver_name);
                    $(lparent_pane).find('#dofc_driver_assistant_name').val(ldofc.driver_assistant_name);
                    $(lparent_pane).find('#dofc_receiver_name').val(ldofc.receiver_name);
                    $(lparent_pane).find('#dofc_delivery_cost').val(ldofc.delivery_cost);
                    
                    $(lparent_pane).find('#dofc_delivery_order_final_confirmation_date').datetimepicker({value:ldofc.dofc_date});
                    $(lparent_pane).find('#dofc_delivery_order_final_confirmation_cancellation_reason').val(ldofc.cancellation_reason);

                    $(lparent_pane).find('#dofc_delivery_order_final_confirmation_status')
                            .select2('data',{id:ldofc.dofc_status
                                ,text:ldofc.dofc_status_text}).change();
                                  
                    $(lparent_pane).find('#dofc_delivery_order_final_confirmation_status')
                            .select2({data:lresponse.dofc_status_list});
                    
                    
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var ltbody = $(lparent_pane).find('#dofc_additional_cost_table tbody')[0];
                    $.each(ldofc_additional_cost, function(lidx, ladd_cost){
                        var lrow = document.createElement('tr');
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'description',col_style:'vertical-align:middle;text-align:center',val:'<span>'+ladd_cost.description+'</span>',type:'text'});                
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'vertical-align:middle;text-align:center',val:'<span>'+ladd_cost.amount+'</span>',type:'text'});                
                        $(ltbody).append(lrow);
                    });
                    
                    break;
            }
        }
        
        
        dofc_methods.enable_disable();
        dofc_methods.show_hide();
        dofc_data_set();
    }
    
    var dofc_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>