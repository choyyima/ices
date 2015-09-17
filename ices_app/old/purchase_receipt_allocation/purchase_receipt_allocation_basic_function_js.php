<script>
    var purchase_receipt_allocation_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var purchase_receipt_allocation_ajax_url = null;
    var purchase_receipt_allocation_index_url = null;
    var purchase_receipt_allocation_view_url = null;
    var purchase_receipt_allocation_window_scroll = null;
    var purchase_receipt_allodation_data_support_url = null;
    
    var purchase_receipt_allocation_init = function(){
        var parent_pane = purchase_receipt_allocation_parent_pane;
        purchase_receipt_allocation_ajax_url = '<?php echo $ajax_url ?>';
        purchase_receipt_allocation_index_url = '<?php echo $index_url ?>';
        purchase_receipt_allocation_view_url = '<?php echo $view_url ?>';
        purchase_receipt_allocation_window_scroll = '<?php echo $window_scroll; ?>';
        purchase_receipt_allocation_data_support_url = '<?php echo $data_support_url; ?>';
        
        purchase_receipt_allocation_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            var lsupplier_id = $(purchase_receipt_allocation_parent_pane).find('#purchase_receipt_allocation_supplier').select2('val');
            return {supplier_id:lsupplier_id};
        };
        
        purchase_receipt_allocation_purchase_receipt_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            var lsupplier_id = $(purchase_receipt_allocation_parent_pane).find('#purchase_receipt_allocation_supplier').select2('val');
            return {supplier_id:lsupplier_id};
        };
    }
    
    var purchase_receipt_allocation_after_submit = function(){
        //function that will be executed after submit 
    }



    var purchase_receipt_allocation_bind_event = function(){
        var parent_pane = purchase_receipt_allocation_parent_pane;
        
        var outstanding_amount_set = function(){
            var data_support_url = purchase_receipt_allocation_data_support_url;
            var purchase_invoice_id = $(parent_pane).find('#purchase_receipt_allocation_purchase_invoice').select2('val');
            var purchase_receipt_id = $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt').select2('val');
            var json_data = {purchase_invoice_id:purchase_invoice_id,purchase_receipt_id:purchase_receipt_id};
            var response = APP_DATA_TRANSFER.ajaxPOST(data_support_url+'purchase_receipt_allocation_outstanding_amount_get',json_data);
            if(typeof response.outstanding_amount !=='undefined'){
                $(parent_pane).find('#purchase_receipt_allocation_outstanding_amount').val(APP_CONVERTER.thousand_separator(response.outstanding_amount));
                var allocated_amount = $(parent_pane).find('#purchase_receipt_allocation_allocated_amount');
                $(allocated_amount).off();
                APP_EVENT.init().component_set(allocated_amount).type_set('input').numeric_set().min_val_set(0).max_val_set(response.outstanding_amount).render();
                var allocated_amount = $(parent_pane).find('#purchase_receipt_allocation_allocated_amount').val('0').blur();
                
            }
        };
        
        
        //$(parent_pane).find('#purchase_receipt_allocation_purchase_receipt').off();
        $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt')
            .on('change',function(){
            <?php /*
            $(parent_pane).find('#purchase_receipt_allocation_supplier_id')
                    .val('');            
            var lpurchase_receipt_id = $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt')
                    .select2('val');
            var json_data = {data:lpurchase_receipt_id};
            var rs_purchase_receipt = APP_DATA_TRANSFER.ajaxPOST(purchase_receipt_allocation_ajax_url+'purchase_receipt_get'
                ,json_data);
            
            $.each(rs_purchase_receipt,function(key, val){
                $(parent_pane).find('#purchase_receipt_allocation_supplier_id')
                    .val(val.supplier_id);
            });
            */?>
            outstanding_amount_set();
        });
        
        //$(parent_pane).find('#purchase_receipt_allocation_purchase_invoice').off();
        $(parent_pane).find("#purchase_receipt_allocation_purchase_invoice")
        .on('change', function(){
            outstanding_amount_set();
        });
        
        $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_allocation_status').off();
        $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_allocation_status')
        .on('change',function(){
            if($(this).select2('val') === 'X'){
                $(parent_pane).find('#purchase_receipt_allocation_div_cancellation_reason').show();
            }
            else{
                $(parent_pane).find('#purchase_receipt_allocation_div_cancellation_reason').hide();
            }
        });
        
        var purchase_receipt_allocation_submit = function(){
            var parent_pane = purchase_receipt_allocation_parent_pane;
            var ajax_url = purchase_receipt_allocation_index_url;
            var method = $(parent_pane).find('#purchase_receipt_allocation_method').val();
            var json_data = {
                ajax_post:true,
                purchase_receipt_allocation:{
                    purchase_receipt_id:'',
                    purchase_invoice_id:'',
                    allocated_amount:'0',
                    notes:'',
                    purchase_receipt_allocation_status:''
                },
                message_session:true               
                
            };
            
            switch(method){
                case 'Add':
                    json_data.purchase_receipt_allocation.purchase_receipt_id = $(parent_pane).find("#purchase_receipt_allocation_purchase_receipt").select2('val');
                    json_data.purchase_receipt_allocation.purchase_invoice_id = $(parent_pane).find("#purchase_receipt_allocation_purchase_invoice").select2('val');
                    json_data.purchase_receipt_allocation.allocated_amount = $(parent_pane).find("#purchase_receipt_allocation_allocated_amount").val().replace(/[,]/g,'');
                    json_data.purchase_receipt_allocation.notes = $(parent_pane).find("#purchase_receipt_allocation_notes").val();
                    ajax_url +='add';
                    break;
                case 'Edit':
                    var purchase_receipt_allocation_id = $(parent_pane).find('#purchase_receipt_allocation_id').val();
                    json_data.purchase_receipt_allocation.notes = $(parent_pane).find('#purchase_receipt_allocation_notes').val();
                    json_data.purchase_receipt_allocation.purchase_receipt_allocation_status = $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt_allocation_status').select2('val');
                    json_data.purchase_receipt_allocation.cancellation_reason = $(parent_pane).find('#purchase_receipt_allocation_cancellation_reason').val();
                    ajax_url +='edit/'+purchase_receipt_allocation_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(parent_pane).find('#purchase_receipt_alllocation_id').val(result.trans_id);
                if(purchase_receipt_allocation_view_url !==''){
                    var url = purchase_receipt_allocation_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    purchase_receipt_allocation_after_submit();
                }
            }
        }
        
        $(parent_pane).find('#purchase_receipt_allocation_submit').off();        
        $(parent_pane).find('#purchase_receipt_allocation_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = purchase_receipt_allocation_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                purchase_receipt_allocation_submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            $(purchase_receipt_allocation_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    
    
</script>