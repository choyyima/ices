<script>
    var po_id = '<?php echo $po_id; ?>';
    var ajax_search = '<?php echo $ajax_search; ?>';
    

    var movement_data={
        id:'',
        code:'',
        po_code:'',
        date:new Date().toJSON().slice(0,10),
        movement_to:{id:'',text:''},
        delivery_note:'',
        movement_status:{},
        cancellation_reason:'',
        cols:[],
        notes:''
    };
    
    var movement_list={
        movement_status : []
    }
    
    var movement_enable_disable_list=[
        'movement_modal_date',
        'movement_modal_delivery_note'
        
    ]
    
    var movement_modal_po_set = function(){
        json_data={data:po_id};
        var po = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'movement_po',json_data);
        movement_data.po_code = po[0].code;
    }
    
    var movement_modal_movement_set = function(movement_id){
        json_data={data:movement_data.id};
        var result = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'movement_movement',json_data);
        movement = result[0];
        movement_data.code = movement.code;
        movement_data.date = movement.date;
        movement_data.delivery_note = movement.delivery_note;
        movement_data.movement_to={id:movement.warehouse_id,text:movement.warehouse_name};
        movement_data.movement_status={id:movement.movement_status,text:movement.movement_status_name};
        movement_data.notes = movement.notes;
    
    }
    
    var movement_modal_disable = function(){
        $.each(movement_enable_disable_list,function(key,val){
            $("#"+val).attr('disabled','');
        });
        $("#movement_modal_movement_to").select2('enable',false);
    }
    
    var movement_modal_enable  = function(){
        $.each(movement_enable_disable_list,function(key,val){
            $("#"+val).removeAttr('disabled');
        })
        $("#movement_modal_movement_to").select2('enable',true);
    }
    
    var movement_modal_init = function(){
        movement_data.cols = [
            {name:'row_num',label:'#',type:'text',visible:true,attr:{style:'text-align:center',col_name:'row_num'}},
            {name:'item_id',label:'',type:'text',visible:false,attr:{style:'text-align:center',col_name:'item_id'}},
            {name:'unit_id',label:'',type:'text',visible:false,attr:{style:'text-align:center',col_name:'unit_id'}},
            {name:'item_name',label:'Item',type:'text',visible:true,attr:{style:'text-align:left',col_name:'item_name'}},
            {name:'ordered_qty',label:'Ordered Qty',type:'text',visible:true,attr:{style:'text-align:center',col_name:'ordered_qty'}},
            {name:'available_qty',label:'Available Qty',type:'text',visible:true,attr:{style:'text-align:center',col_name:'available_qty'}},
            {name:'qty',label:'Qty',type:'input',visible:true,attr:{style:'text-align:center',col_name:'qty'}},
            {name:'unit_name',label:'Unit',type:'text',visible:true,attr:{style:'text-align:center',col_name:'unit_name'}}
        ];    
        movement_list.movement_status=[{id:'F',text:'FINALIZED'}];
        
        movement_modal_po_set();     
        movement_data.code = '';
        movement_data.movement_status = {id:'F',text:'FINALIZED'}
        movement_modal_enable();
        
        item = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'movement_po_detail',json_data);
        for(var i = 0;i<item.length;i++){
            item[i].row_num = i+1; 
        }
        tbl_component = $("#movement_modal_table_item")[0];
        table = APP_COMPONENT.table.init().component_set(tbl_component).data_set(item).column_set(movement_data.cols);
        table.render();
    }
    
    var movement_modal_draw = function(){
        $("#movement_modal_po_code").val(movement_data.po_code);
        $("#movement_modal_movement_code").val(movement_data.code);
        if(movement_data.code.length == 0){
            $("#movement_modal_movement_code").parent().parent().addClass('hide');
        }
        else{
            $("#movement_modal_movement_code").parent().parent().removeClass('hide');
        }
        $("#movement_modal_date").val(movement_data.date);
        $("#movement_modal_notes").val(movement_data.notes);
        $("#movement_modal_movement_to").select2('data',movement_data.movement_to);
        $("#movement_modal_delivery_note").val(movement_data.delivery_note);
        $("#movement_modal_movement_status").select2({data:movement_list.movement_status});
        $("#movement_modal_movement_status").select2('data',movement_data.movement_status);
        $("#movement_modal_cancellation_reason").val(movement_data.cancellation_reason);
        if(movement_data.cancellation_reason.length == 0){
            $("#movement_modal_div_cancellation_reason").addClass('hide');
        }
        else{
            $("#movement_modal_div_cancellation_reason").removeClass('hide');
        }
        
        var qties = $("#movement_modal_table_item").find('[col_name="qty"]');
        $.each(qties,function(){
            qty = $(this)[0].children[0];
            $(qty).attr('style','text-align:left');
            max_val = $(this).parent().find('[col_name="available_qty"]')[0].innerHTML.replace(/[,]/g,'');
            APP_EVENT.init().component_set(qty).type_set('input').numeric_set().max_val_set(max_val).render();

        });
        
        
        method = $("#movement_modal_method").val();
        response = APP_DATA_TRANSFER.ajaxPOST("<?php 
            echo get_instance()->config->base_url()
                    .'common_ajax_listener/controller_permission_check/'
                    .'movement/' ?>"+method,null
            );
        if(!response.result){
            $("#movement_modal_button_save").hide();
        }
        else{
            $("#movement_modal_button_save").show();
        }
        
        if(movement_data.movement_status.id == 'X'){
            $("#movement_modal_button_save").hide();
            $("#movement_modal_notes").attr('disabled','');
        }
        else{
            $("#movement_modal_button_save").show();
            $("#movement_modal_notes").removeAttr('disabled','');
        }
    };
    
    $(function(){
        var a = $("#movement_table").find("a"); 
        $.each(a,function(){
        $(a).on('click',function(e){
            e.preventDefault();           
            $("#movement_modal_method").val('edit')
            movement_modal_po_set();
            movement_data.id = $(this).attr("href");            
            movement_modal_movement_set(movement_data.id);
            if(movement_data.movement_status.id == 'D'){
                movement_list.movement_status = [
                    {id:'D',text:'DELIVERED'},
                    {id:'X',text:'CANCELED'}
                ];
            }
            
            item = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'movement_movement_detail',json_data);
            for(var i = 0;i<item.length;i++){
                item[i].row_num = i+1; 
            }            
            
            movement_data.cols = [
                {name:'row_num',label:'#',type:'text',visible:true,attr:{style:'text-align:center',col_name:'row_num'}},
                {name:'item_id',label:'',type:'text',visible:false,attr:{style:'text-align:center',col_name:'item_id'}},
                {name:'unit_id',label:'',type:'text',visible:false,attr:{style:'text-align:center',col_name:'unit_id'}},
                {name:'item_name',label:'Item',type:'text',visible:true,attr:{style:'text-align:left',col_name:'item_name'}},
                {name:'ordered_qty',label:'Ordered Qty',type:'text',visible:false,attr:{style:'text-align:center',col_name:'ordered_qty'}},
                {name:'available_qty',label:'Available Qty',type:'text',visible:false,attr:{style:'text-align:center',col_name:'available_qty'}},
                {name:'qty',label:'Qty',type:'text',visible:true,attr:{style:'text-align:center',col_name:'qty'}},
                {name:'unit_name',label:'Unit',type:'text',visible:true,attr:{style:'text-align:center',col_name:'unit_name'}}
            ];
            
            tbl_component = $("#movement_modal_table_item")[0];
            table = APP_COMPONENT.table.init().component_set(tbl_component).data_set(item).column_set(movement_data.cols);
            table.render();
            
            movement_modal_draw();
            movement_modal_disable();
            $("#modal_movement").modal('show');
           });
       });
    });
    
    $("#movement_modal_movement_status").on('change',function(){
        if($(this).select2('val') == 'X'){
            $("#movement_modal_div_cancellation_reason").removeClass('hide');
        }
        else{
            $("#movement_modal_div_cancellation_reason").addClass('hide');
        }
    });
    
    $("#new_movement").on('click',function(e){
        e.preventDefault();
        $('#movement_modal_method').val('add');
        movement_modal_init();
        movement_modal_draw();
    })
    
    $("#movement_modal_button_save").on('click',function(e){
        e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');
        var method =$("#movement_modal_method").val();  
        json_data = {
            movement:{
                id:movement_data.id,
                date:$("#movement_modal_date").val(),
                delivery_note:$("#movement_modal_delivery_note").val(),
                movement_to_warehouse_id:$("#movement_modal_movement_to").select2('val'),
                movement_status:$("#movement_modal_movement_status").select2('val'),
                notes:$("#movement_modal_notes").val(),
                cancellation_reason:$("#movement_modal_cancellation_reason").val()
            },
            po:{
                id:$("#movement_modal_po_id").val()
            }
            ,item:[]
        };
        
        if(method == 'add'){
            var rows = $("#movement_modal_table_item").find('tbody')[0].children;
            $.each(rows,function(){
                row = $(this);
                unit_id = $(this).find('td[col_name="unit_id"]')[0].innerHTML;
                item_id = $(this).find('td[col_name="item_id"]')[0].innerHTML;
                qty = $($(this).find('td[col_name="qty"]')[0].children[0]).val().replace(/[,]/g,'');
                json_data.item.push({unit_id:unit_id,item_id:item_id,qty:qty});
            });
        }
        
        // method supposed to be add/update
        var ajax_url = "<?php echo $movement_index_url; ?>"+method+'/purchase_order';
        var current_url = APP_WINDOW.current_url();
        APP_DATA_TRANSFER.submit(ajax_url,json_data,current_url);
        window.scrollTo(0,0);        
        setTimeout(function(){btn.removeClass('disabled')},1000);
        
    });
    
    
</script>