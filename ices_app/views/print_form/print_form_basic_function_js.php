<script>
    var print_form_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var print_form_ajax_url = null;
    var print_form_index_url = null;
    var print_form_view_url = null;
    var print_form_window_scroll = null;
    var print_form_data_support_url = null;
    var print_form_common_ajax_listener = null;
    var print_form_component_prefix_id = '';
    
    var print_form_init = function(){
        var parent_pane = print_form_parent_pane;

        print_form_ajax_url = '<?php echo $ajax_url ?>';
        print_form_index_url = '<?php echo $index_url ?>';
        print_form_view_url = '<?php echo $view_url ?>';
        print_form_window_scroll = '<?php echo $window_scroll; ?>';
        print_form_data_support_url = '<?php echo $data_support_url; ?>';
        print_form_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        print_form_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var print_form_after_submit = function(){

    }
    
    var print_form_data = {
        
    }
    
    var print_form_methods = {
        hide_all:function(){
            var lparent_pane = print_form_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = print_form_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
            
        },
        show_hide: function(){
            var lparent_pane = print_form_parent_pane;
            var lprefix_id = print_form_component_prefix_id;
            print_form_methods.hide_all();
            
        },        
        enable_disable: function(){
            var lparent_pane = print_form_parent_pane;
            var lprefix_id = print_form_component_prefix_id;
            print_form_methods.disable_all();
            
        },
        reset_all:function(){
                
        },
                
    }

    var print_form_bind_event = function(){
        var lparent_pane = print_form_parent_pane;
        var lprefix_id = print_form_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_form_type').on('change',function(){            
            var lval = $(this).select2('val');
            if(lval !== ''){
                var ldata = $(this).select2('data');
                modal_print.init();
                modal_print.menu.add(ldata.text,print_form_index_url+'print_form_print/'+ldata.id);
                modal_print.show();
            }
        });
        
    }
    
    var print_form_components_prepare= function(){
        
        var print_form_data_set = function(){
            var lparent_pane = print_form_parent_pane;
            var lprefix_id = print_form_component_prefix_id;
        }
    
        print_form_methods.enable_disable();
        print_form_methods.show_hide();
        print_form_data_set();
    }
    
</script>