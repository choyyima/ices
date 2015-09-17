<script>
var app_message_refresh_interval = 30000;
var app_message_refresh_handler = null;
var app_message_curr_page = 0;
var app_message_lookup_string = '';
var app_message_methods={
    
    folder_deselect_all:function(){
        $('#app_massage_folder_inbox').parent().removeClass('active');  
    },
    refresh:function(page){
        var lroot = app_message_methods;
        if(typeof page ==='undefined')
            page = 1;
        window.clearInterval(app_message_refresh_handler);
        if($('#app_message_folder_inbox').parent().attr('class') ==='active'){
            lroot.inbox.refresh(page);            
            app_message_refresh_handler = window.setInterval(function() {lroot.inbox.refresh(page);},app_message_refresh_interval);
            
        }
        
    },
    mark_read:function(){
        if($('#app_message_folder_inbox').parent().attr('class') === 'active'){
            app_message_methods.inbox.mark_read();
            app_message_methods.inbox.refresh(app_message_curr_page);
            $('#app_message_check_all').iCheck('uncheck');
        }
    },
    mark_unread:function(){
        if($('#app_message_folder_inbox').parent().attr('class') === 'active'){
            app_message_methods.inbox.mark_unread();
            app_message_methods.inbox.refresh(app_message_curr_page);
            $('#app_message_check_all').iCheck('uncheck');
        }
    },
    inbox:{
        read:function(id){
            var lmsg_id = id;
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
        },
        mark_read:function(){
            var ltbody = $('#app_message_table').find('tbody')[0];
            var lmsg_checkboxes = $(ltbody).find('[type="checkbox"]');
            var lids = [];
            $.each(lmsg_checkboxes,function(key, val){
                
                if($(val).is(":checked")){
                    var lmsg_id = $(val).closest('tr').attr('trans_id');
                    lids.push(lmsg_id);
                }
                
            });
            var lajax_url = "<?php echo ICES_Engine::$app['app_base_url'].'app_message/message_inbox_mark_read/' ?>";
            var ljson_data = lids;
            APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);

        },
        mark_unread:function(){
            var ltbody = $('#app_message_table').find('tbody')[0];
            var lmsg_checkboxes = $(ltbody).find('[type="checkbox"]');
            var lids = [];
            $.each(lmsg_checkboxes,function(key, val){
                
                if($(val).is(":checked")){
                    var lmsg_id = $(val).closest('tr').attr('trans_id');
                    lids.push(lmsg_id);
                }
                
            });
            var lajax_url = "<?php echo ICES_Engine::$app['app_base_url'].'app_message/message_inbox_mark_unread/' ?>";
            var ljson_data = lids;
            APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);

        },
        delete:function(){
            var ltbody = $('#app_message_table').find('tbody')[0];
            var lmsg_checkboxes = $(ltbody).find('[type="checkbox"]');
            var lids = [];
            $.each(lmsg_checkboxes,function(key, val){
                if($(val).is(":checked")){
                    var lmsg_id = $(val).closest('tr').attr('trans_id');
                    lids.push(lmsg_id);
                }
            });
            var lajax_url = "<?php echo ICES_Engine::$app['app_base_url'].'app_message/message_inbox_delete/' ?>";
            var ljson_data = lids;
            APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);

        },
        refresh:function(page){
            
            $('#app_message_overlay').addClass('overlay');
            $('#app_message_overlay').addClass('loading-img');
            var ltbody = $('#app_message_table').find('tbody')[0];
            $(ltbody).empty();
            
            var lajax_url = "<?php echo ICES_Engine::$app['app_base_url'].'app_message/app_message_inbox_get/' ?>"
            var ljson_data = {page:page, lookup_str:app_message_lookup_string};
            var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
            $('#app_message_num_of_rows')[0].innerHTML = 'Showing ' 
                    +lresponse.info.row_start+' to '+lresponse.info.row_end 
                    +' of '+ lresponse.info.num_of_rows;
            $.each(lresponse.msg, function(key, val){
                var lrow = document.createElement('tr');
                if(val.is_read === '0') $(lrow).addClass('unread');
                $(lrow).attr('trans_id',val.id);

                var lcheckbox_td = document.createElement('td');
                $(lcheckbox_td).addClass('small-col');
                var lcheckbox_inpt = document.createElement('input');
                $(lcheckbox_inpt).attr('type','checkbox');                    
                lcheckbox_td.appendChild(lcheckbox_inpt);

                var lsender_td = document.createElement('td');
                $(lsender_td).addClass('name');
                var lsender_a = document.createElement('a');
                $(lsender_a).attr('href','#');
                lsender_a.innerHTML = val.sender_name;
                lsender_td.appendChild(lsender_a);

                var lsubject_td = document.createElement('td');
                $(lsubject_td).addClass('subject');
                var lsubject_a = document.createElement('a');
                $(lsubject_a).attr('href','#');
                lsubject_a.innerHTML = val.msg_header;
                lsubject_td.appendChild(lsubject_a);

                var ltime_td = document.createElement('td');
                $(ltime_td).addClass('time');
                ltime_td.innerHTML = val.date;

                lrow.appendChild(lcheckbox_td);
                lrow.appendChild(lsender_td);
                lrow.appendChild(lsubject_td);
                lrow.appendChild(ltime_td);

                ltbody.appendChild(lrow);



                $(lsender_a).on('click',function(){
                    app_message_methods.inbox.read($(this).closest('tr').attr('trans_id'));
                    app_message_methods.refresh();
                });
                $(lsubject_a).on('click',function(){
                    app_message_methods.inbox.read($(this).closest('tr').attr('trans_id'));
                    app_message_methods.refresh();
                });
            });
            app_message_curr_page = lresponse.info.curr_page;

            
            $(ltbody).find('input').iCheck({checkboxClass: 'icheckbox_minimal'});
            setTimeout(function(){
                $('#app_message_overlay').removeClass('overlay');
                $('#app_message_overlay').removeClass('loading-img');
            },500);
            
            app_message_nav.refresh();
        }
    }
};

