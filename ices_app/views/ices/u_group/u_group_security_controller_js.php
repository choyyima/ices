<script>
    var security_controller_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var security_controller_ajax_url = null;
    var security_controller_index_url = null;
    var security_controller_view_url = null;
    var security_controller_window_scroll = null;
    var security_controller_data_support_url = null;
    var security_controller_common_ajax_listener = null;
    var security_controller_component_prefix_id = '';
    
    
    var security_controller_init = function(){
        var parent_pane = security_controller_parent_pane;

        security_controller_ajax_url = '<?php echo $ajax_url ?>';
        security_controller_index_url = '<?php echo $index_url ?>';
        security_controller_view_url = '<?php echo $view_url ?>';
        security_controller_window_scroll = '<?php echo $window_scroll; ?>';
        security_controller_data_support_url = '<?php echo $data_support_url; ?>';
        security_controller_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        security_controller_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var security_controller_after_submit = function(){

    }
    
    var security_controller_data ={
        
    }
    
    var security_controller_methods = {
        
        hide_all:function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = security_controller_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        
        show_hide: function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();            
            
            security_controller_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    break;
            }
            
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    break;
            }
        },        
        enable_disable: function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            security_controller_methods.disable_all();
            
            switch(lmethod){
                case "add":
                case 'view':
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            
            
        },
        submit:function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            var ajax_url = security_controller_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+"_method").val();
            var security_controller_id = $(lparent_pane).find(lprefix_id+"_id").val();        
            var json_data = {
                ajax_post:true,
                security_controller:{},
                u_group:{},
                message_session:true
            };
            
            json_data.u_group.id = $('#u_group_id').val();
            json_data.security_controller.controller = [];
            var lsecurity_controller_inpt = $(lparent_pane).find('.panel input[type="checkbox"][id*="security_controller"]');
            $.each($(lsecurity_controller_inpt),function(lidx, lrow){
                if($(lrow).is(':checked')){
                    json_data.security_controller.controller.push($(lrow).attr('id').replace(/[security_controller]/g,''));
                }
            });
            
            var lajax_method='security_controller_save';
            
            ajax_url +=lajax_method+'/';
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(security_controller_parent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(security_controller_view_url !==''){
                    var url = security_controller_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    security_controller_after_submit();
                }
            }
        },
        
    }

    var security_controller_bind_event = function(){
        var lparent_pane = security_controller_parent_pane;
        var lprefix_id = security_controller_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_btn_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_btn_submit'),{
            parent_pane:lparent_pane,
            module_method:security_controller_methods
        });
        
        $("#security_controller_check_all").on('ifChecked',function(){
            var checkboxes = $("[type=checkbox]") ;
            checkboxes.each(function(key,val){
                if(val.id.indexOf('security_controller')!= -1){
                     $('#'+val.id).iCheck('check');
                }
            });        
        });

        $("#security_controller_check_all").on('ifUnchecked',function(){

           var checkboxes = $("[type=checkbox]") ;
           checkboxes.each(function(key,val){
               if(val.id.indexOf('security_controller')!= -1){
                    $('#'+val.id).iCheck('uncheck');
               }
           });        
        });
        
    }
    
    var security_controller_components_prepare= function(){
        var lparent_pane = security_controller_parent_pane;
        var lprefix_id = security_controller_component_prefix_id;
        var method = $(security_controller_parent_pane).find(lprefix_id+"_method").val();
                
        var security_controller_data_set = function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            switch(method){
                case "add":
                    security_controller_methods.reset_all();
                    break;
                case "view":
                    
                    break;            
            }
        }
        
        security_controller_methods.enable_disable();
        security_controller_methods.show_hide();
        security_controller_data_set();
    }
    
</script>