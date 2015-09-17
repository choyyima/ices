<script>
    var bom_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var bom_ajax_url = null;
    var bom_index_url = null;
    var bom_view_url = null;
    var bom_window_scroll = null;
    var bom_data_support_url = null;
    var bom_common_ajax_listener = null;
    var bom_component_prefix_id = '';
    
    var bom_init = function(){
        var parent_pane = bom_parent_pane;

        bom_ajax_url = '<?php echo $ajax_url ?>';
        bom_index_url = '<?php echo $index_url ?>';
        bom_view_url = '<?php echo $view_url ?>';
        bom_window_scroll = '<?php echo $window_scroll; ?>';
        bom_data_support_url = '<?php echo $data_support_url; ?>';
        bom_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        bom_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var bom_after_submit = function(){

    }
    
    var bom_methods = {
        
        hide_all:function(){
            var lparent_pane = bom_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = bom_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            
            $.each(lcomponents,function(key, val){
                $(val).prop('disabled',true);
            });
        },
        security_set:function(){
            var lparent_pane = bom_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = bom_methods.status_label_get();
            
            if($(lparent_pane).find('#bom_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('bom',lstatus_label).result){
                lsubmit_show = false;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#bom_submit').show();
                $(lparent_pane).find('#bom_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#bom_submit').hide();
                $(lparent_pane).find('#bom_notes').prop('disabled',true);
            }    
        },
        submit:function(){
            var parent_pane = bom_parent_pane;
            var lprefix_id = bom_component_prefix_id;
            var ajax_url = bom_index_url;
            var lmethod = $(parent_pane).find("#bom_method").val();
            var bom_id = $(parent_pane).find("#bom_id").val();        
            var lbom_type = $(parent_pane).find(lprefix_id+"_type").select2('val');
            var json_data = {
                ajax_post:true,
                bom:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                case 'view':
                    json_data.bom.id = bom_id;
                    json_data.bom.code = $(parent_pane).find(lprefix_id+"_code").val();
                    json_data.bom.name = $(parent_pane).find(lprefix_id+"_name").val();
                    json_data.bom.notes = $(parent_pane).find(lprefix_id+"_notes").val();
                    json_data.bom.bom_type = lbom_type;
                    switch(lbom_type){
                        case 'normal':
                            json_data.bom_result_product = bom_result_product_table_method.setting.func_get_data_table();
                            json_data.bom_component_product = bom_component_product_table_method.setting.func_get_data_table();
                            break;
                    }
                    
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'bom_add';
                    break;
                case 'view':
                    lajax_method = $(parent_pane).find(lprefix_id+'_bom_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+bom_id;
            console.log(json_data);
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(bom_parent_pane).find('#bom_id').val(result.trans_id);
                if(bom_view_url !==''){
                    var url = bom_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    bom_after_submit();
                }
            }
        },
        show_hide: function(){
            var lparent_pane = bom_parent_pane;
            var lprefix_id = bom_component_prefix_id;
            var lmethod = $(lparent_pane).find('#bom_method').val();            
            var lbom_type = $(lparent_pane).find(lprefix_id+'_type').select2('val');
            bom_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_type').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_bom_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    switch(lbom_type){
                        case 'normal':
                            $(lparent_pane).find(lprefix_id+'_result_product_table').closest('div [class*="form-group"]').show();
                            $(lparent_pane).find(lprefix_id+'_component_product_table').closest('div [class*="form-group"]').show();
                            break;
                    }
                    break;
            }
        },        
        enable_disable: function(){
            var lparent_pane = bom_parent_pane;
            var lmethod = $(lparent_pane).find('#bom_method').val();  
            var lprefix_id = bom_component_prefix_id;
            bom_methods.disable_all();
            
            switch(lmethod){
                case "add":
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_type').select2('enable');
                    $(lparent_pane).find(lprefix_id+"_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = bom_parent_pane;
            var lprefix_id = bom_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            $(lparent_pane).find(lprefix_id+'_name').val('');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'bom'}).response;

            $(lparent_pane).find(lprefix_id+'_bom_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];

            bom_result_product_table_method.reset();
            bom_result_product_table_method.head_generate();
            bom_result_product_table_method.input_row_generate({});
            
            bom_component_product_table_method.reset();
            bom_component_product_table_method.head_generate();
            bom_component_product_table_method.input_row_generate({});

        },
        
    }

    var bom_bind_event = function(){
        var parent_pane = bom_parent_pane;
        var lprefix_id = bom_component_prefix_id;
        $(parent_pane).find('#bom_submit').off('click');
        $(parent_pane).find('#bom_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = bom_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                bom_methods.submit();
            });
            $(bom_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $(parent_pane).find(lprefix_id+'_type').on('change',function(e){
            bom_methods.show_hide();
            bom_methods.enable_disable();
            bom_methods.reset_all();
        });
        bom_result_product_bind_event();
        bom_component_product_bind_event();
        
    }
    
    var bom_components_prepare= function(){
        
        var method = $(bom_parent_pane).find("#bom_method").val();
        
        
        var bom_data_set = function(){
            var lparent_pane = bom_parent_pane;
            var lprefix_id = bom_component_prefix_id;
            switch(method){
                case "add":
                    bom_methods.reset_all();
                    
                    break;
                case "view":
                    bom_methods.reset_all();
                    
                    var bom_id = $(bom_parent_pane).find("#bom_id").val();
                    var json_data={data:bom_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(bom_data_support_url+"bom_get",json_data).response;
                    if(lresponse != []){
                        var lbom = lresponse.bom;
                        var lbom_type = lbom.bom_type;
                        $(lparent_pane).find(lprefix_id+'_type').select2('data',{id:lbom.bom_type,text:lbom.bom_type_text}).change();
                        $(lparent_pane).find(lprefix_id+'_code').val(lbom.code);                        
                        $(lparent_pane).find(lprefix_id+'_name').val(lbom.name);
                        
                        
                        $(lparent_pane).find(lprefix_id+'_bom_status')
                            .select2('data',{id:lbom.bom_status
                                ,text:lbom.bom_status_text}).change();
                            
                        $(lparent_pane).find(lprefix_id+'_bom_status')
                            .select2({data:lresponse.bom_status_list});
                        
                        if(lbom_type === 'normal'){
                            bom_result_product_table_method.reset();
                            bom_result_product_table_method.head_generate();
                            bom_result_product_table_method.input_row_generate(lresponse.bom_result_product);
                            
                            bom_component_product_table_method.reset();
                            bom_component_product_table_method.head_generate();

                            $.each(lresponse.bom_component_product,function(lidx, lrow){
                                 bom_component_product_table_method.input_row_generate(lrow);                                
                            });
                            bom_component_product_table_method.input_row_generate({});
                            
                            
                        }
                        
                    };
                    
                    
                    break;            
            }
        }
    
        bom_methods.enable_disable();
        bom_methods.show_hide();
        bom_data_set();
    }
    
</script>