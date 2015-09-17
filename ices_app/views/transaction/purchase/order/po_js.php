<script>
    var po_product_table = jQuery.extend(true, {}, APP_COMPONENT.input_select_table);
    var po_init = function(){
        var tmplt_row = {
            product_id:{visible:false,val:"",type:'text',filter:'',text_align:'left'}
            ,product_name:{visible:true,val:"",type:'text',filter:'',text_align:'left'}
            ,qty:{visible:true,val:"0.00",type:'input',filter:'numeric',text_align:'left',col_name:'qty'}                                
            ,unit_id:{visible:true,val:"",type:'select',filter:'',list:[],text_align:'left'}                                
            ,price:{visible:true,val:"0.00",type:'input',filter:'numeric',text_align:'left',col_name:'price'}
            ,sub_total:{visible:true,val:"0.00",type:'text',filter:'numeric',text_align:'left',col_name:'sub_total'}
        
        };
        
        var unique_col = 'product_id';
        var table = 'po_product_table';
        
        po_product_table.init(tmplt_row,unique_col,table);
        
    };
    
    po_init();
    
    var po_calculate_total = function(){
        data = po_product_table.data.clean_data;
        total = 0;
        $.each(data,function(key, val){
            subtotal = parseFloat(val.price.val.replace(/[,]/g,'')) * parseFloat(val.qty.val.replace(/[,]/g,''));
            total+= subtotal;
        });
        $("#po_total")[0].children[0].innerHTML=APP_CONVERTER.thousand_separator(total);
    }
    
    $("#po_product_input_select").on('change',function(e){
        var json_data = {data:$(this).val()};
        var url = "<?php echo $ajax_url ?>po_product_unit";
        var response = APP_DATA_TRANSFER.ajaxPOST(url,json_data);
        if(response.length>0){
            units = [];
            for(var i = 0;i<response.length;i++){
                units.push({val:response[i].unit_id,label:response[i].unit_name});
            };
            data = [{
                product_id: {val:response[0].product_id},
                product_name: {val:response[0].product_name},
                unit_id:{list:units,val:units[0].val}
            }];           

            po_product_table.append(data);
            po_product_table.draw();
            window.scrollTo(0, 500);
            $(this).select2('data',{id:'',text:''});
            var rows = $("#po_product_table").find('tbody')[0].children;
            $.each(rows,function (key, val){
                qty_input = $(val).find('input[col_name="qty"]');
                price_input = $(val).find('input[col_name="price"]');
                $(qty_input).on('blur',function(){
                    row = $(this).parent().parent();            
                    idx = row.index();
                    qty = po_product_table.data.clean_data[idx].qty.val                    
                    price = po_product_table.data.clean_data[idx].price.val
                    
                    result = price * qty;
                    po_product_table.data.clean_data[idx].sub_total.val = result;                    
                    sub_total = $(row).find('td[col_name="sub_total"]')[0];
                    sub_total.innerHTML=APP_CONVERTER.thousand_separator(po_product_table.data.clean_data[idx].sub_total.val);
                    po_calculate_total();
                });
                
                $(price_input).on('blur',function(){
                    row = $(this).parent().parent();            
                    idx = row.index();
                    qty = po_product_table.data.clean_data[idx].qty.val                    
                    price = po_product_table.data.clean_data[idx].price.val
                    
                    result = price * qty;
                    po_product_table.data.clean_data[idx].sub_total.val = result;                    
                    sub_total = $(row).find('td[col_name="sub_total"]')[0];
                    sub_total.innerHTML=APP_CONVERTER.thousand_separator(po_product_table.data.clean_data[idx].sub_total.val);
                    po_calculate_total();
                });
                
                
                $(val).find('[class="fa fa-trash-o"]').on('click',function(){
                    po_calculate_total();
                });
            })
        }
    });
    
    <?php if(count($po_detail)>0){ ?>
        var tmplt_row = {
            product_id:{visible:false,val:"",type:'text',filter:'',text_align:'left'}
            ,product_name:{visible:true,val:"",type:'text',filter:'',text_align:'left'}
            ,qty:{visible:true,val:"0",type:'text',filter:'numeric',text_align:'left'}                                
            ,unit_id:{visible:true,val:"",type:'text',filter:'',list:[],text_align:'left'}                                
            ,price:{visible:true,val:"0",type:'text',filter:'numeric',text_align:'left'}
            ,sub_total:{visible:true,val:"0",type:'text',filter:'numeric',text_align:'left'}
        };
        
        var unique_col = 'product_id';
        var table = 'po_product_table';
        
        po_product_table.init(tmplt_row,unique_col,table,false);
    <?php
        foreach($po_detail as $detail){
    ?>
            var units = [{val:"<?php echo $detail->unit_id ?>",label:"<?php echo $detail->unit_name ?>"}];
            
            var data = [{
                product_id: {val:"<?php echo $detail->product_id ?>"},
                product_name: {val:"<?php echo $detail->product_name ?>"},
                unit_id:{list:units,val:units[0].label},
                qty:{val:"<?php echo $detail->qty ?>"},
                price:{val:"<?php echo $detail->price ?>"},
                sub_total:{val:"<?php echo $detail->sub_total ?>"}
            }];           
            po_product_table.append(data);
            po_product_table.draw();
            po_calculate_total();
            
    <?php } }?>
    
    $("#po_status").on('change',function(e){
        var po_status = $(this).val();
        if(po_status == 'X'){
            $("#div_po_cancellation_reason").removeClass("hidden");
        }
        else{
            $("#div_po_cancellation_reason").addClass("hidden");
        }
            
    });
    
    $("#po_submit").on('click',function(e){
        e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');
        var json_data = {
            po:{
                code: "",
                purchase_date: $("#po_purchase_date").val(),
                purchase_order_status: $("#po_status").select2('val'),
                notes: $("#po_notes").val(),
                supplier_id: $("#po_supplier_id").select2('val'),
                cancellation_reason:$("#po_cancellation_reason").val(),
                total:$("#po_total").find('strong')[0].innerHTML.replace(/[,]/g,'')
            },
            detail:{products:[]}
        };
        
        if($("#po_status").select2('val') === 'O'){
            for(var i = 0;i<po_product_table.data.clean_data.length;i++){
                var data_row = po_product_table.data.clean_data[i];
                var row = {
                    product_id:data_row.product_id.val,
                    qty:data_row.qty.val,
                    unit_id:data_row.unit_id.val,
                    price:data_row.price.val,
                    sub_total:data_row.sub_total.val
                }
                json_data.detail.products.push(row);
            }                        
        }    
        
        var index_url = "<?php echo $index_url ?>";
        var ajax_url = APP_WINDOW.current_url();

        response = APP_DATA_TRANSFER.submit(ajax_url,json_data);
        if(response.success == 1){
            window.location = index_url+"view/"+response.trans_id;
        }    
        window.scrollTo(0,0);
        
        setTimeout(function(){btn.removeClass('disabled')},1000);
    });
    
</script>