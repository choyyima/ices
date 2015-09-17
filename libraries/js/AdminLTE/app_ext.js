var APP_PATH = ICES_PATH;
$.fn.select2_data_list = function() {    
    return JSON.parse(atob($(this).attr('select2_data_list')));
};

var APP_MESSAGE = {
    def_msg_appear:20000,
    set:function(type,msg,msg_appear){
        var lmsg_appear = APP_MESSAGE.def_msg_appear;
        var icon_class = 'fa fa-check'
        if(typeof msg_appear !== 'undefined'){        
            if($.isNumeric(msg_appear)) lmsg_appear = msg_appear;
        }
        try{
            var msg_class = '';
            var msg_type='';
            var msg_msg='';
            if(type == 'info' || type == 'success'){
                msg_class = 'alert alert-info alert-dismissable';
                if(type == 'info')  msg_type = 'INFORMATION!';
                if(type == 'success')  msg_type = 'Success!';
            }
            else if (type == 'error' || type == 'danger'){
                icon_class = 'fa fa-ban';
                msg_class = 'alert alert-danger alert-dismissable';
                if(type == 'error') msg_type = 'ERROR!';
                if(type == 'danger') msg_type = 'Danger!';
            }
            if(typeof msg !=='undefined'){
                if($.isArray(msg)){
                    $.each(msg,function(key, val){
                        msg_msg+='<li>'+val+'<br/></li>'
                    });
                }
                else{
                    msg_msg+='<li>'+msg+'<br/></li>'
                }
            }
                            
            var lbtn_id = "app_msg_btn_"+APP_GENERATOR.UNIQUEID();
            var modals = $('[class="modal fade in"]').filter('[style*="display: block;"]');
            modal_exists = false;//console.log(modals);
            $.each(modals,function(){
                var body = $(this).find('[class="modal-body"]').not('[no_message="true"]');
                if(body.length>0){
                    
                    $(body).prepend(
                        '<div id="app_msg" class="'+msg_class+'" > '+
                            '<i class="'+icon_class+'"> </i> ' +
                            '<button id="'+lbtn_id+'" type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> '+
                            '<b> '+msg_type+'</b><br/>'+
                            msg_msg+
                        '</div>'
                    ); 
                    modal_exists = true;
                }
            });
            
            if(modal_exists === false){
                
                $("[class=content]").prepend(
                        '<div id="app_msg" class="'+msg_class+'" > '+
                            '<i class="'+icon_class+'"> </i> ' +
                            '<button id="'+lbtn_id+'" type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> '+
                            '<b> '+msg_type+'</b><br/>'+
                            msg_msg+
                        '</div>'
                );                
            }
            
            setTimeout(function(){$("#"+lbtn_id+"").closest('div').remove();},lmsg_appear);
               
        }
        catch(err){
            alert(err.message);
        }
    }
};

var APP_DATA_TRANSFER = {
    ajaxPOST:function($url,$data){
        var response = ICES_DATA_TRANSFER.ajaxPOST($url,$data);
        
        if(response === null || response === typeof 'undefined')  response = {};
        
        if(typeof response.success !== 'undefined'){
            var msg_appear = typeof 'undefined';
            if(typeof response.msg_appear !== 'undefined'){msg_appear = response.msg_appear;}
            if(response.success === 0){
                if(typeof response.msg !== 'undefined'){
                    APP_MESSAGE.set('error',response.msg,msg_appear);
                }
                else{
                    APP_MESSAGE.set('error',response.msg,msg_appear);
                }
                window.scrollTo(0,0);
            }
        }
        return response;
    },
    submit:function(ajax_url,json_data,redirect_url){
        var response = APP_DATA_TRANSFER.ajaxPOST(ajax_url,json_data);
        var msg_appear = typeof 'undefined';
        if(typeof response.msg_appear !== 'undefined'){msg_appear = response.msg_appear;}
        if(typeof response.success !== 'undefined'){
            if(response.success == 1){
                APP_MESSAGE.set('success',response.msg,msg_appear);                
                if(typeof(redirect_url) !== 'undefined'){
                    window.location = redirect_url;
                }
            }
        }
        
        return response;
    },
    after_submit:function(param){
        if(param.result.success ===1){
            if(param.view_url !==''){
                var url = param.view_url+param.result.trans_id;
                window.location.href=url;
            }
            else{
                param.func_after_submit();
            }
        }
    }
}