var app_message_bind_event = function(){
    
    $('#app_message_btn_next_page').on('click',function(){        
        app_message_methods.refresh(app_message_curr_page+1);
    });
    
    $('#app_message_btn_prev_page').on('click',function(){        
        app_message_methods.refresh(app_message_curr_page-1);
    });
    
    $('#app_message_folder_inbox').on('click',function(){
        app_message_methods.folder_deselect_all();
        $('#app_message_folder_inbox').parent().addClass('active');
        app_message_curr_page=1;
        app_message_methods.refresh();
    });
    
    $('#app_message_check_all').on('ifChecked',function(){
        $.each($('#app_message_table').find('[type="checkbox"]'),function(key, val){
            $(val).iCheck('check');
        }); 
    });
    
    $('#app_message_check_all').on('ifUnchecked',function(){
        $.each($('#app_message_table').find('[type="checkbox"]'),function(key, val){
            $(val).iCheck('uncheck');
        }); 
    });
    
    $('#app_message_delete').on('click',function(){
        
        if($('#app_message_folder_inbox').parent().attr('class') === 'active'){
            app_message_methods.inbox.delete();
            app_message_methods.inbox.refresh();
            $('#app_message_check_all').iCheck('uncheck');
        }
        
    });
    
    $('#app_message_mark_unread').on('click',function(){       
        app_message_methods.mark_unread();
        
    });
    
    $('#app_message_mark_read').on('click',function(){       
        app_message_methods.mark_read();        
    });
    
    
    
    $('#app_message_search').keypress(function(e){
        
        if(e.keyCode == 13){
            e.preventDefault();
            app_message_lookup_string = $('#app_message_search').val();
            app_message_methods.refresh();
            $('#app_message_search').val('');
        }
        
    });
    
    $('#app_message_btn_search').on('click',function(e){
        app_message_lookup_string = $('#app_message_search').val();
        app_message_methods.refresh();
        $('#app_message_search').val('');

    });
}

var app_message_init_data= function(){
    app_message_curr_page = 1;
    $('#app_message_folder_inbox').click();
}

$('#app_message_table').parent().slimScroll({
        height: '380px'
    });
    


</script>