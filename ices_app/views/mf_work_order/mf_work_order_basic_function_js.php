<script>
    var mf_work_order_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var mf_work_order_ajax_url = null;
    var mf_work_order_index_url = null;
    var mf_work_order_view_url = null;
    var mf_work_order_window_scroll = null;
    var mf_work_order_data_support_url = null;
    var mf_work_order_common_ajax_listener = null;
    var mf_work_order_component_prefix_id = '';
    
    var mf_work_order_init = function(){
        var parent_pane = mf_work_order_parent_pane;

        mf_work_order_ajax_url = '<?php echo $ajax_url ?>';
        mf_work_order_index_url = '<?php echo $index_url ?>';
        mf_work_order_view_url = '<?php echo $view_url ?>';
        mf_work_order_window_scroll = '<?php echo $window_scroll; ?>';
        mf_work_order_data_support_url = '<?php echo $data_support_url; ?>';
        mf_work_order_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        mf_work_order_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var mf_work_order_after_submit = function(){

    }
    
    var mf_work_order_methods = {
        
        hide_all:function(){
            var lparent_pane = mf_work_order_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = mf_work_order_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        security_set:function(){
            var lparent_pane = mf_work_order_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = mf_work_order_methods.status_label_get();
            
            if($(lparent_pane).find('#mf_work_order_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('mf_work_order',lstatus_label).result){
                lsubmit_show = false;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#mf_work_order_submit').show();
                $(lparent_pane).find('#mf_work_order_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#mf_work_order_submit').hide();
                $(lparent_pane).find('#mf_work_order_notes').prop('disabled',true);
            }    
        },
        show_hide: function(){
            var lparent_pane = mf_work_order_parent_pane;
            var lprefix_id = mf_work_order_component_prefix_id;
            var lmethod = $(lparent_pane).find('#mf_work_order_method').val();            
            var lmf_work_order_type = $(lparent_pane).find(lprefix_id+'_type').select2('val');
            mf_work_order_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_mf_work_order_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_start_date_plan').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_end_date_plan').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_type').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_mf_work_order_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    switch(lmf_work_order_type){
                        case 'normal':
                        case 'bad_stock_transform':
                        case 'good_stock_transform':
                            $(lparent_pane).find(lprefix_id+'_ordered_product_table').closest('div [class*="form-group"]').show();
                            break;
                    }
                    break;
            }
        },        
        enable_disable: function(){
            var lparent_pane = mf_work_order_parent_pane;
            var lprefix_id = mf_work_order_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            mf_work_order_methods.disable_all();
            
            switch(lmethod){
                case "add":
                    $(lparent_pane).find(lprefix_id+'_store').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_type').select2('enable');
                    $(lparent_pane).find(lprefix_id+"_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+'_start_date_plan').prop("disabled",false); 
                    $(lparent_pane).find(lprefix_id+'_end_date_plan').prop("disabled",false); 
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = mf_work_order_parent_pane;
            var lprefix_id = mf_work_order_component_prefix_id;
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            $(lparent_pane).find(lprefix_id+'_name').val('');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'mf_work_order'}).response;

            $(lparent_pane).find(lprefix_id+'_mf_work_order_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            
             $(lparent_pane).find(lprefix_id+'_start_date_plan').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y H:i'),
                minDate:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y'),
                minTime:APP_GENERATOR.CURR_DATETIME('minute',10,'H:i'),
            });
            
            $(lparent_pane).find(lprefix_id+'_end_date_plan').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y H:i'),
                minDate:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y'),
                minTime:APP_GENERATOR.CURR_DATETIME('minute',10,'H:i'),
            });

            mf_work_order_ordered_product_table_method.reset();
            mf_work_order_ordered_product_table_method.head_generate();
            mf_work_order_ordered_product_table_method.input_row_generate({});
           
        },
        submit:function(){
            var lparent_pane = mf_work_order_parent_pane;
            var lprefix_id = mf_work_order_component_prefix_id;
            var ajax_url = mf_work_order_index_url;
            var lmethod = $(lparent_pane).find("#mf_work_order_method").val();
            var mf_work_order_id = $(lparent_pane).find("#mf_work_order_id").val();        
            var lmodule_type = mf_work_order_methods.module_type_get();
            var json_data = {
                ajax_post:true,
                mf_work_order:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                    json_data.mf_work_order.store_id = $(lparent_pane).find(lprefix_id+"_store").select2('val');
                    json_data.mf_work_order.notes = $(lparent_pane).find(lprefix_id+"_notes").val();
                    json_data.mf_work_order.mf_work_order_type = lmodule_type;
                    json_data.mfwo_ordered_product = mf_work_order_ordered_product_table_method.setting.func_get_data_table();
                    json_data.mfwo_info={
                        start_date_plan:new Date($(lparent_pane).find(lprefix_id+'_start_date_plan').val()).format('Y-m-d H:i:s'),
                        end_date_plan:new Date($(lparent_pane).find(lprefix_id+'_end_date_plan').val()).format('Y-m-d H:i:s'),
                    };
                    break;
                case 'view':
                    json_data.mf_work_order.mf_work_order_status = $(lparent_pane).find(lprefix_id+"_mf_work_order_status").select2('val');
                    json_data.mf_work_order.notes = $(lparent_pane).find(lprefix_id+"_notes").val();
                    json_data.mf_work_order.cancellation_reason = $(lparent_pane).find(lprefix_id+"_mf_work_order_cancellation_reason").val();
                    json_data.mfwo_info={
                        approver:$(lparent_pane).find(lprefix_id+'_approver').val(),
                        rejector:$(lparent_pane).find(lprefix_id+'_rejector').val(),
                    };
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'mf_work_order_add';
                    break;
                case 'view':
                    lajax_method = $(lparent_pane).find(lprefix_id+'_mf_work_order_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+mf_work_order_id;
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(mf_work_order_parent_pane).find('#mf_work_order_id').val(result.trans_id);
                if(mf_work_order_view_url !==''){
                    var url = mf_work_order_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    mf_work_order_after_submit();
                }
            }
        },
        module_type_get:function(){
            var lparent_pane = mf_work_order_parent_pane;
            var lprefix_id = mf_work_order_component_prefix_id;
            return $(lparent_pane).find(lprefix_id+'_type').select2('val');
        }
        
    }

    var mf_work_order_bind_event = function(){
        var parent_pane = mf_work_order_parent_pane;
        var lprefix_id = mf_work_order_component_prefix_id;
        $(parent_pane).find('#mf_work_order_submit').off('click');
        $(parent_pane).find('#mf_work_order_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = mf_work_order_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                mf_work_order_methods.submit();
            });
            $(mf_work_order_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $(parent_pane).find(lprefix_id+'_type').on('change',function(e){
            mf_work_order_methods.show_hide();
            mf_work_order_methods.enable_disable();
            mf_work_order_methods.reset_all();
        });
        mf_work_order_ordered_product_bind_event();
        
        $(parent_pane).find(lprefix_id+'_mf_work_order_status').on('change',function(e){
            var lparent_pane = mf_work_order_parent_pane;
            var lprefix_id = mf_work_order_component_prefix_id;
            var lstatus = $(this).select2('val');
            
            $(lparent_pane).find(lprefix_id+'_approver').closest('div [class*="form-group"]').hide();
            $(lparent_pane).find(lprefix_id+'_approved_date').closest('div [class*="form-group"]').hide();
            $(lparent_pane).find(lprefix_id+'_rejector').closest('div [class*="form-group"]').hide();
            $(lparent_pane).find(lprefix_id+'_rejected_date').closest('div [class*="form-group"]').hide();
            
            if(lstatus === 'approved'){
                $(lparent_pane).find(lprefix_id+'_approver').closest('div [class*="form-group"]').show();
                $(lparent_pane).find(lprefix_id+'_approved_date').closest('div [class*="form-group"]').show();
            }
            else if (lstatus === 'rejected'){
                $(lparent_pane).find(lprefix_id+'_rejector').closest('div [class*="form-group"]').show();
                $(lparent_pane).find(lprefix_id+'_rejected_date').closest('div [class*="form-group"]').show();
            }
            
        });
    }
    
    var mf_work_order_components_prepare= function(){
        
        var method = $(mf_work_order_parent_pane).find("#mf_work_order_method").val();
        
        
        var mf_work_order_data_set = function(){
            var lparent_pane = mf_work_order_parent_pane;
            var lprefix_id = mf_work_order_component_prefix_id;
            switch(method){
                case "add":
                    var ldata_list = JSON.parse(atob($(lparent_pane).find(lprefix_id+'_type').attr('select2_data_list')));
                    $(lparent_pane).find(lprefix_id+'_type').select2('data',ldata_list[0]).change();
                    break;
                case "view":
                    var mf_work_order_id = $(mf_work_order_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:mf_work_order_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(mf_work_order_data_support_url+"mf_work_order_get",json_data).response;
                    if(lresponse != []){
                        var lmf_work_order = lresponse.mf_work_order;
                        var lmfwo_info = lresponse.mfwo_info;
                        var lmfwo_ordered_product = lresponse.mfwo_ordered_product;
                        var lmf_work_order_type = lmf_work_order.mf_work_order_type;
                        
                        $(lparent_pane).find(lprefix_id+'_type').select2('data',{id:lmf_work_order.mf_work_order_type,text:lmf_work_order.mf_work_order_type_text}).change();
                        $(lparent_pane).find(lprefix_id+'_code').val(lmf_work_order.code);                        
                        $(lparent_pane).find(lprefix_id+'_mf_work_order_date').val(new Date(lmf_work_order.mf_work_order_date).format('F d, Y H:i'));  
                        $(lparent_pane).find(lprefix_id+'_start_date_plan').val(new Date(lmfwo_info.start_date_plan).format('F d, Y H:i'));  
                        $(lparent_pane).find(lprefix_id+'_end_date_plan').val(new Date(lmfwo_info.end_date_plan).format('F d, Y H:i'));  
                        
                        $(lparent_pane).find(lprefix_id+'_approver').val(lmfwo_info.approver);
                        if(lmfwo_info.approved_date === null) lmfwo_info.approved_date = new Date();
                        $(lparent_pane).find(lprefix_id+'_approved_date').val(new Date(lmfwo_info.approved_date).format('F d, Y H:i'));
                        
                        $(lparent_pane).find(lprefix_id+'_rejector').val(lmfwo_info.rejector);
                        if(lmfwo_info.rejected_date === null) lmfwo_info.rejected_date = new Date();
                        $(lparent_pane).find(lprefix_id+'_rejected_date').val(new Date(lmfwo_info.rejected_date).format('F d, Y H:i'));
                                            
                        $(lparent_pane).find(lprefix_id+'_mf_work_order_status')
                            .select2('data',{id:lmf_work_order.mf_work_order_status
                                ,text:lmf_work_order.mf_work_order_status_text}).change();
                            
                        $(lparent_pane).find(lprefix_id+'_mf_work_order_status')
                            .select2({data:lresponse.mf_work_order_status_list});
                            
                        $(lparent_pane).find(lprefix_id+'_mf_work_order_cancellation_reason').val(lmf_work_order.cancellation_reason);
                            
                        if(lmf_work_order.mf_work_order_status === 'initialized'){
                            $(lparent_pane).find(lprefix_id+'_approver').prop('disabled',false);
                            $(lparent_pane).find(lprefix_id+'_rejector').prop('disabled',false);
                        }
                                                
                        mf_work_order_ordered_product_table_method.reset();
                        mf_work_order_ordered_product_table_method.head_generate();

                        $.each(lmfwo_ordered_product,function(lidx, lrow){
                             mf_work_order_ordered_product_table_method.input_row_generate(lrow);                                
                        });
                        
                        $(lparent_pane).find(lprefix_id+'_notes').val(lmf_work_order.notes);
                        
                    };
                    
                    
                    break;            
            }
        }
    
        mf_work_order_methods.enable_disable();
        mf_work_order_methods.show_hide();
        mf_work_order_data_set();
    }
    
</script>