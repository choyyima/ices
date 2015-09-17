<script>
    var sales_pos_intake_section_methods={
        hide_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            $(lparent_pane).find('[routing_section="intake"]').hide();
            
        },
        disable_all:function(){
            var lparent_pane = sales_pos_parent_pane;
            
        },
        reset_all:function(){
            var lparent_pane = sales_pos_parent_pane;

        },
        btn_controller_set:function(){
            var lvalid = true;
            var lparent_pane = sales_pos_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_pos_method').val();
            
            
            $(lparent_pane).find('#sales_pos_btn_prev').prop('disabled',true);
            $(lparent_pane).find('#sales_pos_btn_next').prop('disabled',true);
            $(lparent_pane).find('#sales_pos_btn_prev').off();
            $(lparent_pane).find('#sales_pos_btn_next').off();
                
        
            var lparent_pane = sales_pos_parent_pane;
            
            if(lvalid){
                $(lparent_pane).find('#sales_pos_btn_next').prop('disabled',false);
                $(lparent_pane).find('#sales_pos_btn_next').on('click',function(e){
                    e.preventDefault();
                    
                });
            }
            
            
            $(lparent_pane).find('#sales_pos_btn_prev').on('click',function(e){
                e.preventDefault();
                sales_pos_routing.set(lmethod,'payment');
            });
            
        },
        /*
        reset_all:function(){
            sales_pos_intake_section_methods.table.reset();
        },
        table:{            
            reset:function(){
                var ltbody = $(sales_pos_parent_pane).find('#sales_pos_intake_table').find('tbody')[0];
                $(ltbody).empty();
            }
        },
        modal:{
            draw:function(){
                var lparent_pane = sales_pos_parent_pane;
                var lproduct_rows = $(lparent_pane).find('#sales_pos_product_table>tbody').children();
                var ltbody = $(lparent_pane).find('#sales_pos_modal_intake_table>tbody')[0];
                $(ltbody).empty();
                var lrow_num = 1;
                for(var i = 0;i<lproduct_rows.length -1;i++){
                    var lproduct_row = lproduct_rows[i];
                    var lproduct_id = $(lproduct_row).find('[col_name="product"]').find('input').select2('val');
                    var lproduct_img = $(lproduct_row).find('[col_name="product_img"]')[0].innerHTML;
                    var lproduct_name = $(lproduct_row).find('[col_name="product"]').find('input').select2('data').text;
                    var lproduct_qty = $(lproduct_row).find('[col_name="qty"]').find('input').val();
                    var lunit_id = $(lproduct_row).find('[col_name="unit"]').find('input').select2('val');
                    var lunit_name = $(lproduct_row).find('[col_name="unit"]').find('input').select2('data').text;
                    
                    fast_draw = APP_COMPONENT.table_fast_draw;
                    var lrow = document.createElement('tr');                      
                    fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:lrow_num,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'',val:lproduct_id,type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:lproduct_img,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:lproduct_name,type:'text'});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'max_qty',style:'vertical-align:middle',val:lproduct_qty,type:'text'});
                    var lqty_td = fast_draw.col_add(lrow,{tag:'td',class:'form-control',col_name:'qty',style:'vertical-align:middle;text-align:right',val:'0.00',type:'input'});
                    var lqty_input = $(lqty_td).find('input')[0];
                    APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set().min_val_set(0).max_val_set(lproduct_qty.replace(/[,]/g,'')).render();
                    fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:lunit_id,type:'text',visible:false});
                    fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:lunit_name,type:'text'});
                    
                    ltbody.appendChild(lrow);
                    lrow_num += 1;
                    
                }                
            }
        }*/
    }
    
    
    
    
</script>