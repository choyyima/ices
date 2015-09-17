<script>

    var product_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var product_ajax_url = null;
    var product_index_url = null;
    var product_view_url = null;
    var product_window_scroll = null;
    var product_data_support_url = null;
    var product_common_ajax_listener = null;
    var product_component_prefix_id = '';
    
    var product_insert_dummy = true;

    var product_init = function(){
        var parent_pane = product_parent_pane;
        product_ajax_url = '<?php echo $ajax_url ?>';
        product_index_url = '<?php echo $index_url ?>';
        product_view_url = '<?php echo $view_url ?>';
        product_window_scroll = '<?php echo $window_scroll; ?>';
        product_data_support_url = '<?php echo $data_support_url; ?>';
        product_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        product_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
                
        
    }
    
    var product_methods = {
        hide_all:function(){
            var lparent_pane = product_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#product_print').hide();
            $(lparent_pane).find('#product_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = product_parent_pane;
            var lmethod = $(lparent_pane).find('#product_method').val();
            product_methods.hide_all();
            
            switch(lmethod){
                case 'add':   
                    $(lparent_pane).find('#product_submit').show();
                    $(lparent_pane).find('#product_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#product_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#product_product_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#product_notes').closest('.form-group').show
                    break;
                case 'view':
                    $(lparent_pane).find('#product_submit').show();
                    $(lparent_pane).find('#product_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#product_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#product_product_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#product_notes').closest('.form-group').show();
                    
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = product_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = product_parent_pane;
            var lmethod = $(lparent_pane).find('#product_method').val();    
            product_methods.disable_all();
            switch(lmethod){
                case 'add':
                   
                    break;
                case 'view':
                   
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = product_parent_pane;
            var lprefix_id = product_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('');
            $(lparent_pane).find(lprefix_id+'_name').val('');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'product'}).response;

            $(lparent_pane).find(lprefix_id+'_product_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];

        },
        submit:function(){
            var lparent_pane = product_parent_pane;
            var lprefix_id = product_component_prefix_id;
            var lajax_url = product_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    var product_subcategory_id = $(lprefix_id+"_subcategory_id").select2('val'); 
                    json_data.product = {
                        code:$(lprefix_id+"_code").val(),
                        name:$(lprefix_id+"_name").val(),
                        notes:$(lprefix_id+"_notes").val(),
                        additional_info:$(lprefix_id+"_additional_info").val(),
                        product_subcategory_id:product_subcategory_id == null?
                            '':product_subcategory_id,            
                        product_img:$(lprefix_id+'_img_view').attr('src')
                    };
                    json_data.unit = get_dt_product_unit_table();
                    json_data.product_unit_parent = product_methods.product_unit_parent_get();
                    json_data.product_cfg = {
                        rswo_product_reference_req:$(lprefix_id+'_rswo_product_reference_req').select2('val')
                    };
                    lajax_url +='product_add/';
                    break;
                case 'view':
                    var product_subcategory_id = $(lprefix_id+"_subcategory_id").select2('val'); 
                    json_data.product = {
                        code:$(lprefix_id+"_code").val(),
                        name:$(lprefix_id+"_name").val(),
                        notes:$(lprefix_id+"_notes").val(),
                        additional_info:$(lprefix_id+"_additional_info").val(),
                        product_subcategory_id:product_subcategory_id == null?
                            '':product_subcategory_id,            
                        product_img:$(lprefix_id+'_img_view').attr('src').replace('<?php echo get_instance()->config->base_url(); ?>img/blank.gif?lastmod=12345678','')
                    };
                    json_data.unit = get_dt_product_unit_table();
                    json_data.product_unit_parent = product_methods.product_unit_parent_get();
                    
                    json_data.product_cfg = {
                        rswo_product_reference_req:$(lprefix_id+'_rswo_product_reference_req').select2('val')
                    };
                    
                    // this function has been generated automatically from input select table js
                    var product_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_product_status').
                        select2('data').method;
                    lajax_url +=lajax_method+'/'+product_id;
                    break;
            }
            
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(product_view_url !==''){
                    var url = product_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    product_after_submit();
                }
            }
            
        },
        product_unit_parent_get:function(){
            var lparent_pane = product_parent_pane;
            var lprefix_id = product_component_prefix_id;
            var lresult = [];
            var lproduct_id = $(lparent_pane).find(lprefix_id+'_id').val();
            
            $.each($(lparent_pane).find(lprefix_id+'_unit_table tbody tr'),function(lidx, lrow){
                var lunit_id = $(lrow).find('[name="id"]').text();
                var ldata = JSON.parse($(lrow).find('[name="product_unit_parent_child"] a').attr('data'));
                lresult.push({
                    product_id:lproduct_id,
                    unit_id:lunit_id,
                    qty:ldata.parent_qty,
                    product_unit_child: ldata.product_unit_child
                });
            });
            return lresult;
        }
    };
    
    var product_bind_event = function(){
        var lparent_pane = product_parent_pane;
        var lprefix_id = product_component_prefix_id;
                
        $(lprefix_id+'_img').on('change',function(){
            var limg_inpt = $(lprefix_id+'_img')[0];
            if(typeof limg_inpt.files[0] !== 'undefined' ){
                lfile = limg_inpt.files[0];
                var lmax_size = 10000;
                if(lfile.size<lmax_size){
                    lfr = new FileReader();
                    lfr.onload = function(e){
                        $(lprefix_id+'_img_view').attr('src',lfr.result);
                    }
                    lfr.readAsDataURL(lfile);                
                }
                else{
                    $(this).val('');
                    alert('File size must be lower than '+lmax_size+' bytes');
                }
            }

        });
                
        $(lparent_pane).find('#product_submit').off();
        var lparam = {
            window_scroll: product_window_scroll,
            parent_pane: product_parent_pane,
            module_method: product_methods
        };
        
        APP_COMPONENT.button.submit.set(
            $(lparent_pane).find('#product_submit')[0],
            lparam
        );

        $(lparent_pane).find(lprefix_id+'_unit').on('change',function(){
            var lparent_pane = product_parent_pane;
            var lprefix_id = product_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_unit_table [name="product_unit_parent_child"] a').last().attr('data','{"parent_qty":"1.00000","product_unit_child":[]}');
            $(lparent_pane).find(lprefix_id+'_unit_table [name="product_unit_parent_child"] a').last().off();
            $(lparent_pane).find(lprefix_id+'_unit_table [name="product_unit_parent_child"] a').last().on('click',function(){
                child_product_methods.load_modal($(this).closest('tr'));
            });
        });
        
        child_product_bind_event();
        
    }
    
    var product_components_prepare = function(){
        

        var product_data_set = function(){
            var lparent_pane = product_parent_pane;
            var lprefix_id = product_component_prefix_id;
            var lmethod = $(lparent_pane).find('#product_method').val();
            
            switch(lmethod){
                case 'add':
                    product_methods.reset_all();
                    if(product_insert_dummy){
                        
                    }
                    break;
                case 'view':
                    
                    var lproduct_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_url = product_data_support_url+'product_get/';
                    var json_data = {data:lproduct_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;

                    var lproduct = lresponse.product;

                    $(lparent_pane).find(lprefix_id+'_img_view').attr('src',lproduct.product_img);
                    $(lparent_pane).find(lprefix_id+'_code').val(lproduct.code);
                    $(lparent_pane).find(lprefix_id+'_name').val(lproduct.name);
                    $(lparent_pane).find(lprefix_id+'_notes').val(lproduct.notes);
                    $(lparent_pane).find(lprefix_id+'_additional_info').val(lproduct.additional_info);
                    
                    $(lparent_pane).find(lprefix_id+'_subcategory_id').select2(
                        'data',{id:lproduct.product_subcategory_id,
                            text:lproduct.product_subcategory_text}
                    );
                    
                    $(lparent_pane).find(lprefix_id+'_rswo_product_reference_req').select2('data',{id:lresponse.product_cfg.rswo_product_reference_req,text:lresponse.product_cfg.rswo_product_reference_req_text});
                    
                    $.each(lresponse.product_unit,function(lidx,lproduct_unit){
                        $(lparent_pane).find(lprefix_id+'_unit').select2('data',{
                            id:lproduct_unit.id, text:lproduct_unit.text
                        }).change();
                        var ltbody = $('#product_unit_table tbody')[0];
                        var lrow = $(ltbody).find('[name="id"]:contains("'+lproduct_unit.id+'")').closest('tr');
                        
                        var json_data2 = {data:lproduct_id};
                        var lresponse2 = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>product/ajax_search/product_buffer_stock_qty_get',
                            json_data2);
                        $.each(lresponse2,function(key, val){
                            var lid_td = $(ltbody).find('[name="id"]:contains("'+val.unit_id+'")');

                            $(lid_td).closest('tr').find('[name="buffer_stock_qty"]').find('input').val(val.qty).blur();
                        });
                        var lresponse2 = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>product/ajax_search/product_sales_multiplication_qty_get',
                            json_data2);
                        $.each(lresponse2,function(key, val){
                            var lid_td = $(ltbody).find('[name="id"]:contains("'+val.unit_id+'")');
                            $(lid_td).closest('tr').find('[name="product_sales_multiplication_qty"]').find('input').val(val.qty).blur();
                        });
                        
                        var lpupc_data = [];
                        $.each(lresponse.product_unit_parent, function(lidx, lrow){
                            if(lrow.product_id === lproduct_id && lrow.unit_id === lproduct_unit.id){
                                lpupc_data={parent_qty:lrow.qty, product_unit_child:lrow.product_unit_child};
                            }
                        });
                        $(lrow).find('[name="product_unit_parent_child"] a').attr('data',JSON.stringify(lpupc_data));
                        
                    });
                    
                    $(lparent_pane).find(lprefix_id+'_product_status')
                        .select2('data',{id:lproduct.product_status
                            ,text:lproduct.product_status_text}).change();
                    
                    $(lparent_pane).find(lprefix_id+'_product_status')
                            .select2({data:lresponse.product_status_list});
                    
                    
                    break;
            }
        }
        
        
        product_methods.enable_disable();
        product_methods.show_hide();
        product_data_set();
    }
    
    var product_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>