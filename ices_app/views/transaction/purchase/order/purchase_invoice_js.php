<script>
    var po_id = $("#purchase_invoice_po_id").val();
    var purchase_invoice_id = $("")
    var ajax_search = '<?php echo $ajax_search; ?>';
    
    var purchase_invoice_data={
        id:'',
        code:'',
        po_code:'',
        purchase_invoice_date:new Date().toJSON().slice(0,10),
        purchase_invoice_status:{},
        supplier:{},
        cancellation_reason:'',
        cols:[],
        notes:'',
        total:'0'
    };
    
    var purchase_invoice_list={
        purchase_invoice_status : [],
        supplier:[]
    }
    
    var purchase_invoice_po_set = function(){
        json_data={data:po_id};
        var po = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'purchase_invoice_po',json_data);
        purchase_invoice_data.po_code = po[0].code;
        
        
    }
    
    var purchase_invoice_supplier_set = function(){
        var method = $("#purchase_invoice_method").val();
        
        var supplier = null;
        if(method == 'Add'){
            json_data={data:po_id};
            supplier = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'purchase_invoice_po_supplier',json_data);
        }
        else{
            json_data={data:purchase_invoice_data.id};
            supplier = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'purchase_invoice_supplier',json_data);
        }
        purchase_invoice_data.supplier = {id:supplier[0].id,text:supplier[0].text};
        purchase_invoice_list.supplier=[{id:supplier[0].id,text:supplier[0].text}];
    
    }
    
    var purchase_invoice_purchase_invoice_set = function(purchase_invoice_id){
        json_data={data:purchase_invoice_data.id};
        var result = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'purchase_invoice_purchase_invoice',json_data);
        purchase_invoice = result[0];
        purchase_invoice_data.code = purchase_invoice.code;
        purchase_invoice_data.purchase_invoice_date = purchase_invoice.purchase_invoice_date;
        purchase_invoice_data.purchase_invoice_status={id:purchase_invoice.purchase_invoice_status,text:purchase_invoice.purchase_invoice_status_name};
        purchase_invoice_data.notes = purchase_invoice.notes;
        purchase_invoice_data.total = purchase_invoice.total;
        purchase_invoice_data.cancellation_reason = purchase_invoice.cancellation_reason;
        
    }
    
    var purchase_invoice_enable_disable = function(){
        var method = $("#purchase_invoice_method").val();
        switch(method){
            case 'Add':
                $("#purchase_invoice_date").removeAttr('disabled','');
                $("#purchase_invoice_cancellation_reason").removeAttr('disabled','');
                break;
            case 'Edit':
                $("#purchase_invoice_date").attr('disabled','');
                if(purchase_invoice_data.purchase_invoice_status.id !== 'X'){
                    $("#purchase_invoice_cancellation_reason").removeAttr('disabled','');
                }
                else{
                    $("#purchase_invoice_cancellation_reason").attr('disabled','');
                }
                break;
        }
       
        
    }
        
    var purchase_invoice_calculate_total = function(){
        var total = 0;
        $.each($($(tbl_component).find('tbody')[0].children),function(key,val){
            var subtotal = $(val).find('[col_name="sub_total"]')[0].innerHTML.replace(/[,]/g,'');
            total+=parseFloat(subtotal);
            $('#purchase_invoice_total')[0].innerHTML = APP_CONVERTER.thousand_separator(total);
        })
    }
    
    var purchase_invoice_init_data_new = function(){
        purchase_invoice_po_set();
        purchase_invoice_data.cols = [
            {name:'row_num',label:'#',type:'text',visible:true,attr:{style:'text-align:center',col_name:'row_num'}},
            {name:'product_id',label:'',type:'text',visible:false,attr:{style:'text-align:center',col_name:'product_id'}},
            {name:'unit_id',label:'',type:'text',visible:false,attr:{style:'text-align:center',col_name:'unit_id'}},
            {name:'product_name',label:'Product',type:'text',visible:true,attr:{style:'text-align:left',col_name:'product_name'}},
            {name:'ordered_qty',label:'Ordered Qty',type:'text',visible:true,attr:{style:'text-align:center',col_name:'ordered_qty'}},
            {name:'available_qty',label:'Available Qty',type:'text',visible:true,attr:{style:'text-align:center',col_name:'available_qty'}},
            {name:'qty',label:'Qty',type:'input',visible:true,attr:{style:'text-align:center',col_name:'qty'}},
            {name:'price',label:'Price',type:'input',visible:true,attr:{style:'text-align:center',col_name:'price'}, disabled:true},
            {name:'unit_name',label:'Unit',type:'text',visible:true,attr:{style:'text-align:center',col_name:'unit_name'}},
            {name:'sub_total',label:'Sub Total',type:'text',visible:true,attr:{style:'text-align:left',col_name:'sub_total'}}
    
        ];    
        purchase_invoice_list.purchase_invoice_status=[{id:'I',text:'INVOICED'}];
        purchase_invoice_data.total = 0;           
        purchase_invoice_supplier_set();
        purchase_invoice_data.code = '[AUTO GENERATE]';
        purchase_invoice_data.purchase_invoice_status = {id:'I',text:'INVOICED'};       
    }
    
    var purchase_invoice_init_data_load = function(){
           
        purchase_invoice_data.cols = [
            {name:'row_num',label:'#',type:'text',visible:true,attr:{style:'text-align:center',col_name:'row_num'}},
            {name:'product_id',label:'',type:'text',visible:false,attr:{style:'text-align:center',col_name:'product_id'}},
            {name:'unit_id',label:'',type:'text',visible:false,attr:{style:'text-align:center',col_name:'unit_id'}},
            {name:'product_name',label:'Product',type:'text',visible:true,attr:{style:'text-align:left',col_name:'product_name'}},
            {name:'ordered_qty',label:'Ordered Qty',type:'text',visible:false,attr:{style:'text-align:center',col_name:'ordered_qty'}},
            {name:'available_qty',label:'Available Qty',type:'text',visible:false,attr:{style:'text-align:center',col_name:'available_qty'}},
            {name:'qty',label:'Qty',type:'text',visible:true,attr:{style:'text-align:center',col_name:'qty'}},
            {name:'unit_name',label:'Unit',type:'text',visible:true,attr:{style:'text-align:center',col_name:'unit_name'}},
            {name:'price',label:'Price',type:'text',visible:true,attr:{style:'text-align:center',col_name:'price'}, disabled:true},
            {name:'sub_total',label:'Sub Total',type:'text',visible:true,attr:{style:'text-align:left',col_name:'sub_total'}}
        ];
        purchase_invoice_po_set();
        purchase_invoice_supplier_set();            
        purchase_invoice_purchase_invoice_set(purchase_invoice_data.id);
        if(purchase_invoice_data.purchase_invoice_status.id === 'I'){
            purchase_invoice_list.purchase_invoice_status = [
                {id:'I',text:'INVOICED'},
                {id:'X',text:'CANCELED'}
            ];
        }
        else{
            purchase_invoice_list.purchase_invoice_status = [
                {id:'X',text:'CANCELED'}
            ];
        }
        
    }
    
    var purchase_invoice_component_draw = function(){

        var method = $("#purchase_invoice_method").val();
        var json_data=null;
        var product = null;
        switch(method){            
            case 'Add':
                json_data = {data:po_id};
                product = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'purchase_invoice_po_product',json_data);
                break;
            case 'Edit':
                json_data = {data:purchase_invoice_data.id};
                product = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'purchase_invoice_purchase_invoice_product',json_data);
                break;
        }
        for(var i = 0;i<product.length;i++){
            product[i].row_num = i+1; 
        }
        tbl_component = $("#purchase_invoice_table_product")[0];
        table = APP_COMPONENT.table.init().component_set(tbl_component).data_set(product).column_set(purchase_invoice_data.cols);
        table.render();
    
        
        tr_total = document.createElement('tr');
        td_total_colspan = document.createElement('td');
        if(method === 'Add'){
            $(td_total_colspan).attr('colspan','6');
        }
        else{
            $(td_total_colspan).attr('colspan','4');
        }
        td_total_label = document.createElement('td');
        $(td_total_label).attr('style','text-align:center');
        td_total_label.innerHTML = '<strong>TOTAL</strong>';
        td_total = document.createElement('td');
        td_total.innerHTML = '<strong id="purchase_invoice_total">'+APP_CONVERTER.thousand_separator(purchase_invoice_data.total)+'</strong>';
        tr_total.appendChild(td_total_colspan);
        tr_total.appendChild(td_total_label);
        tr_total.appendChild(td_total);
        var tfoot = null;
        if(typeof $("#purchase_invoice_table_product").find('tfoot')[0] === 'undefined'){
            tfoot = document.createElement('tfoot');
        }
        else{
            tfoot = $("#purchase_invoice_table_product").find('tfoot')[0];
            $(tfoot).empty();
        }
        tfoot.appendChild(tr_total);
        tbl_component.appendChild(tfoot);
    }
    
    var purchase_invoice_bind_data = function(){
                
        $('#modal_purchase_invoice').find("#purchase_invoice_po_code").select2({data:[{id:purchase_invoice_data.po_code,text:purchase_invoice_data.po_code}]});
        $('#modal_purchase_invoice').find("#purchase_invoice_po_code").select2('data',{id:purchase_invoice_data.po_code,text:purchase_invoice_data.po_code});
        $('#modal_purchase_invoice').find("#purchase_invoice_purchase_invoice_code").val(purchase_invoice_data.code);
        $('#modal_purchase_invoice').find("#purchase_invoice_date").val(purchase_invoice_data.purchase_invoice_date);
        $('#modal_purchase_invoice').find("#purchase_invoice_notes").val(purchase_invoice_data.notes);
        $('#modal_purchase_invoice').find("#purchase_invoice_delivery_note").val(purchase_invoice_data.delivery_note);
        $('#modal_purchase_invoice').find("#purchase_invoice_purchase_invoice_status").select2({data:purchase_invoice_list.purchase_invoice_status});
        $('#modal_purchase_invoice').find("#purchase_invoice_purchase_invoice_status").select2('data',purchase_invoice_data.purchase_invoice_status);
        $('#modal_purchase_invoice').find("#purchase_invoice_supplier").select2({data:purchase_invoice_list.supplier});
        $('#modal_purchase_invoice').find("#purchase_invoice_supplier").select2('data',purchase_invoice_data.supplier);
        $('#modal_purchase_invoice').find("#purchase_invoice_supplier").select2('val',purchase_invoice_data.supplier.id).change();
        $('#modal_purchase_invoice').find("#purchase_invoice_cancellation_reason").val(purchase_invoice_data.cancellation_reason);
        var method = $("#purchase_invoice_method").val();
        if(method === 'Add'){
            $("#purchase_invoice_div_cancellation_reason").addClass('hide');
        }
        else{
            if(purchase_invoice_data.purchase_invoice_status.id === 'X'){
                $("#purchase_invoice_div_cancellation_reason").removeClass('hide');
            }
            else
                $("#purchase_invoice_div_cancellation_reason").addClass('hide');
        }
        
        var qties = $("#purchase_invoice_table_product").find('[col_name="qty"]');
        $.each(qties,function(){
            qty = $(this)[0].children[0];
            $(qty).attr('style','text-align:left');
            max_val = $(this).parent().find('[col_name="available_qty"]')[0].innerHTML.replace(/[,]/g,'');
            APP_EVENT.init().component_set(qty).type_set('input').numeric_set().max_val_set(max_val).render();

        });
        
        method = $("#purchase_invoice_method").val();
        response = APP_DATA_TRANSFER.ajaxPOST("<?php 
            echo get_instance()->config->base_url()
                    .'common_ajax_listener/controller_permission_check/'
                    .'purchase_invoice/' ?>"+method,null
            );
        if(!response.result){
            $("#purchase_invoice_button_save").hide();
        }
        else{
            $("#purchase_invoice_button_save").show();
        }
        
        if(purchase_invoice_data.purchase_invoice_status.id == 'X'){
            $("#purchase_invoice_button_save").hide();
            $("#purchase_invoice_notes").attr('disabled','');
        }
        else{
            $("#purchase_invoice_button_save").show();
            $("#purchase_invoice_notes").removeAttr('disabled','');
        }
    };
    
    var purchase_invoice_bind_event = function(){
        $.each($("#purchase_invoice_table_product").find('[col_name="qty"]'), function(key, val){
           var inpt = val.children[0];
           $(inpt).on('blur',function(){
              row = $(this).parent().parent() ;
              subtotal = $(row).find('[col_name="sub_total"]');
              qty = $(inpt).val().replace(/[,]/g,'');
              price = $($(row).find('[col_name="price"]').find('input')).val().replace(/[,]/g,'');
              subtotal_val = qty * price;
              subtotal[0].innerHTML = APP_CONVERTER.thousand_separator(subtotal_val);
              purchase_invoice_calculate_total();
           });
        });
        
        purchase_invoice_calculate_total();
    }
    
    $("#new_purchase_invoice").on('click',function(e){
        e.preventDefault();
        $('#purchase_invoice_method').val('Add');
        purchase_invoice_init_data_new();
        purchase_invoice_component_draw();        
        purchase_invoice_bind_data();
        purchase_invoice_bind_event();
        purchase_invoice_enable_disable();
    });
    
    $(function(){
        var a = $("#purchase_invoice_table").find("a"); 
        $.each(a,function(){
        $(a).on('click',function(e){
            e.preventDefault();
            purchase_invoice_data.id = $(this).attr("href");
            $("#purchase_invoice_method").val('Edit');
            purchase_invoice_init_data_load();
            purchase_invoice_component_draw();
            purchase_invoice_bind_data();
            purchase_invoice_bind_event();
            purchase_invoice_enable_disable();
            $("#modal_purchase_invoice").modal('show');
           });
       });
    });
    
    $("#purchase_invoice_purchase_invoice_status").on('change',function(){
        if($(this).select2('val') === 'X'){
            $("#purchase_invoice_div_cancellation_reason").removeClass('hide');
        }
        else{
            $("#purchase_invoice_div_cancellation_reason").addClass('hide');
        }
    });
    
    $("#purchase_invoice_button_save").on('click',function(e){
        e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');
        var method =$("#purchase_invoice_method").val();  
        json_data = {
            ajax_post:true,
            purchase_invoice:{
                //id:purchase_invoice_data.id,
                purchase_invoice_date:$("#purchase_invoice_date").val(),
                delivery_note:$("#purchase_invoice_delivery_note").val(),
                purchase_invoice_status:$("#purchase_invoice_purchase_invoice_status").select2('val'),
                notes:$("#purchase_invoice_notes").val(),
                cancellation_reason:$("#purchase_invoice_cancellation_reason").val(),
                supplier_id:$('#purchase_invoice_supplier').select2('val'),
                total:$("#purchase_invoice_total")[0].innerHTML.replace(/[,]/g,'')
            },
            po:{
                id:$("#purchase_invoice_po_id").val()
            }
            ,product:[]
        };
        
        if(method === 'Add'){
            var rows = $("#purchase_invoice_table_product").find('tbody')[0].children;
            $.each(rows,function(){
                row = $(this);
                unit_id = $(this).find('td[col_name="unit_id"]')[0].innerHTML;
                product_id = $(this).find('td[col_name="product_id"]')[0].innerHTML;
                qty = $($(this).find('td[col_name="qty"]')[0].children[0]).val().replace(/[,]/g,'');
                price = $($(this).find('td[col_name="price"]')[0].children[0]).val().replace(/[,]/g,'');
                sub_total = $(this).find('td[col_name="sub_total"]')[0].innerHTML.replace(/[,]/g,'');
                json_data.product.push({unit_id:unit_id,product_id:product_id,qty:qty,price:price,sub_total:sub_total});
            });
        }

        // method supposed to be add/update any other methods will be rejected by server
        var ajax_url = '';
        switch(method){
            case 'Add':
                ajax_url = "<?php echo $purchase_invoice_index_url; ?>"+'add';
                break;
            case 'Edit':
                ajax_url = "<?php echo $purchase_invoice_index_url; ?>"+'edit/'+purchase_invoice_data.id;
                break;
            
        }
        
        var current_url = APP_WINDOW.current_url();
        APP_DATA_TRANSFER.submit(ajax_url,json_data,current_url);
        $("#modal_purchase_invoice").scrollTop(0);        
        setTimeout(function(){btn.removeClass('disabled')},1000);
        
    });
    
    
</script>