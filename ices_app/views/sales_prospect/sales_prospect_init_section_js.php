<script>
    sales_prospect_init_section_methods={
        hide_all:function(){
            var lparent_pane = sales_prospect_parent_pane;            
            $(lparent_pane).find('[routing_section="init"] .hide_all').hide();
            $(lparent_pane).find('#sales_prospect_btn_customer_new').hide();
        },
        show_hide:function(){
            sales_prospect_init_section_methods.hide_all();
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#sales_prospect_btn_customer_new').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#sales_prospect_code').closest('.form-group').show();
                    $(lparent_pane).find('#sales_prospect_sales_prospect_status').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var linit_section = $(sales_prospect_parent_pane).find('[routing_section="init"]')[0];
            APP_COMPONENT.disable_all(linit_section);
            $(lparent_pane).find('#sales_prospect_btn_customer_new').prop('disabled',true);
        },
        enable_disable:function(){
            var lparent_pane = sales_prospect_parent_pane;
            sales_prospect_init_section_methods.disable_all();
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#sales_prospect_customer').select2('enable');
                    $(lparent_pane).find('#sales_prospect_btn_customer_new').prop('disabled',false);
                    $(lparent_pane).find('#sales_prospect_price_list').select2('enable');
                    $(lparent_pane).find('#sales_prospect_sales_inquiry_by').select2('enable');
                break;
            }
        },
        reset_all:function(){
            var lparent_pane = sales_prospect_parent_pane;
            sales_prospect_init_section_methods.customer.reset();
            sales_prospect_init_section_methods.price_list.reset();
        },
        reset_dependent_section:function(){
            sales_prospect_product_section_methods.reset_all();
        },
        btn_controller_set:function(){
            var lvalid = true;
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            sales_prospect_methods.btn_controller_reset();
            
            $(lparent_pane).find('#sales_prospect_btn_back').show();
            $(lparent_pane).find('#sales_prospect_btn_prev').hide();
            $(lparent_pane).find('#sales_prospect_btn_next').show();
        
            var lparent_pane = sales_prospect_parent_pane;
            if($(lparent_pane).find('#sales_prospect_customer').select2('val') ==='') lvalid = false;
            if($(lparent_pane).find('#sales_prospect_price_list').select2('val') ==='') lvalid = false;
            
            
            if(lvalid){
                $(lparent_pane).find('#sales_prospect_btn_next').prop('disabled',false);
                $(lparent_pane).find('#sales_prospect_btn_next').on('click',function(e){
                    e.preventDefault();
                    sales_prospect_routing.set(lmethod,'product');
                });
            }
            
            
            $(lparent_pane).find('#sales_prospect_btn_prev').on('click',function(e){
                e.preventDefault();
            });
            
        },
        customer:{
            reset:function(){       
                var lparent_pane = sales_prospect_parent_pane;
                $(lparent_pane).find('#sales_prospect_customer').select2('data',null);
            },
            set:function(){
            },
            detail_reset:function(){
                var lparent_pane = sales_prospect_parent_pane;
                var ldetails = $(lparent_pane).find('#sales_prospect_customer_detail').find('[id^="sales_prospect_customer_detail"]');
                $.each(ldetails,function(){$(this).text('');})
                
            },
            detail_set:function(){
                var lparent_pane = sales_prospect_parent_pane;
                var lcustomer_id = $(lparent_pane).find('#sales_prospect_customer').select2('val');
                var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'customer_detail_get',{customer_id:lcustomer_id});
                var customer_detail = lresult.response;
                $(lparent_pane).find('#sales_prospect_customer_detail_name').text(customer_detail.customer_name);
                $(lparent_pane).find('#sales_prospect_customer_detail_code').text(customer_detail.customer_code);
                $(lparent_pane).find('#sales_prospect_customer_detail_phone').text(customer_detail.customer_phone);
                $(lparent_pane).find('#sales_prospect_customer_detail_bb_pin').text(customer_detail.customer_bb_pin);
                $(lparent_pane).find('#sales_prospect_customer_detail_email').text(customer_detail.customer_email);
                //$(lparent_pane).find('#sales_prospect_customer_detail_is_credit').text(customer_detail.is_credit);
                $(lparent_pane).find('#sales_prospect_customer_detail_is_sales_receipt_outstanding').text(customer_detail.is_sales_receipt_outstanding);
            }
        },
        price_list:{
            reset:function(){
                var lparent_pane = sales_prospect_parent_pane;
                $(lparent_pane).find('#sales_prospect_price_list').select2('data',null);
                $(lparent_pane).find('#sales_prospect_price_list').select2({data:[]});
                
            },
            set:function(){
                var lcustomer_id = $(sales_prospect_parent_pane).find('#sales_prospect_customer').select2('val');
                var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'price_list_list_get/'
                    ,{customer_id:lcustomer_id});
                var lresponse = lresult.response;
                $(sales_prospect_parent_pane).find('#sales_prospect_price_list').select2({data:lresponse});
            }
        },
    }
    
    sales_prospect_init_section_bind_events = function(){
        var lparent_pane = sales_prospect_parent_pane;
        $(lparent_pane).find('#sales_prospect_customer').on('change',function(){            
            
            var lcustomer_id = $(this).select2('val');
            sales_prospect_init_section_methods.customer.detail_reset();
            sales_prospect_init_section_methods.price_list.reset();
            
            if(lcustomer_id === ''){
                
            }
            else{
                sales_prospect_init_section_methods.customer.detail_set();                
                sales_prospect_init_section_methods.price_list.set();
            }
            sales_prospect_init_section_methods.reset_dependent_section();
            sales_prospect_summary_methods.reset_all();
            sales_prospect_init_section_methods.btn_controller_set();
            
        });
        
        $("#sales_prospect_btn_customer_new").on("click",function(){ 
            $("#modal_customer").find("#customer_method").val("add");
            customer_components_prepare();
            $('#modal_customer').modal('show');
            customer_after_submit = function(){
                var lcustomer_id = $("#modal_customer").find("#customer_id").val();
                var lcustomer_name = $("#modal_customer").find("#customer_name").val();
                $("#sales_prospect_customer").select2("data",{id:lcustomer_id,text:lcustomer_name}).change();
                $('#modal_customer').modal('hide');
                sales_prospect_init_section_methods.reset_dependent_section();
                sales_prospect_summary_methods.reset_all();

            }
        });        
        
        $(lparent_pane).find('#sales_prospect_price_list').on('change',function(){            
            var price_list_id = $(sales_prospect_parent_pane).find('#sales_prospect_price_list').select2('val');
            var lparent_pane = sales_prospect_parent_pane;
            if(price_list_id!==''){
                sales_prospect_init_section_methods.reset_dependent_section();
                var json_data = {price_list_id:price_list_id};
                var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'price_list_get/',json_data);
                var lresponse = lresult.response;
                $(lparent_pane).find('#sales_prospect_delivery_checkbox').closest('label').hide();
                $(lparent_pane).find('#sales_prospect_expedition').closest('.form-group').hide();
                $(lparent_pane).find('#sales_prospect_product_discount').closest('tr').hide();
                $(lparent_pane).find('#sales_prospect_delivery_cost_estimation').closest('tr').hide();
                if(lresponse !== {}){
                    if(lresponse.is_delivery === '1'){ 
                        $(lparent_pane).find('#sales_prospect_delivery_checkbox').closest('label').show();
                        $(lparent_pane).find('#sales_prospect_delivery_cost_estimation').closest('tr').show();
                        $(lparent_pane).find('#sales_prospect_expedition').closest('.form-group').show();
                    }
                    if(lresponse.is_discount === '1') $(lparent_pane).find('#sales_prospect_product_discount').closest('tr').show();                    
                }                
            }
            sales_prospect_summary_methods.reset_all();
            sales_prospect_init_section_methods.btn_controller_set();            
        });        
    }
</script>