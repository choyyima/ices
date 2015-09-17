<script>
    var security_menu_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var security_menu_ajax_url = null;
    var security_menu_index_url = null;
    var security_menu_view_url = null;
    var security_menu_window_scroll = null;
    var security_menu_data_support_url = null;
    var security_menu_common_ajax_listener = null;
    var security_menu_component_prefix_id = '';
    
    
    var security_menu_init = function(){
        var parent_pane = security_menu_parent_pane;

        security_menu_ajax_url = '<?php echo $ajax_url ?>';
        security_menu_index_url = '<?php echo $index_url ?>';
        security_menu_view_url = '<?php echo $view_url ?>';
        security_menu_window_scroll = '<?php echo $window_scroll; ?>';
        security_menu_data_support_url = '<?php echo $data_support_url; ?>';
        security_menu_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        security_menu_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var security_menu_after_submit = function(){

    }
    
    var security_menu_data ={
        
    }
    
    var security_menu_methods = {
        
        hide_all:function(){
            var lparent_pane = security_menu_parent_pane;
            var lprefix_id = security_menu_component_prefix_id;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = security_menu_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        
        show_hide: function(){
            var lparent_pane = security_menu_parent_pane;
            var lprefix_id = security_menu_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();            
            
            security_menu_methods.hide_all();
            
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
            var lparent_pane = security_menu_parent_pane;
            var lprefix_id = security_menu_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            security_menu_methods.disable_all();
            
            switch(lmethod){
                case "add":
                case 'view':
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = security_menu_parent_pane;
            var lprefix_id = security_menu_component_prefix_id;
            
            
        },
        submit:function(){
            var lparent_pane = security_menu_parent_pane;
            var lprefix_id = security_menu_component_prefix_id;
            var ajax_url = security_menu_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+"_method").val();
            var security_menu_id = $(lparent_pane).find(lprefix_id+"_id").val();        
            var json_data = {
                ajax_post:true,
                security_menu:{},
                u_group:{},
                message_session:true
            };
            
            json_data.u_group.id = $('#u_group_id').val();
            
            var menu = [];
            var ltrs = $(lparent_pane).find(lprefix_id+'_table tbody tr');
            $.each($(ltrs),function(lidx, lrow){
                var selected = $(lrow).find('[col_name="selected"] input');
                if($(selected).is(':checked')){
                    menu.push($(lrow).find('[col_name="id"]')[0].innerHTML.replace(/&nbsp; /g,''));
                }
            });
            json_data.security_menu.menu = menu;
            
            var lajax_method='security_menu_save';
            
            ajax_url +=lajax_method+'/';
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(security_menu_parent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(security_menu_view_url !==''){
                    var url = security_menu_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    security_menu_after_submit();
                }
            }
        },
        menu_table:{
            draw:function(iData){
                var lparent_pane = security_menu_parent_pane;
                var lprefix_id = security_menu_component_prefix_id;
                var fast_draw = APP_COMPONENT.table_fast_draw;
                var ltbody = $(lparent_pane).find(lprefix_id+'_table tbody');
                
                var lsecurity_menu_list = iData.security_menu_list;
                var lsecurity_menu = iData.security_menu;
                
                $.each(lsecurity_menu_list,function(sml_idx,sml_row){
                    var lrow = document.createElement('tr');
                     
                    fast_draw.col_add(lrow,{tag:'td',col_name:'selected',col_style:'text-align:center',val:'<input style="width:50px" type="checkbox"/>',type:'text',class:''});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'id',style:'',val:sml_row.id,type:'text',class:'',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'name',style:'text-align:left',val:sml_row.menu,type:'text',class:''});
                    
                    $(ltbody).append(lrow);
                })
                
                $(ltbody).find('input[type="checkbox"]').iCheck({
                    checkboxClass: 'icheckbox_minimal',
                });
                
                $.each(lsecurity_menu,function(sm_idx, sm_row){
                    $.each($(ltbody).find('tr td[col_name="id"]'),function(lidx,ltd){
                        if($(ltd)[0].innerHTML === sm_row.menu_id){
                            $(ltd).closest('tr').find('td[col_name="selected"] input[type="checkbox"]').iCheck('check');
                        }
                    });
                });
            }
        }
    }

    var security_menu_bind_event = function(){
        var lparent_pane = security_menu_parent_pane;
        var lprefix_id = security_menu_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_btn_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_btn_submit'),{
            parent_pane:lparent_pane,
            module_method:security_menu_methods
        });
        
        $(lparent_pane).find("#check_all").on('ifChecked',function(e){
            var ltbody = $(lparent_pane).find(lprefix_id+"_table").find('tbody');
            $.each($(ltbody).children(),function(lidx, lrow){
                var selected = $(lrow).find('[col_name="selected"] input');
                $(selected).iCheck('check');
            });        
        });
        
        $(lparent_pane).find("#check_all").on('ifUnchecked',function(e){
            var ltbody = $(lparent_pane).find(lprefix_id+"_table").find('tbody');
            $.each($(ltbody).children(),function(lidx, lrow){
                var selected = $(lrow).find('[col_name="selected"] input');
                $(selected).iCheck('uncheck');
            });        
        });
        
    }
    
    var security_menu_components_prepare= function(){
        var lparent_pane = security_menu_parent_pane;
        var lprefix_id = security_menu_component_prefix_id;
        var method = $(security_menu_parent_pane).find(lprefix_id+"_method").val();
                
        var security_menu_data_set = function(){
            var lparent_pane = security_menu_parent_pane;
            var lprefix_id = security_menu_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_app_name').val($('#u_group_app_name').select2('val'));
            switch(method){
                case "add":
                    security_menu_methods.reset_all();
                    break;
                case "view":
                    
                    var json_data={
                        app_name:$(lparent_pane).find(lprefix_id+'_app_name').val(),
                        u_group_id:$('#u_group_id').val()
                    };
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(security_menu_data_support_url+"security_menu_get",json_data).response;
                    if(lresponse != []){
                        security_menu_methods.menu_table.draw({security_menu:lresponse.security_menu,security_menu_list:lresponse.security_menu_list});
                    };
                    break;            
            }
        }
        
        security_menu_methods.enable_disable();
        security_menu_methods.show_hide();
        security_menu_data_set();
    }
    
</script>