<script>
    var mf_work_process_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var mf_work_process_ajax_url = null;
    var mf_work_process_index_url = null;
    var mf_work_process_view_url = null;
    var mf_work_process_window_scroll = null;
    var mf_work_process_data_support_url = null;
    var mf_work_process_common_ajax_listener = null;
    var mf_work_process_component_prefix_id = '';
    
    var mf_work_process_init = function(){
        var parent_pane = mf_work_process_parent_pane;

        mf_work_process_ajax_url = '<?php echo $ajax_url ?>';
        mf_work_process_index_url = '<?php echo $index_url ?>';
        mf_work_process_view_url = '<?php echo $view_url ?>';
        mf_work_process_window_scroll = '<?php echo $window_scroll; ?>';
        mf_work_process_data_support_url = '<?php echo $data_support_url; ?>';
        mf_work_process_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        mf_work_process_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var mf_work_process_after_submit = function(){

    }
    
    var mf_work_process_data ={
        current_status:'',
        sir_exists:false,
    }
    
    var mf_work_process_methods = {
        
        hide_all:function(){
            var lparent_pane = mf_work_process_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        
        show_hide: function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            var lmethod = $(lparent_pane).find('#mf_work_process_method').val();            
            var lmf_work_process_type = mf_work_process_methods.module_type_get();
            var lstatus = $(lparent_pane).find(lprefix_id+'_mf_work_process_status').select2('val');
            mf_work_process_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    if(lmf_work_process_type !== ''){
                        $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find(lprefix_id+'_start_date').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find(lprefix_id+'_mf_work_process_status').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find(lprefix_id+'_component_product_table').closest('.form-group').show();
                        $(lparent_pane).find(lprefix_id+'_worker_table').closest('.form-group').show();
                        $(lparent_pane).find(lprefix_id+'_btn_set_component_product').show();
                        $(lparent_pane).find(lprefix_id+'_sir').closest('.form-group').show();
                        $(lparent_pane).find(lprefix_id+'_warehouse').closest('.form-group').show();
                    }
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_start_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_mf_work_process_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_btn_set_component_product').show();
                    $(lparent_pane).find(lprefix_id+'_component_product_table').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_worker_table').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_warehouse').closest('.form-group').show();
                    
                    if(mf_work_process_data.current_status === 'process'){
                        
                        if (lstatus === 'process'){
                            if(mf_work_process_data.sir_exists){
                                $(lparent_pane).find(lprefix_id+'_sir').closest('.form-group').show();
                            }
                        }
                        else if(lstatus === 'done'){
                            $(lparent_pane).find(lprefix_id+'_sir').closest('.form-group').show();
                            $(lparent_pane).find(lprefix_id+'_checker').closest('.form-group').show();
                            $(lparent_pane).find(lprefix_id+'_checker').prop('disabled',false);
                            $(lparent_pane).find(lprefix_id+'_result_product_table').closest('.form-group').show();
                            $(lparent_pane).find(lprefix_id+'_scrap_product_table').closest('.form-group').show();
                            $(lparent_pane).find(lprefix_id+'_end_date').closest('div [class*="form-group"]').show();

                        }

                    }
                    else if ($.inArray(mf_work_process_data.current_status,['done','X'] !== -1)){
                        $(lparent_pane).find(lprefix_id+'_end_date').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find(lprefix_id+'_result_product_table').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find(lprefix_id+'_scrap_product_table').closest('div [class*="form-group"]').show();
                        
                    }

                    break;
                    
            }
            
            
            if(lmethod === 'view'){
                
            }
            
        },        
        enable_disable: function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            mf_work_process_methods.disable_all();
            
            switch(lmethod){
                case "add":
                    $(lparent_pane).find(lprefix_id+'_store').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_warehouse').select2('enable');
                    $(lparent_pane).find(lprefix_id+"_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+'_reference').select2('enable');
                    
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            $(lparent_pane).find(lprefix_id+'_name').val('');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'mf_work_process'}).response;

            $(lparent_pane).find(lprefix_id+'_mf_work_process_status')
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

            mf_work_process_component_product_table_method.reset();
            mf_work_process_component_product_table_method.head_generate();

            
            mf_work_process_worker_table_method.reset();
            mf_work_process_worker_table_method.head_generate();
            mf_work_process_worker_table_method.input_row_generate({});
            $(lparent_pane).find(lprefix_id+'_btn_set_component_product')[0].innerHTML = '<?php echo 'Set '.Lang::get(array('Component','Product')); ?>'
            $(lparent_pane).find(lprefix_id+'_btn_set_component_product').attr('data','[]');
           
        },
        submit:function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            var ajax_url = mf_work_process_index_url;
            var lmethod = $(lparent_pane).find("#mf_work_process_method").val();
            var mf_work_process_id = $(lparent_pane).find("#mf_work_process_id").val();        
            var lmodule_type = mf_work_process_methods.module_type_get();
            var json_data = {
                ajax_post:true,
                mf_work_process:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                    json_data.mf_work_process.store_id = $(lparent_pane).find(lprefix_id+"_store").select2('val');
                    json_data.mf_work_process.mf_work_process_type = lmodule_type;
                    json_data.mf_work_process.reference_id = $(lparent_pane).find(lprefix_id+'_reference').select2('val');
                    json_data.mf_work_process.notes = $(lparent_pane).find(lprefix_id+"_notes").val();
                    
                    json_data.mfwp_info={
                        sir_exists: ($(lparent_pane).find(lprefix_id+'_sir_checkbox').is(':checked'))?'1':'0',
                        warehouse_id: $(lparent_pane).find(lprefix_id+'_warehouse').select2('val'),
                    };
                    json_data.sir = {
                        creator: $(lparent_pane).find(lprefix_id+'_sir_creator').val(),
                        description: $(lparent_pane).find(lprefix_id+'_sir_description').val(),
                    }
                    json_data.mfwp_expected_result_product = JSON.parse(
                        $(lparent_pane).find(lprefix_id+'_btn_set_component_product').attr('data')
                    );
                    json_data.mfwp_component_product = mf_work_process_component_product_table_method.setting.func_get_data_table();
                    json_data.mfwp_worker = mf_work_process_worker_table_method.setting.func_get_data_table();
                    break;
                case 'view':
                    json_data.mf_work_process.mf_work_process_status = $(lparent_pane).find(lprefix_id+'_mf_work_process_status').select2('val');
                    json_data.mf_work_process.cancellation_reason = $(lparent_pane).find(lprefix_id+"_mf_work_process_cancellation_reason").val();
                    json_data.mf_work_process.notes = $(lparent_pane).find(lprefix_id+"_notes").val();
                    json_data.mfwp_info={
                        sir_exists: ($(lparent_pane).find(lprefix_id+'_sir_checkbox').is(':checked'))?'1':'0',
                    };
                    json_data.sir = {
                        creator: $(lparent_pane).find(lprefix_id+'_sir_creator').val(),
                        description: $(lparent_pane).find(lprefix_id+'_sir_description').val(),
                    }
                    json_data.mfwp_checker = {
                        name: $(lparent_pane).find(lprefix_id+"_checker").val()
                    };
                    json_data.mfwp_result_product =  mf_work_process_result_product_table_method.setting.func_get_data_table();
                    json_data.mfwp_scrap_product =  mf_work_process_scrap_product_table_method.setting.func_get_data_table();
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'mf_work_process_add';
                    break;
                case 'view':
                    lajax_method = $(lparent_pane).find(lprefix_id+'_mf_work_process_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+mf_work_process_id;
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(mf_work_process_parent_pane).find('#mf_work_process_id').val(result.trans_id);
                if(mf_work_process_view_url !==''){
                    var url = mf_work_process_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    mf_work_process_after_submit();
                }
            }
        },
        module_type_get:function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            return $(lparent_pane).find(lprefix_id+'_type').val();
        },
        reference_type_get:function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            var lresult = '';
            var lval = $(lparent_pane).find(lprefix_id+'_reference').select2('val');
            if(lval !== ''){
                var ldata = $(lparent_pane).find(lprefix_id+'_reference').select2('data');
                lresult = typeof ldata.reference_type !== 'undefined'? ldata.reference_type : '';
            }
            return lresult;
        }
        
    }

    var mf_work_process_bind_event = function(){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        $(lparent_pane).find('#mf_work_process_submit').off('click');
        $(lparent_pane).find('#mf_work_process_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = mf_work_process_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                mf_work_process_methods.submit();
            });
            $(mf_work_process_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $(lparent_pane).find(lprefix_id+'_reference').on('change',function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            
            var lref_id = $(this).select2('val');
            $(lparent_pane).find(lprefix_id+'_type').val('');                        
            $(lparent_pane).find(lprefix_id+'_reference_detail .extra_info').remove();
            
            $(lparent_pane).find(lprefix_id+'_btn_set_component_product').attr('data','[]');
            $(lparent_pane).find(lprefix_id+'_sir_checkbox').iCheck('uncheck');
            
            mf_work_process_component_product_table_method.reset();
            mf_work_process_component_product_table_method.head_generate();
            
            mf_work_process_worker_table_method.reset();
            mf_work_process_worker_table_method.head_generate();
            mf_work_process_worker_table_method.input_row_generate({});
            
            if(lref_id !== ''){
                var ldata = $(this).select2('data');
                $(lparent_pane).find(lprefix_id+'_type').val(ldata.reference_type);
                var lajax_url = mf_work_process_data_support_url+'reference_dependency_get/';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, {reference_type:ldata.reference_type,reference_id:ldata.id}).response;
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail'),lresponse.reference_detail,{reset:true});
                
            }
            mf_work_process_methods.show_hide();
            
        });
        
        $(lparent_pane).find(lprefix_id+'_type').on('change',function(e){
            mf_work_process_methods.show_hide();
            mf_work_process_methods.enable_disable();
            mf_work_process_methods.reset_all();
        });

        mf_work_process_result_product_bind_event();
        mf_work_process_scrap_product_bind_event();
        mf_work_process_expected_result_product_bind_event();
        mf_work_process_component_product_bind_event();
        mf_work_process_worker_bind_event();
        
        
        $(lparent_pane).find(lprefix_id+'_mf_work_process_status').on('change',function(e){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            var lstatus = $(this).select2('val');
            $(lparent_pane).find(lprefix_id+'_result_product_table').closest('.form-group').hide();
            $(lparent_pane).find(lprefix_id+'_checker').closest('.form-group').hide();
            $(lparent_pane).find(lprefix_id+'_checker').prop('disabled',true);
            $(lparent_pane).find(lprefix_id+'_scrap_product_table').closest('.form-group').hide();
            
            if(mf_work_process_data.current_status === 'process'){
                if(lstatus === 'done'){
                    $(lparent_pane).find(lprefix_id+'_end_date').val(APP_GENERATOR.CURR_DATETIME('','','F d, Y H:i'));
                    mf_work_process_result_product_methods.load_available_result_product();
                    mf_work_process_scrap_product_methods.load_available_scrap_product();
                }                
            }
            
            mf_work_process_methods.show_hide();
            
            
        });
        
        $(lparent_pane).find(lprefix_id+'_btn_set_component_product').off();
        $(lparent_pane).find(lprefix_id+'_btn_set_component_product').on('click',function(){
            mf_work_process_expected_result_product_methods.load_modal();
        });
        
        $(lparent_pane).find(lprefix_id+'_sir_checkbox').on('ifChecked',function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            
            if(lmethod === 'add'){
                var lrows = $(lparent_pane).find(lprefix_id+'_component_product_table tbody tr');
                $.each(lrows,function(li,lrow){
                    var lopt = {tr:lrow};
                    mf_work_process_component_product_table_method.components.trash_set(lopt);
                });
                mf_work_process_component_product_table_method.input_row_generate({});
            }
            else if (lmethod === 'view'){
                var lselected_status = $(lparent_pane).find(lprefix_id+'_mf_work_process_status').select2('val');
                if(mf_work_process_data.current_status === 'process'){
                    if(lselected_status === 'done'){
                        var lrows = $(lparent_pane).find(lprefix_id+'_result_product_table tbody tr');
                        $.each(lrows,function(li,lrow){
                            var lopt = {tr:lrow};
                            mf_work_process_result_product_table_method.components.trash_set(lopt);
                        });
                        mf_work_process_result_product_table_method.input_row_generate({});
                    }
                }
            }
        });
        
        $(lparent_pane).find(lprefix_id+'_sir_checkbox').on('ifUnchecked',function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            
            if(lmethod === 'add'){
                $(lparent_pane).find(lprefix_id+'_btn_set_component_product').attr('data','[]');
                mf_work_process_component_product_table_method.reset();
                mf_work_process_component_product_table_method.head_generate();
            }
            else if (lmethod === 'view'){
                var lselected_status = $(lparent_pane).find(lprefix_id+'_mf_work_process_status').select2('val');
                if(mf_work_process_data.current_status === 'process'){
                    if(lselected_status === 'done'){
                        mf_work_process_result_product_table_method.reset();
                        mf_work_process_result_product_table_method.head_generate();
                        mf_work_process_result_product_methods.load_available_result_product();
                    }
                }
            }

        });
        
    }
    
    var mf_work_process_components_prepare= function(){
        
        var method = $(mf_work_process_parent_pane).find("#mf_work_process_method").val();
        
        
        var mf_work_process_data_set = function(){
            var lparent_pane = mf_work_process_parent_pane;
            var lprefix_id = mf_work_process_component_prefix_id;
            switch(method){
                case "add":
                    mf_work_process_methods.reset_all();
                    break;
                case "view":
                    
                    $(lparent_pane).find(lprefix_id+'_btn_set_component_product')[0].innerHTML = '<?php echo Lang::get(array('Expected','Result','Product')); ?>'
                    
                    var mf_work_process_id = $(mf_work_process_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:mf_work_process_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(mf_work_process_data_support_url+"mf_work_process_get",json_data).response;
                    if(lresponse != []){

                        var lreference = lresponse.reference;
                        var lmf_work_process = lresponse.mf_work_process;
                        var lmfwp_info = lresponse.mfwp_info;
                        var lmfwp_worker = lresponse.mfwp_worker;
                        var lmf_work_process_type = lmf_work_process.mf_work_process_type;
                        var lmfwp_expected_result_product = lresponse.mfwp_expected_result_product;
                        var lmfwp_component_product = lresponse.mfwp_component_product;
                        var lmfwp_result_product = lresponse.mfwp_result_product;
                        var lmfwp_scrap_product = lresponse.mfwp_scrap_product;
                        var lsir = lresponse.sir;                        
                        
                        $(lparent_pane).find(lprefix_id+'_store').select2('data',{id:lmf_work_process.store_id, text:lmf_work_process.store_text});
                        $(lparent_pane).find(lprefix_id+'_reference').select2('data',lreference).change();
                        $(lparent_pane).find(lprefix_id+'_code').val(lmf_work_process.code);                        
                        $(lparent_pane).find(lprefix_id+'_start_date').val(new Date(lmfwp_info.start_date).format('F d, Y H:i'));  
                        $(lparent_pane).find(lprefix_id+'_end_date').val(lmfwp_info.end_date !== null?new Date(lmfwp_info.end_date).format('F d, Y H:i'):'');  
                        mf_work_process_data.current_status = lmf_work_process.mf_work_process_status;
                                  
                        mf_work_process_expected_result_product_table_method.reset();
                        mf_work_process_expected_result_product_table_method.head_generate();
                        $.each(lmfwp_expected_result_product, function(li, lrow){
                            mf_work_process_expected_result_product_table_method.input_row_generate(lrow);
                        });
                        
                        mf_work_process_worker_table_method.reset();
                        mf_work_process_worker_table_method.head_generate();
                        $.each(lmfwp_worker, function(li, lrow){
                            mf_work_process_worker_table_method.input_row_generate(lrow);
                        });
                        
                        mf_work_process_component_product_table_method.reset();
                        mf_work_process_component_product_table_method.head_generate();
                        $.each(lmfwp_component_product, function(li, lrow){
                            mf_work_process_component_product_table_method.input_row_generate(lrow);
                        });
                        
                        mf_work_process_result_product_table_method.reset();
                        mf_work_process_result_product_table_method.head_generate();
                        $.each(lmfwp_result_product, function(li, lrow){
                            mf_work_process_result_product_table_method.input_row_generate(lrow);
                        });
                        
                        mf_work_process_scrap_product_table_method.reset();
                        mf_work_process_scrap_product_table_method.head_generate();
                        $.each(lmfwp_scrap_product, function(li, lrow){
                            mf_work_process_scrap_product_table_method.input_row_generate(lrow);
                        });
                        
                        if(lmf_work_process.mf_work_process_status === 'process'){
                            
                        }
                        else if (lmf_work_process.mf_work_process_status === 'done'){

                        }
                        else if (lmf_work_process.mf_work_process_status === 'X'){
                            $(lparent_pane).find(lprefix_id+'_sir_checkbox').prop('disabled',true);
                        }
                        
                        if(lmfwp_info.sir_exists === '1'){
                            mf_work_process_data.sir_exists = true;
                            $(lparent_pane).find(lprefix_id+'_sir').closest('.form-group').show();
                            mf_work_process_sir_methods.load(lsir.id);
                            $(lparent_pane).find(lprefix_id+'_sir_checkbox').prop('disabled',true);
                        }
                        else{

                        }
                        
                        $(lparent_pane).find(lprefix_id+'_mf_work_process_status')
                            .select2('data',{id:lmf_work_process.mf_work_process_status
                                ,text:lmf_work_process.mf_work_process_status_text}).change();
                            
                        $(lparent_pane).find(lprefix_id+'_mf_work_process_status')
                            .select2({data:lresponse.mf_work_process_status_list});
                            
                        $(lparent_pane).find(lprefix_id+'_mf_work_process_cancellation_reason').val(lmf_work_process.cancellation_reason);
                        
                        
                    };
                    break;            
            }
        }
    
        mf_work_process_methods.enable_disable();
        mf_work_process_methods.show_hide();
        mf_work_process_data_set();
    }
    
</script>