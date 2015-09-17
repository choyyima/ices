
    <script>
        var modal_confirmation_cancel_module_prefix_id = null;
        var modal_confirmation_cancel_primary_data_key = null;
        var modal_confirmation_cancel_module_status_field = null;
        var modal_confirmation_cancel_input_select_id = null;
        var modal_confirmation_cancel_parent = null;
        var modal_confirmation_cancel_view_url = null;
        var modal_confirmation_cancel_ajax_url = null;
        
        var modal_confirmation_cancel_after_submit = function(){
            
        }
        
        $('#modal_confirmation_cancel_btn_submit').on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');            
            modal_confirmation_cancel_methods.submit();
            $(modal_confirmation_cancel_parent).scrollTop(0); 
            $(modal_confirmation_cancel_parent).not($(this)).css('z-index',500);
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $('#modal_confirmation_cancel').on('show.bs.modal',function(){
            $(modal_confirmation_cancel_parent).not($(this)).css('z-index',500);
            $('#modal_confirmation_cancel_cancellation_reason').val('');
            setTimeout(function(){
                $('#modal_confirmation_cancel_btn_submit').focus();
            },500);
        });
        
        $('#modal_confirmation_cancel').on('hidden.bs.modal',function(){
            modal_confirmation_cancel_view_url = null;
            modal_confirmation_cancel_ajax_url = null;
            $('#modal_confirmation_cancel_btn_submit').on('click',function(){
                $('#modal_confirmation_cancel').modal('hide');
            });
            $(modal_confirmation_cancel_parent).not($(this)).css('z-index','');
            modal_confirmation_cancel_parent = null;
            
            var linput_select_status = $("#"+modal_confirmation_cancel_input_select_id);
            if($(linput_select_status).length>0){
                $(linput_select_status).select2('val',$(linput_select_status).attr('old_val')).change();
                $(linput_select_status).attr('old_val',"X");
            }
        });
        
        modal_confirmation_cancel_methods={
            submit:function(){

                var lajax_url = modal_confirmation_cancel_ajax_url;                
                var json_data = {
                    ajax_post:true,
                    message_session:true,
                };
                var lprimary_data_key = modal_confirmation_cancel_primary_data_key;
                var lmodule_status_field = modal_confirmation_cancel_module_status_field;
                var linput_select_status = 
                json_data[lprimary_data_key] = {
                    cancellation_reason: $('#modal_confirmation_cancel_cancellation_reason').val()
                }
                
                json_data[lprimary_data_key][lmodule_status_field] = "X";
                    
                var result = null;
                result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

                if(result.success ===1){
                    if(modal_confirmation_cancel_view_url !==''){
                        var url = modal_confirmation_cancel_view_url+result.trans_id;
                        window.location.href=url;
                    }
                    else{
                        modal_confirmation_cancel_after_submit();
                    }
                    
                }
            }
        }
        
        
    </script>