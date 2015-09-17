<script>
    
    $("#component_check_all").on('ifChecked',function(){

       var checkboxes = $("[type=checkbox]") ;
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_component')!= -1){
                $('#'+val.id).iCheck('check');
           }
       });        
    });
    
    $("#component_check_all").on('ifUnchecked',function(){

       var checkboxes = $("[type=checkbox]") ;
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_component')!= -1){
                $('#'+val.id).iCheck('uncheck');
           }
       });        
    });
    
    $('body').off('click','#component_save');
    
    var getSelectedComponent = function(){
       var checkboxes = $("[type=checkbox]") ;
       var result = [];
       checkboxes.each(function(key,val){
           if(val.id.indexOf('security_component')!= -1){
                var component = $('#'+val.id);
                if(component.is(":checked")){
                    result.push(val);
                }
           }
       });
       return result;
    }
    
    $('body').on('click','#component_save',function(){
        $(this).addClass('disabled');
        var selectedComponent = getSelectedComponent();
        var data = {
            u_group_id:"<?php echo $u_group_id;?>",
            component:[]
        };
        selectedComponent.forEach(function(val){
            data.component.push(val.id);
        });
        /*
         $.ajax({
            url:"<?php echo $component_ajax_url ?>",
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
        var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo $component_ajax_url ?>',data);
        if(lresult.success == 1){
            APP_MESSAGE.set('success',['Data has been updated successfully']);
        }
        
        
        $(window).scrollTop(0);        
        $(this).removeClass('disabled');    
    });
    


</script>