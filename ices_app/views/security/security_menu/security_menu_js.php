<script>
    $("#check_all").on('ifChecked',function(e){
        var tbody = $("#menu_table").find('tbody');
        var menu = [];
        $.each(tbody[0].children,function(key, val){
            var selected = val.children[0].children[0].children[0];
            $(selected).iCheck('check');
        });
        
    });
    
    $("#check_all").on('ifUnchecked',function(e){
        var tbody = $("#menu_table").find('tbody');
        var menu = [];
        $.each(tbody[0].children,function(key, val){
            var selected = val.children[0].children[0].children[0];
            $(selected).iCheck('uncheck');
        });
        
    });

    $("#menu_submit").on('click',function(e){
        e.preventDefault();
        var tbody = $("#menu_table").find('tbody');
        var menu = [];
        $.each(tbody[0].children,function(key, val){
            var selected = val.children[0].children[0].children[0];
            if(selected.checked) menu.push(val.children[1].innerHTML.replace(/&nbsp; /g,''));
        });
        var u_group_id = $("#u_group_id").val();
        var json_data = {
            menu:menu
            ,u_group_id:u_group_id
            ,ajax_post:true
            ,message_session:true
        }
        var ajax_url = "<?php echo $submit_ajax_url ?>";
        
        var lresult = APP_DATA_TRANSFER.submit(ajax_url,json_data);
        if(lresult.success === 1){
            window.location.href = APP_WINDOW.current_url();
        }
    });
    
    var menu_draw = function(menu){
        var tbody = $("#menu_table").find('tbody')[0];
        
        for(var i = 0;i<tbody.children.length;i++){
            var trow = tbody.children[i];
            var checkbox = trow.children[0];
            $(checkbox).iCheck('uncheck');
        }
    
        for(var i = 0;i<menu.length;i++){
            
            for(var j = 0;j<tbody.children.length;j++){
                var trow = tbody.children[j];
                if(trow.children[1].innerHTML.replace(/&nbsp; /g,'') == menu[i].menu_id){
                    var checkbox = trow.children[0];
                    $(checkbox).iCheck('check');
                }
            }
        }
    };
    
    $('#u_group_id').on('change',function(e){
        $("#check_all").iCheck('uncheck');
        json_data={data:$(this).val()};
        var response = APP_DATA_TRANSFER.ajaxPOST("<?php echo $ajax_search_menu ?>",json_data);
        menu_draw(response);
    
    });
</script>