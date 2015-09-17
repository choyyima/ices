<script>
    var sales_pos_extra_charge_section_methods={
        hide_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            $(lparent_pane).find('[routing_section="extra_charge"]').hide();
        },
        reset_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lajax_url = sales_pos_data_support_url+'mismatch_order_rules_message_get/';
            var ljson_data = {};
            ljson_data.price_list_id = $(lparent_pane).find('#sales_pos_price_list').select2('val');
            ljson_data.products = [];
            $.each($(lparent_pane).find('#sales_pos_product_table>tbody>tr'),function(){
                var lproduct_id = $(this).find('[col_name="product"]').find('[original]').select2('val');
                if(lproduct_id !==''){
                    var lunit_id = $(this).find('[col_name="unit"]').find('[original]').select2('val');
                    var lqty = $(this).find('[col_name="qty"]').find('input').val().replace(/[,]/g,'');
                    var lamount = $(this).find('[col_name="amount"]').find('input').val().replace(/[,]/g,'');
                    ljson_data.products.push({product_id:lproduct_id, unit_id:lunit_id, qty:lqty, amount:lamount});
                }
            });
            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
            var lresponse = lresult.response;
            $(lparent_pane).find('#sales_pos_mismatch_order_rules')[0].innerHTML = lresponse;
            $(lparent_pane).find('#sales_pos_extra_charge').val('0.00');
        },
        btn_controller_set:function(){
            var lvalid = true;
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            
            
                
        
            var lparent_pane = sales_pos_parent_pane;
            
            
            if(lvalid){
                $(lparent_pane).find('#sales_pos_btn_next').prop('disabled',false);
                $(lparent_pane).find('#sales_pos_btn_next').on('click',function(e){
                    e.preventDefault();
                    sales_pos_routing.set(lmethod,'payment');
                });
            }
            
            $(lparent_pane).find('#sales_pos_btn_prev').prop('disabled',false);
            $(lparent_pane).find('#sales_pos_btn_prev').on('click',function(e){
                e.preventDefault();
                sales_pos_routing.set(lmethod,'product');
            });
            
        },
        
    }
    
    sales_pos_extra_charge_section_bind_events = function(){
        var lparent_pane = sales_pos_parent_pane;
        var lextra_charge = $(lparent_pane).find('#sales_pos_extra_charge')[0];
        APP_EVENT.init().component_set($(lextra_charge)).type_set('input').numeric_set().min_val_set(0).render();
    }
    
</script>