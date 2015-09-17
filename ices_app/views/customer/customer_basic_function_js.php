<script>
    var customer_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var customer_ajax_url = null;
    var customer_index_url = null;
    var customer_view_url = null;
    var customer_window_scroll = null;
    var customer_data_support_url = null;
    var customer_common_ajax_listener = null;
    
    var customer_init = function(){
        var parent_pane = customer_parent_pane;

        customer_ajax_url = '<?php echo $ajax_url ?>';
        customer_index_url = '<?php echo $index_url ?>';
        customer_view_url = '<?php echo $view_url ?>';
        customer_window_scroll = '<?php echo $window_scroll; ?>';
        customer_data_support_url = '<?php echo $data_support_url; ?>';
        customer_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
    }

    var customer_after_submit = function(){

    }
    
    var customer_methods = {
        status_label_get:function(){
            var parent_pane = customer_parent_pane;
            return $($(parent_pane).find('#customer_customer_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#customer_customer_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#customer_customer_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lcustomer_id = $('#customer_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(customer_data_support_url+'customer_current_status/',{data:lcustomer_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = customer_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = customer_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            
            $.each(lcomponents,function(key, val){
                $(val).prop('disabled',true);
            });
            $(lparent_pane).find('#customer_customer_type').select2('disable',true);
            $(lparent_pane).find('#customer_is_credit').select2('disable',true);
        },
        submit:function(){
            var parent_pane = customer_parent_pane;
            var ajax_url = customer_index_url;
            var method = $(parent_pane).find("#customer_method").val();
            var customer_id = $(parent_pane).find("#customer_id").val();        
            var json_data = {
                ajax_post:true,
                customer:{},
                customer_type:[],
                message_session:true
            };

            switch(method){
                case 'add':
                    json_data.customer.code = $(parent_pane).find("#customer_code").val();
                    json_data.customer.name = $(parent_pane).find("#customer_name").val();
                    json_data.customer.address = $(parent_pane).find("#customer_address").val();
                    json_data.customer.city = $(parent_pane).find("#customer_city").val();
                    json_data.customer.country = $(parent_pane).find("#customer_country").val();
                    json_data.customer.phone = $(parent_pane).find("#customer_phone").val();
                    json_data.customer.phone2 = $(parent_pane).find("#customer_phone2").val();
                    json_data.customer.phone3 = $(parent_pane).find("#customer_phone3").val();
                    json_data.customer.bb_pin = $(parent_pane).find("#customer_bb_pin").val();
                    json_data.customer.email = $(parent_pane).find("#customer_email").val();
                    json_data.customer.notes = $(parent_pane).find("#customer_notes").val();
                    json_data.customer.is_credit = $(parent_pane).find("#customer_is_credit").val();
                    json_data.customer.is_sales_receipt_outstanding = $(parent_pane).find("#customer_is_sales_receipt_outstanding").val();
                    var ltbody = $(parent_pane).find('#customer_customer_type_table').find('tbody')[0];
                    $.each($(ltbody).children(),function(key, val){
                        json_data.customer_type.push($(val).find('[name="id"]').text());
                    });
                    ajax_url +='add/';
                    break;
                case 'view':
                    json_data.customer.id = customer_id;
                    json_data.customer.code = $(parent_pane).find("#customer_code").val();
                    json_data.customer.name = $(parent_pane).find("#customer_name").val();
                    json_data.customer.address = $(parent_pane).find("#customer_address").val();
                    json_data.customer.city = $(parent_pane).find("#customer_city").val();
                    json_data.customer.country = $(parent_pane).find("#customer_country").val();
                    json_data.customer.phone = $(parent_pane).find("#customer_phone").val();
                    json_data.customer.phone2 = $(parent_pane).find("#customer_phone2").val();
                    json_data.customer.phone3 = $(parent_pane).find("#customer_phone3").val();
                    json_data.customer.bb_pin = $(parent_pane).find("#customer_bb_pin").val();
                    json_data.customer.email = $(parent_pane).find("#customer_email").val();
                    json_data.customer.notes = $(parent_pane).find("#customer_notes").val();
                    json_data.customer.customer_status = $(parent_pane).find("#customer_customer_status").val();
                    json_data.customer.is_credit = $(parent_pane).find("#customer_is_credit").val();
                    json_data.customer.is_sales_receipt_outstanding = $(parent_pane).find("#customer_is_sales_receipt_outstanding").val();
                    var ltbody = $(parent_pane).find('#customer_customer_type_table').find('tbody')[0];
                    $.each($(ltbody).children(),function(key, val){
                        json_data.customer_type.push($(val).find('[name="id"]').text());
                    });
                    var lajax_method = $(parent_pane).find('#customer_customer_status').select2('data').method;
                    ajax_url +=lajax_method+'/'+customer_id;
                    break;
            }


            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(customer_parent_pane).find('#customer_id').val(result.trans_id);
                if(customer_view_url !==''){
                    var url = customer_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    customer_after_submit();
                }
            }
        }
    }

    var customer_bind_event = function(){
        var parent_pane = customer_parent_pane;
        
        $(parent_pane).find('#customer_submit').off('click');
        $(parent_pane).find('#customer_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = customer_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                customer_methods.submit();
            });
            $(customer_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        

    }
    
    var customer_components_prepare= function(){
        var method = $(customer_parent_pane).find("#customer_method").val();
        var lparent_pane = customer_parent_pane;
        
        var customer_data_set = function(){
            switch(method){
                case "add":
                    $(customer_parent_pane).find('#customer_customer_type_table').find('tbody').empty();
                    $(customer_parent_pane).find("#customer_code").val("[AUTO GENERATE]");
                    $(customer_parent_pane).find("#customer_name").val("");
                    $(customer_parent_pane).find("#customer_address").val("");
                    $(customer_parent_pane).find("#customer_city").val("");
                    $(customer_parent_pane).find("#customer_country").val("");
                    $(customer_parent_pane).find("#customer_phone").val("");
                    $(customer_parent_pane).find("#customer_phone2").val("");
                    $(customer_parent_pane).find("#customer_phone3").val("");
                    $(customer_parent_pane).find("#customer_bb_pin").val("");
                    $(customer_parent_pane).find("#customer_email").val("");
                    $(customer_parent_pane).find("#customer_notes").val("");
                    $(customer_parent_pane).find("#customer_customer_status").select2(
                            {data:[{id:"A",text:APP_CONVERTER.status_attr("ACTIVE")}]}
                    );
                    
                    $(customer_parent_pane).find("#customer_is_credit").select2(
                        "data",{id:"0",text:APP_CONVERTER.status_attr("False")}
                    );
                    $(customer_parent_pane).find("#customer_is_sales_receipt_outstanding").select2(
                        "data",{id:"0",text:APP_CONVERTER.status_attr("False")}
                    );
            
                    APP_FORM.status.default_status_set('customer',
                        $(lparent_pane).find('#customer_customer_status')
                    );
                    break;
                case "view":
                    $(customer_parent_pane).find('#customer_customer_type_table').find('tbody').empty();
                    var customer_id = $(customer_parent_pane).find("#customer_id").val();
                    var json_data={data:customer_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(customer_data_support_url+"customer_get",json_data);
                    var rs_customer = lresult.response;
                    if(rs_customer !== null){
                        $(customer_parent_pane).find("#customer_code").val(rs_customer.code);
                        $(customer_parent_pane).find("#customer_name").val(rs_customer.name);
                        $(customer_parent_pane).find("#customer_customer_status").select2("data",{id:rs_customer.customer_status,text:rs_customer.customer_status_name});
                        $(customer_parent_pane).find("#customer_is_credit").select2("data",{id:rs_customer.is_credit,text:rs_customer.is_credit_text});
                        $(customer_parent_pane).find("#customer_is_sales_receipt_outstanding").select2("data",{id:rs_customer.is_sales_receipt_outstanding,text:rs_customer.is_sales_receipt_outstanding_text});
                        $(customer_parent_pane).find("#customer_address").val(rs_customer.address);
                        $(customer_parent_pane).find("#customer_city").val(rs_customer.city);
                        $(customer_parent_pane).find("#customer_country").val(rs_customer.country);
                        $(customer_parent_pane).find("#customer_phone").val(rs_customer.phone);
                        $(customer_parent_pane).find("#customer_phone2").val(rs_customer.phone2);
                        $(customer_parent_pane).find("#customer_phone3").val(rs_customer.phone3);
                        $(customer_parent_pane).find("#customer_bb_pin").val(rs_customer.bb_pin);
                        $(customer_parent_pane).find("#customer_email").val(rs_customer.email);
                        $(customer_parent_pane).find("#customer_notes").val(rs_customer.notes);
                        $(customer_parent_pane).find("#customer_credit").val(rs_customer.customer_credit);
                        $(customer_parent_pane).find("#customer_debit").val(rs_customer.customer_debit);
                        
                        var customer_status_list = rs_customer.customer_status_list;
                        $(customer_parent_pane).find("#customer_customer_status").select2({data:customer_status_list});
                        
                    };
                    
                    json_data={data:customer_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(customer_data_support_url+"customer_type_get",json_data);
                    var rs_customer_type  = lresult.response;
                    if(rs_customer_type !== null){
                        $.each(rs_customer_type,function(key, val){
                            var customer_type = {id:val.id, text:val.name};                        
                            $(customer_parent_pane).find("#customer_customer_type").select2('data',customer_type).change();
                        });
                        
                    };
                    
                    break;            
            }
        }
    
        var customer_components_enable_disable = function(){
            var lparent_pane = customer_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_method').val();    
            customer_methods.disable_all();
            
            switch(method){
                case "add":
                case 'view':
                    
                    $(customer_parent_pane).find("#customer_name").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_address").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_city").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_country").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_phone").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_phone2").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_phone3").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_bb_pin").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_email").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_notes").prop("disabled",false);
                    $(customer_parent_pane).find("#customer_customer_type").select2("enable");
                    $(customer_parent_pane).find("#customer_is_credit").select2("enable");
                    $(customer_parent_pane).find("#customer_is_sales_receipt_outstanding").select2("enable");
                    break;
            }
        }
        
        var customer_components_show_hide = function(){
            var lparent_pane = customer_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_method').val();
            customer_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find('#customer_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_address').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_is_credit').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_is_sales_receipt_outstanding').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_customer_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_customer_type').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_phone').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_phone2').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_phone3').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_email').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_bb_pin').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_address').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_city').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_country').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_credit').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_debit').closest('div [class*="form-group"]').show();
                    break;
            }
        }
        
        customer_components_show_hide();
        customer_components_enable_disable();
        customer_data_set();
    }
    
</script>