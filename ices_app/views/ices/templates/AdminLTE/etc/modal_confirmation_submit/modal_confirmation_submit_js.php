<script>
    window.modal_confirmation_submit_parent = null;
    
    $('#modal_confirmation_submit_btn_submit').on('click',function(){
        $('#modal_confirmation_submit').modal('hide');
    });

    $('#modal_confirmation_submit').on('show.bs.modal',function(){
        $(modal_confirmation_submit_parent).not($(this)).css('z-index',500);

        setTimeout(function(){
            $('#modal_confirmation_submit_btn_submit').focus();
        },1100);
    });

    $('#modal_confirmation_submit').on('hidden.bs.modal',function(){
        $('#modal_confirmation_submit_btn_submit').off();
        $('#modal_confirmation_submit_btn_submit').on('click',function(){
            $('#modal_confirmation_submit').modal('hide');
        });

        $(modal_confirmation_submit_parent).not($(this)).css('z-index','');
        modal_confirmation_submit_parent = null;
    });


</script>