var APP_GENERATOR = ICES_GENERATOR;

var APP_CONVERTER = ICES_CONVERTER;
APP_CONVERTER.status_attr = function(status){
    var result = '';
    switch(status){
        case 'ACTIVE':
            result = '<span style=\"color:green\"><strong>'+status+'</strong></span>';
            break;
        case 'INACTIVE':
            result = '<span style=\"color:red\"><strong>'+status+'</strong></span>';
            break;
        case 'INVOICED':
            result = '<span style=\"color:green\"><strong>'+status+'</strong></span>';
            break;
        case 'CANCELED':
            result = '<span style=\"color:red\"><strong>'+status+'</strong></span>';
            break;
        default:
            result = '<span>'+status+'</span>';
            break;
    }

    return (result);
};

var APP_COMPONENT ={ 
    attach:function(itarget,isource){
        itarget.innerHTML = isource.html;
        $.globalEval(isource.script);
    },
    disable_all:function(parent_pane){
        $.each($(parent_pane).find('.disable_all'),function(idx, lcomp){
            var ltype = $(lcomp).attr('disable_all_type');
            switch(ltype){
                case 'select2':
                    $(lcomp).select2('disable');
                    break;
                case 'common':
                    $(lcomp).prop('disabled',true);
                    break;
                case 'iCheck':
                    $(lcomp).iCheck('disable');
                    break;
            }
        });
    },
    focus:function(comp, msec){
        var lmsec = typeof msec !== 'undefined'?msec:100;
        setTimeout(function(){
            comp.focus();
        },msec)
    },
    sticky_relocate : function(comp,comp_width,margin_top,margin_left,margin_right) {
        if(isNaN(parseFloat(margin_left))){
            margin_left = 0;
        }
        if(isNaN(parseFloat(margin_right))){
            margin_right = 0;
        }
        if(isNaN(parseFloat(margin_top))){
            margin_top = 0;
        }
        
        if($(window).width()>767 ){
            var window_top = $(window).scrollTop();
            var window_width = $(window).width();
            var menu_width =$('.left-side.collapse-left').width() !== null?
                0:$('.left-side').width();
            var div_top = $(comp).closest('.form-group').offset().top;

            var my_width = 0;
            if(comp_width.toString().indexOf('%') !== -1){
                my_width = ((window_width - margin_left - margin_right - menu_width) * (parseFloat(comp_width)/100));
            }
            else{
                my_width = ((window_width - margin_left - margin_right - menu_width) * parseFloat(comp_width));
            }
            if (window_top > (div_top - margin_top)) {
                $(comp).css('width',my_width+'px');
                $(comp).css('margin-right',margin_right+'px');
                $(comp).css('margin-top',margin_top+'px');
                $(comp).addClass('stick');

            } else {
                $(comp).css('width','');
                $(comp).css('margin-top','');
                $(comp).css('margin-right','');
                $(comp).removeClass('stick');

            }
        }
    },
    edit_row:function(){
        var lnew_row = document.createElement('button');                    
        $(lnew_row).addClass('fa fa-edit text-blue background-transparent no-border');
        $(lnew_row).attr('style','cursor:pointer');
        
        return lnew_row;
    },
    new_row:function(){
        var lnew_row = document.createElement('button');                    
        $(lnew_row).addClass('fa fa-plus text-blue background-transparent no-border');
        $(lnew_row).attr('style','cursor:pointer');
        
        return lnew_row;
    },
    trash:function(){
        
        var ltrash = document.createElement('button');                    
        $(ltrash).addClass('fa fa-trash-o text-red no-border background-transparent');
        $(ltrash).attr('style','cursor:pointer');
        $(ltrash).on('click',function(){
            var ltr = $(this).closest('tr')[0];
            var ltbody = $(ltr).closest('tbody')[0];
            $(ltr).remove();
            for(var i = 0; i<$(ltbody).children().length;i++){                    
                $($(ltbody).children()[i]).find('[col_name="row_num"]').text(i+1);
            }
        });
        return ltrash;
        
    },
    input_select_table: {
        data:{
            clean_data:[]
            ,tmplt_row:{} //each row must have {text_align:,visible:true,val:"",type:"", filter:'', list:{}->optional }            
            ,unique_col:''
            ,table:''
            ,trash:true
        },
        init:function(tmplt_row,unique_col,table,trash){
            this.data.tmplt_row = tmplt_row;
            this.data.unique_col=unique_col;
            this.data.table = table;
            if(typeof trash!=='undefined'){this.data.trash = trash;}
        },        
        check_duplicate:function(data){
            for(var i = 0;i<this.data.clean_data.length;i++){
                unique_col = this.data.unique_col;
                if(this.data.clean_data[i][unique_col].val == data[unique_col].val){
                    return true;
                }
            }
            return false;
        },                
        valid:function(data){

            var result = false; 
            if(data.length>0){
                if(!this.check_duplicate(data[0])){
                    result = true;
                }
            }
            return result;
        },
        append:function(selected_item){
            if(this.valid(selected_item) ){               
                for(var i = 0;i<selected_item.length;i++){
                    var new_row = JSON.parse(JSON.stringify(this.data.tmplt_row));
                    
                    try{
                        $.each(new_row, function(key, val){
                            new_row[key]['id'] = APP_GENERATOR.UNIQUEID();
                            if(typeof selected_item[i][key] !== 'undefined'){
                                new_row[key].val = selected_item[i][key].val;
                                if(typeof selected_item[i][key].list !== 'undefined'){
                                    new_row[key].list = selected_item[i][key].list;
                                }
                                
                            }
                        });

                    }
                    catch(err){
                        alert(err.message);
                    }
                }
                this.data.clean_data.push(new_row);
            }
        },
        remove:function(id){
            this.data.clean_data.splice(id,1);
            tbody = $("#"+this.data.table).find('tbody')[0];
            $(tbody.children[id]).remove();
            for(var i = 0;i<tbody.children.length;i++){
                trash = $(tbody.children[i]).find('[class="fa fa-trash-o"]')[0];
                $(trash).attr('trans-id',i);
                tbody.children[i].children[0].innerHTML = i+1;
            }
        },
        filter_set:function(obj,binded_data,key,method){
    
            if(method === 'numeric'){
                $(obj).on('mouseup',function(e){
                   e.preventDefault(); 
                });
                $(obj).on('keyup',function(e){                
                    $(this).val(APP_CONVERTER._float($(this).val()));
                    binded_data[key].val = $(this).val();
                });

                $(obj).on('blur',function(e){
                    $(this).val(APP_CONVERTER.thousand_separator($(this).val()));
                    $(this).val($(this).val()==''?'0':$(this).val());
                });

                $(obj).on('focus',function(e){
                    $(this).val($(this).val().replace(/[,]/g,''));
                    $(this).select();
                });         
            }
        },
        rows_draw:function(){
            table = $("#"+this.data.table);
            var tbody = $(table).find('tbody')[0];
            for(var i = 0; i<this.data.clean_data.length; i++){
                var root = this;
                var data_row = this.data.clean_data[i];
                var id = APP_GENERATOR.UNIQUEID();

                var row = document.createElement('tr');
                row.setAttribute('id',id);

                var row_num = document.createElement('td');
                row_num.setAttribute('style','padding-top:16px');
                row_num.innerHTML = (i+1);
                row.appendChild(row_num);

                $.each(this.data.tmplt_row,function(key,val){
                    var col = null;
                    var exists = false;
                    switch(val.type){
                        case 'input':
                            var cell = document.createElement('input');
                            cell.setAttribute('id',data_row[key].id);
                            cell.setAttribute('class','form-control');
                            root.filter_set(cell,data_row,key,val.filter);
                            if(typeof val.col_name!=='undefined'){
                                cell.setAttribute('col_name',val.col_name);
                            }
                            $(cell).val(data_row[key].val);
                            if(typeof data_row[key].text_align !=='undefined'){
                                $(cell).attr('style','text-align: '+data_row[key].text_align+';');
                            }
                            col = document.createElement('td');
                            col.appendChild(cell); 
                            exists = true;
                            break;
                        case 'text':                            
                            col = document.createElement('td');
                            col.innerHTML=data_row[key].val;
                            if(typeof val.col_name !== 'undefined'){
                                col.setAttribute('col_name',val.col_name);
                            }
                            if(typeof data_row[key].text_align !=='undefined'){
                                $(col).attr('style','padding-top:16px;text-align: '+data_row[key].text_align+';');
                            }
                            exists = true;
                            break;
                        case 'select':
                            var select = document.createElement('select');
                            select.setAttribute('id',data_row[key].id);
                            select.setAttribute('class','form-control');
                            for(var j = 0;j<data_row[key].list.length;j++){
                                var option = document.createElement('option');
                                option.setAttribute('value',data_row[key].list[j].val);
                                option.innerHTML=data_row[key].list[j].label;
                                select.appendChild(option);
                            }
                            col = document.createElement('td');
                            col.appendChild(select); 
                            
                            $(select).on('change',function(e){
                                data_row[key].val = $(this).val();
                            });
                            
                            $(select).attr('pre-val',data_row[key].val);
                            
                            exists = true;
                            break;
                    }
                    if(exists){
                        if(val.visible === false){
                            col.setAttribute("class","hidden");
                        }
                        row.appendChild(col);
                    }
                });

                var icon_col = document.createElement('td');
                icon_col.innerHTML = root.data.trash?'<i id="btn_'+id+'" style="padding-top:10px;color:red;cursor:pointer" class="fa fa-trash-o"></i>':'';
                row.appendChild(icon_col);

                tbody.appendChild(row);
                
                selects = $(row).find("select");
                $.each(selects,function(key, val){
                    $(val).val('');
                    if(typeof $(val).attr('pre-val') !=='undefined'){
                        $(val).val($(val).attr('pre-val'));              
                    }
                });
                if(root.data.trash){
                    $("#btn_"+id)[0].setAttribute('trans-id',i);
                    $("#btn_"+id).on('click',function(){                    
                        root.remove($(this).attr('trans-id'));
                    });
                }
            }
        },                
        draw:function(){            
            var root = this;
            var tbody = $("#"+this.data.table).find('tbody');
            $(tbody).empty();            
            root.rows_draw();
        }
    },
    table:{
        component:null,
        columns:[],
        data:[],
        init:function(){
            this.component = null;
            this.columns = [];
            this.data = [];
            return this;
        },
        tmplt_column:{
            name:'',
            type:'',
            visible:true,
            label:'',
            attr:{},
            disabled:false
        },
        data_set:function(data){
            this.data = data;
            return this;
        },
        component_set:function(component){
            this.component = component;
            return this;
        },
        column_set:function(cols){
            root = this;
            $.each(cols,function(key,col){
                var new_col = JSON.parse(JSON.stringify(root.tmplt_column));
                new_col.name = col.name;
                new_col.type = col.type;
                new_col.visible = col.visible;
                new_col.attr = col.attr
                new_col.label = col.label
                if(typeof col.disabled !== 'undefined') new_col.disabled = col.disabled
                root.columns.push(new_col);
            });            
            return this;
        },        
        render:function(){
            root = this;
            thead = $(root.component).find('thead')[0];
            $(thead).empty();
            tr = document.createElement('tr');
            $.each(root.columns,function(col_key, col){
                th = document.createElement('th');
                th.innerHTML = col.label;
                $(th).attr('style','text-align:center');
                if(!col.visible) th.setAttribute('class','hide');                
                tr.appendChild(th);                            
            });
            thead.appendChild(tr);
            tbody = $(root.component).find('tbody')[0];
            $(tbody).empty();
            if(this.component !== null){
                if(root.data.length>0){
                    $.each(root.data,function(data_key,data_row){                        
                        tr = document.createElement('tr');
                        $.each(root.columns,function(col_key, col){
                            td = document.createElement('td');
                            switch(col.type){
                                case 'text':
                                    if(typeof data_row[col.name] !== 'undefined' ){
                                        td.innerHTML = data_row[col.name];
                                    }
                                    td.setAttribute('col_name',col.name);
                                    break;
                                case 'input':
                                    inpt = document.createElement('input');
                                    if(col.disabled) $(inpt).attr('disabled','');
                                    if(typeof data_row[col.name] !== 'undefined' ){
                                        $(inpt).val(data_row[col.name]);
                                    }
                                    td.appendChild(inpt);
                            }
                            if(!col.visible) td.setAttribute('class','hide');
                            $.each(col.attr,function(attr_key,attr_val){
                                td.setAttribute(attr_key,attr_val);
                            });
                            tr.appendChild(td);                            
                        });
                        tbody.appendChild(tr);
                    });
                }
            }
            return this;
        }
        
    },
    table_fast_draw:{
        col_add:function(parent,col){
            root = APP_COMPONENT.table_fast_draw;

            tmplt_col = {
                tag:'td',
                col_name:'',
                type:'text',
                style:'',
                val:'',   
                class:'',
                visible:true,
                col_style:'text-align:middle',
                attr:{},
                comp_attr:{}
            };                    
            if(typeof col.tag !==  'undefined') tmplt_col.tag = col.tag;
            if(typeof col.col_name !==  'undefined') tmplt_col.col_name = col.col_name;
            if(typeof col.type !==  'undefined') tmplt_col.type = (col.type===''?'text':col.type);
            
            if(typeof col.style !==  'undefined') tmplt_col.style += col.style;
            if(typeof col.val !== 'undefined') tmplt_col.val = col.val;                    
            if(typeof col.class !==  'undefined') tmplt_col.class = col.class;                    
            if(typeof col.attr !== 'undefined') tmplt_col.attr = col.attr;
            if(typeof col.comp_attr !== 'undefined') tmplt_col.comp_attr = col.comp_attr;
            if(typeof col.col_style !==  'undefined') tmplt_col.col_style= col.col_style;
            if(typeof col.visible !==  'undefined') tmplt_col.col_style += col.visible==false?';display:none;':'';
            
            var result_col = root.col_generate(tmplt_col);                    
            $(parent).append(result_col);
            return result_col;
        },
        col_generate:function(col){
            var lcol = document.createElement(col.tag);
            $(lcol).attr('col_name',col.col_name);
            
            $.each(col.attr,function(key, val){
                $(lcol).attr(key,val);
            });
            
            switch(col.type){
                case 'div':
                    $(lcol).attr('style',col.col_style);
                    lcol.innerHTML = '<div class="'+col.class+'">'+col.val+'</div>';
                    $(lcol).find('div').attr('style',col.style);
                    $.each(col.comp_attr,function(key, val){
                        $(lcol).find('div').attr(key,val);
                    });
                    break;
                case 'span':
                    $(lcol).attr('style',col.col_style);
                    lcol.innerHTML = '<span class="'+col.class+'">'+col.val+'</span>';
                    $(lcol).find('span').attr('style',col.style);
                    $.each(col.comp_attr,function(key, val){
                        $(lcol).find('span').attr(key,val);
                    });
                    break;
                case 'text':
                    $(lcol).attr('style',col.col_style);
                    $(lcol).attr('class',col.class);
                    lcol.innerHTML = col.val;
                    break;
                case 'textarea':
                    var linput = document.createElement('textarea');
                    $(linput).attr('style',col.style);
                    $(linput).attr('class',col.class);
                    $(linput).attr('rows',4);
                    $(linput).val(col.val);
                    $.each(col.comp_attr,function(key, val){
                        $(linput).attr(key,val);
                    });
                    $(lcol).attr('style',col.col_style);                    
                    lcol.appendChild(linput);
                    
                    break;
                case 'input':
                    var linput = document.createElement('input');
                    $(linput).attr('style',col.style);
                    $(linput).attr('class',col.class);
                    $(linput).val(col.val);
                    $.each(col.comp_attr,function(key, val){
                        $(linput).attr(key,val);
                    });
                    $(lcol).attr('style',col.col_style);                    
                    lcol.appendChild(linput);
                    
                    break;
                case 'select':
                    var lselect = document.createElement('select');
                    $(lselect).attr('style',col.style);
                    $(lselect).attr('class',col.class);
                    $(lselect).val(col.val);
                    lcol.appendChild(lselect);
                    break;
            }
            return lcol;
        }
    },
    input:{
        color_non_zero:function(component, lcolor){
            $(component).on('blur',function(){
                var lval = $(this).val();
                if(parseFloat(lval) > 0){
                    $(this).css('color',lcolor);
                }
                else $(this).css('color','');            
            });
            return APP_COMPONENT.input;
        },    
        numeric:function(component, lsetting){
            if(typeof lsetting === 'undefined') lsetting = {};
            
            if(typeof lsetting.reset !== 'undefined'){
                if(lsetting.reset) $(component).off();
            }
            var ldec = typeof lsetting.dec === 'undefined'?2:lsetting.dec;
            
            var ldata_type = typeof lsetting.data_type === 'undefined'?'float':lsetting.data_type;
            //<editor-fold defaultstate="collapsed" desc="Basic Setup">
            $(component).on('focus',function(){
                
                $(this).val($(this).val().replace(/[,]/g,''));
                $(this).select();
            });
            $(component).on('blur',function(e){
                var lval = '';
                switch(ldata_type){
                    case 'int':
                        lval = APP_CONVERTER._int($(this).val());
                        break;
                    case 'float':
                        lval = APP_CONVERTER._float($(this).val());
                        break
                }
                
                if(lval ==='') lval = '0.00';
                $(this).val(APP_CONVERTER.thousand_separator(lval,ldec));
                
                
                
                $(this).val(lval);
            });
            
            $(component).on('keypress',function(e){
                
                    var lprevent_input = false;
                    var last_position = $(this)[0].selectionStart;
                    var lval_str = $(this).val().toString();
                    
                    if(typeof lval_str !== 'undefined'){
                        if(String.fromCharCode(e.keyCode)!=='.'){
                            if(lval_str.indexOf('.') === -1){
                                if(lval_str.length>15) lprevent_input = true;
                            }
                            else{
                                var lval_before_comma_str = lval_str.slice(0,lval_str.indexOf('.'));
                                if(lval_before_comma_str.length>15) lprevent_input = true;
                            }
                        }
                    }
                    
                    if(lprevent_input){
                        e.preventDefault();
                    }
                
            });
            
            $(component).on('keyup',function(e){
                $(this).val(APP_CONVERTER._float($(this).val()));
                
                
                
            });
            $(component).on('mouseup',function(e){e.preventDefault();})
            //</editor-fold>
            
            if(typeof lsetting.min_val !== 'undefined'){
                //<editor-fold defaultstate="collapsed">
                $(component).on('blur',function(){
                    var curr_val = $(this).val().replace(/[,]/g,'');
                    if(curr_val<parseFloat(lsetting.min_val.toString())) $(this).val(APP_CONVERTER.thousand_separator(lsetting.min_val));
                });
                //</editor-fold>
            }
            
            if(typeof lsetting.max_val !== 'undefined'){
                //<editor-fold defaultstate="collapsed">
                $(component).on('blur',function(){
                    var curr_val = $(this).val().replace(/[,]/g,'');
                    if(curr_val>parseFloat(lsetting.max_val.toString())) $(this).val(APP_CONVERTER.thousand_separator(lsetting.max_val));
                });
                //</editor-fold>
            }
            
            $(component).blur();
            
            return APP_COMPONENT.input;
        }
    },
    input_select:{
        empty:function(icomp,iparam){
            
            $(icomp).select2('data',null);
            $(icomp).select2({data:[]});
        },
        dropdown_get:function(lcomp){
            var lli_arr = $(lcomp).select2('dropdown').find('li');
            var lresult = [];
            $.each(lli_arr, function(li_idx, li){
                
               lresult.push($(li).data().select2Data); 
            });
            return lresult;
        },
        set:function(icomp, isetting, extra_param_func){
            var lcomp = icomp;
            if(typeof isetting === 'undefined') isetting = {};
            var lmin_input_length = typeof isetting.min_input_length ==='undefined'?
                1:isetting.min_input_length;
            var lplace_holder = typeof isetting.place_holder === 'undefined'?
                'Type something to search':isetting.place_holder;
            var lallow_clear = typeof isetting.allow_clear === 'undefined'?
                true:isetting.allow_clear;
            var lajax_url = typeof isetting.ajax_url === 'undefined'?
                '':isetting.ajax_url;
            var lexceptional_data_func =  typeof isetting.exceptional_data_func === 'undefined'?function(){return [];}:isetting.exceptional_data_func;
            var input_select_timeout = null;
            
            if(typeof extra_param_func === 'undefined') extra_param_func = function(){return {};};
            
            
            $(lcomp).select2({
                minimumInputLength:lmin_input_length
                ,placeholder: lplace_holder
                ,allowClear:lallow_clear
                ,query:function(query){
                    window.clearTimeout(input_select_timeout);
                    input_select_timeout = window.setTimeout(function(){    

                        var typed_word = query.term.toLowerCase().trim();
                        if(typed_word.replace(/[' ']/g,'') == '') typed_word = '';

                        var data={results:[]};
                        
                        var json_data = {data:typed_word,extra_param:extra_param_func()}; 
                        if(lajax_url !== ''){
                            var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                            var lexceptional_data = lexceptional_data_func();
                            var lraw_data = result.response;
                            for (var i = 0; i < lraw_data.length;i++ ){
                                var lvalid = true;
                                for (var j = 0;j<lexceptional_data.length;j++){
                                    var all_match = true;
                                    if(lexceptional_data[j].length < 1) all_match = false;
                                    $.each(lexceptional_data[j],function(lkey,lval){
                                        if(typeof lraw_data[i][lkey] !== 'undefined'){
                                            if(lraw_data[i][lkey] !== lval) all_match = false;
                                        }
                                        else all_match = false;
                                    });
                                    if(all_match){
                                        lvalid = false;
                                        break;
                                    }
                                    
                                }
                                if(lvalid) data.results.push(lraw_data[i]);
                            }
                        }
                        
                        query.callback(data);
                    },200);
                }
            });
        },
    },
    text:{
        color_non_zero:function(component, lcolor){            
            $(component).bind('change',function(){
                var lval = $(this).text();
                if(parseFloat(lval.toString().replace(/[^0-9.]/g,'')) > 0){
                    $(this).css('color',lcolor);
                }
                else $(this).css('color','');            
            });
        },
    },
    reference_detail:{
        extra_info_set:function(lcomp, ldata,lopt){
            var lcomp_id = $(lcomp).attr('id');
            var ldiv = document.createElement('div');
            if(typeof lopt === 'undefined') lopt = {};
            var lopt_reset = typeof lopt.reset !== 'undefined'?lopt.reset:false;
            
            if(lopt_reset) $(lcomp).find('.extra_info').remove();
            $(ldiv).addClass('extra_info');
            $.each(ldata, function(lref_idx, lref){
                
                var lref_div = document.createElement('div');
                var lrow_span = document.createElement('span');
                var llabel_strong = document.createElement('strong');
                llabel_strong.innerHTML = lref.label;
                var lval_span = document.createElement('span');
                lval_span.innerHTML = lref.val;
                $(lval_span).attr('id',lcomp_id+'_'+lref.id);
                $(lrow_span).append(llabel_strong);
                $(lrow_span).append(lval_span);
                $(lref_div).append(lrow_span);
                $(ldiv).append(lref_div);
            });
            $(lcomp).find('li').insertAt(ldiv,0);
        }
    },
    button:{
        submit:{
            set:function(iComp, lParam){
                $(iComp).on('click',function(e){
                    e.preventDefault();
                    var btn = $(this);
                    btn.addClass('disabled');
                    var lparent_pane = lParam.parent_pane;
                    modal_confirmation_submit_parent = 
                        $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                            $(lparent_pane).closest('.modal'):null;
                    $('#modal_confirmation_submit').modal('show');
                    $('#modal_confirmation_submit_btn_submit').off('click');
                    $('#modal_confirmation_submit_btn_submit').on('click',function(){
                        lParam.module_method.submit();
                        $('#modal_confirmation_submit').modal('hide');

                    });

                    $(lParam.window_scroll).scrollTop(0);        
                    setTimeout(function(){btn.removeClass('disabled')},1000);            
                });
            }
        },
        mail:{
            set:function(iComp, lParam){
                $(iComp).off('click');
                $(iComp).on('click',function(e){
                    e.preventDefault();
                    var btn = $(this);
                    btn.addClass('disabled');
                    modal_mail_methods.init();
                    $('#modal_mail_mail_from').closest('.form-group').hide();
                    $('#modal_mail_mail_to').val(lParam.mail_to_get());
                    $('#modal_mail_subject').val(lParam.subject);
                    $('#modal_mail_message').val(lParam.message);
                    modal_mail_methods.submit = function(){
                        var lajax_url = lParam.ajax_url;
                        var ljson_data = lParam.json_data_get();
                        var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url, ljson_data);
                        
                        if(lresult.success ===1){
                            window.location.reload();
                        }
                    };
                    $('#modal_mail').modal('show');

                    $(lParam.window_scroll).scrollTop(0);        
                    setTimeout(function(){btn.removeClass('disabled')},1000);            
                });
            }
        }
    },
    modal:{
        fade_out_another:function(imodal){
            var lmodal = imodal;
            $('.modal').not(lmodal).css('z-index',500);
        }
    }
}


