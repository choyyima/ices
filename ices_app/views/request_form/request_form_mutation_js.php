<script>
    var request_form_mutation_methods = {
        hide_show:function(){
            request_form_methods.hide_all();
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();
            $(lparent_pane).find('#request_form_request_form_type').parent().parent('div').show();
            switch(lmethod){
                case  'add':                    
                    $(lparent_pane).find('#request_form_code').parent().parent('div').show();                    
                    $(lparent_pane).find('#request_form_request_form_date').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_mutation_warehouse_from').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_mutation_warehouse_to').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_notes').parent('div').show();
                    $(lparent_pane).find('#request_form_request_form_status').parent().parent().show();
                    $(lparent_pane).find('#request_form_request_form_mutation_product').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_request_form_mutation_add_table').parent().parent('div').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#request_form_code').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_requester').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_request_form_date').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_mutation_warehouse_from').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_mutation_warehouse_to').parent().parent('div').show();
                    $(lparent_pane).find('#request_form_notes').parent('div').show();
                    $(lparent_pane).find('#request_form_request_form_status').parent().parent().show();
                    $(lparent_pane).find('#request_form_request_form_mutation_view_table').parent().parent('div').show();
                    break;
            }
            
        },
        enable_disable: function(){
            request_form_methods.disable_all();
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#request_form_request_form_type').select2('enable');
                    $(lparent_pane).find('#request_form_mutation_warehouse_from').select2('enable');
                    $(lparent_pane).find('#request_form_mutation_warehouse_to').select2('enable');
                    $(lparent_pane).find('#request_form_notes').prop('disabled',false);
                    $(lparent_pane).find('#request_form_request_form_status').select2('enable');
                    $(lparent_pane).find('#request_form_request_form_date').prop('disabled',false);
                    $(lparent_pane).find('#request_form_request_form_mutation_product').select2('enable');
                    break;
                case 'view':
                    $(lparent_pane).find('#request_form_request_form_status').select2('enable');
                    $(lparent_pane).find('#request_form_notes').prop('disabled',false);
                    break;
                    
            }
        } ,
        init_data:function(){
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#request_form_warehouse_to').select2('data',{id:'',text:''});
                    $(lparent_pane).find('#request_form_warehouse_from').select2('data',{id:'',text:''});

                    $(lparent_pane).find('#request_form_request_form_date')
                            .datetimepicker({
                                minDate:APP_GENERATOR.CURR_DATE(),
                                minTime:APP_GENERATOR.CURR_TIME(),
                                value:APP_GENERATOR.CURR_DATETIME()
                            });

                    var ldefault_status = null;
                    ldefault_status = APP_DATA_TRANSFER.ajaxPOST(request_form_data_support_url+'default_mutation_status_get');
                    $(lparent_pane).find('#request_form_request_form_status')
                            .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();

                    var lrequest_form_status_list = [
                        {id:ldefault_status.val,text:ldefault_status.label}//,
                    ]
                    $(lparent_pane).find('#request_form_request_form_status')
                            .select2({data:lrequest_form_status_list});

                    $(lparent_pane).find('#request_form_notes').val('');
                    $(lparent_pane).find('#request_form_cancellation_reason').val('');
                    $(lparent_pane).find('#request_form_request_form_mutation_add_table > tbody').empty();
                    
                    
                    break;
                case 'view':                    
                    var lrequest_form_id = $(lparent_pane).find('#request_form_id').val();
                    var lajax_url = request_form_ajax_url+'request_form_request_form_mutation_ajax_get';
                    var json_data = {data:lrequest_form_id};
                    var lrequest_form = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                    $(lparent_pane).find('#request_form_code').val(lrequest_form.code);
                    $(lparent_pane).find('#request_form_requester').val(lrequest_form.requester_name);
                    $(lparent_pane).find('#request_form_mutation_warehouse_to')
                        .select2('data',{id:lrequest_form.request_form_mutation_warehouse_to_id
                            ,text:lrequest_form.request_form_mutation_warehouse_to_name
                        });
                    $(lparent_pane).find('#request_form_mutation_warehouse_from')
                        .select2('data',{id:lrequest_form.request_form_mutation_warehouse_from_id
                            ,text:lrequest_form.request_form_mutation_warehouse_from_name
                        });
                    $(lparent_pane).find('#request_form_request_form_date')
                        .datetimepicker({value:lrequest_form.request_form_date})
                    $(lparent_pane).find('#request_form_notes').val(lrequest_form.notes);
                    
                    $(lparent_pane).find('#request_form_request_form_status')
                            .select2('data',{id:lrequest_form.request_form_status
                                ,text:lrequest_form.request_form_status_name}).change();
                    var lrequest_form_status_list = [
                        {id:lrequest_form.request_form_status,text:lrequest_form.request_form_status_name}
                    ];
                    
                    lajax_url = request_form_data_support_url+'next_allowed_mutation_status';
                    json_data = {data:lrequest_form.request_form_status};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                    $.each(lresponse,function(key, val){
                        lrequest_form_status_list.push({id:val.val,text:val.label});
                    });
                    
                    $(lparent_pane).find('#request_form_request_form_status')
                            .select2({data:lrequest_form_status_list});
                    
                    $(lparent_pane).find('#request_form_notes').val(lrequest_form.notes);
                    
                    this.load_view_table();
                    break;
            }
            
        },
        load_view_table: function(){
            var lparent_pane = request_form_parent_pane;
            var lajax_url = request_form_ajax_url+'request_form_request_form_mutation_product_ajax_get';
            var ljson_data = {data:$(lparent_pane).find('#request_form_id').val()};
            var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, ljson_data);
            var ltbody = $(lparent_pane).find('#request_form_request_form_mutation_view_table').find('tbody')[0];
            var cont = true;
            
            $.each(lresponse,function(key, val){
                var lrow = document.createElement('tr');

                var lrow_num_td = document.createElement('td');
                $(lrow_num_td).attr('col_name','row_num');
                $(lrow_num_td).attr('style','vertical-align:middle');
                lrow_num_td.innerHTML = $(ltbody).children().length+1;

                var laction_td = document.createElement('td');
                $(laction_td).attr('style','vertical-align:middle');

                var lproduct_img_td = document.createElement('td');
                $(lproduct_img_td).attr('style','vertical-align:middle');
                $(lproduct_img_td).attr('col_name','product_img');
                lproduct_img_td.innerHTML = val.product_img;    

                var lproduct_name_td = document.createElement('td');
                $(lproduct_name_td).attr('style','vertical-align:middle');
                $(lproduct_name_td).attr('col_name','product_name');                    
                lproduct_name_td.innerHTML = val.product_name;    

                var lunit_name_td = document.createElement('td');
                $(lunit_name_td).attr('style','vertical-align:middle');
                lunit_name_td.innerHTML = val.unit_name

                var lqty_td = document.createElement('td');
                lqty_td.innerHTML = val.qty;
                
                lrow.appendChild(lrow_num_td);
                lrow.appendChild(lproduct_img_td);
                lrow.appendChild(lproduct_name_td);
                lrow.appendChild(lqty_td);                    
                lrow.appendChild(lunit_name_td);
                lrow.appendChild(laction_td);

                ltbody.appendChild(lrow);
            });           
        },
        product_table_add : function(){
            var lparent_pane = request_form_parent_pane;
            var ldata = $(lparent_pane).find('#request_form_request_form_mutation_product').select2('data');
            $(lparent_pane).find('#request_form_request_form_mutation_product').select2('data',{id:'',text:''}); 
            var lajax_url = request_form_ajax_url+'request_form_mutation_product_get';
            var ljson_data = {data:ldata.id};
            var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, ljson_data);
            var ltbody = $(lparent_pane).find('#request_form_request_form_mutation_add_table').find('tbody')[0];
            var text_padding = '15px';
            var cont = true;
            if(typeof lresponse.product !== 'undefined' && typeof lresponse.unit !== 'undefined'){
                $.each($(ltbody).find('[col_name="product_id"]'),function(key, val){
                    if(val.innerHTML === lresponse.product.product_id) cont = false;
                });
                if(cont){
                    var lrow = document.createElement('tr');
                    
                    var lrow_num_td = document.createElement('td');
                    $(lrow_num_td).attr('col_name','row_num');
                    $(lrow_num_td).attr('style','vertical-align:middle');
                    lrow_num_td.innerHTML = $(ltbody).children().length+1;

                    var laction_td = document.createElement('td');
                    $(laction_td).attr('style','vertical-align:middle');
                    var li = document.createElement('i');
                    $(li).addClass('fa fa-trash-o');
                    $(li).attr('style','cursor:pointer;color:red');
                    laction_td.appendChild(li);

                    var lproduct_id_td = document.createElement('td');
                    $(lproduct_id_td).attr('style','vertical-align:middle');
                    $(lproduct_id_td).attr('col_name','product_id');
                    $(lproduct_id_td).attr('style','display:none');
                    lproduct_id_td.innerHTML = lresponse.product.product_id;
                    
                    var lproduct_img_td = document.createElement('td');
                    $(lproduct_img_td).attr('style','vertical-align:middle');
                    $(lproduct_img_td).attr('col_name','product_img');
                    lproduct_img_td.innerHTML = lresponse.product.product_img;    
                    
                    var lproduct_name_td = document.createElement('td');
                    $(lproduct_name_td).attr('style','vertical-align:middle');
                    $(lproduct_name_td).attr('col_name','product_name');                    
                    lproduct_name_td.innerHTML = lresponse.product.product_name;    

                    var lunit_id_td = document.createElement('td');
                    $(lunit_id_td).attr('style','vertical-align:middle');
                    $(lunit_id_td).attr('col_name','unit_id');
                    $(lunit_id_td).attr('style','display:none;');
                    lunit_id_td.innerHTML = '';                    
                    
                    var lunit_name_td = document.createElement('td');
                    $(lunit_name_td).attr('style','vertical-align:middle');
                    var lunit_name_select = document.createElement('select');
                    
                    $.each(lresponse.unit, function(unit_key, unit_val){
                        var lunit_name_option = document.createElement('option');
                        $(lunit_name_option).val(unit_val.unit_id);
                        lunit_name_option.innerHTML = unit_val.unit_name;
                        lunit_name_select.appendChild(lunit_name_option);
                    });
                    $(lunit_name_select).addClass('form-control');
                    $(lunit_name_td).attr('col_name','unit_name');
                    lunit_name_td.appendChild(lunit_name_select);
                    
                    var lqty_td = document.createElement('td');
                    $(lqty_td).attr('style','vertical-align:middle');
                    var lqty_inpt = document.createElement('input');
                    $(lqty_inpt).addClass('form-control');
                    lqty_td.appendChild(lqty_inpt);
                    $(lqty_td).attr('col_name','qty');
                    APP_EVENT.init().component_set(lqty_inpt).type_set('input').numeric_set().min_val_set(0).render();
                    
                    lrow.appendChild(lrow_num_td);
                    lrow.appendChild(lproduct_id_td);
                    lrow.appendChild(lunit_id_td);
                    lrow.appendChild(lproduct_img_td);
                    lrow.appendChild(lproduct_name_td);
                    lrow.appendChild(lqty_td);                    
                    lrow.appendChild(lunit_name_td);
                    lrow.appendChild(laction_td);

                    ltbody.appendChild(lrow);
                    
                    //set event
                    $(laction_td).find('i').on('click',function(){
                        var ltbody = $(this).closest('tbody');
                        $(this).closest('tr').remove();

                        for(var i = 0;i<$(ltbody).children().length ;i++){
                            $($(ltbody).children()[i]).find('[col_name="row_num"]')[0].innerHTML = i+1;
                        }   
                        
                    });
                    $(lunit_name_select).on('change',function(){
                        $(this).closest('tr').find('[col_name="unit_id"]')[0].innerHTML = $(this).val();
                    });
                    $(lunit_name_select).change();                    
                    $(lqty_inpt).blur();
                }
            }
        },
        submit:function(){
            var lparent_pane = request_form_parent_pane;
            var lajax_url = request_form_index_url;
            var lmethod = $(lparent_pane).find('#request_form_method').val();
            var lrequest_form_type = 'mutation';
            var json_data = {
                ajax_post:true,
                message_session:true,
                
            };
            
            switch(lmethod){
                case 'add':
                    json_data.request_form_mutation_warehouse_from={
                        warehouse_id :
                            $(lparent_pane).find('#request_form_mutation_warehouse_from').select2('val')                        
                    };
                    json_data.request_form_mutation_warehouse_to={
                        warehouse_id : 
                            $(lparent_pane).find('#request_form_mutation_warehouse_to').select2('val'),
                    };
                    json_data.request_form = {                        
                        request_form_date:
                            $(lparent_pane).find('#request_form_request_form_date').val(),
                        notes:
                            $(lparent_pane).find('#request_form_notes').val()
                    }
                    
                    json_data.request_form_mutation_product=[];
                    var lproduct = $(lparent_pane).find('#request_form_request_form_mutation_add_table')[0];
                    $.each($(lproduct).find('tbody').children(),function(key, val){
                        json_data.request_form_mutation_product.push({
                            product_id:$(val).find('[col_name="product_id"]')[0].innerHTML,
                            unit_id:$(val).find('[col_name="unit_id"]')[0].innerHTML,
                            qty:$(val).find('[col_name="qty"]>input').val().replace(/[,]/g,'')
                        });
                    });
                    lajax_url +=lrequest_form_type+'_add';
                    break;
                case 'view':
                    var request_form_id = $(lparent_pane).find('#request_form_id').val();
                    var lajax_method = request_form_methods.status_label_get();
                    
                    json_data.request_form = {                        
                        request_form_status:
                            $(lparent_pane).find('#request_form_request_form_status').select2('val'),
                        notes:
                            $(lparent_pane).find('#request_form_notes').val(),
                        cancellation_reason:
                            $(lparent_pane).find('#request_form_cancellation_reason').val()
                    }
                    
                    lajax_url +=lrequest_form_type+'_'+lajax_method+'/'+request_form_id;
                    break;
            }
            
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            
            if(result.success ===1){
                $(lparent_pane).find('#request_form_id').val(result.trans_id);
                if(request_form_view_url !==''){
                    var url = request_form_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    request_form_after_submit();
                }
            }
            
        },
        mutation_status_event: function(){
            var lparent_pane = request_form_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = request_form_methods.status_label_get();
            
            if($(lparent_pane).find('#request_form_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('request_form','mutation_'+lstatus_label).result){
                lsubmit_show = false;
            }
            
            if($(lparent_pane).find('#request_form_id').val() !== ''){
                if($.inArray(request_form_mutation_methods.current_status_get(),['X'])!== -1){
                    lsubmit_show = false;
                }
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#request_form_submit').show();
                $(lparent_pane).find('#request_form_cancellation_reason').prop('disabled',false);
                $(lparent_pane).find('#request_form_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#request_form_submit').hide();
                //$(lparent_pane).find('#request_form_request_form_status').select2('disable');
                $(lparent_pane).find('#request_form_cancellation_reason').prop('disabled',true);
                $(lparent_pane).find('#request_form_notes').prop('disabled',true);
            }
        },
        current_status_get: function(){
            var lrequest_form_id = $('#request_form_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(request_form_data_support_url+'request_form_current_status/',{data:lrequest_form_id});

            return lresult;
        }
    }
    
</script>