<?php 
    $properties = json_decode(json_encode($properties));
    $id = $properties->input_select_properties->id;
?>

<script>
    var <?php echo $id;?>_methods={
        setting:{
            properties_get:function(){
                var lresult = <?php echo json_encode($properties);?>;                
                return lresult;
            }
        },
        show_hide:function(){
            var lprop = this.setting.properties_get();
            var is_checked = $('#'+lprop.input_select_properties.id+'_checkbox').is(':checked');
            if(is_checked){
                $('#'+lprop.input_select_properties.id+'').closest('div[class*="input-group"]').show();
                $('#'+lprop.input_select_properties.id+'_detail').show();
            }
            else{
                $('#'+lprop.input_select_properties.id+'').closest('div[class*="input-group"]').hide();
                $('#'+lprop.input_select_properties.id+'_detail').hide();
            }
        },
        detail_render:function(ldata){
            var lprop = this.setting.properties_get();
            var ldetail_li = $('#'+lprop.input_select_properties.id+'_detail').find('li');
            var lmodule_name = Object.keys(ldata).length > 0?ldata.module_name:lprop.detail_properties.module_name.val;
            var lmodule_name_text = Object.keys(ldata).length > 0?ldata.module_name_text:lprop.detail_properties.module_name.label;
            $(ldetail_li).empty();
            $(ldetail_li).append(
                '<div>'
                    +'<span>'
                        +'<strong>Module Name:</strong>'
                        +'<span id = "'+lprop.input_select_properties.id+'_module_name_text"> '+lmodule_name_text+'</span>'
                        +'<span id = "'+lprop.input_select_properties.id+'_module_name" style="display:none"> '+lmodule_name+'</span>'
                    +'</span>'
                +'</div>'
            );
    
            var lmodule_action = Object.keys(ldata).length > 0?ldata.module_action:lprop.detail_properties.module_action.val;
            var lmodule_action_text = Object.keys(ldata).length > 0?ldata.module_action_text:lprop.detail_properties.module_action.label;
            $(ldetail_li).append(
                '<div>'
                    +'<span>'
                        +'<strong>Module Action:</strong>'
                        +'<span id = "'+lprop.input_select_properties.id+'_module_action_text"> '+lmodule_action_text+'</span>'
                        +'<span id = "'+lprop.input_select_properties.id+'_module_action" style="display:none"> '+lmodule_action+'</span>'
                    +'</span>'
                +'</div>'
            );
    
            if(Object.keys(ldata).length  === 0){
                $(ldetail_li).append(
                    '<div class="form-group">'
                        +'<span>'
                            +'<input id = '+lprop.input_select_properties.id+'_creator class="form-control" placeholder="Creator">'
                        +'</span>'
                    +'</div>'
                );

            }
            else{
                $(ldetail_li).append(
                    '<div>'
                        +'<span>'
                            +'<strong>Creator:</strong>'
                            +'<span id = '+lprop.input_select_properties.id+'_creator> '+ldata.creator+'</span>'
                        +'</span>'
                    +'</div>'
                );
            }
            
            var ldescription = Object.keys(ldata).length > 0 ?ldata.description: '';
            $(ldetail_li).append(
                '<div class="form-group">'
                    +'<span>'
                        +'<textarea id = '+lprop.input_select_properties.id+'_description class="form-control" placeholder="Description">'+ldescription+'</textarea>'
                    +'</span>'
                +'</div>'
            );
            if(Object.keys(ldata).length > 0 ){
                $('#'+lprop.input_select_properties.id+'_description').prop('disabled',true);
            }
        },
        reset_all:function(){
            var lprop = this.setting.properties_get();
            $('#'+lprop.input_select_properties.id).off();
            
            APP_COMPONENT.input_select.set($('#'+lprop.input_select_properties.id)[0],
            {
                place_holder:''
            });
            $('#'+lprop.input_select_properties.id).select2('disable');
            this.detail_render({});
            
            
        },
        data_get:function(){
            var lprop = this.setting.properties_get();
            var lprefix_id = '#'+lprop.input_select_properties.id;
            var lresult = {};
            
            lresult.module_name = $(lprefix_id+'_module_name').text();
            lresult.module_action = $(lprefix_id+'_module_action').text();
            lresult.creator = $(lprefix_id+'_creator').val();
            lresult.description = $(lprefix_id+'_description').val();
            
            return lresult;
        },
        load:function(lsir_id){
            var lprop = this.setting.properties_get();
            var lajax_url = lprop.input_select_properties.data_support_url;
            var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{data:lsir_id}).response;
            $('#'+lprop.input_select_properties.id+'_checkbox').iCheck('check');
            $('#'+lprop.input_select_properties.id).select2('data',{id:lresponse.sir.id,text:'<strong>'+lresponse.sir.code+'<strong>'});
            this.detail_render(lresponse.sir);
            
        },
        init:function(){
            this.show_hide();
            this.reset_all();            
        }
    }
    $('#<?php echo $properties->input_select_properties->id; ?>_checkbox').on('ifChecked',function(){
        <?php echo $properties->input_select_properties->id;?>_methods.init();

    });
    $('#<?php echo $properties->input_select_properties->id; ?>_checkbox').on('ifUnchecked',function(){
        <?php echo $properties->input_select_properties->id;?>_methods.init();

    });
    
   
    
</script>