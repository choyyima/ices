<script>
    var buffer_stock_watcher_refresh_every_ms=300000;
    var buffer_stock_watcher_height='200px';
    var buffer_stock_watcher = {
        refresh:function(){
            var ltbl = $('#buffer_stock_watcher_table')[0];
            $(ltbl).find('tbody').empty();            
            
            $('#buffer_stock_watcher_overlay').addClass('overlay');
            $('#buffer_stock_watcher_loading').addClass('loading-img');
            

            var permission = APP_SECURITY.permission_get('product','dashboard_product_buffer_stock_watcher');
            if(permission.result){
                var lajax_url = '<?php echo get_instance()->config->base_url().'product/dashboard_product_buffer_stock_watcher' ?>';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,null);
                
                $.each(lresponse, function(key, row){
                    ltr = document.createElement('tr');

                    lrow_num_td = document.createElement('td');
                    lrow_num_td.innerHTML = row.row_num;

                    row.product_name = '<a href="<?php echo get_instance()->config->base_url().'product/view/' ?>'+row.product_id+'">'+row.product_name+'</a>'
                    lproduct_name_td = document.createElement('td');
                    lproduct_name_td.innerHTML = row.product_name;

                    lproduct_stock_qty_td = document.createElement('td');
                    lproduct_stock_qty_td.innerHTML = row.product_stock_qty;

                    lbuffer_stock_qty_td = document.createElement('td');
                    lbuffer_stock_qty_td.innerHTML = row.buffer_stock_qty;

                    lproduct_qty_difference_td = document.createElement('td');
                    lproduct_qty_difference_td.innerHTML = row.product_qty_difference;

                    lunit_name_td = document.createElement('td');
                    lunit_name_td.innerHTML = row.unit_name;


                    ltr.appendChild(lrow_num_td);
                    ltr.appendChild(lproduct_name_td);
                    ltr.appendChild(lproduct_stock_qty_td);
                    ltr.appendChild(lbuffer_stock_qty_td);
                    ltr.appendChild(lproduct_qty_difference_td);
                    ltr.appendChild(lunit_name_td);

                    $(ltbl).find('tbody')[0].appendChild(ltr);
                });
            }
            setTimeout(function(){
                    $('#buffer_stock_watcher_overlay').removeClass('overlay');
            $('#buffer_stock_watcher_loading').removeClass('loading-img');
                },500);
            //$('#buffer_stock_watcher_div').removeClass('collapsed-box');    
            //$('#buffer_stock_watcher_div').find('[class="slimScrollDiv"]').height(buffer_stock_watcher_height);
            if($('#buffer_stock_watcher_div[class*="collapsed-box"]').length>0)
            $('#buffer_stock_watcher_minus').click();
            $('#buffer_stock_watcher_div').find('[class="box-body"]').slimScroll({ scrollTo : '0px' });
            
        }
    };
    
    $('#buffer_stock_watcher_minus').on('click',function(){
        if($('#buffer_stock_watcher_div').find('[class="slimScrollDiv"]').height()>0){
            $('#buffer_stock_watcher_div').find('[class="slimScrollDiv"]').height(0);
        }
        else{
            $('#buffer_stock_watcher_div').find('[class="slimScrollDiv"]').height(buffer_stock_watcher_height);
        }
        
    });
    
    $('#buffer_stock_watcher_div').find('[class="box-body"]').slimScroll({
        height: buffer_stock_watcher_height
    });
    
    $('#buffer_stock_watcher_refresh').on('click',function(){
        buffer_stock_watcher.refresh();
    });
    buffer_stock_watcher.refresh();
    window.setInterval(function(){buffer_stock_watcher.refresh()},buffer_stock_watcher_refresh_every_ms);
    
</script>