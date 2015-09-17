<script>
$(function(){
    
    var lprefix_id  = '#app_access_time';
    $(lprefix_id+"_check_all").on('ifChecked',function(){
        
        $('input[id*="app_access_time_"][type=checkbox]').iCheck('check') ;
        
    });
    
    $(lprefix_id+"_check_all").on('ifUnchecked',function(){
        $('input[id*="app_access_time_"][type=checkbox]').iCheck('uncheck') ;
    });
    
    $('body').off('click','#app_access_time_save');
    
    var getSelectedAppAccessTime = function(){
       var checkboxes = $('input[id*="app_access_time_"][type=checkbox]') ;
       var result = [];
       $.each(checkboxes,function(key,val){
           if(val.id.indexOf('app_access_time')!= -1){
                var controller = $('#'+val.id);
                if(controller.is(":checked")){
                    result.push(val);
                }
           }
       });
       return result;
    }
    
    $('body').on('click','#app_access_time_save',function(){
        $(this).addClass('disabled');
        var selectedAppAccessTime = getSelectedAppAccessTime();
        var data = {
            message_session:true,
            u_group_id:"<?php echo $u_group_id;?>",
            app_access_time:[]
        };
        selectedAppAccessTime.forEach(function(val){
            data.app_access_time.push(val.id);
        });
        
        var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo $app_access_time_ajax_url ?>',data);
        if(lresult.success == 1){            
            window.location.href = '';
        }
        
        
        $(window).scrollTop(0);        
        $(this).removeClass('disabled');    
    });

});


</script>