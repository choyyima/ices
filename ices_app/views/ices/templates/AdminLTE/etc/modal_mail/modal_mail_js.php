<script>
    
    window.modal_mail_parent_pane = $('#modal_mail')[0];
    window.modal_mail_methods = {

        init:function(){
            var lparent_pane= modal_mail_parent_pane;
            $(lparent_pane).find('#modal_mail_mail_from').val('');
            $(lparent_pane).find('#modal_mail_mail_to').val('');
            $(lparent_pane).find('#modal_mail_subject').val('');
            $(lparent_pane).find('#modal_mail_message').val('');
        },
        submit:function(){

        },
    }
    $('#modal_mail_btn_submit').off();
    $('#modal_mail_btn_submit').on('click',function(e){
        e.preventDefault();
        var btn = $(this);
        btn.addClass('disabled');            
        var lparent_pane = modal_mail_parent_pane;
        modal_mail_methods.submit();
        $(lparent_pane).scrollTop(0);        
        setTimeout(function(){btn.removeClass('disabled')},1000);
    });
</script>