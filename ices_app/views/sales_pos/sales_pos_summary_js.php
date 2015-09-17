<script>
    sales_pos_summary_methods={
        reset_all:function(){
            sales_pos_summary_methods.customer_set();
            sales_pos_summary_methods.price_list_set();
            sales_pos_summary_methods.product_grand_total_calculate();
            sales_pos_summary_methods.payment_total_calculate();
            sales_pos_summary_methods.change_amount_calculate();
            sales_pos_summary_methods.outstanding_amount_calculate();
            
            //sales_pos_summary_methods.overpaid_calculate();
        },
        customer_set:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lcustomer = $(lparent_pane).find('#sales_pos_customer_detail_name').text();
            $(lparent_pane).find('#sales_pos_summary_customer').text(lcustomer);
        },
        price_list_set:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lprice_list = $('#sales_pos_price_list').select2('data') !==null?
                    $('#sales_pos_price_list').select2('data').text:'';
            $(lparent_pane).find('#sales_pos_summary_price_list').text(lprice_list);
        },
        product_grand_total_calculate:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lproduct_grand_total = $(lparent_pane).find('#sales_pos_product_grand_total').text().replace(/[,]/g,'');
            $(lparent_pane).find('#sales_pos_summary_product_grand_total').text(APP_CONVERTER.thousand_separator(lproduct_grand_total));
        },
        payment_total_calculate:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lpayment_total = parseFloat('0.00');
            var ltotal_receipt = $(lparent_pane).find('#sales_pos_payment_total').text().replace(/[^0-9.]/g,'');
            var ltotal_customer_deposit = $(lparent_pane).find('#sales_pos_customer_deposit_allocated_amount_total').text().replace(/[^0-9.]/g,'');
            ltotal_receipt = isNaN(parseFloat(ltotal_receipt))?parseFloat('0'):parseFloat(ltotal_receipt);
            ltotal_customer_deposit = isNaN(parseFloat(ltotal_customer_deposit))?parseFloat('0'):parseFloat(ltotal_customer_deposit);
            var lpayment_total = ltotal_receipt+ltotal_customer_deposit;
            
            $(lparent_pane).find('#sales_pos_summary_payment_grand_total').text(APP_CONVERTER.thousand_separator(lpayment_total));

        },
        outstanding_amount_calculate:function(){
            var lparent_pane = sales_pos_parent_pane;
            var loutstanding_amount = '0.00';
            var lproduct_grand_total = $(lparent_pane).find('#sales_pos_summary_product_grand_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lproduct_grand_total))) lproduct_grand_total = '0';
            var lpayment_total = $(lparent_pane).find('#sales_pos_payment_allocated_amount_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lpayment_total))) lpayment_total = '0';
            var lcustomer_deposit_total = $(lparent_pane).find('#sales_pos_customer_deposit_allocated_amount_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lcustomer_deposit_total))) lcustomer_deposit_total = '0';
            
            
            loutstanding_amount = parseFloat(lproduct_grand_total) - parseFloat(lpayment_total) - parseFloat(lcustomer_deposit_total);
            if(parseFloat(loutstanding_amount)<parseFloat('0') ) loutstanding_amount = parseFloat('0.00');
            $(lparent_pane).find('#sales_pos_summary_outstanding_amount').text(APP_CONVERTER.thousand_separator(loutstanding_amount));
        },
        change_amount_calculate:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lchange_amount = '0.00';
            var lproduct_grand_total = $(lparent_pane).find('#sales_pos_summary_product_grand_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lproduct_grand_total))) lproduct_grand_total = '0';
            var lpayment_total = $(lparent_pane).find('#sales_pos_payment_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lpayment_total))) lpayment_total = '0';
            var lcustomer_deposit_total = $(lparent_pane).find('#sales_pos_customer_deposit_allocated_amount_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lcustomer_deposit_total))) lcustomer_deposit_total = '0';

            lchange_amount =  parseFloat(lpayment_total) +parseFloat(lcustomer_deposit_total)- parseFloat(lproduct_grand_total);
            if(parseFloat(lchange_amount)<parseFloat('0') ) lchange_amount = parseFloat('0.00');
            $(lparent_pane).find('#sales_pos_summary_change_amount').text(APP_CONVERTER.thousand_separator(lchange_amount));

        },
        
    }
</script>