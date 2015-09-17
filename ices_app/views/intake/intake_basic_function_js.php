<script>

    var intake_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var intake_ajax_url = null;
    var intake_index_url = null;
    var intake_view_url = null;
    var intake_window_scroll = null;
    var intake_data_support_url = null;
    var intake_common_ajax_listener = null;

    var intake_init = function(){
        var parent_pane = intake_parent_pane;
        intake_ajax_url = '<?php echo $ajax_url ?>';
        intake_index_url = '<?php echo $index_url ?>';
        intake_view_url = '<?php echo $view_url ?>';
        intake_window_scroll = '<?php echo $window_scroll; ?>';
        intake_data_support_url = '<?php echo $data_support_url; ?>';
        intake_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        intake_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var intake_methods = {
        hide_all:function(){
            var lparent_pane = intake_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all');
            $.each(lc_arr, function(c_idx, c){
                $(c).closest('.form-group').attr('style','display:none');
            });
            $(lparent_pane).find('#intake_rma_view_table').hide();
            $(lparent_pane).find('#intake_rma_add_table').hide();
            $('#intake_print').hide();
            
        },
        show_hide:function(){
            var lparent_pane = intake_parent_pane;
            var lmethod = $(lparent_pane).find('#intake_method').val();
            intake_methods.hide_all();
            var ldo_type = $(lparent_pane).find('#intake_type').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#intake_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#intake_print').hide();
                    break;
                case 'view':
                    $(lparent_pane).find('#intake_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#intake_print').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = intake_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = intake_parent_pane;
            var lmethod = $(lparent_pane).find('#intake_method').val();    
            intake_methods.disable_all();
            var ldo_type = $(lparent_pane).find('#intake_type').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#intake_reference').select2('enable');
                    break;
                case 'view':
                    $(lparent_pane).find('#intake_reference').select2('disable');
                    $(lparent_pane).find('#intake_notes').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = intake_parent_pane;
            $(lparent_pane).find('#intake_code').val('[AUTO GENERATE]');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.ajaxPOST(intake_data_support_url+'default_status_get');

            $(lparent_pane).find('#intake_intake_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lintake_status_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            $(lparent_pane).find('#intake_intake_status').
                select2({data:lintake_status_list});
            $(lparent_pane).find('#intake_warehouse_from').select2('data',null);
            $(lparent_pane).find('#intake_warehouse_to').select2('data',null);


            var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>'+
                'store/data_support/default_store_get/');
            var ldefault_store = lresult.response;
            $(lparent_pane).find('#intake_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
        },
        table:{
            product:{
                reset:function(){
                    var lparent_pane = intake_parent_pane;
                    $(lparent_pane).find('#intake_product_table tbody').empty();
                },
                load:function(itype, iproduct_arr){
                    var lparent_pane = intake_parent_pane;
                    var ltbody = $(lparent_pane).find('#intake_product_table tbody')[0];
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    $.each(iproduct_arr, function (lidx, lproduct){
                        var lrow = document.createElement('tr');
                        var row_num = lidx;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:lproduct.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'reference_type',style:'vertical-align:middle',val:lproduct.reference_type,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'reference_id',style:'vertical-align:middle',val:lproduct.reference_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_type',style:'vertical-align:middle',val:lproduct.product_type,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:lproduct.product_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:lproduct.product_text,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:lproduct.unit_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:lproduct.unit_code,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:middle',val:APP_CONVERTER.thousand_separator(lproduct.qty),type:'text',col_style:'text-align:right'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'vertical-align:middle',val:'',type:'text',style:""});
                        ltbody.appendChild(lrow);
                    });
                }
            }
        },
        submit:function(){
            var lparent_pane = intake_parent_pane;
            var lajax_url = intake_index_url;
            var lmethod = $(lparent_pane).find('#intake_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.intake = {
                        intake_type:$(lparent_pane).find('#intake_type').val(),
                        store_id:$(lparent_pane).find('#intake_store').select2('val'),
                        intake_status:$(lparent_pane).find('#intake_intake_status').select2('val'),
                        intake_date:$(lparent_pane).find('#intake_intake_date').val(),
                        notes:$(lparent_pane).find('#intake_notes').val()                                            
                    };
                    json_data.warehouse_from ={
                        warehouse_id: $(lparent_pane).find('#intake_warehouse_from').select2('val')
                    };
                    json_data.warehouse_to ={
                        warehouse_id: $(lparent_pane).find('#intake_warehouse_to').select2('val'),
                        contact_name: $(lparent_pane).find('#intake_warehouse_to_contact_name').val(),
                        address: $(lparent_pane).find('#intake_warehouse_to_address').val(),
                        phone: $(lparent_pane).find('#intake_warehouse_to_phone').val()
                    }; 
                    json_data.rma_intake = {
                        rma_id:$(lparent_pane).find('#intake_reference').select2('val')
                    };
                    json_data.intake_product=[];
                    var lproduct = $(lparent_pane).find('#intake_rma_add_table')[0];
                        $.each($(lproduct).find('tbody').children(),function(key, val){
                            json_data.intake_product.push({
                                product_id:$(val).find('[col_name="product_id"]')[0].innerHTML,
                                unit_id:$(val).find('[col_name="unit_id"]')[0].innerHTML,
                                qty:$(val).find('[col_name="qty"]').find('input').val().replace(/[,]/g,'')
                            });
                        });
                    lajax_url +='intake_add/';
                    break;
                case 'view':
                    json_data.intake = {
                        intake_status:$(lparent_pane).find('#intake_intake_status').select2('val'),
                        notes:$(lparent_pane).find('#intake_notes').val(),
                        cancellation_reason:$(lparent_pane).find('#intake_intake_cancellation_reason').val()
                    };
                    var intake_id = $(lparent_pane).find('#intake_id').val();
                    var lajax_method = $(lparent_pane).find('#intake_intake_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+intake_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#intake_id').val(result.trans_id);
                if(intake_view_url !==''){
                    var url = intake_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    intake_after_submit();
                }
            }
        }
    };
    
    var intake_bind_event = function(){
        var parent_pane = intake_parent_pane;
        
        <?php  ?>        
        $(parent_pane).find('#intake_print').on('click',function(){            
            var lintake_id = $(parent_pane).find('#intake_id').val();
            modal_print.init();
            modal_print.menu.add('<?php echo Lang::get(array("Product Intake")); ?>',intake_index_url+'intake_print/'+lintake_id+'/intake_form');
            modal_print.show();
        })  ;      
        <?php  ?>
        
        $(parent_pane).find("#intake_reference")
        .on('change', function(){
            
            var lparent_pane = intake_parent_pane;
            var lmethod = $(this).find('#intake_method').val();
            var ldo_type = '';
            
            $('#intake_type').val(ldata.reference_type);
            
            intake_methods.show_hide();
            intake_methods.enable_disable();
            
            $('#intake_reference_detail').find('.extra_info').remove();
            
            if($(this).select2('val')!== ''){
                var ldata = $(this).select2('data');

            }
        });
        
        $(parent_pane).find('#intake_warehouse_from').on('change',function(){
            var lparent_pane =  intake_parent_pane;
            switch($(lparent_pane).find('#intake_type').val()){
                case 'rma':
                    intake_rma_methods.load_product_table();
                    break;
            }
        });
        
        $(parent_pane).find('#intake_submit').off();        
        $(parent_pane).find('#intake_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = intake_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                intake_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
                
            
            $(intake_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    var intake_components_prepare = function(){
        

        var intake_data_set = function(){
            var lparent_pane = intake_parent_pane;
            var lmethod = $(lparent_pane).find('#intake_method').val();
            
            switch(lmethod){
                case 'add':
                    intake_methods.reset_all();
                    break;
                case 'view':
                    var lintake_id = $(lparent_pane).find('#intake_id').val();
                    var lajax_url = intake_data_support_url+'intake_get/';
                    var json_data = {data:lintake_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lintake = lresponse.intake;
                    var lproduct = lresponse.product;
                    
                    $(lparent_pane).find('#intake_reference').select2(
                        'data',lresponse.reference);                    
                    APP_COMPONENT.reference_detail.extra_info_set($('#intake_reference_detail'),lresponse.reference_detail,{reset:true});
                                        
                    $(lparent_pane).find('#intake_store').select2('data',{id:lintake.store_id
                        ,text:lintake.store_text});
                    $(lparent_pane).find('#intake_code').val(lintake.code);
                    $(lparent_pane).find('#intake_warehouse_from').
                        select2('data',{id:lintake.warehouse_from_id,
                            text:lintake.warehouse_from_text}
                        );
                    
                    $(lparent_pane).find('#intake_intake_date').datetimepicker({value:lintake.intake_date});
                    $(lparent_pane).find('#intake_notes').val(lintake.notes);
                    $(lparent_pane).find('#intake_intake_cancellation_reason').val(lintake.cancellation_reason);
                    
                    
                    $(lparent_pane).find('#intake_intake_status')
                            .select2('data',{id:lintake.intake_status
                                ,text:lintake.intake_status_text}).change();
                    
                    $(lparent_pane).find('#intake_intake_status')
                            .select2({data:lresponse.intake_status_list});
                    
                    intake_methods.table.product.load(lintake.intake_type,lproduct);
                    
                    break;
            }
        }
        
        
        intake_methods.enable_disable();
        intake_methods.show_hide();
        intake_data_set();
    }
    
    var intake_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>