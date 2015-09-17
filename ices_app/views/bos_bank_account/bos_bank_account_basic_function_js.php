<script>
    var bos_bank_account_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var bos_bank_account_ajax_url = null;
    var bos_bank_account_index_url = null;
    var bos_bank_account_view_url = null;
    var bos_bank_account_window_scroll = null;
    var bos_bank_account_data_support_url = null;
    var bos_bank_account_common_ajax_listener = null;
    var bos_bank_account_component_prefix_id = '';
    
    var bos_bank_account_init = function(){
        var parent_pane = bos_bank_account_parent_pane;

        bos_bank_account_ajax_url = '<?php echo $ajax_url ?>';
        bos_bank_account_index_url = '<?php echo $index_url ?>';
        bos_bank_account_view_url = '<?php echo $view_url ?>';
        bos_bank_account_window_scroll = '<?php echo $window_scroll; ?>';
        bos_bank_account_data_support_url = '<?php echo $data_support_url; ?>';
        bos_bank_account_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        bos_bank_account_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
    }

    var bos_bank_account_after_submit = function(){

    }
    
    var bos_bank_account_methods = {
        hide_all:function(){
            var lparent_pane = bos_bank_account_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = bos_bank_account_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            
            $.each(lcomponents,function(key, val){
                $(val).prop('disabled',true);
            });
        },
        submit:function(){
            var parent_pane = bos_bank_account_parent_pane;
            var ajax_url = bos_bank_account_index_url;
            var method = $(parent_pane).find("#bba_method").val();
            var bos_bank_account_id = $(parent_pane).find("#bba_id").val();        
            var json_data = {
                ajax_post:true,
                bos_bank_account:{},
                bos_bank_account_type:[],
                message_session:true
            };

            switch(method){
                case 'add':
                    json_data.bos_bank_account.code = $(parent_pane).find("#bba_code").val();
                    json_data.bos_bank_account.bank_name = $(parent_pane).find("#bba_bank_name").val();
                    json_data.bos_bank_account.account_number = $(parent_pane).find("#bba_account_number").val();
                    json_data.bos_bank_account.notes = $(parent_pane).find("#bba_notes").val();
                    ajax_url +='add/';
                    break;
                case 'view':
                    json_data.bos_bank_account.code = $(parent_pane).find("#bba_code").val();
                    json_data.bos_bank_account.bank_name = $(parent_pane).find("#bba_bank_name").val();
                    json_data.bos_bank_account.account_number = $(parent_pane).find("#bba_account_number").val();
                    json_data.bos_bank_account.notes = $(parent_pane).find("#bba_notes").val();
                    var lajax_method = $(parent_pane).find('#bba_bos_bank_account_status').select2('data').method;
                    ajax_url +=lajax_method+'/'+bos_bank_account_id;
                    break;
            }


            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(bos_bank_account_parent_pane).find('#bba_id').val(result.trans_id);
                if(bos_bank_account_view_url !==''){
                    var url = bos_bank_account_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    bos_bank_account_after_submit();
                }
            }
        }
    }

    var bos_bank_account_bind_event = function(){
        var parent_pane = bos_bank_account_parent_pane;
        var lprefix_id = bos_bank_account_component_prefix_id;
        
        $(parent_pane).find(lprefix_id+'_submit').off('click');
        APP_COMPONENT.button.submit.set($(parent_pane).find(lprefix_id+'_submit'),{
            parent_pane:parent_pane,
            module_method:bos_bank_account_methods
        });
        

    }
    
    var bos_bank_account_components_prepare= function(){
        var method = $(bos_bank_account_parent_pane).find("#bba_method").val();
        var lparent_pane = bos_bank_account_parent_pane;
        
        var bos_bank_account_data_set = function(){
            switch(method){
                case "add":
                    $(bos_bank_account_parent_pane).find('#bba_bos_bank_account_type_table').find('tbody').empty();
                    $(bos_bank_account_parent_pane).find("#bba_code").val("");
                    $(bos_bank_account_parent_pane).find("#bba_bank_name").val("");
                    $(bos_bank_account_parent_pane).find("#bba_account_number").val("");
                    $(bos_bank_account_parent_pane).find("#bba_notes").val("");
                    
                    APP_FORM.status.default_status_set('bos_bank_account',
                        $(lparent_pane).find('#bba_bos_bank_account_status')
                    );
                    break;
                case "view":
                    var bos_bank_account_id = $(bos_bank_account_parent_pane).find("#bba_id").val();
                    var json_data={data:bos_bank_account_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(bos_bank_account_data_support_url+"bos_bank_account_get",json_data);
                    var rs_bos_bank_account = lresult.response.bos_bank_account;
                    if(rs_bos_bank_account !== null){
                        $(bos_bank_account_parent_pane).find("#bba_code").val(rs_bos_bank_account.code);
                        $(bos_bank_account_parent_pane).find("#bba_bank_name").val(rs_bos_bank_account.bank_name);
                        $(bos_bank_account_parent_pane).find("#bba_bos_bank_account_status").select2("data",{id:rs_bos_bank_account.bos_bank_account_status,text:rs_bos_bank_account.bos_bank_account_status_text});
                        $(bos_bank_account_parent_pane).find("#bba_account_number").val(rs_bos_bank_account.account_number);
                        $(bos_bank_account_parent_pane).find("#bba_notes").val(rs_bos_bank_account.notes);
                        $(bos_bank_account_parent_pane).find("#bba_bos_bank_account_status").select2({data:lresult.response.bos_bank_account_status_list});
                        
                    };
                    
                    
                    break;            
            }
        }
    
        var bos_bank_account_components_enable_disable = function(){
            var lparent_pane = bos_bank_account_parent_pane;
            var lmethod = $(lparent_pane).find('#bba_method').val();    
            bos_bank_account_methods.disable_all();
            
            switch(method){
                case "add":
                case 'view':
                    
                    $(bos_bank_account_parent_pane).find("#bba_bank_name").prop("disabled",false);
                    $(bos_bank_account_parent_pane).find("#bba_account_number").prop("disabled",false);
                    $(bos_bank_account_parent_pane).find("#bba_notes").prop("disabled",false);
                    break;
            }
        }
        
        var bos_bank_account_components_show_hide = function(){
            var lparent_pane = bos_bank_account_parent_pane;
            var lmethod = $(lparent_pane).find('#bba_method').val();
            bos_bank_account_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find('#bba_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#bba_bank_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#bba_account_number').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#bba_bos_bank_account_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#bba_notes').closest('div [class*="form-group"]').show();
                    break;
            }
        }
        
        bos_bank_account_components_show_hide();
        bos_bank_account_components_enable_disable();
        bos_bank_account_data_set();
    }
    
</script>