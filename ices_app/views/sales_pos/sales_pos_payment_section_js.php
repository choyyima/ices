<script>
    sales_pos_payment_props = {
        reset_all:true,
    };
    
    sales_pos_payment_data = {
        bos_bank_account_list:<?php echo json_encode($bos_bank_account_list); ?>
    }
    
    sales_pos_payment_section_methods={
        hide_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            $(lparent_pane).find('[routing_section="payment"]').hide();
            
        },
        show_hide:function(){
            sales_pos_payment_section_methods.hide_all();
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
            var lsection = $(sales_pos_parent_pane).find('[routing_section="payment"]')[0];
            APP_COMPONENT.disable_all(lsection);
            
        },
        enable_disable:function(){
            var lparent_pane = sales_pos_parent_pane;
            sales_pos_payment_section_methods.disable_all();
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            switch(lmethod){
                case 'add':
                break;
            }
        },
        reset_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            if(sales_pos_payment_props.reset_all){   
                sales_pos_payment_section_methods.table.customer_deposit.reset();
                sales_pos_payment_section_methods.table.payment.reset();                
                sales_pos_payment_props.reset_all = false;
            }
        },
        btn_controller_set:function(){
            var lvalid = true;
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            sales_pos_methods.btn_controller_reset();
            
            $(lparent_pane).find('#sales_pos_btn_prev').show();
            $(lparent_pane).find('#sales_pos_btn_next').show();
            
            
            switch(lmethod){
                case 'add':

                    if(!sales_pos_payment_section_methods.check_payment_valid()){
                        lvalid = false;
                    }
                    break;
            }
                
            if(lvalid){
                $(lparent_pane).find('#sales_pos_btn_next').prop('disabled',false);
                $(lparent_pane).find('#sales_pos_btn_next').on('click',function(e){
                    e.preventDefault();
                    sales_pos_routing.set(lmethod,'movement');
                    
                });
            }
            
            $(lparent_pane).find('#sales_pos_btn_prev').prop('disabled',false);
            $(lparent_pane).find('#sales_pos_btn_prev').on('click',function(e){
                e.preventDefault();
                sales_pos_routing.set(lmethod,'product');
                
            });
            
        },
        check_overpaid:function(){
            var lresult = false;
            var lparent_pane = sales_pos_parent_pane;
            var lrow = $(lparent_pane).find('#sales_pos_payment_table>tbody>tr').last()[0];
            var lallocated_amount_input = $(lrow).find('[col_name="allocated_amount"]>input')[0];
            var lproduct_grand_total = parseFloat($(lparent_pane).find('#sales_pos_product_grand_total').text().replace(/[,]/g,''));
            lproduct_grand_total  = isNaN(lproduct_grand_total)?0:lproduct_grand_total;
            var lpayment_total = parseFloat($(lparent_pane).find('#sales_pos_payment_allocated_amount_total').text().replace(/[,]/g,''));
            lpayment_total  = isNaN(lpayment_total)?0:lpayment_total;
            
            $(lallocated_amount_input).popover_danger('destroy','','');
            if(lpayment_total !== 0){
                if( lproduct_grand_total < lpayment_total){
                    $(lallocated_amount_input).popover_danger('init','','Overpaid');
                    lresult = true;
                }
            }
            return lresult;
        },
        check_payment_valid:function(){
            var lresult = true;
            var lparent_pane = sales_pos_parent_pane;
            var lproduct_grand_total = parseFloat($(lparent_pane).find('#sales_pos_product_grand_total').text().replace(/[,]/g,''));
            lproduct_grand_total  = isNaN(lproduct_grand_total)?0:lproduct_grand_total;
            var lpayment_allocated_amount_total = parseFloat($(lparent_pane).find('#sales_pos_payment_allocated_amount_total').text().replace(/[,]/g,''));
            lpayment_allocated_amount_total  = isNaN(lpayment_allocated_amount_total)?0:lpayment_allocated_amount_total;
            var lcustomer_deposit_allocated_amount_total = parseFloat($(lparent_pane).
                    find('#sales_pos_customer_deposit_allocated_amount_total').text().replace(/[^0-9.]/g,''));
            lcustomer_deposit_allocated_amount_total  = isNaN(lcustomer_deposit_allocated_amount_total)?0:lcustomer_deposit_allocated_amount_total;
            var lallocated_amount_total = lpayment_allocated_amount_total+lcustomer_deposit_allocated_amount_total;
            
            var lis_sales_receipt_outstanding = $(lparent_pane).find('#sales_pos_customer_detail_is_sales_receipt_outstanding').text();
            
            
            if(lallocated_amount_total < lproduct_grand_total && lis_sales_receipt_outstanding !== 'True'){
                lresult = false;
            }
            else {}
            /*
            if(lallocated_amount_total > lproduct_grand_total){
                lresult = false;
            }
            */
            return lresult;
        },
        customer_deposit:{
            total_get:function(){
                var lparent_pane = sales_pos_parent_pane;                
                var result = parseFloat('0');                
                var lrow_arr = $(lparent_pane).find('#sales_pos_customer_deposit_table tbody tr');
                $.each(lrow_arr,function(lrow_idx, lrow){
                    result+=parseFloat($(lrow).find('[col_name="allocated_amount"] span')
                        .text().replace(/[^0-9.]/g,''));
                });
                
                return result;
            }
        },
        receipt:{
            type_get:function(){
                var json_data = {customer_id:$(sales_pos_parent_pane).find('#sales_pos_customer').select2('val')}
                var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_pos_data_support_url+'payment_type_get/',json_data);
                var lresponse = lresult.response;
                var lrow_arr = $('#sales_pos_payment_table tbody tr');                
                $.each(lrow_arr, function(idx, lrow){
                    var linpt = $(lrow).find('[col_name="payment_type"] input[original]')[0];
                    lresponse = $.grep(lresponse, function(lres){
                        if(lres.text !== 'CASH' || lres.id !== $(linpt).select2('val') ){
                            return true;
                        }
                    });
                });

                return lresponse;
            },
            max_get:function(){
                var lparent_pane = sales_pos_parent_pane;
                var prod_grand_total = parseFloat($(lparent_pane).find('#sales_pos_product_grand_total')
                    .text().replace(/[^0-9.]/g,''));
                var result = parseFloat('0');
                var total_receipt = parseFloat('0');
                var lcustomer_deposit = sales_pos_payment_section_methods.customer_deposit.total_get();
                var lrow_arr = $(lparent_pane).find('#sales_pos_payment_table tbody tr');
                $.each(lrow_arr,function(lrow_idx, lrow){
                    
                    if($(lrow).find('[col_name="amount"] input').prop('disabled')){
                        total_receipt+=parseFloat($(lrow).find('[col_name="allocated_amount"] input')
                            .val().replace(/[^0-9.]/g,''));
                    }
                });
                result = prod_grand_total - total_receipt - lcustomer_deposit;
                
                return result;
            },
            input_amount_set:function(c){
                $(c).off();
                var lrow = $(c).closest('tr')[0];
                var is_cash = $(lrow).find('[col_name="payment_type"] input[original]').select2('data').text === 'CASH'?true:false;
                
                if(!is_cash){
                    APP_EVENT.init().component_set(c).type_set('input')
                        .numeric_set()
                        .max_val_set(sales_pos_payment_section_methods.receipt.max_get())
                        .min_val_set(0).render();
                }
                else{
                    APP_EVENT.init().component_set(c).type_set('input')
                        .numeric_set()
                        .min_val_set(0).render();
                }
            
                $(c).on('blur',function(){
                    var lparent_pane = sales_pos_parent_pane;
                    var lf_grand_total = parseFloat($(lparent_pane).find('#sales_pos_product_grand_total').text().replace(/[^0-9.]/g,''));
                    var lrow = $(c).closest('tr')[0];
                    var lallocated_amount_td = $(lrow).find('[col_name="allocated_amount"]')[0];
                    var lf_amount = parseFloat($(this).val().replace(/[^0-9.]/g,''));
                    var lallocated_amount_input = $(lallocated_amount_td).find('input')[0];
                    var lf_alloc_amount_total = parseFloat('0');
                    var ltr_arr = $(lparent_pane).find('#sales_pos_payment_table tbody tr:not(:last)');
                    var lf_cust_dep_total = parseFloat($(lparent_pane).find('#sales_pos_customer_deposit_allocated_amount_total').text().replace(/[^0-9.]/g,''));
                    
                    $.each(ltr_arr, function(ltr_idx, ltr){
                        lf_alloc_amount_total += parseFloat($(ltr).find('[col_name="allocated_amount"] input').val().replace(/[^0-9.]/g,''));
                    });
                    
                    var lf_alloc_amount = lf_amount > (lf_grand_total  - lf_cust_dep_total - lf_alloc_amount_total)?
                        (lf_grand_total  - lf_cust_dep_total - lf_alloc_amount_total): lf_amount;

                    $(lallocated_amount_input).off();
                    APP_EVENT.init().component_set(lallocated_amount_input).type_set('input')
                            .numeric_set().min_val_set(0).render();
                    $(lallocated_amount_input).val(lf_alloc_amount).blur();
                    
                    sales_pos_payment_props.change_occurs=true;
                    sales_pos_payment_section_methods.table.total_calculate();
                    sales_pos_summary_methods.reset_all(); 
                    sales_pos_payment_section_methods.btn_controller_set();

                });
                
            }
        },
        
        table:{
            total_calculate:function(){
                var ltbody = $(sales_pos_parent_pane).find('#sales_pos_payment_table').find('tbody')[0];
                var lamount_total = 0;
                var lallocated_amount_total = 0;
                $.each($(ltbody).find('tr'),function(key, ltr){
                    var lamount = parseFloat($(ltr).find('[col_name="amount"] input').val().replace(/[,]/g,''));            
                    var lallocated_amount = parseFloat($(ltr).find('[col_name="allocated_amount"] input').val().replace(/[,]/g,''));            
                    lamount_total+=lamount;
                    lallocated_amount_total+=lallocated_amount;
                });
                
                $(sales_pos_parent_pane).find('#sales_pos_payment_total').text(APP_CONVERTER.thousand_separator(lamount_total));
                $(sales_pos_parent_pane).find('#sales_pos_payment_allocated_amount_total').text(APP_CONVERTER.thousand_separator(lallocated_amount_total));
            },
            customer_deposit:{
                empty:function(){
                    var lparent_pane = sales_pos_parent_pane;
                    $(lparent_pane).find('#sales_pos_customer_deposit_table tbody').empty();
                    $(lparent_pane).find('#sales_pos_customer_deposit_allocated_amount_total')
                        .text(APP_CONVERTER.thousand_separator('0.00'));
                },
                load:function(){
                    var lparent_pane = sales_pos_parent_pane;
                    var lproduct_grand_total = $(lparent_pane).find('#sales_pos_product_grand_total')
                            .text().replace(/[^0-9.]/g,'');
                    var lcustomer_id = $(lparent_pane).find('#sales_pos_customer').select2('val');
                    var ljson_data = {customer_id:lcustomer_id,product_grand_total:lproduct_grand_total};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(sales_pos_data_support_url+'customer_deposit_get/',ljson_data).response;
                    var ltbody = $(lparent_pane).find('#sales_pos_customer_deposit_table tbody')[0];
                    fast_draw = APP_COMPONENT.table_fast_draw;
                    var lallocated_amount_total = parseFloat('0');
                    $.each(lresponse, function(idx, customer_deposit){
                        var lrow = document.createElement('tr');  
                        var row_num = $(sales_pos_parent_pane).find('#sales_pos_customer_deposit_table').find('tbody').children().length;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_deposit_id',col_style:'vertical-align:middle;display:none',val:customer_deposit.customer_deposit_id,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_deposit_code',style:'vertical-align:middle;',val:'<span>'+customer_deposit.customer_deposit_code+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'customer_deposit_date',style:'vertical-align:middle',val:'<span>'+customer_deposit.customer_deposit_date+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'vertical-align:middle;text-align:right',val:'<span>'+customer_deposit.amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'allocated_amount',col_style:'vertical-align:middle;text-align:right',val:'<span>'+customer_deposit.allocated_amount+'</span>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'',val:'',type:'text'});
                        ltbody.appendChild(lrow);
                        lallocated_amount_total+=parseFloat(customer_deposit.allocated_amount.replace(/[^0-9.]/g,''));
                    });
                    $(lparent_pane).find('#sales_pos_customer_deposit_allocated_amount_total')
                            .text(APP_CONVERTER.thousand_separator(lallocated_amount_total));
                    sales_pos_summary_methods.reset_all();
                },
                reset:function(){
                    sales_pos_payment_section_methods.table.customer_deposit.empty();
                    var lparent_pane = sales_pos_parent_pane;
                    var lmethod = $(lparent_pane).find('#sales_pos_method').val();
                    switch(lmethod){
                        case 'add':
                            sales_pos_payment_section_methods.table.customer_deposit.load();
                            break;
                        case 'view':
                            break;
                    }
                },
            },
            payment:{
                empty:function(){
                    var lparent_pane = sales_pos_parent_pane;
                    $(lparent_pane).find('#sales_pos_payment_table tbody').empty();
                    $(lparent_pane).find('#sales_pos_payment_allocated_amount_total')
                        .text(APP_CONVERTER.thousand_separator('0.00'));
                    $(lparent_pane).find('#sales_pos_payment_total')
                        .text(APP_CONVERTER.thousand_separator('0.00'));
                },
                input_row_generate:function(){
                    fast_draw = APP_COMPONENT.table_fast_draw;
                    var lrow = document.createElement('tr');  
                    var row_num = $(sales_pos_parent_pane).find('#sales_pos_payment_table').find('tbody').children().length;
                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                    var lreceipt_code_td = fast_draw.col_add(lrow,{tag:'td',col_name:'code',style:'vertical-align:middle',val:'<span>[AUTO]</span>',type:'text'});                
                    var lpayment_type_td = fast_draw.col_add(lrow,{tag:'td',col_name:'payment_type',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});                
                    var lreceipt_date_td = fast_draw.col_add(lrow,{tag:'td',col_name:'receipt_date',style:'vertical-align:middle',val:'<span>'+APP_GENERATOR.CURR_DATE()+'</span>',type:'text'});
                    var lcustomer_bank_acc_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'customer_bank_acc',style:'vertical-align:middle;text-align:left',val:' ',type:'input'});
                    fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'bos_bank_account_id',style:'vertical-align:middle;text-align:left',val:'',type:'div',visible:false});
                    var lbos_bank_account_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'bos_bank_account',style:'vertical-align:middle;text-align:left',val:'<input original></input>',type:'text'});
                    var lamount_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'amount',style:'vertical-align:middle;text-align:right',val:'0.00',type:'input'});
                    var lallocated_amount_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'allocated_amount',style:'vertical-align:middle;text-align:right;',val:'0.00',type:'input',comp_attr:{disabled:true}});
                    var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    var lnew_row = APP_COMPONENT.new_row();    
                    laction.appendChild(lnew_row);

                    var lamount_input = $(lamount_td).find('input')[0];
                    APP_EVENT.init().component_set(lamount_input).type_set('input')
                        .numeric_set().min_val_set(0).max_val_set(0).render();

                    var lallocated_amount_input = $(lallocated_amount_td).find('input')[0];
                    APP_EVENT.init().component_set(lallocated_amount_input).type_set('input')
                        .numeric_set().min_val_set(0).max_val_set(0).render();

                    $(lbos_bank_account_td).find('input[original]').on('change',function(){
                        var lrow = $(this).closest('tr');
                        var lval = $(this).select2('val');
                        $(lrow).find('[col_name="bos_bank_account_id"] div')[0].innerHTML = lval;
                    });

                    $(lnew_row).on('click',function(){
                        var lparent_pane = sales_pos_parent_pane;
                        var lpayment_type_list = sales_pos_payment_section_methods.receipt.type_get();
                        var lrow = $(this).closest('tr');  
                        var lallocated_amount = parseFloat($(lrow).find('[col_name="allocated_amount"]').find('input').val());
                        var lreceipt_allocated_amount_total = parseFloat($(lparent_pane).find('#sales_pos_payment_allocated_amount_total').text().replace(/[,]/g,''));
                        var lcustomer_deposit_allocated_amount_total = parseFloat($(lparent_pane).find('#sales_pos_customer_deposit_allocated_amount_total').text().replace(/[,]/g,''));
                        var lallocated_amount_total = lreceipt_allocated_amount_total+lcustomer_deposit_allocated_amount_total;
                        var lproduct_grand_total = parseFloat($(lparent_pane).find('#sales_pos_product_grand_total').text().replace(/[,]/g,''));
                        var cont = true;

                        var lpayment_type = $(lrow).find('[col_name="payment_type"]')
                                .find('input').select2('data').text;
                        if(lallocated_amount === 0) cont = false;
                        if(lpayment_type !== 'CASH'){
                            var lbos_bank_account_val = $(lrow).find('[col_name="bos_bank_account"]').find('input[original]').select2('val');
                            
                            if($(lrow).find('[col_name="customer_bank_acc"]').find('input').val().replace(/[' ']/g,'') === ''){ 
                                cont = false;
                            }
                            
                            if(lbos_bank_account_val == '') cont = false;
                        }
                        if(sales_pos_payment_section_methods.check_overpaid()) cont = false;
                        if(lproduct_grand_total<=lallocated_amount_total) cont = false;

                        if(lpayment_type_list.length == 0) false
                        if(cont){
                            var lbos_bank_account_data = $(lrow).find('[col_name="bos_bank_account"]')
                                .find('input[original]').select2('data');
                            var lbox_bank_account_text = lbos_bank_account_data === null?'':lbos_bank_account_data.text;
                            $(lrow).find('[col_name="customer_bank_acc"]').find('input').prop('disabled',true);
                            $(lrow).find('[col_name="bos_bank_account"]')[0].innerHTML = '<div>'+lbox_bank_account_text+'</div>';
                            $(lrow).find('[col_name="amount"]').find('input').prop('disabled',true);
                            $(lrow).find('[col_name="allocated_amount"]').find('input').prop('disabled',true);
                            $(lrow).find('[col_name="payment_type"] input[original]').select2('disable');
                            var ltrash = APP_COMPONENT.trash();
                            $(ltrash).on('click',function(){
                                var lparent_pane = sales_pos_parent_pane;
                                var lmypayment_type_list = sales_pos_payment_section_methods.receipt.type_get();
                                var lrec_type_inpt = $(lparent_pane).find('#sales_pos_payment_table').find('tbody tr:last-child [col_name="payment_type"] input[original]')[0];
                                var lselected_receipt = $(lrec_type_inpt).select2('data');
                                var lon_the_list = false;
                                $.each(lmypayment_type_list,function(rec_idx,rec){
                                    if(rec.id === lselected_receipt.id) lon_the_list = true;
                                });
                                if(!lon_the_list) lmypayment_type_list.push(lselected_receipt);
                                $(lrec_type_inpt).select2({data:lmypayment_type_list});

                                var lmyamount_input = $(lparent_pane).find('#sales_pos_payment_table').find('tbody tr:last-child [col_name="amount"] input')[0];
                                sales_pos_payment_section_methods.receipt.input_amount_set(lmyamount_input);

                                sales_pos_payment_section_methods.table.total_calculate();
                                sales_pos_summary_methods.reset_all();
                                sales_pos_payment_section_methods.btn_controller_set();
                            });
                            $(lrow).find('[col_name="action"]').empty();
                            $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                            var ltbody = $(sales_pos_parent_pane).find('#sales_pos_payment_table').find('tbody')[0];
                            var linput_row = sales_pos_payment_section_methods.table.payment.input_row_generate();
                            ltbody.appendChild(linput_row);

                            setTimeout(function(){$(linput_row).find('[col_name="amount"]').find('input').select()},200);
                        }
                    });

                    $(lpayment_type_td).find('input').on('change',function(){
                        var lrow = $(this).closest('tr');
                        var linput = $(lrow).find('[col_name="amount"] input')[0];
                        $(linput).val('').blur();

                        sales_pos_payment_section_methods.receipt.input_amount_set(linput);
                        
                        $(lbos_bank_account_td).find('input[original]').select2('data',null);
                        $(lbos_bank_account_td).find('input[original]').select2('disable');
                        
                        if($(this).select2('data')['text'] ==='CASH'){
                            $(lcustomer_bank_acc_td).find('input').val('');
                            $(lcustomer_bank_acc_td).find('input').prop('disabled',true);
                            
                        }
                        else{
                            $(lcustomer_bank_acc_td).find('input').prop('disabled',false);
                            $(lbos_bank_account_td).find('input[original]').select2('enable');
                        }
                    });

                    $($(lbos_bank_account_td).find('input[original]')).select2({data:sales_pos_payment_data.bos_bank_account_list});
                    $($(lbos_bank_account_td).find('input[original]')).select2('data',null);
                    $($(lbos_bank_account_td).find('input[original]')).select2('disable');
                    
                    var lpayment_type_list = sales_pos_payment_section_methods.receipt.type_get();
                    $($(lpayment_type_td).find('input[original]')).select2({data:lpayment_type_list});
                    $($(lpayment_type_td).find('input[original]')).select2('data',lpayment_type_list[0]).change();

                    
                    return lrow;
                },
                reset:function(){
                    sales_pos_payment_section_methods.table.payment.empty();
                    var lparent_pane = sales_pos_parent_pane;
                    var lmethod = $(lparent_pane).find('#sales_pos_method').val();
                    var ltbody = $(sales_pos_parent_pane).find('#sales_pos_payment_table tbody')[0];
                    switch(lmethod){
                        case 'add':
                            var linput_row = sales_pos_payment_section_methods.table.payment.input_row_generate();
                            ltbody.appendChild(linput_row);
                            break;
                        case 'view':
                            break;
                    }
                    
                    
                    sales_pos_payment_section_methods.table.total_calculate();
                },
            },
        }
    }
</script>