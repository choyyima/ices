<script>
    
    $("#helper_check_all").on('ifChecked',function(){

       var checkboxes = $("[type=checkbox]") ;
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_helper')!= -1){
                $('#'+val.id).iCheck('check');
           }
       });        
    });
    
    $("#helper_check_all").on('ifUnchecked',function(){

       var checkboxes = $("[type=checkbox]") ;
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_helper')!= -1){
                $('#'+val.id).iCheck('uncheck');
           }
       });        
    });
    
    $('body').off('click','#helper_save');
    
    var getSelectedHelper = function(){
       var checkboxes = $("[type=checkbox]") ;
       var result = [];
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_helper')!= -1){
                var helper = $('#'+val.id);
                if(helper.is(":checked")){
                    result.push(val);
                }
           }
       });
       return result;
    }
    
    $('body').on('click','#helper_save',function(){
        $(this).addClass('disabled');
        var selectedHelper = getSelectedHelper();
        var data = {
            u_group_id:"<?php echo $u_group_id;?>",
            helper:[]
        };
        selectedHelper.forEach(function(val){
            data.helper.push(val.id);
        });
        
         $.ajax({
            url:"<?php echo $helper_ajax_url ?>",
            dataType: "json",
            type: "POST",
            data :JSON.stringify(data),
            global: false,
            async:false,
            cache: false,
            success: function(data) {
                console.log(data);
                if(data.status == 1){
                    APP_MESSAGE.set('success',['Data has been updated successfully']);
                }
                else{
                    APP_MESSAGE.set('error',[data.msg]);
                }
            },
            error:function(xhr, status, error){                                    
                APP_MESSAGE.set('error',[error]);
            }
        });
        
                
        $(this).removeClass('disabled');    
    });
    


</script>