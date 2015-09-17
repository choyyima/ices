<script>
    var sales_pos_movement_props={
        reset_all:true,
        module:'intake',
    };
    var sales_pos_movement_section_bind_events=function(){
        var lparent_pane = sales_pos_parent_pane;
        
        $(lparent_pane).find('#sales_pos_movement_modal_product_btn_cancel').on('click',function(e){     
            $(lparent_pane).find('#sales_pos_movement_modal_product_btn_ok').off();
            $(this).off();
        });
        
    }
    
    var sales_pos_movement_section_methods={
        hide_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            $(lparent_pane).find('[routing_section="movement"]').hide();
            
        },
        show_hide:function(){
            sales_pos_movement_section_methods.hide_all();
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            
            var lstatus = $(lparent_pane).find('#sales_pos_sales_pos_status').select2('val');
            
            
        },
        disable_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lsection = $(sales_pos_parent_pane).find('[routing_section="movement"]')[0];
            APP_COMPONENT.disable_all(lsection);
            
        },
        enable_disable:function(){
            var lparent_pane = sales_pos_parent_pane;
            sales_pos_movement_section_methods.disable_all();
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            switch(lmethod){
                case 'add':
                break;
            }
        },
        reset_all:function(){
            if(sales_pos_movement_props.reset_all){
                var lparent_pane = sales_pos_parent_pane;
                sales_pos_movement_section_methods.table.reset('intake');
                sales_pos_movement_section_methods.table.reset('delivery');
                sales_pos_movement_props.reset_all = false;
            }
            
        },
        show_hide_routing:function(){
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            var lis_delivery = $(lparent_pane).find('#sales_pos_delivery_checkbox').is(':checked');
            if(lis_delivery){
                $(lparent_pane).find('#sales_pos_delivery').show();
                $(lparent_pane).find('#sales_pos_intake').hide();
            }
            else{
                $(lparent_pane).find('#sales_pos_delivery').hide();
                $(lparent_pane).find('#sales_pos_intake').show();                            
            }
            $(lparent_pane).find('#sales_pos_new_dof').hide();
            $(lparent_pane).find('#sales_pos_new_intake_final').hide();
            
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    var lstatus = $(lparent_pane).find('#sales_pos_sales_pos_status').select2('val');
                    
                    switch(sales_pos_movement_props.module){
                        case 'delivery':
                            if(lstatus ==='invoiced')
                            $(lparent_pane).find('#sales_pos_new_dof').show();
                            break;
                        case 'intake':
                            if(lstatus === 'invoiced')
                            $(lparent_pane).find('#sales_pos_new_intake_final').show();
                            break;
                    }
            
                    break;
            }
        },
        check_still_something_left:function(){
            
            var lresult = false;
            
            var lparent_pane = sales_pos_parent_pane;
            var lproduct = sales_pos_movement_section_methods.existing_product_to_json();
            $.each(lproduct.pos,function(idx, pos_product){
                var qty_diff = parseFloat(pos_product.qty);
                
                for(i = 0;i<lproduct.movement.length;i++){
                    for (j=0;j<lproduct.movement[i].length;j++){
                        for(k = 0;k<lproduct.movement[i][j].product.length;k++){
                            lproduct_id = typeof lproduct.movement[i][j].product[k].product_id!=='undefined'? 
                                lproduct.movement[i][j].product[k].product_id:'';
                            lunit_id = typeof lproduct.movement[i][j].product[k].unit_id!=='undefined'? 
                                lproduct.movement[i][j].product[k].unit_id:'';
                            lqty = typeof lproduct.movement[i][j].product[k].qty!=='undefined'? 
                                lproduct.movement[i][j].product[k].qty:'0';
                            
                            if(lproduct_id === pos_product.product_id && lunit_id === pos_product.unit_id){
                                qty_diff -= parseFloat(lqty);
                            }
                        }
                    }
                }
                
                if(qty_diff >0 && qty_diff < APP_CONVERTER._float(pos_product.total_stock)
                    && APP_CONVERTER._float(pos_product.total_stock) > APP_CONVERTER._float(0)
                ){
                    lresult = true;
                    
                }
                
                if(lresult) return false;
            });
            
            return lresult;
            
        },
        btn_controller_set:function(){
            var lvalid = true;
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            var lis_delivery = $(lparent_pane).find('#sales_pos_delivery_checkbox').is(':checked');
            
            sales_pos_methods.btn_controller_reset();
            
            $(lparent_pane).find('#sales_pos_btn_prev').show();
            if(lmethod === 'add'){
                $(lparent_pane).find('#sales_pos_submit').show();    
            }
            else if(lmethod ==='view'){
                if(lis_delivery){
                    $(lparent_pane).find('#sales_pos_btn_next').show();
                }
                
            }
            
            
            
            switch(lmethod){
                case 'add':
                    lvalid = sales_pos_movement_section_methods.check_still_something_left()?false:true;
                    
                    if(lvalid){
                        $(lparent_pane).find('#sales_pos_submit').prop('disabled',false);                        
                    }
                    break;
                case 'view':
                    $(lparent_pane).find('#sales_pos_btn_next').prop('disabled',false);
                    $(lparent_pane).find('#sales_pos_btn_next').on('click',function(e){
                        e.preventDefault();
                        sales_pos_routing.set(lmethod,'cd_cb');
                    });
                    break;
            }
            
            $(lparent_pane).find('#sales_pos_btn_prev').prop('disabled',false);
            $(lparent_pane).find('#sales_pos_btn_prev').on('click',function(e){
                e.preventDefault();
                sales_pos_routing.set(lmethod,'payment');
            });
            
            
        },
        table:{
            reset:function(module){
                var lparent_pane = sales_pos_parent_pane;
                switch(module){
                    case 'intake':                        
                        ltbody = $(lparent_pane).find('#sales_pos_movement_intake_table').find('tbody')[0];
                        $(ltbody).empty();
                        var linput_row = sales_pos_movement_section_methods.table.input_row_generate(module);
                        ltbody.appendChild(linput_row);
                        break;
                    case 'delivery':                        
                        ltbody = $(lparent_pane).find('#sales_pos_movement_delivery_table').find('tbody')[0];
                        $(ltbody).empty();
                        var linput_row = sales_pos_movement_section_methods.table.input_row_generate(module);
                        ltbody.appendChild(linput_row);
                        break;
                }
                
            },
            input_row_generate:function(module){                
                fast_draw = APP_COMPONENT.table_fast_draw;
                var lrow = document.createElement('tr');  
                $(lrow).attr('module',module);
                fast_draw.col_add(lrow,{tag:'td',col_name:'id',col_style:'display:none',val:'',type:'text'});
                var row_num = $(sales_pos_parent_pane).find('#sales_pos_movement_'+module+'_table').find('tbody').children().length;
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                fast_draw.col_add(lrow,{tag:'td',col_name:'code',style:'vertical-align:middle',val:'<strong>[AUTO GENERATE]</strong>',type:'div'});
                var ldate_td = fast_draw.col_add(lrow,{tag:'td',col_name:module+'_date',style:'',val:'<div><input class="form-control"></div>',type:'text'});
                var list_of_product_td = fast_draw.col_add(lrow,{tag:'td',col_name:'',style:'',val:'<span><a href="#" method="add" >Product/s</a></span>',type:'text'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'movement_data',col_style:'display:none',val:'{}',type:'text'});
                var lmodule_status_td = fast_draw.col_add(lrow,{tag:'td',col_name:'movement_status',style:'vertical-align:middle',val:'',type:'text'});
                var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                var lnew_row = APP_COMPONENT.new_row();    
                laction.appendChild(lnew_row);
                if(module==='intake') $(laction).empty();
                
                $(ldate_td).find('input')
                .datetimepicker({
                    value:APP_GENERATOR.CURR_DATETIME('minute',10,'F d, Y H:i'),
                    format:'F d, Y H:i'
                });
                
                var lajax_url = '';
                var ljson_data = {};
                
                lajax_url = '<?php echo get_instance()->config->base_url(); ?>common_ajax_listener/module_status/default_status_get/';
                
                if(module === 'delivery'){
                    ljson_data.module = 'delivery_order_final';
                }
                else if(module === 'intake'){
                    ljson_data.module = 'intake_final';
                }

                var ldefault_status = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data).response;

                $(lmodule_status_td)[0].innerHTML = ldefault_status.label;
                
                $(list_of_product_td).find('a').on('click',function(){
                    var lparent_pane = sales_pos_parent_pane;
                    var lmethod = $(this).attr('method');
                    var lmodule = $(this).closest('tr').attr('module');
                    var lrow = $(this).closest('tr')[0];
                    switch(lmethod){
                        case 'add':
                            sales_pos_movement_section_methods.modal_product.add(lmodule,lrow);
                            break;
                        case 'view':
                            sales_pos_movement_section_methods.modal_product.view(lmodule,lrow);
                            break;                                
                    }
                    var ltitle = lmodule==='delivery'?'<?php echo Lang::get('Delivery Order'); ?>' :'<?php echo Lang::get('Product Intake'); ?>' ;
                    $('#sales_pos_movement_modal_product_title').text(ltitle);
                    $('#sales_pos_movement_modal_product').modal('show');
                });
                
                if(module==='delivery'){
                    $(lnew_row).on('click',function(){
                        var lmodule = $(this).closest('tr').attr('module');
                        var cont = true;
                        var lrow = $(this).closest('tr');
                        var lparent_pane = sales_pos_parent_pane;
                        var lcurr_final_movement = $(lrow).find('[col_name="movement_data"]').text();
                        var curr_final_movement_has_product = false;
                        $.each(JSON.parse(lcurr_final_movement),function(idx0, movement){
                            $.each(movement.product,function(idx1, product){
                                curr_final_movement_has_product = true;
                            });
                        });
                        if(!curr_final_movement_has_product) cont = false;
                        if(!sales_pos_movement_section_methods.check_still_something_left()) cont = false;
                        if( cont ){
                            $(lrow).find('[col_name="'+lmodule+'_date"]').find('input').prop('disabled',true);
                            $(lrow).find('[method]').attr('method','view');
                            $(lrow).find('[col_name="action"]').empty();
                            var ltrash = APP_COMPONENT.trash();                        
                            $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                            $(ltrash).on('click',function(){                            
                                sales_pos_movement_section_methods.btn_controller_set();                            
                            });
                            var ltbody = $(lparent_pane).find('#sales_pos_movement_'+lmodule+'_table').find('tbody')[0];
                            var linput_row = sales_pos_movement_section_methods.table.input_row_generate(lmodule);
                            ltbody.appendChild(linput_row);
                            sales_pos_movement_section_methods.btn_controller_set();
                        }
                        APP_WINDOW.scroll_bottom();
                    });
                }
                return lrow;
            }
        },
        existing_product_to_json:function(){
            
            var result = {pos:[],movement:[]};
            var lparent_pane = sales_pos_parent_pane;
            var ltrs = $(lparent_pane).find('#sales_pos_product_table>tbody>tr');
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            $.each(ltrs, function(idx, ltr){
                var lproduct_id = '';
                
                lproduct_id = $(ltr).find('[col_name="product_id"] span').text();
                
                if(lproduct_id !== ''){
                    var lproduct_text = '';                    
                    var lunit_id = '';
                    var lunit_text = '';
                    var lqty = '';
                    var ltotal_stock = $(ltr).find('[col_name="total_stock"] span').text().replace(/[,]/g,'');
                    var lproduct_img = $(ltr).find('[col_name="product_img"]')[0].innerHTML                    

                    lproduct_text = '';
                    if($(ltr).find('[col_name="product"] input[original]').length>0){
                        lproduct_text = $(ltr).find('[col_name="product"] input[original]').select2('data')['text'];
                    }
                    else{
                        lproduct_text = $(ltr).find('[col_name="product"] div')[0].innerHTML;
                    }
                    
                    lunit_id = $(ltr).find('[col_name="unit_id"] span').text();
                    lunit_text = '';
                    if($(ltr).find('[col_name="unit"] input[original]').length>0){
                        lunit_text = $(ltr).find('[col_name="unit"] input[original]').select2('data')['text'];
                    }
                    else{
                        lunit_text = $(ltr).find('[col_name="unit"] span')[0].innerHTML;
                    }
                    
                    lqty = '0';
                    if($(ltr).find('[col_name="qty"] input').length>0){
                        lqty = $(ltr).find('[col_name="qty"] input').val().replace(/[,]/g,'');
                    }
                    else{
                        lqty = $(ltr).find('[col_name="qty"] span').text().replace(/[,]/g,'');
                    }
                    
                    result.pos.push({
                        product_id:lproduct_id,
                        product_img:lproduct_img,
                        product_text:lproduct_text,
                        unit_id:lunit_id,
                        unit_text:lunit_text,
                        qty:lqty,
                        total_stock:ltotal_stock
                    });
                }
            });
            
            var ltrs = $(lparent_pane).find('#sales_pos_movement_'+sales_pos_movement_props.module+'_table>tbody>tr');
            $.each(ltrs, function(idx, ltr){
                var lproduct_data = JSON.parse($(ltr).find('[col_name="movement_data"]').text());
                result.movement.push(lproduct_data);
            });
            
            return result;
            
        },
        modal_product:{
            reserved_qty_calculate:function(lrow){
                var lparent_pane = sales_pos_parent_pane;
                var lproduct_id = $(lrow).find('[col_name="product_id"]').text();
                var lunit_id = $(lrow).find('[col_name="unit_id"]').text();
                var lordered_qty = parseFloat($('#sales_pos_movement_modal_product_table tbody tr:eq('+$(lrow).index()+') [col_name="ordered_qty"] span').text().replace(/[^0-9.]/g,''));
                var lselected_qty = sales_pos_movement_section_methods.modal_product.selected_qty_get(lproduct_id, lunit_id);
                var lqty_input_arr = $(lrow).find('[col_name="qty"] input');
                var lqty_total = parseFloat('0');
                $.each(lqty_input_arr, function(lidx, linput){
                    lqty_total+= parseFloat($(linput).val().replace(/[^0-9.]/g,''));
                });
                
                var lreserved_qty = lordered_qty - lselected_qty - lqty_total; 
                
                $(lparent_pane).find('#sales_pos_movement_modal_product_table tbody tr:eq('+$(lrow).index()+') [col_name="reserved_qty"] span').text(
                    APP_CONVERTER.thousand_separator(lreserved_qty)
                ).change();
            },
            selected_qty_get:function(lproduct_id, lunit_id){
                var lparent_pane = sales_pos_parent_pane;
                var lmodule= sales_pos_movement_props.module;
                var ltbody = $(lparent_pane).find('#sales_pos_movement_'+lmodule+'_table tbody')[0];
                var lresult = parseFloat('0');
                $.each($(ltbody).find('tr:not(:last)'), function(lidx, lrow){
                    var ldata = JSON.parse($(lrow).find('[col_name="movement_data"]')[0].innerHTML);
                    for(i = 0;i<ldata.length;i++){
                        $.each(ldata[i].product, function(lp_idx, lp){
                            if(lp.product_id === lproduct_id && lp.unit_id === lunit_id){
                                lresult+= parseFloat(lp.qty);
                            }
                        });
                    }
                });
                
                return lresult;
            },
            modal_product_to_json:function(){
                
                var lresult = [];
                var lparent_pane = sales_pos_parent_pane;
                var ltbody = $(lparent_pane).find('#sales_pos_movement_modal_qty_table tbody')[0];
                var warehouses = APP_DATA_TRANSFER.ajaxPOST(sales_pos_data_support_url+'warehouse_list_get/',{}).response;
                
                var lmovement = [];
                $.each(warehouses, function(idx, warehouse){
                    var lproduct = [];
                    $.each($(ltbody).find('tr'),function(idx, row){
                        var lproduct_id = $(row).find('[col_name="product_id"]').text();
                        var lunit_id = $(row).find('[col_name="unit_id"]').text();
                        var lqty =  parseFloat($(row).find('input[warehouse_id="'+warehouse.id+'"]').val().replace(/[,]/g,''));
                        lqty = isNaN(lqty)?0:lqty;
                        if(lqty>0){
                            lproduct.push({
                                product_id:lproduct_id,
                                unit_id:lunit_id,
                                qty:lqty.toString()
                            });
                        }
                    });
                    lmovement.push({
                        product: lproduct,
                        warehouse_id:warehouse.id
                    });
                });
                
                lresult = lmovement;
                
                return lresult;                
            },
            draw_table:function(laction,lshow_reserved_qty){
                var lparent_pane = sales_pos_parent_pane;
                if(typeof lshow_reserved_qty === 'undefined'){
                    lshow_reserved_qty = false;
                }
                var lform = '';
                var lproduct_thead = $(lparent_pane).find('#sales_pos_movement_modal_product_table thead tr')[0];
                var ltbody_product = $(lparent_pane).find('#sales_pos_movement_modal_product_table tbody')[0];
                var ltbody_qty = $(lparent_pane).find('#sales_pos_movement_modal_qty_table tbody')[0];
                $(ltbody_product).empty();
                $(ltbody_qty).empty();
                var lexisting_product = sales_pos_movement_section_methods.existing_product_to_json();
                var warehouses = APP_DATA_TRANSFER.ajaxPOST(sales_pos_data_support_url+'warehouse_list_get/',{}).response;
                var lmethod = $(lparent_pane).find('#sales_pos_method').val();
                var lthead = $(ltbody_qty).closest('table').find('thead')[0];
                
                //DRAWING HEADER
                $(lproduct_thead).find('[col_name="reserved_qty"]').remove();
                if(lshow_reserved_qty){
                    var lth = document.createElement('th');
                    lth.innerHTML = 'Reserved<br/>Qty';
                    $(lth).attr('col_name','reserved_qty');
                    $(lth).css('text-align','right');
                    $(lproduct_thead).append(lth);
                }
                
                $(lthead).empty();
                var ltr = document.createElement('tr');
                fast_draw.col_add(ltr,{tag:'th',col_name:''
                    ,style:'vertical-align:middle;width:0px',val:'',type:'text'});
                
                // warehouse                
                $.each(warehouses, function(idx, warehouse){
                    fast_draw.col_add(ltr,{tag:'th',col_name:''
                        ,col_style:'vertical-align:middle;width:35px;margin-left:8px',val:'',type:'text',
                        attr:{warehouse_id:warehouse.id.toString()}
                    });
                    fast_draw.col_add(ltr,{tag:'th',col_name:''
                        ,col_style:'vertical-align:middle;text-align:center;width:150px'
                        ,val:'<br/>'+warehouse.name,type:'text',
                        attr:{colspan:'2',warehouse_id:warehouse.id.toString(),warehouse_header:''}});
                });
                
                lthead.appendChild(ltr);
                var ljson_data = [];
                $.each(lexisting_product.pos, function(idx, product){
                    ljson_data.push({product_id:product.product_id,unit_id:product.unit_id});
                });
                
                var lproduct_stock = {};
                if(lmethod === 'add'){
                    lproduct_stock = APP_DATA_TRANSFER.common_ajax_listener('product_stock/stock_sales_available_get',
                        ljson_data).response;
                }
                
                //DRAWING CONTENT
                
                $.each(lexisting_product.pos, function(idx, product){
                    var lordered_qty = parseFloat(product.qty);
                    var lmax_qty = parseFloat('0');
                    var lselected_qty = sales_pos_movement_section_methods.modal_product.selected_qty_get(product.product_id, product.unit_id);
                    lmax_qty = lordered_qty - parseFloat(lselected_qty);
                    
                    //draw PRODUCT
                    fast_draw = APP_COMPONENT.table_fast_draw;
                    var lrow = document.createElement('tr');  
                    var row_num = $(sales_pos_parent_pane).find('#sales_pos_movement_modal_product_table').find('tbody').children().length;
                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:product.product_id,type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:product.product_img,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:'<div style="max-height:100px">'+product.product_text+'<div>',type:'text'});
                    var ordered_qty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'ordered_qty',col_style:'vertical-align:middle;text-align:right',val:'<span>'+APP_CONVERTER.thousand_separator(product.qty)+'</span>',type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:product.unit_id,type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:product.unit_text,type:'text'});
                    
                    if(lshow_reserved_qty){
                        var lreserved_qty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'reserved_qty',style:'vertical-align:middle'
                            ,val:APP_CONVERTER.thousand_separator(lmax_qty)
                            ,type:'span'
                            ,col_style:'text-align:right;min-width:100px'});
                        APP_COMPONENT.text.color_non_zero($(lreserved_qty_td).find('span')[0],'red');
                        $(lreserved_qty_td).find('span').change();
                        
                    }
                    ltbody_product.appendChild(lrow);
                    
                    
                    // draw QTY
                    var lrow = document.createElement('tr');

                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:'<img class="product-img" style="width:0px">',type:'text',attr:{style:'width:0px'}});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:product.product_id,type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:product.unit_id,type:'text',visible:false});
                    
                    //draw reserved product
                    
                    
                    $.each(warehouses, function(idx, warehouse){
                        var lmax_input = lmax_qty;
                        var lstock = '0.00';
                        $.each(lproduct_stock, function(idx, row){
                            if(
                                row.product_id.toString() === product.product_id.toString() && 
                                row.unit_id.toString() === product.unit_id.toString() && 
                                row.warehouse_id.toString() === warehouse.id.toString() 
                            ) lstock = row.qty;
                        });

                        if(parseFloat(lmax_input)>parseFloat(lstock.replace(/[^0-9.]/g,''))){
                            lmax_input = lstock.replace(/[^0-9.]/g,'');
                        }
                        if(lmethod ==='add'){
                            var lcheckbox_td = fast_draw.col_add(lrow,{tag:'td',col_name:'checkbox',style:'vertical-align:middle'
                                ,val:'<input type="checkbox" >',type:'text',attr:{warehouse_id:warehouse.id.toString()}});
                                lstock_text = '';
                                if(lmethod ==='add') lstock_text='<span>'+lstock+'<br/>(stock)</span>';


                            var lstock_qty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'stock_qty'
                                ,style:'vertical-align:middle;text-align:right'
                                ,val:lstock_text,type:'text',attr:{warehouse_id:warehouse.id.toString()}});
                        }
                        var lqty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:middle'
                            ,val:'<div><input warehouse_id = "'+warehouse.id+'" class="form-control" style="text-align:right;font-size:12px;font-weight:bold;min-width:100px" disabled></div>'
                            ,type:'text',attr:{warehouse_id:warehouse.id.toString()}});
                        var lqty_input = $(lqty_td).find('input')[0];
                        if(lmethod ==='view'){
                            
                            $(lqty_td).attr('colspan',3);
                        }
                        
                        if(lmethod ==='add'){
                            if(laction === 'add'){
                                APP_COMPONENT.input.numeric(lqty_input,{max_val:lmax_input,min_val:'0'});
                            }
                            else if (laction === 'view'){
                                APP_COMPONENT.input.numeric(lqty_input,{min_val:0});
                            }
                        }
                        else{
                            APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set()
                                .min_val_set(0).render();                        
                        }
                        
                        APP_COMPONENT.input.color_non_zero(lqty_input,'blue');
                        $(lqty_input).blur();    
                        
                        $(lqty_input).on('blur',function(){
                            sales_pos_movement_section_methods.modal_product.reserved_qty_calculate($(this).closest('tr')[0]);
                        });
                        
                    });
                    
                    ltbody_qty.appendChild(lrow);
                    
                });                
                
                $('#sales_pos_movement_modal_qty_table tbody [col_name="checkbox"] input[type="checkbox"]').iCheck({checkboxClass: 'icheckbox_minimal'});
                $('#sales_pos_movement_modal_qty_table tbody [col_name="checkbox"] input[type="checkbox"]').on('ifToggled',function(){
                    var lidx = $(this).closest('td').index();   
                    var inc = 2;
                    var lqty_input = $($(this).closest('tr').find('td')[lidx+inc]).find('input')[0];
                    if($(this).is(':checked')){
                        $(lqty_input).prop('disabled',false);
                    }
                    else{
                        $(lqty_input).prop('disabled',true);
                        $(lqty_input).val('').blur();
                    }
                    
                    setTimeout(function(){$(lqty_input).focus();},200);
                });
                
                
                
            },
            assign_product_to_table:function(final_movement){
                var lparent_pane = sales_pos_parent_pane;
                var ltable = $(lparent_pane).find('#sales_pos_movement_modal_qty_table')[0];
                var ltbody = $(lparent_pane).find('#sales_pos_movement_modal_qty_table tbody');
                $.each(final_movement, function(idx, movement){
                    $.each(movement.product, function(idx, product){
                        var lrow = $(ltbody).find('tr').filter(function(){
                            if($(this).find('[col_name="product_id"]').text() === product.product_id
                                && $(this).find('[col_name="unit_id"]').text() === product.unit_id
                            ){
                                return this;
                            }
                        });
                        
                        var lqty = $(lrow).find('input[warehouse_id="'+movement.warehouse_id+'"]')[0];
                        $(lqty).val(product.qty).blur();
                        
                    });
                    var lmovement_code = '';
                    if(typeof movement.code !== 'undefined'){
                        lmovement_code = '<a target="_blank" href="<?php echo get_instance()->config->base_url();?>'+
                        ($('#sales_pos_delivery_checkbox').is(':checked')?'delivery_order/view/':'intake/view/')+movement.id+'">'+movement.code+'</a>';
                    };
                    $('[warehouse_header][warehouse_id="'+movement.warehouse_id+'"]')[0].innerHTML = 
                        lmovement_code+$('[warehouse_header][warehouse_id="'+movement.warehouse_id+'"]')[0].innerHTML;
                });
            },
            add:function(module,row){
                var lparent_pane = sales_pos_parent_pane;
                var final_movement = JSON.parse($(row).find('[col_name="movement_data"]').text());
                sales_pos_movement_section_methods.modal_product.draw_table('add',true);
                sales_pos_movement_section_methods.modal_product.assign_product_to_table(final_movement);
                
                
                $(lparent_pane).find('#sales_pos_movement_modal_product_btn_ok').off();                
                $(lparent_pane).find('#sales_pos_movement_modal_product_btn_ok').on('click',function(e){
                    var lmovement_data = sales_pos_movement_section_methods.modal_product.modal_product_to_json();
                    var lmodule = sales_pos_movement_props.module;
                    var lvalid = sales_pos_movement_section_methods.modal_product.validate_movement(lmovement_data);
                    if(lvalid){
                        var lrow = null;
                        switch(lmodule){
                            case 'delivery':
                                lrow = $(lparent_pane).find('#sales_pos_movement_delivery_table tbody>tr').last()[0];
                                break;
                            case 'intake':
                                lrow = $(lparent_pane).find('#sales_pos_movement_intake_table tbody>tr').last()[0];
                                break;

                        }
                        $(lrow).find('[col_name="movement_data"]').text(JSON.stringify(lmovement_data,null,null));
                        $(lparent_pane).find('#sales_pos_movement_modal_product').modal('hide');
                    }
                    
                    sales_pos_movement_section_methods.btn_controller_set();
                });
            },
            view:function(module,row){
                var lparent_pane = sales_pos_parent_pane;
                var final_movement = JSON.parse($(row).find('[col_name="movement_data"]').text());
                sales_pos_movement_section_methods.modal_product.draw_table('view');
                sales_pos_movement_section_methods.modal_product.assign_product_to_table(final_movement);
                $('#sales_pos_movement_modal_qty_table input[type="checkbox"]').iCheck('destroy');
                $('#sales_pos_movement_modal_qty_table input[type="checkbox"]').remove();
                $(lparent_pane).find('#sales_pos_movement_modal_product_btn_ok').on('click',function(e){
                    $(lparent_pane).find('#sales_pos_movement_modal_product').modal('hide');
                });
                $(lparent_pane).find('#sales_pos_movement_modal_product_btn_ok').off();
                $(lparent_pane).find('#sales_pos_movement_modal_product_btn_ok').on('click',function(){
                    $(lparent_pane).find('#sales_pos_movement_modal_product').modal('hide');
                });
            },
            validate_movement:function(lnew_movement){
                var lresult = true;
                var lexisting_product = sales_pos_movement_section_methods.existing_product_to_json();
                var ljson_data = {pos:[],movement:[]};
                
                $.each(lexisting_product.pos, function(idx, product){
                    ljson_data.pos.push({
                        product_id:product.product_id,
                        unit_id:product.unit_id,
                        qty:product.qty
                    });
                });
                
                for(i = 0;i<lexisting_product.movement.length-1;i++){
                    var lmovement = [];
                    for(j = 0;j<lexisting_product.movement[i].length;j++){
                        for(k = 0;k<lexisting_product.movement[i][j].product.length;k++){
                            lmovement.push({
                                product_id:lexisting_product.movement[i][j].product[k].product_id,
                                unit_id:lexisting_product.movement[i][j].product[k].unit_id,
                                qty:lexisting_product.movement[i][j].product[k].qty
                            });
                        }
                    }
                    ljson_data.movement.push(lmovement);
                }
                
                $.each(lnew_movement, function(idx, movement){
                    ljson_data.movement.push(movement.product);
                });
                
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(sales_pos_data_support_url+'movement_product_diff_get/',
                    ljson_data).response;
                $.each(lresponse, function(idx, product){
                    if(parseFloat(product.qty_diff)<parseFloat('0')){
                        lresult = false;
                        
                        
                    }
                });
                
                if(!lresult){
                    APP_MESSAGE.set('error','Over Qty',5000);
                    $('#sales_pos_movement_modal_product').scrollTop(0);
                };
                
                return lresult;
            }
        }
        
    }
    
    
    
    
</script>