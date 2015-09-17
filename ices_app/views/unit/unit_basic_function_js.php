<script>
var unit_parent_pane = $('<?php echo $detail_tab; ?>')[0];
var unit_ajax_url = null;
var unit_index_url = null;
var unit_view_url = null;
var unit_window_scroll = null;
var unit_data_support_url = null;
var unit_common_ajax_listener = null;
var unit_component_prefix_id = '';

var unit_init = function(){
    var parent_pane = unit_parent_pane;
    unit_ajax_url = '<?php echo $ajax_url ?>';
    unit_index_url = '<?php echo $index_url ?>';
    unit_view_url = '<?php echo $view_url ?>';
    unit_window_scroll = '<?php echo $window_scroll; ?>';
    unit_data_support_url = '<?php echo $data_support_url; ?>';
    unit_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
}

var unit_after_submit = function(){

}

var unit_methods = {
    hide_all:function(){
        var lparent_pane = unit_parent_pane;
        $(lparent_pane).find('.hide_all').hide();
    },
    disable_all:function(){
        var lparent_pane = unit_parent_pane;
        var lcomponents = $(lparent_pane).find('.disable_all');
        APP_COMPONENT.disable_all(lparent_pane);
        
    },
    submit:function(){
        var lparent_pane = unit_parent_pane;
        var lprefix_id = unit_component_prefix_id;
        var ajax_url = unit_index_url;
        var lmethod = $(lparent_pane).find(lprefix_id+"_method").val();
        var unit_id = $(lparent_pane).find(lprefix_id+"_id").val();
        
        var json_data = {
            ajax_post:true,
            message_session:true,
            unit:{}
        };
        
        switch(lmethod){
            case 'add':
                json_data.unit.code = $(lparent_pane).find("#unit_code").val();
                json_data.unit.name = $(lparent_pane).find("#unit_name").val();
                
                break;
            case 'view':
                json_data.unit.id = unit_id;
                json_data.unit.code = $(lparent_pane).find("#unit_code").val();
                json_data.unit.name = $(lparent_pane).find("#unit_name").val();
                
                break;
        }
        
        var lajax_method='';
        switch(lmethod){
            case 'add':
                lajax_method = 'unit_add';
                break;
            case 'view':
                lajax_method = 'unit_update';
                break;
        }
        ajax_url +=lajax_method+'/'+unit_id;
        
        result = null;
        result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
        if(result.success ===1){
            $(unit_parent_pane).find('#unit_id').val(result.trans_id);
            if(unit_view_url !==''){
                var url = unit_view_url+result.trans_id;
                window.location.href=url;
            }
            else{
                unit_after_submit();
            }
        }
        
    },
    delete:function(){
        var lparent_pane = unit_parent_pane;
        var lprefix_id = unit_component_prefix_id;
        var ajax_url = unit_index_url;
        var lmethod = $(lparent_pane).find(lprefix_id+"_method").val();
        var unit_id = $(lparent_pane).find(lprefix_id+"_id").val();
        
        var json_data = {
            ajax_post:true,
            message_session:true,
        };
        
        ajax_url +='unit_delete/'+unit_id;
        
        result = null;
        result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
        if(result.success ===1){
            window.location.href = unit_index_url;
        }
        
    }
}

var unit_bind_event = function(){
    var parent_pane = unit_parent_pane;
    var amount = $(parent_pane).find('#unit_amount');
    APP_EVENT.init().component_set(amount).type_set('input').numeric_set().render();
    $(amount).on('blur',function(){
        $(parent_pane).find('#unit_available_amount').val($(this).val());
    });

    if($(parent_pane).find("#unit_submit").length>0){
        $(parent_pane).find('#unit_submit').off('click');
        $(parent_pane).find('#unit_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = unit_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                e.preventDefault();
                unit_methods.submit();
            });
            $(unit_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
    }
    
    $(parent_pane).find('#unit_delete').off('click');
    $(parent_pane).find('#unit_delete').on('click',function(e){
        e.preventDefault();
        
        
        $("#modal_confirmation_form").attr("action",unit_index_url+'unit_delete/'+$(parent_pane).find('#unit_id').val());
        $("#modal_confirmation_msg").text("Are you sure want to delete your data?");
        $('#modal_confirmation').modal('show');
        
        $('#modal_confirmation_btn_submit').off();
        $('#modal_confirmation_btn_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            
            unit_methods.delete();
            
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $(unit_window_scroll).scrollTop(0);        
        
    });
    

}
    
var unit_components_prepare= function(){
    var method = $(unit_parent_pane).find("#unit_method").val();
    var lparent_pane = unit_parent_pane;

    var unit_data_set = function(){
        switch(method){
            case "add":
                $(unit_parent_pane).find("#unit_code").val("");
                $(unit_parent_pane).find("#unit_name").val("");                
                break;
                
            case "view":                
                $(lparent_pane).find('#unit_unit_type_table').find('tbody').empty();
                var unit_id = $(lparent_pane).find("#unit_id").val();
                var json_data={data:unit_id};
                var lresult = APP_DATA_TRANSFER.ajaxPOST(unit_data_support_url+"unit_get",json_data);
                var rs_unit = lresult.response.unit;
                if(rs_unit !== null){
                    $(lparent_pane).find("#unit_code").val(rs_unit.code);
                    $(lparent_pane).find("#unit_name").val(rs_unit.name);
                };
                break;                
        }
    }

    var unit_components_enable_disable = function(){
        var lparent_pane = unit_parent_pane;
        var lmethod = $(lparent_pane).find('#unit_method').val();    
        unit_methods.disable_all();

        switch(method){
            case "add":
            case 'view':
                $(unit_parent_pane).find("#unit_code").prop("disabled",false);
                $(unit_parent_pane).find("#unit_name").prop("disabled",false);
                break;
        }
    }

    var unit_components_show_hide = function(){
        var lparent_pane = unit_parent_pane;
        var lmethod = $(lparent_pane).find('#unit_method').val();
        unit_methods.hide_all();

        switch(lmethod){
            case 'add':
            case 'view':
                $(lparent_pane).find('#unit_code').closest('div [class*="form-group"]').show();
                $(lparent_pane).find('#unit_name').closest('div [class*="form-group"]').show();
                break;
        }
        
        switch(lmethod){
            case 'add':
                
                break;
            case 'view':
                $(lparent_pane).find('#unit_delete').show();
                break;
        }
    }

    unit_components_show_hide();
    unit_components_enable_disable();
    unit_data_set();
}
        
    
</script>