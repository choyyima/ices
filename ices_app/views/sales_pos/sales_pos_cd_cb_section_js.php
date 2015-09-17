<script>
    var sales_pos_cd_cb_props={
        reset_all:true,
        module:'intake',
    };
    var sales_pos_cd_cb_section_bind_events=function(){
        var lparent_pane = sales_pos_parent_pane;
        
        $(lparent_pane).find('#sales_pos_cd_cb_modal_product_btn_cancel').on('click',function(e){     
            $(lparent_pane).find('#sales_pos_cd_cb_modal_product_btn_ok').off();
            $(this).off();
        });
        
    }
    
    var sales_pos_cd_cb_section_methods={
        hide_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            $(lparent_pane).find('[routing_section="cd_cb"]').hide();
            
        },
        show_hide:function(){
            sales_pos_cd_cb_section_methods.hide_all();
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lsection = $(sales_pos_parent_pane).find('[routing_section="cd_cb"]')[0];
            APP_COMPONENT.disable_all(lsection);
            
        },
        enable_disable:function(){
            var lparent_pane = sales_pos_parent_pane;
            sales_pos_cd_cb_section_methods.disable_all();
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            switch(lmethod){
                case 'add':
                break;
            }
        },
        reset_all:function(){
            if(sales_pos_cd_cb_props.reset_all){
                var lparent_pane = sales_pos_parent_pane;
                //sales_pos_cd_cb_section_methods.table.reset('intake');
                //sales_pos_cd_cb_section_methods.table.reset('delivery');
                sales_pos_cd_cb_props.reset_all = false;
            }
            
        },
        show_hide:function(){
            
        },
        show_hide_routing:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lis_delivery = $(lparent_pane).find('#sales_pos_delivery_checkbox').is(':checked');
            if(lis_delivery){
                $(lparent_pane).find('#sales_pos_delivery').show();
                $(lparent_pane).find('#sales_pos_intake').hide();
            }
            else{
                $(lparent_pane).find('#sales_pos_delivery').hide();
                $(lparent_pane).find('#sales_pos_intake').show();                            
            }
        },
        
        btn_controller_set:function(){
            var lvalid = true;
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            sales_pos_methods.btn_controller_reset();
            
            $(lparent_pane).find('#sales_pos_btn_prev').show();
            $(lparent_pane).find('#sales_pos_btn_next').hide();            
            
            if(lvalid){
            
            }
            
            $(lparent_pane).find('#sales_pos_btn_prev').prop('disabled',false);
            $(lparent_pane).find('#sales_pos_btn_prev').on('click',function(e){
                e.preventDefault();
                sales_pos_routing.set(lmethod,'movement');
            });
            
        },
        
        
    }
    
    
    
    
</script>