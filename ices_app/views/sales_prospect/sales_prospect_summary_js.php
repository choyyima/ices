<script>
    sales_prospect_summary_methods={
        reset_all:function(){
            sales_prospect_summary_methods.customer_set();
            sales_prospect_summary_methods.price_list_set();
            sales_prospect_summary_methods.product_grand_total_calculate();
            sales_prospect_summary_methods.payment_total_calculate();
            sales_prospect_summary_methods.outstanding_amount_calculate();
            sales_prospect_summary_methods.exchange_amount_calculate();
            //sales_prospect_summary_methods.overpaid_calculate();
        },
        customer_set:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lcustomer = $(lparent_pane).find('#sales_prospect_customer_detail_name').text();
            $(lparent_pane).find('#sales_prospect_summary_customer').text(lcustomer);
        },
        price_list_set:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lprice_list = $('#sales_prospect_price_list').select2('data') !==null?
                    $('#sales_prospect_price_list').select2('data').text:'';
            $(lparent_pane).find('#sales_prospect_summary_price_list').text(lprice_list);
        },
        product_grand_total_calculate:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lproduct_grand_total = $(lparent_pane).find('#sales_prospect_product_grand_total').text().replace(/[,]/g,'');
            $(lparent_pane).find('#sales_prospect_summary_product_grand_total').text(APP_CONVERTER.thousand_separator(lproduct_grand_total));
        },
        payment_total_calculate:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lpayment_total = '0.00';
            lpayment_total = $(lparent_pane).find('#sales_prospect_payment_allocated_amount_total').text().replace(/[,]/g,'');
            $(lparent_pane).find('#sales_prospect_summary_payment_total').text(APP_CONVERTER.thousand_separator(lpayment_total));

        },
        outstanding_amount_calculate:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var loutstanding_amount = '0.00';
            var lproduct_grand_total = $(lparent_pane).find('#sales_prospect_summary_product_grand_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lproduct_grand_total))) lproduct_grand_total = '0';
            var lpayment_total = $(lparent_pane).find('#sales_prospect_payment_allocated_amount_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lpayment_total))) lpayment_total = '0';

            loutstanding_amount = parseFloat(lproduct_grand_total) - parseFloat(lpayment_total);
            if(parseFloat(loutstanding_amount)<parseFloat('0') ) loutstanding_amount = parseFloat('0.00');
            $(lparent_pane).find('#sales_prospect_summary_outstanding_amount').text(APP_CONVERTER.thousand_separator(loutstanding_amount));

        },
        exchange_amount_calculate:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lexchange_amount = '0.00';
            var lproduct_grand_total = $(lparent_pane).find('#sales_prospect_summary_product_grand_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lproduct_grand_total))) lproduct_grand_total = '0';
            var lpayment_total = $(lparent_pane).find('#sales_prospect_payment_allocated_amount_total').text().replace(/[,]/g,'');
            if(isNaN(parseFloat(lpayment_total))) lpayment_total = '0';

            lexchange_amount =  parseFloat(lpayment_total) - parseFloat(lproduct_grand_total);
            if(parseFloat(lexchange_amount)<parseFloat('0') ) lexchange_amount = parseFloat('0.00');
            $(lparent_pane).find('#sales_prospect_summary_exchange_amount').text(APP_CONVERTER.thousand_separator(lexchange_amount));

        },
        /*
        overpaid_calculate:function(){
            var lparent_pane = sales_prospect_parent_pane;            
            var lproduct_grand_total = parseFloat($(lparent_pane).find('#sales_prospect_summary_product_grand_total').text().replace(/[,]/g,''));
            var lpayment_total = parseFloat($(lparent_pane).find('#sales_prospect_summary_payment_total').text().replace(/[,]/g,''));
            var loverpaid = lpayment_total - lproduct_grand_total;
            
            $(lparent_pane).find('#sales_prospect_summary_overpaid').text(APP_CONVERTER.thousand_separator(loverpaid));
        }
        */
    }
</script>