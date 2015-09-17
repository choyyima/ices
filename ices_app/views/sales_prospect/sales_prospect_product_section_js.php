<script>
    var sales_prospect_product_timeout = 200;
    sales_prospect_product_section_methods={
        hide_all:function(){
            var lparent_pane = sales_prospect_parent_pane;
            $(lparent_pane).find('[routing_section="product"] .hide_all').hide();
        },
        show_hide:function(){
            sales_prospect_product_section_methods.hide_all();
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#sales_prospect_delivery_cost_estimation').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#sales_prospect_delivery_cost_estimation_text').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lproduct_section = $(sales_prospect_parent_pane).find('[routing_section="product"]')[0];
            APP_COMPONENT.disable_all(lproduct_section);
        },
        enable_disable:function(){
            sales_prospect_product_section_methods.disable_all();
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#sales_prospect_expedition').select2('enable');
                    $(lparent_pane).find('#sales_prospect_delivery_checkbox').iCheck('enable');
                    //$(lparent_pane).find('#sales_prospect_delivery_cost_estimation').prop('disabled',false);
                    break;
                case 'view':
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = sales_prospect_parent_pane;
            $(lparent_pane).find('#sales_prospect_product_total').text('0.00');
            $(lparent_pane).find('#sales_prospect_product_discount').text('0.00');
            $(lparent_pane).find('#sales_prospect_product_discount_percent').val('0.00');
            $(lparent_pane).find('#sales_prospect_product_extra_charge').text('0.00');
            $(lparent_pane).find('#sales_prospect_product_grand_total').text('0.00');
            $(lparent_pane).find('#sales_prospect_expedition_weight_total').text('0.00 KG');
            $(lparent_pane).find('#sales_prospect_delivery_cost_estimation input').text('0.00');
            sales_prospect_product_section_methods.table.reset();
            sales_prospect_product_section_methods.table.calculate_all();
        },
        reset_dependent_section:function(){
        },
        btn_controller_set:function(){
            var lvalid = true;
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            sales_prospect_methods.btn_controller_reset();
            
            $(lparent_pane).find('#sales_prospect_btn_prev').show();
            $(lparent_pane).find('#sales_prospect_submit').show();
            
            switch(lmethod){
                case 'add':
                    var ltr = $(lparent_pane).find('#sales_prospect_product_table>tbody>tr').last()[0];
                    var lproduct_id = $(ltr).find('[col_name="product_id"] span').text();
                    var lqty = '0';
                    
                    if($(ltr).find('[col_name="qty"] input').length>0){
                        lqty = $(ltr).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'')
                    }
                    else{
                        lqty = $(ltr).find('[col_name="qty"] span').text().replace(/[^0-9.]/g,'')
                    }
                    
                    if($(lparent_pane).find('#sales_prospect_product_table>tbody').children().length === 1){
                        if(lproduct_id ===''){
                            lvalid = false;
                        }
                        else if (lproduct_id !=='' && parseFloat(lqty)<=parseFloat('0')){
                            lvalid = false;
                        }

                    }
                    else{
                        if (lproduct_id !=='' && parseFloat(lqty)<=parseFloat('0')){
                            lvalid = false;
                        }
                    }            

                    if(!sales_prospect_product_section_methods.table.check_multiple_qty()){
                        lvalid = false;
                    }
                    
                    $.each($(lparent_pane).find('[additional_cost_row]'),function(idx, lrow){
                        var lamount = parseFloat($(lrow).find('[col_name="additional_cost_amount"] input').val().replace(/[^0-9.]/g,''));
                        if(lamount>0 && $(lrow).find('[col_name="additional_cost_description"] input').val().replace(/[ ]/g,'').length === 0){
                            lvalid = false;
                        }
                    });
                    break;
                case 'view':
                    $(lparent_pane).find('#sales_prospect_submit').hide();
                    break;
            }
            
            if(lmethod !== 'add'){lvalid = false;}
            
            if(lvalid){                
                if(APP_SECURITY.permission_get('sales_prospect','sales_prospect_add').result){
                    $(lparent_pane).find('#sales_prospect_submit').prop('disabled',false);
                    
                }
            }
            
            $(lparent_pane).find('#sales_prospect_btn_prev').prop('disabled',false);
            $(lparent_pane).find('#sales_prospect_btn_prev').on('click',function(e){
                e.preventDefault();
                sales_prospect_routing.set(lmethod,'init');
            });
            
        },
        
        table:{
            calculate_all:function(){
              sales_prospect_product_section_methods.table.total_calculate();  
              sales_prospect_product_section_methods.table.discount_calculate();
              sales_prospect_product_section_methods.table.extra_charge_calculate();              
              sales_prospect_product_section_methods.table.grand_total_calculate();
            },
            grand_total_calculate:function(){
                var lparent_pane = sales_prospect_parent_pane;
                var ltotal = parseFloat($(lparent_pane).find('#sales_prospect_product_total').text().replace(/[,]/g,''));
                var ldiscount = parseFloat($(lparent_pane).find('#sales_prospect_product_discount').val().replace(/[,]/g,''));
                var lextra_charge = parseFloat($(lparent_pane).find('#sales_prospect_product_extra_charge').text().replace(/[,]/g,''));
                var ldelivery_cost_estimation = parseFloat($(lparent_pane).find('#sales_prospect_delivery_cost_estimation').val().replace(/[,]/g,''));
                
                var ladditional_cost = parseFloat('0');
                $.each($(lparent_pane).find('[col_name="additional_cost_amount"]'),function(idx,additional_cost_amount){
                    ladditional_cost +=parseFloat($(additional_cost_amount).find('input').val().replace(/[^0-9.]/g,''));
                });
                
                var lgrand_total = ltotal - ldiscount + lextra_charge + ldelivery_cost_estimation +ladditional_cost;
                $(lparent_pane).find('#sales_prospect_product_grand_total').text(APP_CONVERTER.thousand_separator(lgrand_total.toString()));
            },
            discount_calculate:function(){
                var ltotal = parseFloat($(sales_prospect_parent_pane).find('#sales_prospect_product_total').text().replace(/[,]/g,''));
                var ldiscount_percent = parseFloat($(sales_prospect_parent_pane).find('#sales_prospect_product_discount_percent').val().toString().replace(/[,]/g,''));
                var ldiscount = parseFloat($(sales_prospect_parent_pane).find('#sales_prospect_product_discount').val().toString().replace(/[,]/g,''));
                if(ldiscount_percent>0){
                     ldiscount = (ltotal * ldiscount_percent /100);
                     
                }
                $(sales_prospect_parent_pane).find('#sales_prospect_product_discount').val(APP_CONVERTER.thousand_separator(ldiscount.toString()));
                $(sales_prospect_parent_pane).find('#sales_prospect_summary_product_discount').text(APP_CONVERTER.thousand_separator(ldiscount.toString()));
            },
            total_calculate:function(){
                var ltbody = $(sales_prospect_parent_pane).find('#sales_prospect_product_table').find('tbody')[0];
                var lsub_totals = $(ltbody).find('[col_name="sub_total"]>span');
                var ltotal = 0;
                $.each(lsub_totals,function(key, val){
                    ltotal += parseFloat($(val).text().replace(/[,]/g,''));
                });
                $(sales_prospect_parent_pane).find('#sales_prospect_product_total').text(APP_CONVERTER.thousand_separator(ltotal));
            },
            total_expedition_weight_calculate:function(){
                var ltbody = $(sales_prospect_parent_pane).find('#sales_prospect_product_table').find('tbody')[0];
                var lsub_totals = $(ltbody).find('[col_name="expedition_weight"]>span');
                var ltotal = 0;
                var lunit = '';
                $.each(lsub_totals,function(key, val){
                    var lweight = $(val).text().replace(/[^0-9.]/g,'');
                    lunit = $(val).text().substr($(val).text().indexOf(' '),$(val).text().length - $(val).text().indexOf(' '));
                    ltotal += parseFloat(lweight);
                });
                $(sales_prospect_parent_pane).find('#sales_prospect_expedition_weight_total').text(APP_CONVERTER.thousand_separator(ltotal)+lunit);
            },
            sub_total_calculate:function(lrow){
                var lqty = '0';
                if($(lrow).find('[col_name="qty"] input').length > 0){
                    lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                }
                else{
                    lqty = $(lrow).find('[col_name="qty"] span').text().replace(/[^0-9.]/g,'');
                }
                
                var lamount = '0';
                if($(lrow).find('[col_name="amount"] input').length > 0){
                    lamount = $(lrow).find('[col_name="amount"] input').val().replace(/[^0-9.]/g,'');
                }
                else{
                    lamount = $(lrow).find('[col_name="amount"] span').text().replace(/[^0-9.]/g,'');
                }

                var lsub_total = parseFloat(lqty) * parseFloat(lamount);
                $(lrow).find('[col_name="sub_total"] span').text(APP_CONVERTER.thousand_separator(lsub_total.toString()));
            },
            extra_charge_calculate:function(){
                var lparent_pane = sales_prospect_parent_pane;
                var lajax_url = sales_prospect_data_support_url+'extra_charge_get';
                var ljson_data = {};
                ljson_data.price_list_id = $(lparent_pane).find('#sales_prospect_price_list').select2('val');
                ljson_data.products = [];
                ljson_data.delivery = $(lparent_pane).find('#sales_prospect_delivery_checkbox').is(':checked');
                $.each($(lparent_pane).find('#sales_prospect_product_table>tbody>tr'),function(){
                    var lproduct_id = $(this).find('[col_name="product_id"] span').text();
                    if(lproduct_id !==''){
                        var lunit_id = $(this).find('[col_name="unit_id"] span').text();
                        var lqty = '0';
                        var lamount = '0';
                        
                        if($(this).find('[col_name="qty"] input').length>0){
                            lqty = $(this).find('[col_name="qty"] input').val().replace(/[,]/g,'');
                        }
                        else{
                            lqty = $(this).find('[col_name="qty"] span').text().replace(/[,]/g,'');
                        }
                        
                        if($(this).find('[col_name="amount"] input').length>0){
                            lamount = $(this).find('[col_name="amount"] input').val().replace(/[,]/g,'');
                        }
                        else{
                            lamount = $(this).find('[col_name="amount"] span').text().replace(/[,]/g,'');
                        }
                        
                        ljson_data.products.push({product_id:lproduct_id, unit_id:lunit_id, qty:lqty, amount:lamount});
                    }
                });
                var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
                var lresponse = lresult.response;
                $(lparent_pane).find('#sales_prospect_modal_extra_charge .modal-body')[0].innerHTML = lresponse.msg;
                $(lparent_pane).find('#sales_prospect_product_extra_charge').text(lresponse.amount);
            },
            expedition_weight_calculate:function(lrow){
                var lparent_pane = sales_prospect_parent_pane;
                var lajax_url = sales_prospect_data_support_url+'expedition_weight_get';
                var ljson_data = {};
                
                ljson_data.expedition_id = $(lparent_pane).find('#sales_prospect_expedition').select2('val');
                ljson_data.product_id = $(lrow).find('[col_name="product_id"] span')[0].innerHTML;
                ljson_data.unit_id = $(lrow).find('[col_name="unit_id"] span')[0].innerHTML;
                ljson_data.qty = '0';
                if($(lrow).find('[col_name="qty"] input').length>0){
                    
                    ljson_data.qty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                }
                else{
                    ljson_data.qty = $(lrow).find('[col_name="qty"] span').text().replace(/[^0-9.]/g,'');
                }
                
                var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
                var lresponse = lresult.response;
                
                $(lrow).find('[col_name="expedition_weight"] span').text(lresponse.weight+' '+lresponse.unit_name);
            },
            check_multiple_qty:function(){
                var lresult = true;
                var lparent_pane = sales_prospect_parent_pane;
                var lrow = $(lparent_pane).find('#sales_prospect_product_table>tbody>tr').last()[0];
                var lqty = '0';
                if($(lrow).find('[col_name="qty"] input').length>0){
                    var lqty_input = $(lrow).find('[col_name="qty"] input')[0];
                    lqty = $(lrow).find('[col_name="qty"] input').val().replace(/[^0-9.]/g,'');
                    lqty  = parseFloat(lqty);
                    var lmult_qty = parseFloat($(lrow).find('[col_name="mult_qty"] span').text().replace(/[^0-9.]/g,''));
                    $(lqty_input).popover_danger('destroy','','');
                    if(lqty !== 0){
                        if( lqty % lmult_qty !== 0){
                            $(lqty_input).popover_danger('init','','Mismatch Multiplication Qty');
                            lresult = false;
                        }
                    }
                }
                return lresult;
            },          
            additional_cost_generate:function(){
                fast_draw = APP_COMPONENT.table_fast_draw;
                var lrow = document.createElement('tr'); 
                $(lrow).attr('additional_cost_row','');
                fast_draw.col_add(lrow,{tag:'td',attr:{colspan:'7'},class:'',col_name:'',style:'',val:'',type:'text'});                
                var ldescription_td = fast_draw.col_add(lrow,{tag:'td',attr:{colspan:'2'},class:'form-control',col_name:'additional_cost_description',style:'vertical-align:middle;',val:'',type:'input'});                
                $(ldescription_td).find('input').attr('placeholder','Additional Cost Description');
                $(ldescription_td).find('input').on('blur',function(){
                   sales_prospect_product_section_methods.btn_controller_set(); 
                });
                var lamount_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'additional_cost_amount',style:'vertical-align:middle;text-align:right',val:'0.00',type:'input'});
                var lamount_input = $(lamount_td).find('input')[0];
                APP_EVENT.init().component_set(lamount_input).type_set('input').numeric_set().min_val_set(0).render();
                $(lamount_input).on('blur',function(){
                    sales_prospect_product_section_methods.table.grand_total_calculate();
                    sales_prospect_summary_methods.reset_all();
                    sales_prospect_product_section_methods.btn_controller_set(); 
                });
                
                var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'additional_cost_action',style:'vertical-align:middle',val:'',type:'text'});
                var lnew_row = APP_COMPONENT.new_row();    
                laction.appendChild(lnew_row);
        
                $(lnew_row).on('click',function(){
                    var lrow = $(this).closest('tr');
                    var ldescription = $(lrow).find('[col_name="additional_cost_description"] input').val();
                    var lamount = $(lrow).find('[col_name="additional_cost_amount"] input').val().replace(/[,]/g,'');
                    if(parseFloat(lamount)>0 && ldescription !=='' ){
                        $(lrow).find('[col_name="additional_cost_description"]').find('input').prop('disabled',true);
                        $(lrow).find('[col_name="additional_cost_amount"]').find('input').prop('disabled',true);
                        var ltrash = APP_COMPONENT.trash();
                        $(lrow).find('[col_name="additional_cost_action"]').empty();
                        $(lrow).find('[col_name="additional_cost_action"]')[0].appendChild(ltrash);
                        $(ltrash).on('click',function(){
                            sales_prospect_product_section_methods.table.grand_total_calculate();
                            sales_prospect_summary_methods.reset_all();
                            sales_prospect_product_section_methods.btn_controller_set();
                        })
                        var lgrand_total = $(sales_prospect_parent_pane).find('#sales_prospect_product_grand_total').closest('tr')[0];
                        var ladditional_cost_row = sales_prospect_product_section_methods.table.additional_cost_generate();
                        $(lgrand_total).before(ladditional_cost_row);
                        APP_COMPONENT.focus($(ladditional_cost_row).find('[col_name="additional_cost_description"] input'));
                        
                        sales_prospect_product_section_methods.table.grand_total_calculate();
                        sales_prospect_summary_methods.reset_all();
                        sales_prospect_product_section_methods.btn_controller_set();
                    }
                    APP_WINDOW.scroll_bottom();
                });
                
                return lrow;
            },
            input_row_generate:function(){
                fast_draw = APP_COMPONENT.table_fast_draw;
                var lrow = document.createElement('tr');  
                var row_num = $(sales_prospect_parent_pane).find('#sales_prospect_product_table').find('tbody').children().length;
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});                            
                fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:'<img class="product-img" src="<?php echo get_instance()->config->base_url(); ?>/img/blank.gif?lastmod=12345678">',type:'text'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:'',type:'span',visible:false});
                var lproduct_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'product',style:'vertical-align:middle',val:'<div><input original class="pos-product-search"> </div>',type:'text'});
                var ltotal_stock_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'total_stock',col_style:'vertical-align:middle;text-align:right',val:'<span>0.00</span>',type:'text'});
                var lmult_qty_td = fast_draw.col_add(lrow,{tag:'td',class:'',col_name:'mult_qty',col_style:'vertical-align:middle;text-align:right',val:'<span>0.00</span>',type:'text'});
                var lqty_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'qty', col_style:'vertical-align:middle;text-align:right', style:'text-align:right;vertical-align:middle',val:'0.00',type:'input'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:'',type:'span',visible:false});
                var lunit_td = fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:'<div><input original class="pos-unit-search"> </div>',type:'text'});
                $(lunit_td).find('input').select2({data:[],placeholder:''});
                var lexpedition_weight_td = fast_draw.col_add(lrow,{tag:'td',col_name:'expedition_weight',col_style:'vertical-align:middle;text-align:right',val:'<span>0.00 KG</span>',type:'text'});
                var lamount_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'amount', col_style:'vertical-align:middle;text-align:right', style:'text-align:right;font-size:6px',val:'0.00',type:'input'});
                fast_draw.col_add(lrow,{tag:'td',col_name:'sub_total',col_style:'vertical-align:middle;text-align:right',val:'<span>0.00</span>',type:'text'});
                var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'vertical-align:middle',val:'',type:'text'});
                var lnew_row = APP_COMPONENT.new_row();    
                laction.appendChild(lnew_row);
                
                var lqty_input = $(lqty_td).find('input')[0];
                APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set().min_val_set(0).render();
                
                var lamount_input = $(lamount_td).find('input')[0];
                APP_EVENT.init().component_set(lamount_input).type_set('input').numeric_set().min_val_set(0).render();
                $(lamount_input).prop('disabled',true);
                
                $(lunit_td).find('input').on('change',function(){
                    var lunit_id = $(this).select2('val');
                    var lparent_pane = sales_prospect_parent_pane;
                    var lrow = $(this).closest('tr');
                    var lprice_list_id = $(lparent_pane).find('#sales_prospect_price_list').select2('val');
                    var lproduct_id = $(lrow).find('[col_name="product"]').find('input').select2('val');
                    var lqty_input = $(lrow).find('[col_name="qty"]>input')[0];
                    
                    $(lqty_input).off();
                    $(lrow).find('[col_name="unit_id"] span').text(lunit_id);
                    if(lunit_id !== '' && lproduct_id!== '' && lprice_list_id !== ''){                        
                        var json_data = {price_list_id:lprice_list_id,unit_id:lunit_id,product_id:lproduct_id};
                        var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'product_unit_dependency_get/',json_data);
                        var lresponse = lresult.response;
                        
                        $(lrow).find('[col_name="mult_qty"]>span').text(lresponse.mult_qty);
                        
                        APP_EVENT.init().component_set(lqty_input)
                                .type_set('input').numeric_set().min_val_set(0)
                                .render();
                        $(lrow).find('[col_name="total_stock"]>span').text(lresponse.stock_qty);
                        $(lqty_input).on('blur',function(){
                            var lrow = $(this).closest('tr')[0];
                            var lproduct_id = $(lrow).find('[col_name="product"]').find('input').select2('val');
                            var lunit_id = $(lrow).find('[col_name="unit"]').find('input').select2('val');
                            var lprice_list_id = $(sales_prospect).find('#sales_prospect_price_list').select2('val');
                            var lqty = $(this).val().replace(/[,]/g,'');
                            var json_data = {product_id:lproduct_id, price_list_id:lprice_list_id, qty:lqty, unit_id:lunit_id};
                            var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'product_price_get/',json_data);
                            var lresponse = lresult.response;
                            var lamount = lresponse.toString();
                            $(lrow).find('[col_name="amount"]').find('input').val(lamount.replace(/[,]/g,'')).blur();

                            sales_prospect_product_section_methods.table.sub_total_calculate(lrow);
                            sales_prospect_product_section_methods.table.expedition_weight_calculate(lrow);
                            sales_prospect_product_section_methods.table.total_expedition_weight_calculate();
                            sales_prospect_product_section_methods.btn_controller_set();
                            sales_prospect_product_section_methods.table.check_multiple_qty();
                            sales_prospect_product_section_methods.table.calculate_all();
                            sales_prospect_summary_methods.reset_all();

                        });
                    }
                });
                
                $(lnew_row).on('click',function(){
                    var lrow = $(this).closest('tr');
                    var lsub_total = $(lrow).find('[col_name="sub_total"]').text();
                    var lproduct_id = $(lrow).find('[col_name="product"]').find('input').select2('val');
                    var lunit_id = $(lrow).find('[col_name="unit"]').find('input').select2('val');
                    var lqty = $(lrow).find('[col_name="qty"]').find('input').val().replace(/[,]/g,'');
                    if(parseFloat(lqty)>0 && lproduct_id !=='' && lunit_id !== ''
                        && sales_prospect_product_section_methods.table.check_multiple_qty()
                    ){
                        var lproduct_data = $(lrow).find('[col_name="product"] input').select2('data');
                        var lunit_data = $(lrow).find('[col_name="unit"] input').select2('data'); 
                        var lqty = $(lrow).find('[col_name="qty"] input').val();
                        var lamount = $(lrow).find('[col_name="amount"] input').val();
                        $(lrow).find('[col_name="product"]')[0].innerHTML = '<div>'+lproduct_data.text+'</div>';
                        $(lrow).find('[col_name="unit"]')[0].innerHTML = '<span>'+lunit_data.text+'</span>';
                        $(lrow).find('[col_name="qty"]')[0].innerHTML = '<span>'+lqty+'</span>';
                        $(lrow).find('[col_name="amount"]')[0].innerHTML = '<span>'+lamount+'</span>';
                        var ltrash = APP_COMPONENT.trash();
                        $(lrow).find('[col_name="action"]').empty();
                        $(lrow).find('[col_name="action"]')[0].appendChild(ltrash);
                        $(ltrash).on('click',function(){
                            sales_prospect_product_section_methods.table.calculate_all();
                            sales_prospect_product_section_methods.table.total_expedition_weight_calculate();
                            sales_prospect_summary_methods.reset_all();
                            sales_prospect_product_section_methods.btn_controller_set();
                        })
                        var ltbody = $(sales_prospect_parent_pane).find('#sales_prospect_product_table').find('tbody')[0];
                        var linput_row = sales_prospect_product_section_methods.table.input_row_generate();
                        ltbody.appendChild(linput_row);
                        $(linput_row).find('[col_name="product"]').find('input').select2('open');
                        sales_prospect_product_section_methods.table.calculate_all();
                        sales_prospect_product_section_methods.table.total_expedition_weight_calculate();
                        sales_prospect_summary_methods.reset_all();
                        sales_prospect_product_section_methods.btn_controller_set();
                    }
                    APP_WINDOW.scroll_bottom();
                });
                
                $(lamount_input).on('blur',function(){
                    var lrow = $(this).closest('tr')[0];
                    sales_prospect_product_section_methods.table.sub_total_calculate(lrow);
                    sales_prospect_product_section_methods.table.expedition_weight_calculate(lrow);
                    sales_prospect_product_section_methods.table.calculate_all();
                    sales_prospect_summary_methods.reset_all();
                });
                
                $(lproduct_td).find('input').on('change',function(){
                    var lparent_pane = sales_prospect_parent_pane;
                    var lproduct_id = $(this).select2('val');
                    var lrow = $(this).closest('tr')[0];
                    
                    $(lrow).find('[col_name="product_id"] span').text(lproduct_id);
                    if(lproduct_id === ''){                        
                        $(this).closest('tr').remove();
                        var linput_row = sales_prospect_product_section_methods.table.input_row_generate();                        
                        $(lparent_pane).find('#sales_prospect_product_table>tbody')[0].appendChild(linput_row);
                    }
                    else{
                        var ldata = $(this).select2('data');
                        $(this).select2('data',{id:ldata.id,text:ldata.text});
                        
                        var json_data={product_id:lproduct_id};
                        var response = null;
                        var lresult = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'product_img_get',json_data);
                        response = lresult.response;
                        $(lrow).find('[col_name="product_img"]')[0].innerHTML=response.product_img;
                        var lprice_list_id = $(lparent_pane).find('#sales_prospect_price_list').select2('val');
                        json_data={product_id:lproduct_id,price_list_id:lprice_list_id};
                        lresult = APP_DATA_TRANSFER.ajaxPOST(sales_prospect_data_support_url+'product_unit_get',json_data);
                        response = lresult.response;
                        if(response !== null){
                            var lunits = [];
                            $.each(response.unit,function(key, val){
                                lunits.push({id:val.id,text:val.code});                                
                            });
                            $(lrow).find('[col_name="unit"]').find('input').select2({data:lunits});
                            if(lunits.length>0){
                                $(lrow).find('[col_name="unit"]').find('input').select2('data',lunits[0]).change();
                            }
                        }
                        $(lrow).find('[col_name="qty"]').find('input').val('0.00').blur();
                        //setTimeout(function(){$(lrow).find('[col_name="qty"]').find('input').focus();},500);
                    }
                    
                    sales_prospect_product_section_methods.btn_controller_set();
                    sales_prospect_product_section_methods.table.calculate_all();
                    sales_prospect_summary_methods.reset_all();
                    sales_prospect_product_section_methods.reset_dependent_section();
                });               
                
                
                $(lproduct_td).find('input').select2({                    
                    minimumInputLength:1
                    ,placeholder: 'Search Product'
                    ,allowClear:true
                    ,query:function(query){
                        window.clearTimeout(sales_prospect_product_timeout);
                        sales_prospect_product_timeout = window.setTimeout(function(){    
                            var lparent_pane = sales_prospect_parent_pane;
                            var typed_word = query.term.toLowerCase();
                            if(typed_word.replace(' ','') == '') typed_word = '';
                            if(typed_word[0] == ' '){typed_word=typed_word.substr(1,typed_word.length-1);}
                            var data={results:[]};
                            var lrows = $(sales_prospect_parent_pane).find('#sales_prospect_product_table>tbody>tr');
                            var excluded_product = [];
                            $.each(lrows,function(key, val){
                                excluded_product.push($(val).find('[col_name="product_id"] span').text());
                            });
                            var lprice_list_id = $(lparent_pane).find('#sales_prospect_price_list').select2('val');
                            var json_data = {data:typed_word,excluded_product:excluded_product,price_list_id:lprice_list_id}; 
                            var url = "<?php echo get_instance()->config->base_url().'sales_prospect/ajax_search/input_select_product_search/' ?>";            
                            var result = APP_DATA_TRANSFER.ajaxPOST(url,json_data);
                            var raw_data_sales_prospect_product = null;
                            raw_data_sales_prospect_product = result.response;
                            for (var i = 0; i < raw_data_sales_prospect_product.length;i++ ){
                                var item={id:"",text:""};
                                item = raw_data_sales_prospect_product[i];
                                data.results.push(item);
                            }
                            query.callback(data);
                        },200);
                    }
                    });                
                
                return lrow;
            },
            reset:function(){
                var lparent_pane = sales_prospect_parent_pane;
                var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
                var ltbody = $(sales_prospect_parent_pane).find('#sales_prospect_product_table').find('tbody')[0];
                $(ltbody).empty();
                $(sales_prospect_parent_pane).find('[additional_cost_row]').remove();
                switch(lmethod){
                    case 'add':
                        var linput_row = sales_prospect_product_section_methods.table.input_row_generate();
                        ltbody.appendChild(linput_row);
                        
                        var ladditional_cost_row = sales_prospect_product_section_methods.table.additional_cost_generate();
                        $(sales_prospect_parent_pane).find('#sales_prospect_product_table tfoot tr:last-child').before(ladditional_cost_row);
                        break;
                    case 'view':
                        
                        break;
                }
                
                sales_prospect_product_section_methods.table.total_calculate();
                sales_prospect_product_section_methods.table.discount_calculate();
                sales_prospect_product_section_methods.table.grand_total_calculate();
                sales_prospect_summary_methods.reset_all();
                //sales_prospect_product_section_methods.btn_controller_set();
            }
        }
       
    }
    
    sales_prospect_product_section_bind_events = function(){
        var lparent_pane = sales_prospect_parent_pane;
        
        APP_EVENT.init().component_set($('#sales_prospect_product_discount')).type_set('input').numeric_set().min_val_set(0).render();
        APP_EVENT.init().component_set($('#sales_prospect_product_discount_percent')).type_set('input').numeric_set().min_val_set(0).max_val_set(100).render();
        APP_EVENT.init().component_set($('#sales_prospect_delivery_cost_estimation')).type_set('input').numeric_set().min_val_set(0).render();


        $('#sales_prospect_product_discount').blur();
        $('#sales_prospect_discount_percent').blur();
        $('#sales_prospect_delivery_cost_estimation').blur();
        $('#seles_pos_modal_intake_intake_date').datetimepicker(); 

        $(lparent_pane).find('#sales_prospect_approval').on('change',function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lapproval_id = $(this).select2('val');
            if(lapproval_id !== ''){
                $(lparent_pane).find('[col_name="amount"]').find('input').prop('disabled',false);
            }
            else{
                sales_prospect_product_section_methods.table.reset();
                $(lparent_pane).find('[col_name="amount"]').find('input').prop('disabled',true);
            }
        });

        $(sales_prospect_parent_pane).find('#sales_prospect_product_discount').on('blur',function(){
            sales_prospect_product_section_methods.reset_dependent_section();
            sales_prospect_product_section_methods.table.discount_calculate();
            sales_prospect_product_section_methods.table.grand_total_calculate();
            sales_prospect_summary_methods.reset_all();
        });

        $(sales_prospect_parent_pane).find('#sales_prospect_product_discount_percent').on('blur',function(){
            sales_prospect_product_section_methods.reset_dependent_section();
            sales_prospect_product_section_methods.table.discount_calculate();
            sales_prospect_product_section_methods.table.grand_total_calculate();
            sales_prospect_summary_methods.reset_all();
            
        });
        
        $(sales_prospect_parent_pane).find('#sales_prospect_btn_extra_charge').on('click',function(){
            $(sales_prospect_parent_pane).find('#sales_prospect_modal_extra_charge').modal('show');
        });
        
        $(sales_prospect_parent_pane).find('#sales_prospect_btn_expedition_weight').on('click',function(){
            $(sales_prospect_parent_pane).find('#sales_prospect_modal_expedition_weight').modal('show');
        });
        
        $(sales_prospect_parent_pane).find('#sales_prospect_delivery_checkbox').on('ifToggled',function(){
            var lparent_pane = sales_prospect_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_prospect_method').val();
            
            $(lparent_pane).find('#sales_prospect_delivery_cost_estimation').val('0').blur();
            if($('#sales_prospect_delivery_checkbox').is(':checked')){
                
                $(lparent_pane).find('#sales_prospect_delivery_cost_estimation').prop('disabled',false);
            }
            else{
                
                $(lparent_pane).find('#sales_prospect_delivery_cost_estimation').prop('disabled',true);
            }
            sales_prospect_product_section_methods.table.calculate_all();
            sales_prospect_summary_methods.reset_all();
            
            
        });
        
        $(sales_prospect_parent_pane).find('#sales_prospect_expedition').on('change',function(){
            var lexpedition_id = $(this).select2('val');
            var lparent_pane = sales_prospect_parent_pane;
            var lrows = $(lparent_pane).find('#sales_prospect_product_table tbody tr');
            $.each(lrows, function (idx, lrow){
                $(lrow).find('[col_name="expedition_weight"] span').text('0.00 KG');
                
            });
            
            if(lexpedition_id ===''){
                $(sales_prospect_parent_pane).find('#sales_prospect_delivery_checkbox').iCheck('uncheck');
                $(sales_prospect_parent_pane).find('#sales_prospect_delivery_checkbox').iCheck('enable');;
            }
            else{
                $(sales_prospect_parent_pane).find('#sales_prospect_delivery_checkbox').iCheck('check');
                $(sales_prospect_parent_pane).find('#sales_prospect_delivery_checkbox').iCheck('disable');
                                    
                var lrows = $(lparent_pane).find('#sales_prospect_product_table tbody tr');
                $.each(lrows, function (idx, lrow){
                    sales_prospect_product_section_methods.table.expedition_weight_calculate(lrow);
                });

            }
            
            sales_prospect_product_section_methods.table.total_expedition_weight_calculate();            
            sales_prospect_product_section_methods.table.calculate_all();
            sales_prospect_summary_methods.reset_all();
        });
        
        $(lparent_pane).find('#sales_prospect_delivery_cost_estimation').on('blur',function(){
            sales_prospect_product_section_methods.table.grand_total_calculate();
            sales_prospect_summary_methods.reset_all();
            sales_prospect_product_section_methods.btn_controller_set();  
        });
    }
</script>