<script>
    var expedition_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var expedition_ajax_url = null;
    var expedition_index_url = null;
    var expedition_view_url = null;
    var expedition_window_scroll = null;
    var expedition_data_support_url = null;
    var expedition_common_ajax_listener = null;
    
    var expedition_init = function(){
        var parent_pane = expedition_parent_pane;

        expedition_ajax_url = '<?php echo $ajax_url ?>';
        expedition_index_url = '<?php echo $index_url ?>';
        expedition_view_url = '<?php echo $view_url ?>';
        expedition_window_scroll = '<?php echo $window_scroll; ?>';
        expedition_data_support_url = '<?php echo $data_support_url; ?>';
        expedition_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
    }

    var expedition_after_submit = function(){

    }
    
    var expedition_methods = {
        status_label_get:function(){
            var parent_pane = expedition_parent_pane;
            return $($(parent_pane).find('#expedition_expedition_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#expedition_expedition_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#expedition_expedition_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lexpedition_id = $('#expedition_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(expedition_data_support_url+'expedition_current_status/',{data:lexpedition_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = expedition_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = expedition_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            
            $.each(lcomponents,function(key, val){
                $(val).prop('disabled',true);
            });
        },
        security_set:function(){
            var lparent_pane = expedition_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = expedition_methods.status_label_get();
            
            if($(lparent_pane).find('#expedition_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('expedition',lstatus_label).result){
                lsubmit_show = false;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#expedition_submit').show();
                $(lparent_pane).find('#expedition_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#expedition_submit').hide();
                $(lparent_pane).find('#expedition_notes').prop('disabled',true);
            }    
        },
        submit:function(){
            var parent_pane = expedition_parent_pane;
            var ajax_url = expedition_index_url;
            var method = $(parent_pane).find("#expedition_method").val();
            var expedition_id = $(parent_pane).find("#expedition_id").val();        
            var json_data = {
                ajax_post:true,
                expedition:{},
                message_session:false
            };

            switch(method){
                case 'add':
                    json_data.expedition.code = $(parent_pane).find("#expedition_code").val();
                    json_data.expedition.name = $(parent_pane).find("#expedition_name").val();
                    json_data.expedition.address = $(parent_pane).find("#expedition_address").val();
                    json_data.expedition.city = $(parent_pane).find("#expedition_city").val();
                    json_data.expedition.country = $(parent_pane).find("#expedition_country").val();
                    json_data.expedition.phone = $(parent_pane).find("#expedition_phone").val();
                    json_data.expedition.phone2 = $(parent_pane).find("#expedition_phone2").val();
                    json_data.expedition.phone3 = $(parent_pane).find("#expedition_phone3").val();
                    json_data.expedition.bb_pin = $(parent_pane).find("#expedition_bb_pin").val();
                    json_data.expedition.email = $(parent_pane).find("#expedition_email").val();
                    json_data.expedition.notes = $(parent_pane).find("#expedition_notes").val();
                    json_data.expedition.measurement_unit_id = $(parent_pane).find("#expedition_measurement_unit").select2('val');
                    ajax_url +='add/';
                    break;
                case 'view':
                    json_data.expedition.id = expedition_id;
                    json_data.expedition.code = $(parent_pane).find("#expedition_code").val();
                    json_data.expedition.name = $(parent_pane).find("#expedition_name").val();
                    json_data.expedition.address = $(parent_pane).find("#expedition_address").val();
                    json_data.expedition.city = $(parent_pane).find("#expedition_city").val();
                    json_data.expedition.country = $(parent_pane).find("#expedition_country").val();
                    json_data.expedition.phone = $(parent_pane).find("#expedition_phone").val();
                    json_data.expedition.phone2 = $(parent_pane).find("#expedition_phone2").val();
                    json_data.expedition.phone3 = $(parent_pane).find("#expedition_phone3").val();
                    json_data.expedition.bb_pin = $(parent_pane).find("#expedition_bb_pin").val();
                    json_data.expedition.email = $(parent_pane).find("#expedition_email").val();
                    json_data.expedition.notes = $(parent_pane).find("#expedition_notes").val();
                    json_data.expedition.expedition_status = $(parent_pane).find("#expedition_expedition_status").val();
                    json_data.expedition.measurement_unit_id = $(parent_pane).find("#expedition_measurement_unit").select2('val');
                    var lajax_method = expedition_methods.status_label_get();
                    ajax_url +=lajax_method+'/'+expedition_id;
                    break;
            }


            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(expedition_parent_pane).find('#expedition_id').val(result.trans_id);
                if(expedition_view_url !==''){
                    var url = expedition_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    expedition_after_submit();
                }
            }
        }
    }

    var expedition_bind_event = function(){
        var parent_pane = expedition_parent_pane;
        /*
        var amount = $(parent_pane).find('#expedition_amount');
        APP_EVENT.init().component_set(amount).type_set('input').numeric_set().render();
        $(amount).on('blur',function(){
            $(parent_pane).find('#expedition_available_amount').val($(this).val());
        });
        */
        
        $(parent_pane).find('#expedition_submit').off('click');
        $(parent_pane).find('#expedition_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = expedition_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                expedition_methods.submit();
            });
            $(expedition_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        

    }
    
    var expedition_components_prepare= function(){
        var method = $(expedition_parent_pane).find("#expedition_method").val();
        
        
        var expedition_data_set = function(){
            switch(method){
                case "add":
                    $(expedition_parent_pane).find("#expedition_code").val("[AUTO GENERATE]");
                    $(expedition_parent_pane).find("#expedition_name").val("");
                    $(expedition_parent_pane).find("#expedition_address").val("");
                    $(expedition_parent_pane).find("#expedition_city").val("");
                    $(expedition_parent_pane).find("#expedition_country").val("");
                    $(expedition_parent_pane).find("#expedition_phone").val("");
                    $(expedition_parent_pane).find("#expedition_phone2").val("");
                    $(expedition_parent_pane).find("#expedition_phone3").val("");
                    $(expedition_parent_pane).find("#expedition_bb_pin").val("");
                    $(expedition_parent_pane).find("#expedition_email").val("");
                    $(expedition_parent_pane).find("#expedition_notes").val("");
                    $(expedition_parent_pane).find("#expedition_measurement_unit").select2('data',null);
                    $(expedition_parent_pane).find("#expedition_expedition_status").select2(
                            {data:[{id:"A",text:APP_CONVERTER.status_attr("ACTIVE")}]}
                    );
                    $(expedition_parent_pane).find("#expedition_expedition_status").select2(
                        "data",{id:"A",text:APP_CONVERTER.status_attr("ACTIVE")}
                    );
                    
                    break;
                case "view":
                    var expedition_id = $(expedition_parent_pane).find("#expedition_id").val();
                    var json_data={data:expedition_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(expedition_data_support_url+"expedition_get",json_data);
                    var rs_expedition = lresult.response;
                    if(rs_expedition !== null){
                        $(expedition_parent_pane).find("#expedition_code").val(rs_expedition.code);
                        $(expedition_parent_pane).find("#expedition_name").val(rs_expedition.name);
                        $(expedition_parent_pane).find("#expedition_expedition_status").select2("data",{id:rs_expedition.expedition_status,text:rs_expedition.expedition_status_name});
                        $(expedition_parent_pane).find("#expedition_address").val(rs_expedition.address);
                        $(expedition_parent_pane).find("#expedition_city").val(rs_expedition.city);
                        $(expedition_parent_pane).find("#expedition_country").val(rs_expedition.country);
                        $(expedition_parent_pane).find("#expedition_phone").val(rs_expedition.phone);
                        $(expedition_parent_pane).find("#expedition_phone2").val(rs_expedition.phone2);
                        $(expedition_parent_pane).find("#expedition_phone3").val(rs_expedition.phone3);
                        $(expedition_parent_pane).find("#expedition_bb_pin").val(rs_expedition.bb_pin);
                        $(expedition_parent_pane).find("#expedition_email").val(rs_expedition.email);
                        $(expedition_parent_pane).find("#expedition_notes").val(rs_expedition.notes);
                        var expedition_status_list = [];
                        expedition_status_list.push({id:"A",text:APP_CONVERTER.status_attr("ACTIVE")});
                        expedition_status_list.push({id:"I",text:APP_CONVERTER.status_attr("INACTIVE")});
                        $(expedition_parent_pane).find("#expedition_expedition_status").select2({data:expedition_status_list});
                        
                        $(expedition_parent_pane).find("#expedition_measurement_unit").select2('data',
                            {id:rs_expedition.measurement_unit_id, text:rs_expedition.measurement_unit_name});
                    };
                    
                    json_data={data:expedition_id};
                    
                    break;            
            }
        }
    
        var expedition_components_enable_disable = function(){
            var lparent_pane = expedition_parent_pane;
            var lmethod = $(lparent_pane).find('#expedition_method').val();    
            expedition_methods.disable_all();
            
            switch(method){
                case "add":
                case 'view':
                    
                    $(expedition_parent_pane).find("#expedition_name").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_address").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_city").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_country").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_phone").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_phone2").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_phone3").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_bb_pin").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_email").prop("disabled",false);
                    $(expedition_parent_pane).find("#expedition_notes").prop("disabled",false);
                    
                    break;
            }
        }
        
        var expedition_components_show_hide = function(){
            var lparent_pane = expedition_parent_pane;
            var lmethod = $(lparent_pane).find('#expedition_method').val();
            expedition_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find('#expedition_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_address').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_expedition_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_measurement_unit').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_phone').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_phone2').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_phone3').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_email').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_bb_pin').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_address').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_city').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#expedition_country').closest('div [class*="form-group"]').show();
                    break;
            }
        }
        
        expedition_components_show_hide();
        expedition_components_enable_disable();
        expedition_data_set();
    }
    
</script>