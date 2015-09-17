<script>
    var sales_inquiry_by_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var sales_inquiry_by_ajax_url = null;
    var sales_inquiry_by_index_url = null;
    var sales_inquiry_by_view_url = null;
    var sales_inquiry_by_window_scroll = null;
    var sales_inquiry_by_data_support_url = null;
    var sales_inquiry_by_common_ajax_listener = null;
    
    var sales_inquiry_by_init = function(){
        var parent_pane = sales_inquiry_by_parent_pane;

        sales_inquiry_by_ajax_url = '<?php echo $ajax_url ?>';
        sales_inquiry_by_index_url = '<?php echo $index_url ?>';
        sales_inquiry_by_view_url = '<?php echo $view_url ?>';
        sales_inquiry_by_window_scroll = '<?php echo $window_scroll; ?>';
        sales_inquiry_by_data_support_url = '<?php echo $data_support_url; ?>';
        sales_inquiry_by_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
    }

    var sales_inquiry_by_after_submit = function(){

    }
    
    var sales_inquiry_by_methods = {
        status_label_get:function(){
            var parent_pane = sales_inquiry_by_parent_pane;
            return $($(parent_pane).find('#sales_inquiry_by_sales_inquiry_by_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#sales_inquiry_by_sales_inquiry_by_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#sales_inquiry_by_sales_inquiry_by_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lsales_inquiry_by_id = $('#sales_inquiry_by_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_inquiry_by_data_support_url+'sales_inquiry_by_current_status/',{data:lsales_inquiry_by_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = sales_inquiry_by_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = sales_inquiry_by_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            
            $.each(lcomponents,function(key, val){
                $(val).prop('disabled',true);
            });
            $(lparent_pane).find('#sales_inquiry_by_sales_inquiry_by_type').select2('disable',true);
            $(lparent_pane).find('#sales_inquiry_by_is_credit').select2('disable',true);
        },
        security_set:function(){
            var lparent_pane = sales_inquiry_by_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = sales_inquiry_by_methods.status_label_get();
            
            if($(lparent_pane).find('#sales_inquiry_by_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('sales_inquiry_by',lstatus_label).result){
                lsubmit_show = false;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#sales_inquiry_by_submit').show();
                $(lparent_pane).find('#sales_inquiry_by_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#sales_inquiry_by_submit').hide();
                $(lparent_pane).find('#sales_inquiry_by_notes').prop('disabled',true);
            }    
        },
        submit:function(){
            var parent_pane = sales_inquiry_by_parent_pane;
            var ajax_url = sales_inquiry_by_index_url;
            var method = $(parent_pane).find("#sales_inquiry_by_method").val();
            var sales_inquiry_by_id = $(parent_pane).find("#sales_inquiry_by_id").val();        
            var json_data = {
                ajax_post:true,
                sales_inquiry_by:{},
                message_session:false
            };

            switch(method){
                case 'add':
                    json_data.sales_inquiry_by.code = $(parent_pane).find("#sales_inquiry_by_code").val();
                    json_data.sales_inquiry_by.name = $(parent_pane).find("#sales_inquiry_by_name").val();
                    json_data.sales_inquiry_by.notes = $(parent_pane).find("#sales_inquiry_by_notes").val();
                    ajax_url +='add/';
                    break;
                case 'view':
                    json_data.sales_inquiry_by.id = sales_inquiry_by_id;
                    json_data.sales_inquiry_by.code = $(parent_pane).find("#sales_inquiry_by_code").val();
                    json_data.sales_inquiry_by.name = $(parent_pane).find("#sales_inquiry_by_name").val();
                    json_data.sales_inquiry_by.notes = $(parent_pane).find("#sales_inquiry_by_notes").val();
                    json_data.sales_inquiry_by.sales_inquiry_by_status = $(parent_pane).find("#sales_inquiry_by_sales_inquiry_by_status").val();
                    var lajax_method = sales_inquiry_by_methods.status_label_get();
                    ajax_url +=lajax_method+'/'+sales_inquiry_by_id;
                    break;
            }


            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(sales_inquiry_by_parent_pane).find('#sales_inquiry_by_id').val(result.trans_id);
                if(sales_inquiry_by_view_url !==''){
                    var url = sales_inquiry_by_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    sales_inquiry_by_after_submit();
                }
            }
        }
    }

    var sales_inquiry_by_bind_event = function(){
        var parent_pane = sales_inquiry_by_parent_pane;
        /*
        var amount = $(parent_pane).find('#sales_inquiry_by_amount');
        APP_EVENT.init().component_set(amount).type_set('input').numeric_set().render();
        $(amount).on('blur',function(){
            $(parent_pane).find('#sales_inquiry_by_available_amount').val($(this).val());
        });
        */
        
        $(parent_pane).find('#sales_inquiry_by_submit').off('click');
        $(parent_pane).find('#sales_inquiry_by_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = sales_inquiry_by_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                sales_inquiry_by_methods.submit();
            });
            $(sales_inquiry_by_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        

    }
    
    var sales_inquiry_by_components_prepare= function(){
        var method = $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_method").val();
        
        
        var sales_inquiry_by_data_set = function(){
            switch(method){
                case "add":
                    $(sales_inquiry_by_parent_pane).find('#sales_inquiry_by_sales_inquiry_by_type_table').find('tbody').empty();
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_code").val("");
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_name").val("");
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_notes").val("");
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_sales_inquiry_by_status").select2(
                            {data:[{id:"A",text:APP_CONVERTER.status_attr("ACTIVE")}]}
                    );
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_sales_inquiry_by_status").select2(
                        "data",{id:"A",text:APP_CONVERTER.status_attr("ACTIVE")}
                    );
                    
                    break;
                case "view":
                    $(sales_inquiry_by_parent_pane).find('#sales_inquiry_by_sales_inquiry_by_type_table').find('tbody').empty();
                    var sales_inquiry_by_id = $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_id").val();
                    var json_data={data:sales_inquiry_by_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_inquiry_by_data_support_url+"sales_inquiry_by_get",json_data);
                    var rs_sales_inquiry_by = lresult.response;
                    if(rs_sales_inquiry_by !== null){
                        $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_code").val(rs_sales_inquiry_by.code);
                        $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_name").val(rs_sales_inquiry_by.name);
                        $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_sales_inquiry_by_status").select2("data",{id:rs_sales_inquiry_by.sales_inquiry_by_status,text:rs_sales_inquiry_by.sales_inquiry_by_status_name});
                        $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_notes").val(rs_sales_inquiry_by.notes);
                        
                        var sales_inquiry_by_status_list = [];
                        sales_inquiry_by_status_list.push({id:"A",text:APP_CONVERTER.status_attr("ACTIVE")});
                        sales_inquiry_by_status_list.push({id:"I",text:APP_CONVERTER.status_attr("INACTIVE")});
                        $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_sales_inquiry_by_status").select2({data:sales_inquiry_by_status_list});
                    };
                    
                    break;            
            }
        }
    
        var sales_inquiry_by_components_enable_disable = function(){
            var lparent_pane = sales_inquiry_by_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_inquiry_by_method').val();    
            sales_inquiry_by_methods.disable_all();
            
            switch(method){
                case "add":
                case 'view':
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_code").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_name").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_address").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_city").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_country").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_phone").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_phone2").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_phone3").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_bb_pin").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_email").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_notes").prop("disabled",false);
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_sales_inquiry_by_type").select2("enable");
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_is_credit").select2("enable");
                    $(sales_inquiry_by_parent_pane).find("#sales_inquiry_by_is_sales_receipt_outstanding").select2("enable");
                    break;
            }
        }
        
        var sales_inquiry_by_components_show_hide = function(){
            var lparent_pane = sales_inquiry_by_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_inquiry_by_method').val();
            sales_inquiry_by_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find('#sales_inquiry_by_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_address').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_is_credit').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_is_sales_receipt_outstanding').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_sales_inquiry_by_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_sales_inquiry_by_type').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_phone').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_phone2').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_phone3').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_email').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_bb_pin').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_address').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_city').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_inquiry_by_country').closest('div [class*="form-group"]').show();
                    break;
            }
        }
        
        sales_inquiry_by_components_show_hide();
        sales_inquiry_by_components_enable_disable();
        sales_inquiry_by_data_set();
    }
    
</script>