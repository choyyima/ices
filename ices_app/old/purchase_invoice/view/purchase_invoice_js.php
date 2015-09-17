<script>
    var purchase_invoice_product_table = jQuery.extend(true, {}, APP_COMPONENT.input_select_table);
    var purchase_invoice_init = function(){
        var tmplt_row = {
            product_id:{visible:false,val:"",type:'text',filter:'',text_align:'left'},
            product_img:{visible:true,val:"",type:'text',filter:'',text_align:'left'}
            ,product_name:{visible:true,val:"",type:'text',filter:'',text_align:'center'}
            ,qty:{visible:true,val:"0.00",type:'input',filter:'numeric',text_align:'right',col_name:'qty'}                                
            ,unit_id:{visible:true,val:"",type:'select',filter:'',list:[],text_align:'left'}                                
            ,amount:{visible:true,val:"0.00",type:'input',filter:'numeric',text_align:'right',col_name:'amount'}
            ,sub_total:{visible:true,val:"0.00",type:'text',filter:'numeric',text_align:'right',col_name:'sub_total'}
        
        };
        
        var unique_col = 'product_id';
        var table = 'purchase_invoice_product_table';
        
        purchase_invoice_product_table.init(tmplt_row,unique_col,table);
        
    };
    
    purchase_invoice_init();
    
    var purchase_invoice_calculate_total = function(){
        data = purchase_invoice_product_table.data.clean_data;
        total = 0;
        $.each(data,function(key, val){
            subtotal = parseFloat(val.amount.val.replace(/[,]/g,'')) * parseFloat(val.qty.val.replace(/[,]/g,''));
            total+= subtotal;
        });
        $("#purchase_invoice_total")[0].children[0].innerHTML=APP_CONVERTER.thousand_separator(total);
    }
    
    $("#purchase_invoice_product").on('change',function(e){
        var json_data = {data:$(this).val()};
        var url = "<?php echo $ajax_url ?>purchase_invoice_product_unit";
        var response = APP_DATA_TRANSFER.ajaxPOST(url,json_data);
        if(response.length>0){
            units = [];
            for(var i = 0;i<response.length;i++){
                units.push({val:response[i].unit_id,label:response[i].unit_name});
            };
            data = [{
                product_id: {val:response[0].product_id},
                product_name: {val:response[0].product_name},
                product_img: {val:response[0].product_img},
                unit_id:{list:units,val:units[0].val}
            }];           

            purchase_invoice_product_table.append(data);
            purchase_invoice_product_table.draw();
            window.scrollTo(0, 500);
            $(this).select2('data',{id:'',text:''});
            var rows = $("#purchase_invoice_product_table").find('tbody')[0].children;
            $.each(rows,function (key, val){
                qty_input = $(val).find('input[col_name="qty"]');
                amount_input = $(val).find('input[col_name="amount"]');
                $(qty_input).on('blur',function(){
                    row = $(this).parent().parent();            
                    idx = row.index();
                    qty = purchase_invoice_product_table.data.clean_data[idx].qty.val                    
                    amount = purchase_invoice_product_table.data.clean_data[idx].amount.val
                    
                    result = amount * qty;
                    purchase_invoice_product_table.data.clean_data[idx].sub_total.val = result;                    
                    sub_total = $(row).find('td[col_name="sub_total"]')[0];
                    sub_total.innerHTML=APP_CONVERTER.thousand_separator(purchase_invoice_product_table.data.clean_data[idx].sub_total.val);
                    purchase_invoice_calculate_total();
                });
                
                $(amount_input).on('blur',function(){
                    row = $(this).parent().parent();            
                    idx = row.index();
                    qty = purchase_invoice_product_table.data.clean_data[idx].qty.val                    
                    amount = purchase_invoice_product_table.data.clean_data[idx].amount.val
                    
                    result = amount * qty;
                    purchase_invoice_product_table.data.clean_data[idx].sub_total.val = result;                    
                    sub_total = $(row).find('td[col_name="sub_total"]')[0];
                    sub_total.innerHTML=APP_CONVERTER.thousand_separator(purchase_invoice_product_table.data.clean_data[idx].sub_total.val);
                    purchase_invoice_calculate_total();
                });
                
                
                $(val).find('[class="fa fa-trash-o"]').on('click',function(){
                    purchase_invoice_calculate_total();
                });
            })
        }
    });
    
    $("#purchase_invoice_purchase_invoice_status").on('change',function(e){
        var purchase_invoice_status = $(this).val();
        if(purchase_invoice_status === 'X'){
            $("#purchase_invoice_div_cancellation_reason").removeClass("hidden");
        }
        else{
            $("#purchase_invoice_div_cancellation_reason").addClass("hidden");
        }
            
    });
    
    var purchase_invoice_submit=function(){
        var json_data = {
            purchase_invoice:{
                store_id:$('#purchase_invoice_store').select2('val'),
                code: "",
                purchase_invoice_date: $("#purchase_invoice_date").val(),
                purchase_invoice_status: $("#purchase_invoice_purchase_invoice_status").select2('val'),
                notes: $("#purchase_invoice_notes").val(),
                supplier_id: $("#purchase_invoice_supplier").select2('val'),
                cancellation_reason:$("#purchase_invoice_cancellation_reason").val(),
                total_product:$("#purchase_invoice_total").find('strong')[0].innerHTML.replace(/[,]/g,''),
                total_expense:$("#purchase_invoice_expense_total").find('strong')[0].innerHTML.replace(/[,]/g,'')
            },
            expense:[],
            product:[]
        };
        var lajax_method ='';
        if($("#purchase_invoice_method").val() === 'Add'){
            lajax_method='add';
            for(var i = 0;i<purchase_invoice_product_table.data.clean_data.length;i++){
                var data_row = purchase_invoice_product_table.data.clean_data[i];
                var row = {
                    product_id:data_row.product_id.val,
                    qty:data_row.qty.val,
                    unit_id:data_row.unit_id.val,
                    amount:data_row.amount.val,
                    sub_total:data_row.sub_total.val
                }
                json_data.product.push(row);
            }
            
            for(var i = 0;i<$(purchase_invoice_expense_tbl.tbl).find('tbody').children().length;i++){
                var lrow = $(purchase_invoice_expense_tbl.tbl).find('tbody').children()[i];
                var ldesc = $(lrow).find('[col_name="description"]').val();
                var lamount = $(lrow).find('[col_name="amount"]').val().replace(/[,]/g,'');
                if(ldesc.replace(/[ ]/g,'') !== '' || parseFloat(lamount)!==0)
                json_data.expense.push({description:ldesc,amount:lamount});
            }
        }
        else{
            lajax_method='edit/'+$('#purchase_invoice_id').val();
        }

        var index_url = "<?php echo $index_url ?>";
        var ajax_url = "<?php echo get_instance()->config->base_url().'purchase_invoice/' ?>"+lajax_method;
        
        response = APP_DATA_TRANSFER.submit(ajax_url,json_data);
        if(response.success == 1){
            window.location = index_url+"view/"+response.trans_id;
        }
    }
    
    $("#purchase_invoice_submit").on('click',function(e){
        e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');

        $('#modal_confirmation_submit').modal('show');
        $('#modal_confirmation_submit_btn_submit').on('click',function(){
            purchase_invoice_submit();
            $('#modal_confirmation_submit').modal('hide');

        });

        window.scrollTo(0,0);
        setTimeout(function(){btn.removeClass('disabled')},1000);
        
    });
    
    $("#purchase_invoice_button_supplier_edit").hide();

    $("#purchase_invoice_supplier").on("change",function(e){
        if($("#purchase_invoice_supplier").select2("val") === ""){
            $("#purchase_invoice_button_supplier_edit").hide();
        }
        else{
            $("#purchase_invoice_button_supplier_edit").show();
        }   
    });    

    $("#purchase_invoice_button_supplier_new").on("click",function(){
        $("#modal_supplier").find("#supplier_method").val("Add");
        supplier_init();
        supplier_components_prepare();
        supplier_bind_event();
        supplier_after_submit = function(){
            var lsupplier_id = $("#modal_supplier").find("#supplier_id").val();
            var lsupplier_ajax_url = '<?php echo $ajax_url ?>detail_supplier_get';
            var json_data = {data:lsupplier_id};
            lsupplier = APP_DATA_TRANSFER.ajaxPOST(lsupplier_ajax_url, json_data);
            lsupplier = lsupplier;
            $("#purchase_invoice_supplier").select2("data",{id:lsupplier.id,text:lsupplier.name}).change();
            $('#modal_supplier').modal('hide');
            
        }
        
    });
    
    $("#purchase_invoice_button_supplier_edit").on("click",function(e){
        var lsupplier = $("#purchase_invoice_supplier").select2("data");
        if(lsupplier !== null){
            var supplier_id = lsupplier.id;
            var supplier_name = lsupplier.text
            $("#modal_supplier").find("#supplier_method").val("Edit");
            $("#modal_supplier").find("#supplier_id").val(supplier_id);
            supplier_init();
            supplier_components_prepare();
            supplier_bind_event();                                
            supplier_after_submit = function(){
                var lsupplier_id = $("#modal_supplier").find("#supplier_id").val();
                var lsupplier_ajax_url = '<?php echo $ajax_url ?>detail_supplier_get';
                var json_data = {data:lsupplier_id};
                lsupplier = APP_DATA_TRANSFER.ajaxPOST(lsupplier_ajax_url,json_data);
                lsupplier = lsupplier;
                $("#purchase_invoice_supplier").select2("data",{id:lsupplier.id,text:lsupplier.name}).change();
                $('#modal_supplier').modal('hide');
                
            }
            
                
            
        }
    });
    
    
    
</script>