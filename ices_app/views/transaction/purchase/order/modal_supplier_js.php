<script>
    var supplier_components_prepare= function(){
        var supplier_parent_pane = '#modal_supplier';
        var method = $(supplier_parent_pane).find("#supplier_method").val();
        var supplier_ajax_url = '<?php echo $supplier_ajax_url ?>';
        var supplier_data_set = function(){
            switch(method){
                case "Add":
                    $(supplier_parent_pane).find("#supplier_code").val("");
                    $(supplier_parent_pane).find("#supplier_name").val("");
                    $(supplier_parent_pane).find("#supplier_address").val("");
                    $(supplier_parent_pane).find("#supplier_city").val("");
                    $(supplier_parent_pane).find("#supplier_country").val("");
                    $(supplier_parent_pane).find("#supplier_phone").val("");
                    $(supplier_parent_pane).find("#supplier_email").val("");
                    $(supplier_parent_pane).find("#supplier_notes").val("");
                    $(supplier_parent_pane).find("#supplier_supplier_status").select2(
                            {data:[{id:"A",text:APP_CONVERTER.status_attr("ACTIVE")}]}
                    );
                    $(supplier_parent_pane).find("#supplier_supplier_status").select2(
                        "data",{id:"A",text:APP_CONVERTER.status_attr("ACTIVE")}
                    );
                    break;
                case "Edit":
                case "View":
                    var supplier_id = $(supplier_parent_pane).find("#supplier_id").val();
                    var json_data={data:supplier_id};
                    rs_supplier = APP_DATA_TRANSFER.ajaxPOST(supplier_ajax_url+"supplier_ajax_get",json_data);
                    if(rs_supplier !== null){
                        $(supplier_parent_pane).find("#supplier_code").val(rs_supplier.code);
                        $(supplier_parent_pane).find("#supplier_name").val(rs_supplier.name);
                        $(supplier_parent_pane).find("#supplier_supplier_status").select2("data",{id:rs_supplier.supplier_status,text:rs_supplier.supplier_status_name});
                        $(supplier_parent_pane).find("#supplier_address").val(rs_supplier.address);
                        $(supplier_parent_pane).find("#supplier_city").val(rs_supplier.city);
                        $(supplier_parent_pane).find("#supplier_country").val(rs_supplier.country);
                        $(supplier_parent_pane).find("#supplier_phone").val(rs_supplier.phone);
                        $(supplier_parent_pane).find("#supplier_email").val(rs_supplier.email);
                        $(supplier_parent_pane).find("#supplier_notes").val(rs_supplier.notes);
                        var supplier_status_list = [];
                        supplier_status_list.push({id:"A",text:APP_CONVERTER.status_attr("ACTIVE")});
                        supplier_status_list.push({id:"I",text:APP_CONVERTER.status_attr("INACTIVE")});
                        $(supplier_parent_pane).find("#supplier_supplier_status").select2({data:supplier_status_list});
                    };
                    break;            
            }
        }
    
        var supplier_components_enable_disable = function(){
            switch(method){
                case "Add":
                    $(supplier_parent_pane).find("#supplier_code").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_name").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_address").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_city").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_country").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_phone").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_email").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_notes").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_supplier_status").removeAttr("disabled");
                    break;
                case "Edit":
                    $(supplier_parent_pane).find("#supplier_code").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_name").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_address").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_city").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_country").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_phone").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_email").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_notes").removeAttr("disabled");
                    $(supplier_parent_pane).find("#supplier_supplier_status").removeAttr("disabled");
                    break;
                case "View":
                    $(supplier_parent_pane).find("#supplier_code").attr("disabled","");
                    $(supplier_parent_pane).find("#supplier_name").attr("disabled","")
                    $(supplier_parent_pane).find("#supplier_address").attr("disabled","")
                    $(supplier_parent_pane).find("#supplier_city").attr("disabled","")
                    $(supplier_parent_pane).find("#supplier_country").attr("disabled","")
                    $(supplier_parent_pane).find("#supplier_phone").attr("disabled","")
                    $(supplier_parent_pane).find("#supplier_email").attr("disabled","")
                    $(supplier_parent_pane).find("#supplier_notes").attr("disabled","")
                    $(supplier_parent_pane).find("#supplier_supplier_status").attr("disabled","")
                    break;
            }
        }
        supplier_data_set();
        supplier_components_enable_disable();
    }

    $("#purchase_order_button_supplier_edit").hide();

    $("#po_supplier_id").on("change",function(e){
        if($("#supplier_id").select2("val") === ""){
            $("#purchase_order_button_supplier_edit").hide();
        }
        else{
            $("#purchase_order_button_supplier_edit").show();
        }   
    });    

    $("#purchase_order_button_supplier_new").on("click",function(){
        $("#modal_supplier").find("#supplier_method").val("Add");
        supplier_init();
        supplier_components_prepare();
        supplier_bind_event();
        supplier_after_submit = function(){
            $("#po_supplier_id").select2("data",{id:'',text:''}).change();
            $(supplier_parent_pane).modal('hide');
        };
        
    })
    
    $("#purchase_order_button_supplier_edit").on("click",function(e){
        var lsupplier = $("#po_supplier_id").select2("data");
        if(lsupplier !== null){
            var supplier_id = lsupplier.id;
            var supplier_name = lsupplier.text
            $("#modal_supplier").find("#supplier_method").val("Edit");
            $("#modal_supplier").find("#supplier_id").val(supplier_id);
            supplier_init();
            supplier_components_prepare();
            supplier_bind_event();                                
            supplier_after_submit = function(){
                $("#po_supplier_id").select2("data",{id:'',text:''}).change();
                $(supplier_parent_pane).modal('hide');
            }                            
            
        }
    });
    
</script>