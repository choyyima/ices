<script>
    var purchase_receipt_allocation_components_prepare= function(){
        var parent_pane = '#modal_purchase_receipt_allocation';
        var method = $(purchase_receipt_allocation_parent_pane).find("#purchase_receipt_allocation_method").val();
        var ajax_url = '<?php echo $ajax_url ?>';
        
        var purchase_receipt_allocation_data_set = function(){
            switch(method){
                case "Add":
                    $(parent_pane).find('#purchase_receipt_allocation_supplier').select2('data',{id:'',text:''});
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt')
                        .select2('data',{id:'',text:''}).change();
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice')
                        .select2('data',{id:'',text:''}).change();
                    $(parent_pane).find("#purchase_receipt_allocation_purchase_receipt_allocation_status").select2(
                            'data',{id:"I",text:APP_CONVERTER.status_attr("INVOICED")}
                    );
                    $(parent_pane).find("#purchase_receipt_allocation_purchase_receipt_allocation_status").select2(
                            {data:[{id:"I",text:APP_CONVERTER.status_attr("INVOICED")}]}
                    );
                    $(parent_pane).find('#purchase_receipt_allocation_cancellation_reason').val('');
                    $(parent_pane).find('#purchase_receipt_allocation_outstanding_amount').val('');
                    $(parent_pane).find('#purchase_receipt_allocation_allocated_amount').val('');
                    $(parent_pane).find('#purchase_receipt_allocation_notes').val('');
                    break;
                case "Edit":
                    var purchase_receipt_allocation_id = $(parent_pane).find("#purchase_receipt_allocation_id").val();
                    var json_data={data:purchase_receipt_allocation_id};
                    var lrs = APP_DATA_TRANSFER.ajaxPOST(ajax_url+"purchase_receipt_allocation_ajax_get",json_data);
                    if(lrs !== null){
                        $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt')
                            .select2('data',{id:lrs.purchase_receipt_id,text:lrs.purchase_receipt_code});
                        $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_detail_code')[0].innerHTML=lrs.purchase_receipt_code;
                        $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_detail_amount')[0].innerHTML=lrs.purchase_receipt_amount;
                        $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice')
                            .select2('data',{id:lrs.purchase_invoice_id,text:lrs.purchase_invoice_code});
                        $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice_detail_code')[0].innerHTML=lrs.purchase_invoice_code;
                        $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice_detail_grand_total')[0].innerHTML=lrs.purchase_invoice_amount;

                        $(parent_pane).find("#purchase_receipt_allocation_purchase_receipt_allocation_status").select2(
                                'data',{id:lrs.purchase_receipt_allocation_status,text:APP_CONVERTER.status_attr(lrs.purchase_receipt_allocation_status_name)}
                        );
                        var pra_status_list = [{id:lrs.purchase_receipt_allocation_status,text:APP_CONVERTER.status_attr(lrs.purchase_receipt_allocation_status_name)}];;
                        if(lrs.purchase_receipt_allocation_status === 'I'){
                            pra_status_list.push({id:'X',text:APP_CONVERTER.status_attr('CANCELED')});
                        }
                        $(parent_pane).find("#purchase_receipt_allocation_purchase_receipt_allocation_status").select2(
                                {data:pra_status_list}
                        );
                        $(parent_pane).find('#purchase_receipt_allocation_cancellation_reason').val(lrs.cancellation_reason);
                        $(parent_pane).find('#purchase_receipt_allocation_outstanding_amount').val('');
                        $(parent_pane).find('#purchase_receipt_allocation_allocated_amount').val(lrs.allocated_amount);
                        $(parent_pane).find('#purchase_receipt_allocation_notes').val(lrs.notes);
                        
                    }
                    break;
                case "View":
                    
                    break;            
            }
        }
    
        var purchase_receipt_allocation_components_enable_disable = function(){
            switch(method){
                case "Add":
                    $(parent_pane).find('#purchase_receipt_allocation_supplier')
                        .select2('disable');
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt')
                        .select2('enable');
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice')
                        .select2('enable');
                    $(parent_pane).find("#purchase_receipt_allocation_purchase_receipt_allocation_status")
                        .removeAttr('disabled');
                    $(parent_pane).find('#purchase_receipt_allocation_cancellation_reason').removeAttr('disabled');
                    $(parent_pane).find('#purchase_receipt_allocation_outstanding_amount').attr('disabled');
                    $(parent_pane).find('#purchase_receipt_allocation_allocated_amount').removeAttr('disabled');
                    $(parent_pane).find('#purchase_receipt_allocation_notes').removeAttr('disabled');
                    $(parent_pane).find('#purchase_receipt_allocation_cancellation_reason').attr('disabled','');
                    break;
                case "Edit":
                    $(parent_pane).find('#purchase_receipt_allocation_supplier')
                        .select2('disable');
                    var lstatus = $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_allocation_status').select2('val');
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt')
                        .select2('disable');
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice')
                        .select2('disable');
                    $(parent_pane).find("#purchase_receipt_allocation_purchase_receipt_allocation_status")
                        .removeAttr('disabled');                    
                    $(parent_pane).find('#purchase_receipt_allocation_outstanding_amount')
                        .attr('disabled','');
                    $(parent_pane).find('#purchase_receipt_allocation_allocated_amount')
                        .attr('disabled','');
                    
                    $(parent_pane).find('#purchase_receipt_allocation_cancellation_reason')
                        .removeAttr('disabled');
                        
                    if(lstatus === 'I'){
                        $(parent_pane).find('#purchase_receipt_allocation_cancellation_reason')
                            .removeAttr('disabled');
                        $(parent_pane).find('#purchase_receipt_allocation_notes')
                            .removeAttr('disabled');
                    }
                    else if (lstatus === 'X'){
                        $(parent_pane).find('#purchase_receipt_allocation_cancellation_reason')
                            .attr('disabled','');
                        $(parent_pane).find('#purchase_receipt_allocation_notes')
                            .attr('disabled','');
                    }
                        
                    break;
                case "View":
                    
                    break;
            }
        }
        
        var purchase_receipt_allocation_components_show_hide = function(){
            switch(method){
                case "Add":
                    $(parent_pane).find('#purchase_receipt_allocation_div_cancellation_reason').hide();
                    $(parent_pane).find('#purchase_receipt_allocation_outstanding_amount').parent().parent()
                        .show();
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_detail_outstanding_amount').parent().show();
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice_detail_unresolved_amount').parent().show();
                    $(parent_pane).find('#purchase_receipt_allocation_submit').show();
                    break;
                case "Edit":
                    var lstatus = $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_allocation_status').select2('val');
                    $(parent_pane).find('#purchase_receipt_allocation_outstanding_amount').parent().parent()
                            .hide();
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_detail_outstanding_amount').parent().hide();
                    $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice_detail_outstanding_amount').parent().hide();
                    if(lstatus ==='I'){
                        $(parent_pane).find('#purchase_receipt_allocation_div_cancellation_reason').hide();
                        $(parent_pane).find('#purchase_receipt_allocation_submit').show();
                    }
                    else if (lstatus === 'X'){
                        $(parent_pane).find('#purchase_receipt_allocation_div_cancellation_reason').show();                        
                        $(parent_pane).find('#purchase_receipt_allocation_submit').hide();
                    }
                    break;
                case "View":
                    
                    break;
            }
        }
        
        purchase_receipt_allocation_data_set();
        purchase_receipt_allocation_components_enable_disable();
        purchase_receipt_allocation_components_show_hide();
        
    }
</script>