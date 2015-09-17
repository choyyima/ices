<script>
    $(document).ready(function(){
        
        var show_user_profile = function(iUser_Info){

            if(iUser_Info.user_id !== ''){
                $('[fullname]').html(iUser_Info.name);
                $('li.dropdown.user.user-menu').show();
            }
        }
        
        var ices_data_support = "<?php echo get_instance()->config->base_url().'ices/home/data_support';?>";
        var sign_in_url = "<?php echo get_instance()->config->base_url().'ices/sign_in/';?>";
        $('a[open_app]').on('click',function(e){
            e.preventDefault();
        });
        $('a[open_app]').on('click',function(e){
            e.preventDefault();
            var lajax_url = ices_data_support+'/is_auth';
            var ljson_data = {app_name:$(this).attr('app_name')};
            var lresponse = ICES_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data).response;
            if(!lresponse.is_auth){
                $('#modal_sign_in').modal('show');
                $('#modal_sign_in button').attr('app_name',$(this).attr('app_name'));
                var lmargin_top = 100 ;
                $("#modal_sign_in .modal-dialog").css('margin-top',lmargin_top+'px');
                
            }
            else{
                window.location = lresponse.app_url;
            }
        });
        $('#modal_sign_in_btn_close').on('click',function(e){
            e.preventDefault();
           $('#modal_sign_in').modal('hide'); 
        });
        
        $('#modal_sign_in button').off('click');
        $('#modal_sign_in button').on('click',function(e){
            e.preventDefault();
            var lajax_url = sign_in_url+'';
            var ljson_data = {app_name:$(this).attr('app_name'),username:$('input[name="username"]').val(),password:$('input[name="password"]').val()};
            var lresult = ICES_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
            $('#login_msg')[0].innerHTML = lresult.msg;
            if(lresult.response.username_pwd_match === 1){
                show_user_profile(lresult.response.user_info);
                
                if(lresult.success === 1){
                    window.location = lresult.response.app_url;
                }
            }
        });
        
        
        
        show_user_profile(JSON.parse('<?php echo json_encode(User_Info::get());?>'));
        
    });
</script>