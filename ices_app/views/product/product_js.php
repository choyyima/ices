<script>
    
    var ltbody = $('#tbody_unit_table')[0];
    var lproduct_id = $('#product_id').val();
    if(lproduct_id !== ''){
        var json_data = {data:lproduct_id};
        var response = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>product/ajax_search/product_buffer_stock_qty_get',json_data);
        $.each(response,function(key, val){
            var lid_td = $(ltbody).find('[name="id"]:contains("'+val.unit_id+'")');
            
            $(lid_td).closest('tr').find('[name="buffer_stock_qty"]').find('input').val(val.qty).blur();
        });
        var response = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>product/ajax_search/product_sales_multiplication_qty_get',json_data);
        $.each(response,function(key, val){
            var lid_td = $(ltbody).find('[name="id"]:contains("'+val.unit_id+'")');
            
            $(lid_td).closest('tr').find('[name="product_sales_multiplication_qty"]').find('input').val(val.qty).blur();
        });
    }
    
    $('#product_img').on('change',function(){
        var limg_inpt = $('#product_img')[0];
        if(typeof limg_inpt.files[0] !== 'undefined' ){
            lfile = limg_inpt.files[0];
            var lmax_size = 10000;
            if(lfile.size<lmax_size){
                lfr = new FileReader();
                lfr.onload = function(e){
                    $('#product_img_view').attr('src',lfr.result);
                }
                lfr.readAsDataURL(lfile);                
            }
            else{
                $(this).val('');
                alert('File size must be lower than '+lmax_size+' bytes');
            }
        }
        
    });
    
    $("#product_submit").click(function(event){
        event.preventDefault();
        var btn = $(this);
        btn.addClass('disabled');
        var product_subcategory_id = $("#product_subcategory_id").select2('data'); 
        var json_data = {
            product_code:$("#product_code").val(),
            product_name:$("#product_name").val(),
            product_notes:$("#product_notes").val(),
            product_subcategory_id:product_subcategory_id == null?'':product_subcategory_id['id'],            
            units:get_dt_unit_table(), // this function has been generated automatically from input select table js
            product_img:null
        };
        var ajax_url = APP_WINDOW.current_url();
        var index_url = "<?php echo get_instance()->config->base_url() ?>product/";
        
        json_data.product_img = $('#product_img_view').attr('src');
        var result = APP_DATA_TRANSFER.submit(ajax_url,json_data,index_url);
        if(result.success ===1){
            window.location.href=index_url+'view/'+result.trans_id;
        }
        
        setTimeout(function(){btn.removeClass('disabled')},2000);
        
        
    });
    
    
</script>