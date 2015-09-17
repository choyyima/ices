<script>
    var ajax_search = "<?php echo $ajax_search; ?>";
    
    $("#so_customer_id").on('change',function(){
        $("#so_customer_detail").removeClass('hidden');
        json_data = {data:$(this).select2('val')};
        response = APP_DATA_TRANSFER.ajaxPOST(ajax_search+'so_customer_detail',json_data)
        $("#so_customer_detail_address")[0].innerHTML=response[0].address;
        $("#so_customer_detail_phone")[0].innerHTML=response[0].phone;
        $("#so_customer_detail_city")[0].innerHTML=response[0].city;
        $("#so_customer_detail_customer_type")[0].innerHTML=response[0].customer_type_name;
    });
</script>