<script>
var supplier_parent_pane = $('<?php echo $detail_tab; ?>')[0];
var supplier_ajax_url = null;
var supplier_index_url = null;
var supplier_view_url = null;
var supplier_window_scroll = null;
var supplier_data_support_url = null;
var supplier_common_ajax_listener = null;
var supplier_component_prefix_id = '';

var supplier_init = function(){
    var parent_pane = supplier_parent_pane;

    supplier_ajax_url = '<?php echo $ajax_url ?>';
    supplier_index_url = '<?php echo $index_url ?>';
    supplier_view_url = '<?php echo $view_url ?>';
    supplier_window_scroll = '<?php echo $window_scroll; ?>';
    supplier_data_support_url = '<?php echo $data_support_url; ?>';
    supplier_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
}

var supplier_after_submit = function(){

}

var supplier_methods = {
    hide_all:function(){
        var lparent_pane = supplier_parent_pane;
        $(lparent_pane).find('.hide_all').hide();
    },
    disable_all:function(){
        var lparent_pane = supplier_parent_pane;
        var lcomponents = $(lparent_pane).find('.disable_all');
        APP_COMPONENT.disable_all(lparent_pane);
        
    },
    submit:function(){
        var lparent_pane = supplier_parent_pane;
        var lprefix_id = supplier_component_prefix_id;
        var ajax_url = supplier_index_url;
        var lmethod = $(lparent_pane).find(lprefix_id+"_method").val();
        var supplier_id = $(lparent_pane).find(lprefix_id+"_id").val();
        
        var json_data = {
            ajax_post:true,
            message_session:true,
            supplier:{}
        };
        
        switch(lmethod){
            case 'add':
                json_data.supplier.code = $(lparent_pane).find("#supplier_code").val();
                json_data.supplier.name = $(lparent_pane).find("#supplier_name").val();
                json_data.supplier.address = $(lparent_pane).find("#supplier_address").val();
                json_data.supplier.city = $(lparent_pane).find("#supplier_city").val();
                json_data.supplier.country = $(lparent_pane).find("#supplier_country").val();
                json_data.supplier.phone = $(lparent_pane).find("#supplier_phone").val();
                json_data.supplier.phone2 = $(lparent_pane).find("#supplier_phone2").val();
                json_data.supplier.phone3 = $(lparent_pane).find("#supplier_phone3").val();
                json_data.supplier.bb_pin = $(lparent_pane).find("#supplier_bb_pin").val();
                json_data.supplier.email = $(lparent_pane).find("#supplier_email").val();
                json_data.supplier.notes = $(lparent_pane).find("#supplier_notes").val();
                json_data.supplier.supplier_status = $(lparent_pane).find("#supplier_supplier_status").val();
                break;
            case 'view':
                json_data.supplier.id = supplier_id;
                json_data.supplier.code = $(lparent_pane).find("#supplier_code").val();
                json_data.supplier.name = $(lparent_pane).find("#supplier_name").val();
                json_data.supplier.address = $(lparent_pane).find("#supplier_address").val();
                json_data.supplier.city = $(lparent_pane).find("#supplier_city").val();
                json_data.supplier.country = $(lparent_pane).find("#supplier_country").val();
                json_data.supplier.phone = $(lparent_pane).find("#supplier_phone").val();
                json_data.supplier.phone2 = $(lparent_pane).find("#supplier_phone2").val();
                json_data.supplier.phone3 = $(lparent_pane).find("#supplier_phone3").val();
                json_data.supplier.bb_pin = $(lparent_pane).find("#supplier_bb_pin").val();
                json_data.supplier.email = $(lparent_pane).find("#supplier_email").val();
                json_data.supplier.notes = $(lparent_pane).find("#supplier_notes").val();
                json_data.supplier.supplier_status = $(lparent_pane).find("#supplier_supplier_status").val();
                break;
        }
        
        var lajax_method='';
        switch(lmethod){
            case 'add':
                lajax_method = 'supplier_add';
                break;
            case 'view':
                lajax_method = $(lparent_pane).find(lprefix_id+'_supplier_status').select2('data').method;
                break;
        }
        ajax_url +=lajax_method+'/'+supplier_id;
        
        result = null;
        result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
        if(result.success ===1){
            $(supplier_parent_pane).find('#supplier_id').val(result.trans_id);
            if(supplier_view_url !==''){
                var url = supplier_view_url+result.trans_id;
                window.location.href=url;
            }
            else{
                supplier_after_submit();
            }
        }
        
    }
}

var supplier_bind_event = function(){
    var parent_pane = supplier_parent_pane;
    var amount = $(parent_pane).find('#supplier_amount');
    APP_EVENT.init().component_set(amount).type_set('input').numeric_set().render();
    $(amount).on('blur',function(){
        $(parent_pane).find('#supplier_available_amount').val($(this).val());
    });

    if($(parent_pane).find("#supplier_submit").length>0){
        $(parent_pane).find('#supplier_submit').off('click');
        $(parent_pane).find('#supplier_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = supplier_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                supplier_methods.submit();
            });
            $(supplier_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
    }

}
    
