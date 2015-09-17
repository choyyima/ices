<script>
    var dashboard_refresh_every_ms=300000;
    var dashboard = {
        number_of_dashboard:0,
        scroll_height:200,
        dashboard_draw: function(irow){
            var lscroll_height = 200;
            $(irow.target_data)[0].innerHTML = irow.data;        
            var lprefix_id = irow.prefix_id;
            
            
            
        },
        dashboard_get:function(imodule_arr){
            var ldata = {
                module:imodule_arr
            };
            
            var lajax_url = "<?php echo ICES_Engine::$app['app_base_url'];?>"+'dashboard'+
                '/'+'data_support/data_get';

            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ldata);
            if(lresult.status === 1){
                $.each(lresponse, function(ldashboard_idx, lrow){
                    dashboard.dashboard_draw(lrow);
                });
            }
        },
        refresh:function(imodule){
            imodule = typeof imodule === 'undefined'? 
                [imodule] : [];
            dashboard.dashboard_get(imodule);
        }
    }
    
    dashboard.refresh(null);
    window.setInterval(function(){dashboard.refresh(null)},dashboard_refresh_every_ms);
    $('[dashboard_component] [id*="_refresh"]').on('click',function(){
        
        var ldiv = $(this).closest('.box.box-primary')[0];
        $(ldiv).find('[id*="_overlay"]').addClass('overlay');
        $(ldiv).find('[id*="_loading"]').addClass('loading-img');
        setTimeout(function(){
            $(ldiv).find('[id*="_overlay"]').removeClass('overlay');
            $(ldiv).find('[id*="_loading"]').removeClass('loading-img');
        },500);
            
        dashboard.refresh($(ldiv).attr('module_name'));
    });
    
    $('[dashboard_component] .box-body').slimScroll({
        height: dashboard.scroll_height
    });
    
    $('[dashboard_component] [id*="_minus"]').on('click',function(){
        var lprefix_id = $(this).closest('[module_name]').attr('module_name');
        if($('#'+lprefix_id+'_div').find('[class="slimScrollDiv"]').height()>0){
            $('#'+lprefix_id+'_div').find('[class="slimScrollDiv"]').height(0);
        }
        else{
            $('#'+lprefix_id+'_div').find('[class="slimScrollDiv"]').height(dashboard.scroll_height);
        }

    });
    
    
</script>