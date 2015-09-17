<script>

    var rppl_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var rppl_ajax_url = null;
    var rppl_index_url = null;
    var rppl_view_url = null;
    var rppl_window_scroll = null;
    var rppl_data_support_url = null;
    var rppl_common_ajax_listener = null;
    var rppl_component_prefix_id = '';
    
    var rppl_insert_dummy = true;

    var rppl_init = function(){
        
        var parent_pane = rppl_parent_pane;
        rppl_ajax_url = '<?php echo $ajax_url ?>';
        rppl_index_url = '<?php echo $index_url ?>';
        rppl_view_url = '<?php echo $view_url ?>';
        rppl_window_scroll = '<?php echo $window_scroll; ?>';
        rppl_data_support_url = '<?php echo $data_support_url; ?>';
        rppl_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        rppl_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }
    
    var rppl_methods = {
        hide_all:function(){
            var lparent_pane = rppl_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#rppl_print').hide();
            $(lparent_pane).find('#rppl_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = rppl_parent_pane;
            var lmethod = $(lparent_pane).find('#rppl_method').val();
            rppl_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    
                case 'view':
                    $(lparent_pane).find('#rppl_submit').show();
                    $(lparent_pane).find('#rppl_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rppl_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rppl_refill_product_price_list_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rppl_notes').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = rppl_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = rppl_parent_pane;
            var lmethod = $(lparent_pane).find('#rppl_method').val();    
            rppl_methods.disable_all();
            switch(lmethod){
                case 'add':
                   
                    break;
                case 'view':
                   
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = rppl_parent_pane;
            var lprefix_id = rppl_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('');
            $(lparent_pane).find(lprefix_id+'_name').val('');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'refill_product_price_list'}).response;
            
            rppl_methods.product_medium_unit.reset();
            $(lparent_pane).find(lprefix_id+'_refill_product_price_list_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
        },
        product_medium_unit:{
            
            input_row_generate:function(){
                
                var lparent_pane = rppl_parent_pane;
                var lprefix_id = rppl_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody')[0];
                
                var lrow = document.createElement('tr');
                var fast_draw = APP_COMPONENT.table_fast_draw;
                
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'text'});                            
                var lproduct_category_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_category',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'product_category_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                var lproduct_medium_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_medium',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'product_medium_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                var lcapacity_unit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'capacity_unit',style:'vertical-align:middle',val:'<div><input original> </div>',type:'text'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'capacity_unit_id',style:'vertical-align:middle',val:'',type:'text',visible:false});
                var ldata_td = fast_draw.col_add(lrow,{tag:'td',col_name:'data',style:'vertical-align:middle',val:'<a>Price List Data</a><span style="display:none">[{"min_cap":"0","max_cap":"0","price":"C+0.00"}]</span>',type:'text',});
                var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                var lnew_row = APP_COMPONENT.new_row();    
                laction.appendChild(lnew_row);
        
                $(ltbody).append(lrow);
                
                $(ldata_td).find('a').hover(function(){
                    $(this).css('cursor','pointer');
                },function(){
                    $(this).css('cursor','auto');
                });
                
                $(ldata_td).find('a').on('click',function(){
                    rppl_methods.modal_price_list.show($(this).closest('tr'));
                    
                });
                
                $(lproduct_category_td).find('input[original]').on('change',function(){
                    
                    var lparent_pane = rppl_parent_pane;
                    var lproduct_category_id = $(this).select2('val');
                    var lrow = $(this).closest('tr')[0];
                    $(lrow).find('[col_name="product_category_id"]').text(lproduct_category_id);
                    
                    $(lrow).find('[col_name="product_medium"] input[original]').select2('data',null);
                    $(lrow).find('[col_name="product_medium"] input[original]').select2({data:[]});
                    
                    $(lrow).find('[col_name="capacity_unit"] input[original]').select2('data',null);
                    $(lrow).find('[col_name="capacity_unit"] input[original]').select2({data:[]});
                    
                    if($(this).select2('val')!==''){
                        var lproduct_medium = $(this).select2('data').product_medium;
                        $(lrow).find('[col_name="product_medium"] input[original]').select2({data:lproduct_medium});
                    }
                    
                }); 
                
                $(lproduct_medium_td).find('input[original]').on('change',function(){
                    
                    var lparent_pane = rppl_parent_pane;
                    var lproduct_medium_id = $(this).select2('val');
                    var lrow = $(this).closest('tr')[0];
                    $(lrow).find('[col_name="product_medium_id"]').text(lproduct_medium_id);
                    
                    $(lrow).find('[col_name="capacity_unit"] input[original]').select2('data',null);
                    $(lrow).find('[col_name="capacity_unit"] input[original]').select2({data:[]});
                    
                    if($(this).select2('val')!==''){
                        var lcapacity_unit = $(this).select2('data').capacity_unit;
                        $(lrow).find('[col_name="capacity_unit"] input[original]').select2({data:lcapacity_unit});
                    }
                    
                }); 
                
                $(lcapacity_unit_td).find('input[original]').on('change',function(){
                    
                    var lparent_pane = rppl_parent_pane;
                    var lcapacity_unit_id = $(this).select2('val');
                    var lrow = $(this).closest('tr')[0];
                    $(lrow).find('[col_name="capacity_unit_id"]').text(lcapacity_unit_id)
                    
                    
                }); 
                                
                APP_COMPONENT.input_select.set($(lproduct_category_td).find('input[original]')[0],
                    {min_input_length:0,ajax_url:'<?php echo $ajax_url.'input_select_refill_product_category_search/'?>'});
                APP_COMPONENT.input_select.set($(lproduct_medium_td).find('input[original]')[0]);
                APP_COMPONENT.input_select.set($(lcapacity_unit_td).find('input[original]')[0]);
            
                $(lnew_row).on('click',function(){
                    
                    var ltbody = $(this).closest('tbody')[0];
                    var lrow = $(this).closest('tr')[0];
                    var lpc_id = $(lrow).find('[col_name="product_category"] input[original]').select2('val');
                    var lpm_id = $(lrow).find('[col_name="product_medium"] input[original]').select2('val');
                    var lcapacity_unit_id = $(lrow).find('[col_name="capacity_unit"] input[original]').select2('val');
                    if(lpc_id !== '' && lpm_id!=='' && lcapacity_unit_id!=='' ){
                        var lpc_data = $(lrow).find('[col_name="product_category"] input[original]').select2('data');
                        var lpm_data = $(lrow).find('[col_name="product_medium"] input[original]').select2('data');
                        var lcapacity_unit_data = $(lrow).find('[col_name="capacity_unit"] input[original]').select2('data');
                        $(lrow).find('[col_name="product_category"]').empty();
                        $(lrow).find('[col_name="product_category"]')[0].innerHTML = '<span>'+lpc_data.text+'</span>';
                        $(lrow).find('[col_name="product_medium"]').empty();
                        $(lrow).find('[col_name="product_medium"]')[0].innerHTML = '<span>'+lpm_data.text+'</span>';
                        $(lrow).find('[col_name="capacity_unit"]').empty();
                        $(lrow).find('[col_name="capacity_unit"]')[0].innerHTML = '<span>'+lcapacity_unit_data.text+'</span>';
                        var ltrash = APP_COMPONENT.trash();
                        $(lrow).find('[col_name="action"]').empty();
                        $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                        $(ltbody).append(rppl_methods.product_medium_unit.input_row_generate());                        
                        
                    }
                    
                });
                
            },
            reset:function(){
                var lparent_pane = rppl_parent_pane;
                var lprefix_id = rppl_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody')[0];
                $(ltbody).empty();
                rppl_methods.product_medium_unit.input_row_generate();
            },
            load:function(idata_arr){
                var lparent_pane = rppl_parent_pane;
                var lprefix_id = rppl_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody')[0];
                $(ltbody).empty();
                fast_draw = APP_COMPONENT.table_fast_draw;
                $.each(idata_arr, function(ldix, ldata){
                    var lrow = document.createElement('tr');                    
                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'text'});                            
                    var lproduct_category_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_category',style:'vertical-align:middle',val:ldata.rpc_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_category_id',style:'vertical-align:middle',val:ldata.rpc_id,type:'text',visible:false});
                    var lproduct_medium_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product_medium',style:'vertical-align:middle',val:ldata.rpm_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_medium_id',style:'vertical-align:middle',val:ldata.rpm_id,type:'text',visible:false});
                    var lcapacity_unit_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'capacity_unit',style:'vertical-align:middle',val:ldata.capacity_unit_text,type:'span'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'capacity_unit_id',style:'vertical-align:middle',val:ldata.capacity_unit_id,type:'text',visible:false});
                    var ldata_td = fast_draw.col_add(lrow,{tag:'td',col_name:'data',style:'vertical-align:middle',val:'<a>Price List Data</a><span style="display:none">'+JSON.stringify(ldata.rppl_product_price)+'</span>',type:'text',});
                    var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    $(ltbody).append(lrow);
                    
                    var ltrash = APP_COMPONENT.trash();
                    $(lrow).find('[col_name="action"]').empty();
                    $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                    
                    
                    $(ldata_td).find('a').hover(function(){
                        $(this).css('cursor','pointer');
                    },function(){
                        $(this).css('cursor','auto');
                    });

                    $(ldata_td).find('a').on('click',function(){
                        rppl_methods.modal_price_list.show($(this).closest('tr'));

                    });
                });
            },
            get:function(){
                
                var lresult = [];
                var lparent_pane = rppl_parent_pane;
                var lprefix_id = rppl_component_prefix_id;
                var ltbody = $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody')[0];
                $.each($(ltbody).find('tr'),function(lidx, lrow){
                    var lpc_id = $(lrow).find('[col_name="product_category_id"]').text();
                    var lpm_id = $(lrow).find('[col_name="product_medium_id"]').text();
                    var lcapacity_unit_id = $(lrow).find('[col_name="capacity_unit_id"]').text();
                    var lrppl_product_price = JSON.parse($(lrow).find('[col_name="data"] span').text());
                    
                    if(lpm_id !== '' && lcapacity_unit_id !== ''){
                        lresult.push({refill_product_category_id:lpc_id,refill_product_medium_id:lpm_id,capacity_unit_id:lcapacity_unit_id,rppl_product_price:lrppl_product_price});
                    }
                });
                return lresult;
                
            }
            
        },
        modal_price_list:{
            title_set:function(irow){
                var lparent_pane = rppl_parent_pane;
                var lprefix_id = rppl_component_prefix_id;
                var lmodal = $(lparent_pane).find(lprefix_id+'_modal_price_list')[0];
                var is_select2 = $(irow).index() === $(lparent_pane).find('#rppl_product_medium_unit_table tbody tr').length-1? true:false;

                var lproduct_category_text = '';
                if(is_select2){
                    if($(irow).find('[col_name="product_category"] input[original]').select2('val')!== ''){
                        lproduct_category_text = $(irow).find('[col_name="product_category"] input[original]').select2('data').text;
                    }
                }else{
                    lproduct_category_text = $(irow).find('[col_name="product_category"] span').text();
                }
                
                var lproduct_medium_text = '';
                if(is_select2){
                    if($(irow).find('[col_name="product_medium"] input[original]').select2('val')!== ''){
                        lproduct_medium_text = ' - '+$(irow).find('[col_name="product_medium"] input[original]').select2('data').text;
                    }
                }else{
                    lproduct_medium_text = ' - '+$(irow).find('[col_name="product_medium"] span').text();
                }
                
                var lcapacity_unit_text = '';
                if(is_select2){
                    if($(irow).find('[col_name="capacity_unit"] input[original]').select2('val')!== ''){
                        lcapacity_unit_text = ' - '+$(irow).find('[col_name="capacity_unit"] input[original]').select2('data').text;
                    }
                }else{
                    lcapacity_unit_text = ' - '+$(irow).find('[col_name="capacity_unit"] span').text();
                }
                
                var lmodal_title = lproduct_category_text + lproduct_medium_text + lcapacity_unit_text;
                $(lmodal).find('.modal-header span')[0].innerHTML = lmodal_title;
                
            },
            price_list_table:{
                set:function(irow){
                    var lparent_pane = rppl_parent_pane;
                    var lprefix_id = rppl_component_prefix_id;
                    var lmodal = $(lparent_pane).find(lprefix_id+'_modal_price_list')[0];
                    var ltbody = $(lmodal).find('#price_list_table tbody')[0];
                    $(ltbody).empty();
                    
                    var ldata_arr = JSON.parse($(irow).find('[col_name="data"] span').text());
                    
                    if(ldata_arr.length === 0)
                        rppl_methods.modal_price_list.price_list_table.input_row_generate([]);
                    
                    $.each(ldata_arr, function(lidx, ldata){
                        rppl_methods.modal_price_list.price_list_table.input_row_generate(ldata);
                        if(lidx< ldata_arr.length-1){
                            var ltrash = APP_COMPONENT.trash();
                            var lrow = $(ltbody).find('tr:last()')[0];
                            $(lrow).find('[col_name="action"]').empty();
                            $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                        }
                    });
                },
                input_row_generate:function(ldata){
                    var lparent_pane = rppl_parent_pane;
                    var lprefix_id = rppl_component_prefix_id;
                    var lmodal = $(lparent_pane).find(lprefix_id+'_modal_price_list')[0];
                    var ltbody = $(lmodal).find('#price_list_table tbody')[0];
                    
                    var lrow = document.createElement('tr');
                    var fast_draw = APP_COMPONENT.table_fast_draw;

                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:$(ltbody).children().length+1,type:'text'});
                    var lmin_cap = typeof ldata.min_cap !== 'undefined'?ldata.min_cap:'';
                    var lmin_cap_td = fast_draw.col_add(lrow,{tag:'td',col_name:'min_cap',style:'text-align:right',class:'form-control',val:lmin_cap,type:'input'});
                    APP_COMPONENT.input.numeric($(lmin_cap_td).find('input')[0]);
                    $(lmin_cap_td).find('input').blur();
                    
                    var lmax_cap = typeof ldata.max_cap !== 'undefined'?ldata.max_cap:'';
                    var lmax_cap_td = fast_draw.col_add(lrow,{tag:'td',col_name:'max_cap',style:'text-align:right',class:'form-control',val:lmax_cap,type:'input'});
                    APP_COMPONENT.input.numeric($(lmax_cap_td).find('input')[0]);
                    $(lmax_cap_td).find('input').blur();
                    
                    var lprice = typeof ldata.price !== 'undefined'?ldata.price:'C+0.00';
                    var lprice_td = fast_draw.col_add(lrow,{tag:'td',col_name:'price',style:'text-align:right',comp_attr:{placeholder:'use C as a variable in your function'},class:'form-control',val:lprice,type:'input'});
                    $(lprice_td).find('input').on('click',function(){
                        $(this).select();
                    });
                    $(lprice_td).find('input').on('keyup',function(e){
                        if([37,39,9].indexOf(e.keyCode)== -1 ){
                            var c = $(this);
                            var ltext = c.val();
                            if(ltext.match(/[^0-9cC+-/*//^()]/g)!== null){
                                ltext = ltext.replace(/[^0-9cC+-/*//^()]/g,'');
                                c.val(ltext);
                            }
                        }
                    });
                    
                    var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                    var lnew_row = APP_COMPONENT.new_row();    
                    laction.appendChild(lnew_row);

                    $(lnew_row).on('click',function(){
                        var ltbody = $(this).closest('tbody')[0];
                        var lrow = $(this).closest('tr')[0];
                        var ltrash = APP_COMPONENT.trash();
                        $(lrow).find('[col_name="action"]').empty();
                        $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                        $(ltbody).append(rppl_methods.modal_price_list.price_list_table.input_row_generate({}));
                        setTimeout(function(){$(ltbody).find('tr:last() [col_name="min_cap"] input').focus();},250);
                    });

                    $(ltbody).append(lrow);
                }
            },
            data_get:function(){
                var lresult = [];
                var lparent_pane = rppl_parent_pane;
                var lprefix_id = rppl_component_prefix_id;
                var lmodal = $(lparent_pane).find(lprefix_id+'_modal_price_list')[0];
                var ltbody = $(lmodal).find('#price_list_table tbody')[0];
                $.each($(ltbody).find('tr'),function(lidx, lrow){
                    var ltemp = {
                        min_cap:$(lrow).find('[col_name="min_cap"] input').val().replace(/[^0-9.]/g,''),
                        max_cap:$(lrow).find('[col_name="max_cap"] input').val().replace(/[^0-9.]/g,''),
                        price:$(lrow).find('[col_name="price"] input').val().replace(/[,]/g,''),
                    };
                    
                        lresult.push(ltemp);
                    
                });
                return lresult;
            },
            show:function(irow){
                var lparent_pane = rppl_parent_pane;
                var lprefix_id = rppl_component_prefix_id;
                var lmodal = $(lparent_pane).find(lprefix_id+'_modal_price_list')[0];
                
                rppl_methods.modal_price_list.title_set(irow);
                rppl_methods.modal_price_list.price_list_table.set(irow);
                $(lmodal).find('#product_category_row').val(irow.index());                                                
                
                $(lmodal).attr('active_child',$(this).closest('tr').index());
                $(lmodal).modal('show');

            }
        },
        submit:function(){
            
            var lparent_pane = rppl_parent_pane;
            var lprefix_id = rppl_component_prefix_id;
            var lajax_url = rppl_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.rppl = {
                        code:$(lparent_pane).find(lprefix_id+'_code').val(),
                        name:$(lparent_pane).find(lprefix_id+'_name').val(),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    };
                    
                    json_data.rppl_product = rppl_methods.product_medium_unit.get();
                    
                    lajax_url +='refill_product_price_list_add/';
                    break;
                case 'view':
                    json_data.rppl = {
                        code:$(lparent_pane).find(lprefix_id+'_code').val(),
                        name:$(lparent_pane).find(lprefix_id+'_name').val(),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    };
                    json_data.rppl_product = rppl_methods.product_medium_unit.get();
                    
                    var rppl_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_refill_product_price_list_status').
                        select2('data').method;
                    lajax_url +=lajax_method+'/'+rppl_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(rppl_view_url !==''){
                    var url = rppl_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    rppl_after_submit();
                }
            }
            
        }
    };
    
    var rppl_bind_event = function(){
        
        var lparent_pane = rppl_parent_pane;
        var lprefix_id = rppl_component_prefix_id;
        
        $(lparent_pane).find('#rppl_submit').off();
        var lparam = {
            window_scroll: rppl_window_scroll,
            parent_pane: rppl_parent_pane,
            module_method: rppl_methods
        };
        
        APP_COMPONENT.button.submit.set(
            $(lparent_pane).find('#rppl_submit')[0],
            lparam
        );
        
        $('#rpl_modal_price_list_btn_ok').on('click',function(){
            var lparent_pane = rppl_parent_pane;
            var lprefix_id = rppl_component_prefix_id;
            var lmodal = $(lparent_pane).find(lprefix_id+'_modal_price_list')[0];

            var ldata = rppl_methods.modal_price_list.data_get();
            var lajax_url = rppl_data_support_url+'price_list_function_is_valid/';
            
            var lresponse = APP_DATA_TRANSFER.submit(lajax_url,ldata);

            if(lresponse.success === 1){
                var lfinal_data = rppl_methods.modal_price_list.data_get();
                var lrow_idx = $(lmodal).find('#product_category_row').val();
                $(lparent_pane).find(lprefix_id+'_product_medium_unit_table tbody tr:eq('+lrow_idx+') [col_name="data"] span')[0].innerHTML = 
                    JSON.stringify(lfinal_data);
                $('#app_msg').remove();
                $(lmodal).modal('hide');
            }
        });
        
    }
    
    var rppl_components_prepare = function(){
        

        var rppl_data_set = function(){
            var lparent_pane = rppl_parent_pane;
            var lprefix_id = rppl_component_prefix_id;
            var lmethod = $(lparent_pane).find('#rppl_method').val();
            
            switch(lmethod){
                case 'add':
                    rppl_methods.reset_all();
                    if(rppl_insert_dummy){
                        
                    }
                    break;
                case 'view':
                    
                    var lrppl_id = $(lparent_pane).find('#rppl_id').val();
                    var lajax_url = rppl_data_support_url+'rppl_get/';
                    var json_data = {data:lrppl_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lrppl = lresponse.rppl;
                    var lrppl_product = lresponse.rppl_product;

                    $(lparent_pane).find('#rppl_code').val(lrppl.code);
                    $(lparent_pane).find('#rppl_name').val(lrppl.name);
                    $(lparent_pane).find('#rppl_notes').val(lrppl.notes);
                    
                    rppl_methods.product_medium_unit.load(lrppl_product);
                    rppl_methods.product_medium_unit.input_row_generate();
                    
                    $(lparent_pane).find('#rppl_refill_product_price_list_status')
                        .select2('data',{id:lrppl.refill_product_price_list_status
                            ,text:lrppl.refill_product_price_list_status_text}).change();
                    
                    $(lparent_pane).find('#rppl_refill_product_price_list_status')
                            .select2({data:lresponse.refill_product_price_list_status_list});
                    
                    break;
            }
        }
        
        
        rppl_methods.enable_disable();
        rppl_methods.show_hide();
        rppl_data_set();
    }
    
    var rppl_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>