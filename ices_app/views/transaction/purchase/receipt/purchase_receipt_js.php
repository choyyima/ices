<script>
    var purchase_receipt_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var purchase_receipt_ajax_url = null;
    var purchase_receipt_index_url = null;
    var purchase_receipt_view_url = null;
    var purchase_receipt_window_scroll = null;
    
    
    var purchase_receipt_init = function(){
        var parent_pane = purchase_receipt_parent_pane;
        purchase_receipt_ajax_url = '<?php echo $ajax_url ?>';
        purchase_receipt_index_url = '<?php echo $index_url ?>';
        purchase_receipt_view_url = '<?php echo $view_url ?>';
        purchase_receipt_window_scroll = '<?php echo $window_scroll; ?>';
        
    }
    
    
    
    var purchase_receipt_bind_event = function(){
        var parent_pane = purchase_receipt_parent_pane;
        var amount = $(parent_pane).find('#purchase_receipt_amount');
        APP_EVENT.init().component_set(amount).type_set('input').numeric_set().render();
        $(amount).on('blur',function(){
                $(parent_pane).find('#purchase_receipt_available_amount').val($(this).val());
            });
        
        $("#purchase_receipt_purchase_receipt_status").on('change',function(e){
            var purchase_receipt_status = $(this).val();
            if(purchase_receipt_status == 'X'){
                $("#purchase_receipt_div_cancellation_reason").removeClass("hidden");
            }
            else{
                $("#purchase_receipt_div_cancellation_reason").addClass("hidden");
            }

        });
        
        $('#purchase_receipt_payment_type').on('change',function(e){
            var lpayment_type_id = $(this).select2('val');
            $('#purchase_receipt_bank_acc').val('');
            $('#purchase_receipt_bank_acc_supplier').val('');
            if(lpayment_type_id !== '1'){
                $('#purchase_receipt_div_bank_acc').show();
                $('#purchase_receipt_div_bank_acc_supplier').show();
            }
            else{
                $('#purchase_receipt_div_bank_acc').hide();    
                $('#purchase_receipt_div_bank_acc_supplier').hide(); 
            }
        });
        
        var purchase_receipt_submit = function(){
            var ajax_url = purchase_receipt_index_url;
            var json_data = {
                ajax_post:true,
                purchase_receipt:{
                    store_id:'',
                    purchase_receipt_date:'',
                    notes:'',
                    amount:'',
                    purchase_receipt_status:'',
                    cancellation_reason:'',
                    supplier_id:'',
                    payment_type_id:'',
                    bank_acc:'',
                    bank_acc_supplier:''
                },
                message_session:true
            };
            var method = $(parent_pane).find('#purchase_receipt_method').val();
            switch(method){
                case 'Add':
                    json_data.purchase_receipt.store_id=$(parent_pane).find('#purchase_receipt_store').select2('val');
                    json_data.purchase_receipt.purchase_receipt_date=$(parent_pane).find('#purchase_receipt_purchase_receipt_date').val();
                    json_data.purchase_receipt.notes = $(parent_pane).find("#purchase_receipt_notes").val();
                    json_data.purchase_receipt.amount = $(parent_pane).find("#purchase_receipt_amount").val();
                    json_data.purchase_receipt.supplier_id = $(parent_pane).find("#purchase_receipt_supplier").select2('val')
                    json_data.purchase_receipt.payment_type_id = $(parent_pane).find("#purchase_receipt_payment_type").select2('val')
                    json_data.purchase_receipt.bank_acc = $(parent_pane).find("#purchase_receipt_bank_acc").val();
                    json_data.purchase_receipt.bank_acc_supplier = $(parent_pane).find("#purchase_receipt_bank_acc_supplier").val();
                    ajax_url +='add';
                    break;
                case 'View':
                    var purchase_receipt_id = $(parent_pane).find('#purchase_receipt_id').val();
                    json_data.purchase_receipt.notes = $(parent_pane).find("#purchase_receipt_notes").val();
                    json_data.purchase_receipt.purchase_receipt_status = $(parent_pane).find("#purchase_receipt_purchase_receipt_status").select2('val');
                    json_data.purchase_receipt.cancellation_reason = $(parent_pane).find("#purchase_receipt_cancellation_reason").val();
                    ajax_url +='edit/'+purchase_receipt_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                if(purchase_receipt_view_url !==''){
                    var url = purchase_receipt_view_url+result.trans_id;
                    window.location.href=url;
                }
            }
        }
        
        $(parent_pane).find('#purchase_receipt_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                purchase_receipt_submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            $(purchase_receipt_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
    }
    
    purchase_receipt_init();
    purchase_receipt_bind_event();
    
</script>