var APP_WINDOW = {
    scroll_bottom:function(){
        window.scrollTo(0,document.body.scrollHeight);
    },
    current_url:function(){
        return window.location.href.split('#')[0];
    }
}

var APP_FORM={
    status:{
        default_status_set: function(imodule,icomp){
            var ljson_data = {
                    module:imodule
                };

            var lresponse = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'common_ajax_listener/module_status/default_status_get/',ljson_data).response;
            var ldata = {id:lresponse.val,text:lresponse.text,method:lresponse.method};
            $(icomp).select2({data:[]});
            $(icomp).select2('data',ldata);

        },
    },
}

var APP_USER = ICES_USER;

$(document).ready(function(){
    
    setInterval(function(){
        var lresponse = APP_USER.check_timeout();        
        if(lresponse === true){
            window.location.href=APP_WINDOW.current_url();
        }        
    },300000);

    $('#sidebar_search').on('keypress',function(key){        

        var ldata = $(this).val();
        if(key.keyCode == 13){
            key.preventDefault();
            window.open(APP_PATH.base_url+'smart_search/index/'+ldata);

        }
    });

    $('#sidebar_search_btn').on('click',function(key){        

        var ldata = $('#sidebar_search').val();
        window.open(APP_PATH.base_url+'smart_search/index/'+ldata);
        key.preventDefault();
    });
    
    var menu_position = $('.sidebar-menu').position();
    var window_height = $(window).height();
    var window_width = $(window).width();
    if(window_width>992){
        $('header').addClass('fixed-important');
        $('aside.left-side').addClass('fixed-important');
        $('.sidebar-menu').slimScroll({height:($(window).height() - 200)+'px'});
        $('.content-header').addClass('fixed-important');
        $('.content-header').css('right','0px');
        if($('.right-side.strech').length === 0){
            $('.content-header').css('left','220px');
        }
        else{
            $('.content-header').css('left','0px');
        }
        $('.content').css('padding-top','75px');
    }
    else{
        
    }
    
    setTimeout(
        function(){$('.alert.alert-danger.alert-dismissable button').click();}
        ,APP_MESSAGE.def_msg_appear
    );
    
    $(document).on("keydown", function (e) {
        if (e.which === 8 && !$(e.target).is("input, textarea")) {
            e.preventDefault();
        }
    });
    
    function preventBack(){window.history.go(1);}
    setTimeout(preventBack(), 0);
    window.onunload=function(){null};
    
});