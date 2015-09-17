    
<script>
    modal_print_parent_pane = $('#modal_print')[0];
    modal_print={
        init:function(){
            modal_print.menu.clear();
        },
        menu:{
            clear:function(){
                var lparent_pane = modal_print_parent_pane;
                $(lparent_pane).find('.sidebar ul').empty();
            },
            add:function(menu_name, menu_link){
                var lparent_pane = modal_print_parent_pane;
                var lul = $(lparent_pane).find('.sidebar ul')[0];
                var lli = document.createElement('li');
                var la = document.createElement('a');
                $(la).attr('href','#');
                $(la).attr('print_url',menu_link);
                var lspan = document.createElement('span');
                $(lspan).text(menu_name);
                la.appendChild(lspan);
                lli.appendChild(la);
                lul.appendChild(lli);
                $(la).on('click',function(){
                    $(lparent_pane).find('#modal_print_preview').empty();
                    var lembed = document.createElement('embed');
                    $(lembed).attr('src',$(la).attr('print_url'));
                    $(lembed).attr('style','height:100%;width:100%');
                    $(lparent_pane).find('#modal_print_preview')[0].appendChild(lembed);
                });
            },
        },        
        show:function(){
            var lactive_modal = $('.modal.fade.in').not($(modal_print_parent_pane));
            $.each(lactive_modal,function(lidx,lrow){
                $(lrow).attr('z_index_old',$(lrow).css('z-index'));
                $(lrow).css('z-index',500);
            });
            $('#modal_print').modal('show');
            $('#modal_print').find('.sidebar-menu a').first().click();
        },
        hide:function(){
            var lactive_modal = $('.modal.fade.in').not($(modal_print_parent_pane));
            $.each(lactive_modal,function(lidx,lrow){
                var lz_index_old = $(lrow).attr('z_index_old');                
                $(lrow).attr('z-index_old','');
                $(lrow).css('z-index',lz_index_old);
            });
        },
    };
    
    $(modal_print_parent_pane).on('hidden.bs.modal',function(){
        modal_print.hide();
    });


</script>