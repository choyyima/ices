<script>
    
var scroll_up_down_methods = {
    show_hide:function(){
        if($(window).width()<=900){
            var lcomp = $('body');
            if($('.modal.fade.in').length === 1){
                lcomp = $('.modal.fade.in .modal-dialog')[0];
            }
            if($(lcomp).height()>$(window).height()+50){
                $('#scroll_up').show();
                $('#scroll_down').show();
            }
            else{
                $('#scroll_up').hide();
                $('#scroll_down').hide();
            }
        }
    },
    position_set:function(){
        var lstart_pos = $(window).height()*0.40;
        $('.scroll-up').css('top',lstart_pos+'px');
        $('.scroll-down').css('top',(lstart_pos+50)+'px');
    }
}

scroll_up_down_methods.position_set();
scroll_up_down_methods.show_hide();

$(window).on('resize',function(){
    scroll_up_down_methods.position_set();
    scroll_up_down_methods.show_hide();
});

$('.modal.fade').on('shown.bs.modal',function(){
    scroll_up_down_methods.position_set();
    scroll_up_down_methods.show_hide();
});

$('.modal.fade').on('hidden.bs.modal',function(){
    scroll_up_down_methods.position_set();
    scroll_up_down_methods.show_hide();
});


$('#scroll_up').on('click',function(){
    var lwindow = $(window)[0];
    var lcomp = $('body');
    if($('.modal.fade.in').length === 1){
        lwindow = $('.modal.fade.in')[0];
        lcomp = $('.modal.fade.in')[0];
        
    }
    var lst = $(lwindow).scrollTop();
    var lweight = $(window).height();
    var lscrollto = lst-lweight;

    $(lcomp).animate({
        scrollTop: lscrollto
      }, 1000);
});

$('#scroll_down').on('click',function(){
    var lwindow = $(window)[0];
    var lcomp = $('body');
    if($('.modal.fade.in').length === 1){
        lwindow = $('.modal.fade.in')[0];
        lcomp = $('.modal.fade.in')[0];
        
    }
    var lst = $(lwindow).scrollTop();
    var lweight = $(window).height();
    var lscrollto = lst+lweight;

    $(lcomp).animate({
        scrollTop: lscrollto
      }, 1000);
});
</script>