var supplier_components_prepare= function(){
    var method = $(supplier_parent_pane).find("#supplier_method").val();
    var lparent_pane = supplier_parent_pane;

    var supplier_data_set = function(){
        switch(method){
            case "add":
                $(supplier_parent_pane).find("#supplier_code").val("");
                $(supplier_parent_pane).find("#supplier_name").val("");
                $(supplier_parent_pane).find("#supplier_address").val("");
                $(supplier_parent_pane).find("#supplier_city").val("");
                $(supplier_parent_pane).find("#supplier_country").val("");
                $(supplier_parent_pane).find("#supplier_phone").val("");
                $(supplier_parent_pane).find("#supplier_phone2").val("");
                $(supplier_parent_pane).find("#supplier_phone3").val("");
                $(supplier_parent_pane).find("#supplier_bb_pin").val("");
                $(supplier_parent_pane).find("#supplier_email").val("");
                $(supplier_parent_pane).find("#supplier_notes").val("");
                $(supplier_parent_pane).find("#supplier_supplier_status").select2(
                        {data:[{id:"A",text:APP_CONVERTER.status_attr("ACTIVE")}]}
                );

                APP_FORM.status.default_status_set('supplier',
                    $(lparent_pane).find('#supplier_supplier_status')
                );
                break;
            case "view":
                
                $(lparent_pane).find('#supplier_supplier_type_table').find('tbody').empty();
                var supplier_id = $(lparent_pane).find("#supplier_id").val();
                var json_data={data:supplier_id};
                var lresult = APP_DATA_TRANSFER.ajaxPOST(supplier_data_support_url+"supplier_get",json_data);
                var rs_supplier = lresult.response.supplier;
                if(rs_supplier !== null){
                    $(lparent_pane).find("#supplier_code").val(rs_supplier.code);
                    $(lparent_pane).find("#supplier_name").val(rs_supplier.name);
                    $(lparent_pane).find("#supplier_supplier_status").select2("data",{id:rs_supplier.supplier_status,text:rs_supplier.supplier_status_text});
                    $(lparent_pane).find("#supplier_is_credit").select2("data",{id:rs_supplier.is_credit,text:rs_supplier.is_credit_text});
                    $(lparent_pane).find("#supplier_is_sales_receipt_outstanding").select2("data",{id:rs_supplier.is_sales_receipt_outstanding,text:rs_supplier.is_sales_receipt_outstanding_text});
                    $(lparent_pane).find("#supplier_address").val(rs_supplier.address);
                    $(lparent_pane).find("#supplier_city").val(rs_supplier.city);
                    $(lparent_pane).find("#supplier_country").val(rs_supplier.country);
                    $(lparent_pane).find("#supplier_phone").val(rs_supplier.phone);
                    $(lparent_pane).find("#supplier_phone2").val(rs_supplier.phone2);
                    $(lparent_pane).find("#supplier_phone3").val(rs_supplier.phone3);
                    $(lparent_pane).find("#supplier_bb_pin").val(rs_supplier.bb_pin);
                    $(lparent_pane).find("#supplier_email").val(rs_supplier.email);
                    $(lparent_pane).find("#supplier_notes").val(rs_supplier.notes);
                    $(lparent_pane).find("#supplier_credit").val(rs_supplier.supplier_credit);
                    $(lparent_pane).find("#supplier_debit").val(rs_supplier.supplier_debit);

                    var supplier_status_list = lresult.response.supplier_status_list;
                    $(lparent_pane).find("#supplier_supplier_status").select2({data:supplier_status_list});

                };

                break;            
        }
    }

    var supplier_components_enable_disable = function(){
        var lparent_pane = supplier_parent_pane;
        var lmethod = $(lparent_pane).find('#supplier_method').val();    
        supplier_methods.disable_all();

        switch(method){
            case "add":
            case 'view':

                $(supplier_parent_pane).find("#supplier_name").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_address").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_city").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_country").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_phone").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_phone2").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_phone3").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_bb_pin").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_email").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_notes").prop("disabled",false);
                $(supplier_parent_pane).find("#supplier_supplier_type").select2("enable");
                $(supplier_parent_pane).find("#supplier_is_credit").select2("enable");
                $(supplier_parent_pane).find("#supplier_is_sales_receipt_outstanding").select2("enable");
                break;
        }
    }

    var supplier_components_show_hide = function(){
        var lparent_pane = supplier_parent_pane;
        var lmethod = $(lparent_pane).find('#supplier_method').val();
        supplier_methods.hide_all();

        switch(lmethod){
            case 'add':
            case 'view':
                $(lparent_pane).find('#supplier_code').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_name').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_address').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_is_credit').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_is_sales_receipt_outstanding').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_supplier_status').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_supplier_type').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_notes').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_phone').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_phone2').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_phone3').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_email').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_bb_pin').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_address').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_city').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_country').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_credit').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#supplier_debit').closest('div [class*="form-group"]').show();
                break;
        }
    }

    supplier_components_show_hide();
    supplier_components_enable_disable();
    supplier_data_set();
}
        
    
</script>