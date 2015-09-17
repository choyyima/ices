<script>
    var refill_subcon_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var refill_subcon_ajax_url = null;
    var refill_subcon_index_url = null;
    var refill_subcon_view_url = null;
    var refill_subcon_window_scroll = null;
    var refill_subcon_data_support_url = null;
    var refill_subcon_common_ajax_listener = null;
    var refill_subcon_component_prefix_id = '';
    
    var refill_subcon_init = function(){
        var parent_pane = refill_subcon_parent_pane;

        refill_subcon_ajax_url = '<?php echo $ajax_url ?>';
        refill_subcon_index_url = '<?php echo $index_url ?>';
        refill_subcon_view_url = '<?php echo $view_url ?>';
        refill_subcon_window_scroll = '<?php echo $window_scroll; ?>';
        refill_subcon_data_support_url = '<?php echo $data_support_url; ?>';
        refill_subcon_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        refill_subcon_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var refill_subcon_after_submit = function(){

    }
    
    var refill_subcon_methods = {
        status_label_get:function(){
            var parent_pane = refill_subcon_parent_pane;
            return $($(parent_pane).find('#refill_subcon_refill_subcon_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#refill_subcon_refill_subcon_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#refill_subcon_refill_subcon_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lrefill_subcon_id = $('#refill_subcon_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(refill_subcon_data_support_url+'refill_subcon_current_status/',{data:lrefill_subcon_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = refill_subcon_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = refill_subcon_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            
            $.each(lcomponents,function(key, val){
                $(val).prop('disabled',true);
            });
        },
        security_set:function(){
            var lparent_pane = refill_subcon_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = refill_subcon_methods.status_label_get();
            
            if($(lparent_pane).find('#refill_subcon_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('refill_subcon',lstatus_label).result){
                lsubmit_show = false;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#refill_subcon_submit').show();
                $(lparent_pane).find('#refill_subcon_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#refill_subcon_submit').hide();
                $(lparent_pane).find('#refill_subcon_notes').prop('disabled',true);
            }    
        },
        submit:function(){
            var parent_pane = refill_subcon_parent_pane;
            var lprefix_id = refill_subcon_component_prefix_id;
            var ajax_url = refill_subcon_index_url;
            var lmethod = $(parent_pane).find("#refill_subcon_method").val();
            var refill_subcon_id = $(parent_pane).find("#refill_subcon_id").val();        
            var json_data = {
                ajax_post:true,
                refill_subcon:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                case 'view':
                    json_data.refill_subcon.id = refill_subcon_id;
                    json_data.refill_subcon.code = $(parent_pane).find("#refill_subcon_code").val();
                    json_data.refill_subcon.name = $(parent_pane).find("#refill_subcon_name").val();
                    json_data.refill_subcon.address = $(parent_pane).find("#refill_subcon_address").val();
                    json_data.refill_subcon.city = $(parent_pane).find("#refill_subcon_city").val();
                    json_data.refill_subcon.country = $(parent_pane).find("#refill_subcon_country").val();
                    json_data.refill_subcon.phone = $(parent_pane).find("#refill_subcon_phone").val();
                    json_data.refill_subcon.phone2 = $(parent_pane).find("#refill_subcon_phone2").val();
                    json_data.refill_subcon.phone3 = $(parent_pane).find("#refill_subcon_phone3").val();
                    json_data.refill_subcon.bb_pin = $(parent_pane).find("#refill_subcon_bb_pin").val();
                    json_data.refill_subcon.email = $(parent_pane).find("#refill_subcon_email").val();
                    json_data.refill_subcon.notes = $(parent_pane).find("#refill_subcon_notes").val();
                    json_data.refill_subcon.refill_subcon_status = $(parent_pane).find("#refill_subcon_refill_subcon_status").val();
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'refill_subcon_add';
                    break;
                case 'view':
                    lajax_method = $(parent_pane).find(lprefix_id+'_refill_subcon_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+refill_subcon_id;

            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(refill_subcon_parent_pane).find('#refill_subcon_id').val(result.trans_id);
                if(refill_subcon_view_url !==''){
                    var url = refill_subcon_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    refill_subcon_after_submit();
                }
            }
        },
        show_hide: function(){
            var lparent_pane = refill_subcon_parent_pane;
            var lprefix_id = refill_subcon_component_prefix_id;
            var lmethod = $(lparent_pane).find('#refill_subcon_method').val();            
            refill_subcon_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_address').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_subcon_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_measurement_unit').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_phone').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_phone2').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_phone3').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_email').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_bb_pin').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_address').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_city').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_country').closest('div [class*="form-group"]').show();
                    break;
            }
        },        
        enable_disable: function(){
            var lparent_pane = refill_subcon_parent_pane;
            var lmethod = $(lparent_pane).find('#refill_subcon_method').val();  
            var lprefix_id = refill_subcon_component_prefix_id;
            refill_subcon_methods.disable_all();
            
            switch(lmethod){
                case "add":
                case 'view':
                    $(lparent_pane).find(lprefix_id+"_code").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_address").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_city").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_country").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_phone").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_phone2").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_phone3").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_bb_pin").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_email").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    
                    break;
            }
        },
        
    }

    var refill_subcon_bind_event = function(){
        var parent_pane = refill_subcon_parent_pane;
        
        $(parent_pane).find('#refill_subcon_submit').off('click');
        $(parent_pane).find('#refill_subcon_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = refill_subcon_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                refill_subcon_methods.submit();
            });
            $(refill_subcon_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        

    }
    
    var refill_subcon_components_prepare= function(){
        
        var method = $(refill_subcon_parent_pane).find("#refill_subcon_method").val();
        
        
        var refill_subcon_data_set = function(){
            var lparent_pane = refill_subcon_parent_pane;
            var lprefix_id = refill_subcon_component_prefix_id;
            switch(method){
                case "add":
                    $(lparent_pane).find("#refill_subcon_code").val("");
                    $(lparent_pane).find("#refill_subcon_name").val("");
                    $(lparent_pane).find("#refill_subcon_address").val("");
                    $(lparent_pane).find("#refill_subcon_city").val("");
                    $(lparent_pane).find("#refill_subcon_country").val("");
                    $(lparent_pane).find("#refill_subcon_phone").val("");
                    $(lparent_pane).find("#refill_subcon_phone2").val("");
                    $(lparent_pane).find("#refill_subcon_phone3").val("");
                    $(lparent_pane).find("#refill_subcon_bb_pin").val("");
                    $(lparent_pane).find("#refill_subcon_email").val("");
                    $(lparent_pane).find("#refill_subcon_notes").val("");
                    
                    APP_FORM.status.default_status_set('refill_subcon',
                        $(lparent_pane).find(lprefix_id+'_refill_subcon_status')
                    );            
                    break;
                case "view":
                    var refill_subcon_id = $(refill_subcon_parent_pane).find("#refill_subcon_id").val();
                    var json_data={data:refill_subcon_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(refill_subcon_data_support_url+"refill_subcon_get",json_data).response;
                    if(lresponse != []){
                        var lrefill_subcon = lresponse.refill_subcon;                    
                        $(lparent_pane).find("#refill_subcon_code").val(lrefill_subcon.code);
                        $(lparent_pane).find("#refill_subcon_name").val(lrefill_subcon.name);
                        $(lparent_pane).find("#refill_subcon_refill_subcon_status").select2("data",{id:lrefill_subcon.refill_subcon_status,text:lrefill_subcon.refill_subcon_status_text});
                        $(lparent_pane).find("#refill_subcon_address").val(lrefill_subcon.address);
                        $(lparent_pane).find("#refill_subcon_city").val(lrefill_subcon.city);
                        $(lparent_pane).find("#refill_subcon_country").val(lrefill_subcon.country);
                        $(lparent_pane).find("#refill_subcon_phone").val(lrefill_subcon.phone);
                        $(lparent_pane).find("#refill_subcon_phone2").val(lrefill_subcon.phone2);
                        $(lparent_pane).find("#refill_subcon_phone3").val(lrefill_subcon.phone3);
                        $(lparent_pane).find("#refill_subcon_bb_pin").val(lrefill_subcon.bb_pin);
                        $(lparent_pane).find("#refill_subcon_email").val(lrefill_subcon.email);
                        $(lparent_pane).find("#refill_subcon_notes").val(lrefill_subcon.notes);
                        
                        $(lparent_pane).find('#refill_subcon_refill_subcon_status')
                            .select2('data',{id:lrefill_subcon.refill_subcon_status
                                ,text:lrefill_subcon.refill_subcon_status_text}).change();

                        $(lparent_pane).find('#refill_subcon_refill_subcon_status')
                            .select2({data:lresponse.refill_subcon_status_list});
                    };
                    
                    json_data={data:refill_subcon_id};
                    
                    break;            
            }
        }
    
        refill_subcon_methods.enable_disable();
        refill_subcon_methods.show_hide();
        refill_subcon_data_set();
    }
    
</script>