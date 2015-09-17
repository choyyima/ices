<script>
    var comp_mail_manager_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var comp_mail_manager_ajax_url = null;
    var comp_mail_manager_index_url = null;
    var comp_mail_manager_view_url = null;
    var comp_mail_manager_window_scroll = null;
    var comp_mail_manager_data_support_url = null;
    var comp_mail_manager_common_ajax_listener = null;
    var comp_mail_manager_component_prefix_id = '';
    
    var comp_mail_manager_init = function(){
        var parent_pane = comp_mail_manager_parent_pane;

        comp_mail_manager_ajax_url = '<?php echo $ajax_url ?>';
        comp_mail_manager_index_url = '<?php echo $index_url ?>';
        comp_mail_manager_view_url = '<?php echo $view_url ?>';
        comp_mail_manager_window_scroll = '<?php echo $window_scroll; ?>';
        comp_mail_manager_data_support_url = '<?php echo $data_support_url; ?>';
        comp_mail_manager_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        comp_mail_manager_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var comp_mail_manager_after_submit = function(){

    }
    
    var comp_mail_manager_data ={
        current_status:'',
        sir_exists:false,
    }
    
    var comp_mail_manager_methods = {
        
        hide_all:function(){
            var lparent_pane = comp_mail_manager_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = comp_mail_manager_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        
        show_hide: function(){
            var lparent_pane = comp_mail_manager_parent_pane;
            var lprefix_id = comp_mail_manager_component_prefix_id;
            var lmethod = $(lparent_pane).find('#comp_mail_manager_method').val();            
            var lcomp_mail_manager_type = comp_mail_manager_methods.module_type_get();
            var lstatus = $(lparent_pane).find(lprefix_id+'_comp_mail_manager_status').select2('val');
            comp_mail_manager_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_start_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_comp_mail_manager_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_btn_set_component_product').show();
                    $(lparent_pane).find(lprefix_id+'_component_product_table').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_worker_table').closest('.form-group').show();
                    
                    if(comp_mail_manager_data.current_status === 'process'){
                        
                        if (lstatus === 'process'){
                            if(comp_mail_manager_data.sir_exists){
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
                    else if ($.inArray(comp_mail_manager_data.current_status,['done','X'] !== -1)){
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
            var lparent_pane = comp_mail_manager_parent_pane;
            var lprefix_id = comp_mail_manager_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            comp_mail_manager_methods.disable_all();
            
            switch(lmethod){
                case "add":
                    break;
                case 'view':
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = comp_mail_manager_parent_pane;
            var lprefix_id = comp_mail_manager_component_prefix_id;
        },
        submit:function(){
            var lparent_pane = comp_mail_manager_parent_pane;
            var lprefix_id = comp_mail_manager_component_prefix_id;
            var ajax_url = comp_mail_manager_index_url;
            var lmethod = $(lparent_pane).find("#comp_mail_manager_method").val();
            var comp_mail_manager_id = $(lparent_pane).find("#comp_mail_manager_id").val();        
            var lmodule_type = comp_mail_manager_methods.module_type_get();
            var json_data = {
                ajax_post:true,
                comp_mail_manager:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                    break;
                case 'view':
                    json_data.comp_mail_manager.company_mail = comp_mail_manager_mail_list_table_method.setting.func_get_data_table();
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    break;
                case 'view':
                    lajax_method = 'comp_mail_manager_save';
                    break;
            }
            ajax_url +=lajax_method+'/'+comp_mail_manager_id;
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(comp_mail_manager_parent_pane).find('#comp_mail_manager_id').val(result.trans_id);
                if(comp_mail_manager_view_url !==''){
                    var url = comp_mail_manager_index_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    comp_mail_manager_after_submit();
                }
            }
        },
        module_type_get:function(){
            var lparent_pane = comp_mail_manager_parent_pane;
            var lprefix_id = comp_mail_manager_component_prefix_id;
            return $(lparent_pane).find(lprefix_id+'_type').val();
        },
        
        
    }

    var comp_mail_manager_bind_event = function(){
        var lparent_pane = comp_mail_manager_parent_pane;
        var lprefix_id = comp_mail_manager_component_prefix_id;
        $(lparent_pane).find('#comp_mail_manager_submit').off('click');
        $(lparent_pane).find('#comp_mail_manager_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = comp_mail_manager_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                comp_mail_manager_methods.submit();
            });
            $(comp_mail_manager_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        comp_mail_manager_mail_list_bind_event();
        
        
    }
    
    var comp_mail_manager_components_prepare= function(){
        
        var method = $(comp_mail_manager_parent_pane).find("#comp_mail_manager_method").val();
        
        
        var comp_mail_manager_data_set = function(){
            var lparent_pane = comp_mail_manager_parent_pane;
            var lprefix_id = comp_mail_manager_component_prefix_id;
            switch(method){
                case "add":
                    comp_mail_manager_methods.reset_all();
                    break;
                case "view":
                   
                    var comp_mail_manager_id = $(comp_mail_manager_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:comp_mail_manager_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(comp_mail_manager_data_support_url+"comp_mail_manager_get",json_data).response;
                    if(lresponse != []){
                        var lcomp_mail_manager = lresponse.comp_mail_manager;
                        comp_mail_manager_mail_list_table_method.reset();
                        comp_mail_manager_mail_list_table_method.head_generate();
                        $.each(lcomp_mail_manager, function(li, lrow){
                            comp_mail_manager_mail_list_table_method.input_row_generate(lrow);
                        });
                        
                    };
                    break;            
            }
        }
    
        comp_mail_manager_methods.enable_disable();
        comp_mail_manager_methods.show_hide();
        comp_mail_manager_data_set();
    }
    
</script>