<script>
    var notification_refresh_every_ms=300000;
    var notification = {
        number_of_notification:0,
        notification_draw: function(lresponse){
            this.number_of_notificaton+=parseInt(lresponse.msg.substr(1,lresponse.msg.indexOf(' ',2)));
            
            var lli = document.createElement('li');
            var lahref = document.createElement('a');
            var li = lresponse.icon;
            lahref.innerHTML = li;
            lahref.innerHTML += lresponse.msg;
            $(lahref).addClass('text-blue');
            $(lahref).attr('href',lresponse.href);
            lli.appendChild(lahref);
            $('#notification_body')[0].appendChild(lli);
        },
        notification_get:function(){
            var lnotification_arr = [
                
            ];

            var lajax_url = "<?php echo ICES_Engine::$app['app_base_url']; ?>"+'app_notification'+
                '/'+'notification_get/';
            var lresponse = null;
            lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,lnotification_arr).response;
            $.each(lresponse, function(lnotification_idx, lnotification){
                notification.notification_draw(lnotification);
            });

            
            
        },
        refresh:function(){
            
            this.number_of_notificaton = 0;
            //get warning            
            $('#notification_body').empty();  
            this.notification_get();
            
            $('#notification_number')[0].innerHTML = this.number_of_notificaton>0?this.number_of_notificaton:'';
            $('#notification_header')[0].innerHTML = 'You have '+this.number_of_notificaton+' notifications';

        }        
    }
    
    notification.refresh();
    window.setInterval(function(){notification.refresh()},notification_refresh_every_ms);

</script>