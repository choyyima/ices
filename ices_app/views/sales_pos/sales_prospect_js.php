<script>
    $(function(){
        var lparent_pane = sales_pos_parent_pane;
        var lref_id = $(lparent_pane).find('#sales_pos_reference_id').select2('val');
        var lref_type = $(lparent_pane).find('#sales_pos_reference_type').val();
        var lmethod = $(lparent_pane).find('#sales_pos_method').val();
        
        if(lref_id !== '' && lref_type ==='sales_prospect' && lmethod === 'add'){
            $(function(){
                $(lparent_pane).find('#sales_pos_sales_inquiry_by').select2('disable');
                $(lparent_pane).find('#sales_pos_customer').select2('disable');
                $(lparent_pane).find('#sales_pos_price_list').select2('disable');
                $(lparent_pane).find('#sales_pos_approval').select2('disable');
                $(lparent_pane).find('#sales_pos_expedition').select2('disable');
                $(lparent_pane).find('#sales_pos_delivery_checkbox').iCheck('disable');
                $(lparent_pane).find('#sales_pos_product_discount_percent').select2('disable');
                $(lparent_pane).find('#sales_pos_product_discount').select2('disable');
                $(lparent_pane).find('#sales_pos_reference_id').closest('.form-group').show();
                $(lparent_pane).find('#sales_pos_btn_customer_new').hide();
            });
            
            $(function(){

                var lajax_url = sales_pos_data_support_url+'sales_prospect_get/';
                var ljson_data = {id:lref_id};
                var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url, ljson_data);
                var lresponse = lresult.response;
                var lsales_info = lresponse.sales_info;

                APP_COMPONENT.reference_detail.extra_info_set(
                    $(lparent_pane).find('#sales_pos_reference_id_detail'),
                    lresponse.reference_detail,
                    {reset:true}
                );

                $(lparent_pane).find('#sales_pos_sales_inquiry_by').select2('data',{
                        id:lsales_info.sales_inquiry_by_id,
                        text:lsales_info.sales_inquiry_by_text
                    });
                    
                $(lparent_pane).find('#sales_pos_customer').select2('data',{id:lresponse.customer.id, text:lresponse.customer.text});
                $(lparent_pane).find('#sales_pos_customer_detail_code').text(lresponse.customer.code);
                $(lparent_pane).find('#sales_pos_customer_detail_name').text(lresponse.customer.name);
                $(lparent_pane).find('#sales_pos_customer_detail_phone').text(lresponse.customer.phone);
                $(lparent_pane).find('#sales_pos_customer_detail_bb_pin').text(lresponse.customer.bb_pin);
                $(lparent_pane).find('#sales_pos_customer_detail_email').text(lresponse.customer.email);
                $(lparent_pane).find('#sales_pos_customer_detail_is_sales_receipt_outstanding').text(lresponse.customer.sales_receipt_outstanding);
                
                $(lparent_pane).find('#sales_pos_price_list')
                        .select2('data',{id:lresponse.price_list.id,text:lresponse.price_list.text});
                
                if(lresponse.expedition  !== null){
                    $(lparent_pane).find('#sales_pos_expedition')
                        .select2('data',{id:lresponse.expedition.id,text:lresponse.expedition.text});
                    $(lparent_pane).find('#sales_pos_delivery_checkbox').iCheck('check');
                }
                sales_pos_product_section_methods.table.reset();
                var ltbody = $(lparent_pane).find('#sales_pos_product_table tbody')[0];
                $.each(lresponse.products,function(idx, product){
                    var lrow = $(ltbody).find('tr').last()[0];
                    $(lrow).find('[col_name="product"] input[original]')
                        .select2({data:[{id:product.product_id,text:product.product_text}]});
                    $(lrow).find('[col_name="product"] input[original]')
                        .select2('data',{id:product.product_id,text:product.product_text})
                        .change();
                    $(lrow).find('[col_name="unit"] input[original]')
                        .select2('data',{id:product.unit_id,text:product.unit_text})
                        .change();
                    $(lrow).find('[col_name="qty"] input').val(product.qty).blur();
                    $(lrow).find('[col_name="action"] button').click();
                    $(lrow).find('[col_name="action"] button').remove();
                });                
                $('#sales_pos_product_table>tbody>tr').last().find('[col_name="product"]')
                    .find('input[original]').select2('close');
                $('#sales_pos_product_table>tbody>tr').last().remove();
                
                if(lresponse.price_list.isdiscount !== '1'){
                    $(lparent_pane).find('#sales_pos_product_discount').closest('tr').hide();
                }
                else{
                    $(lparent_pane).find('#sales_pos_product_discount').text(lresponse.sales_prospect.discount);
                }
                
                $(lparent_pane).find('#sales_pos_delivery_cost_estimation').prop('disabled',true);
                $(lparent_pane).find('#sales_pos_delivery_cost_estimation').val(lresponse.sales_prospect.delivery_cost_estimation).blur();
                
                $.each(lresponse.additional_cost,function(idx, additional_cost){
                    var lrow = $(lparent_pane).find('[additional_cost_row]').last()[0];
                    $(lrow).find('[col_name="additional_cost_description"] input').val(additional_cost.description);
                    $(lrow).find('[col_name="additional_cost_amount"] input').val(additional_cost.amount);
                    $(lrow).find('[col_name="additional_cost_action"] button').click();
                    $(lrow).find('[col_name="additional_cost_action"] button').remove();
                });
                $(lparent_pane).find('[additional_cost_row]').last().remove();
                sales_pos_routing.set('add','init');
                sales_pos_product_props.reset_all = false;
                
            });
        }
    });
</script>