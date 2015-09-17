<script>
    
    $("#controller_check_all").on('ifChecked',function(){

       var checkboxes = $("[type=checkbox]") ;
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_controller')!= -1){
                $('#'+val.id).iCheck('check');
           }
       });        
    });
    
    $("#controller_check_all").on('ifUnchecked',function(){

       var checkboxes = $("[type=checkbox]") ;
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_controller')!= -1){
                $('#'+val.id).iCheck('uncheck');
           }
       });        
    });
    
    $('body').off('click','#controller_save');
    
    var getSelectedController = function(){
       var checkboxes = $("[type=checkbox]") ;
       var result = [];
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_controller')!= -1){
                var controller = $('#'+val.id);
                if(controller.is(":checked")){
                    result.push(val);
                }
           }
       });
       return result;
    }
    
    $('body').on('click','#controller_save',function(){
        $(this).addClass('disabled');
        var selectedController = getSelectedController();
        var data = {
            u_group_id:"<?php echo $u_group_id;?>",
            controller:[]
        };
        selectedController.forEach(function(val){
            data.controller.push(val.id);
        });
        /*
         $.ajax({
            url:"",
            dataType: "json",
            type: "POST",
            data :JSON.stringify(data),
            global: false,
            async:false,
            cache: false,
            success: function(data) {
                if(data.success == 1){
                    APP_MESSAGE.set('success',['Data has been updated successfully']);
                }
                else{
                    APP_MESSAGE.set('error',data.msg);
                }
            },
            error:function(xhr, status, error){                                    
                APP_MESSAGE.set('error',[error]);
            }
        });
        */
        var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo $controller_ajax_url ?>',data);
        if(lresult.success == 1){
            APP_MESSAGE.set('success',['Data has been updated successfully']);
        }
        
        $(window).scrollTop(0);        
        $(this).removeClass('disabled');    
    });
    


</script>