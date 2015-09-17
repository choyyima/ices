<script>
    var app_message_nav_refresh_every_ms=300000;
    var app_message_nav = {
        number_of_new_message:0
        
        ,message_nav_get:function(){
            var lroot = app_message_nav;
            var lajax_url = "<?php echo ICES_Engine::$app['app_base_url'].'app_message/' ?>";
            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url+'message_nav_get/');
            
            if(lresult.success === 1){
                $.each(lresult.response, function(key, val){
                    lroot.number_of_new_message+=1;
                    var lli = document.createElement('li');
                    var la = document.createElement('a');
                    $(la).attr('href','#');
                    $(la).attr('trans-id',val.id);
                    var lmsg_header = document.createElement('h4');
                    lmsg_header.innerHTML = val.sender_name;
                    var lmsg_content = document.createElement('p');
                    lmsg_content.innerHTML = val.msg_header;
                    var limg_div = document.createElement('div');
                    $(limg_div).addClass('pull-left')
                    limg_div.innerHTML='<img src="<?php echo get_instance()->config->base_url(); ?>libraries/img/avatar.png" class="img-circle">';
                    var lmoddate_small = document.createElement('small');
                    lmoddate_small.innerHTML = val.moddate;
                    lmsg_header.appendChild(lmoddate_small);
                    la.appendChild(limg_div);
                    la.appendChild(lmsg_header);
                    la.appendChild(lmsg_content);
                    lli.appendChild(la);
                    $('#message_nav_body')[0].appendChild(lli);

                    $(la).on('click',function(){
                        var lmsg_id = $(this).attr('trans-id');
                        var lajax_url = "<?php echo ICES_Engine::$app['app_base_url'].'app_message/message_get/' ?>"+lmsg_id;
                        var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,null);
                        if( typeof lresponse !=='undefined'){
                            $('#app_message_preview_modal_header')[0].innerHTML = lresponse.msg_header;
                            $('#app_message_preview_modal_sender')[0].innerHTML = 'Sender: '+lresponse.sender_name
                            $('#app_message_preview_modal_content')[0].innerHTML = lresponse.msg_body;
                            $('#app_message_preview_modal').modal('show');
                            var lajax_url = "<?php echo ICES_Engine::$app['app_base_url'].'app_message/message_inbox_mark_read/' ?>";
                            var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,[lmsg_id]);
                        }
                        lroot.refresh();
                    });
                });
            };

        },
        refresh:function(){
            var lroot = app_message_nav;
            this.number_of_new_message = 0;
            //get warning            
            $('#message_nav_body').empty();  
            lroot.message_nav_get();           
            
            
            $('#message_nav_number')[0].innerHTML = lroot.number_of_new_message>0?lroot.number_of_new_message:'';
            $('#message_nav_header')[0].innerHTML = 'You have '+lroot.number_of_new_message+' new messages';

        }        
    }
    
    app_message_nav.refresh();
    window.setInterval(function(){app_message_nav.refresh()},app_message_nav_refresh_every_ms);

</script>