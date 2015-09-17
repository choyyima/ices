<script>
    var <?php echo $id ?> = {
       tmplt_col:{
            name:''
            ,label:''
            ,data_type:'text'
            ,is_key:false
            ,is_key2:false
            ,asc:true
            ,row_attrib:{}
            ,attribute:{}
            ,data_format:[]
       },
       tmplt_control:
       {
            label:'',
            base_url:'',
            confirmation:true
       },
       additional_filter:[],
       controls:[],
       sort_by:'',
       filter:'',
       key_column:null,
       key_column2:null,
       columns:[],
       data:[],
       ajax_url:null,
       base_href:null,
       base_href2:null,
       parent:null,
       page:{active:-1,total_page:0,page_shown_max:5,records_per_page:5},
       html:{
            div_id:null,
            records_page_id:null,
            filter_id:null,
            tbl_id:null,
            thead_id:null,
            tbody_id:null,
            info_id:null,
            pagination_id:null,
            loading_id:null,
            loading_overlay_id:null
            
       },
       init:function(){
           this.methods.root = this;
       },
       methods:{
            root:null,
            sort_set:function(data){
                root = this.root;
                root.sort_by=data;
                root.columns.forEach(function(col){
                    if(col.name == data){
                        col.asc = ! col.asc;
                    }
                });
                this.data_show(root.page.active);
            },
            sort_get:function(){
                var result = '';
                var root = this.root;
                if(root.sort_by != ''){
                    root.columns.forEach(function(col){
                        if(col.name == root.sort_by){
                            result = col.name;
                            if(col.asc) result+= ' asc ';
                            else result+= ' desc ';
                        }
                    });
                }
                return result;
            },        
            controls_set:function(data){
                var root = this.root;
                root.controls = [];
                data.forEach(function(control){
                    var new_control = JSON.parse(JSON.stringify(root.tmplt_control));
                    new_control.label = control.label;
                    new_control.base_url = control.base_url
                    if("confirmation" in control) new_control.confirmation = control.confirmation;
                    root.controls.push(new_control);
                });
                
            },        
            config_set:function(id){
                var root = this.root;
                id = "#"+id;
                if(id){
                    root.html.div_id = id;
                    root.html.records_page_id = id+"_records_page";
                    root.html.filter_id = id+"_filter";
                    root.html.tbl_id = id+"_tbl";
                    root.html.thead_id = id+"_thead";
                    root.html.tbody_id = id+"_tbody";                    
                    root.html.info_id = id+"_info";  
                    root.html.pagination_id = id+"_pagination";  
                    root.html.loading_id = id+"_loading";
                    root.html.loading_overlay_id = id+"_overlay";
                }
            },
            columns_set:function(data){
                //data is supposed to be a 2 dimensional array of data
                var root = this.root;
                root.columns = [];
                data.forEach(function(row){
                    var new_col = JSON.parse(JSON.stringify(root.tmplt_col));
                    new_col.label = row.label;
                    new_col.name = row.name;
                    if(typeof row.row_attrib !== 'undefined') new_col.row_attrib = row.row_attrib;
                    if(typeof row.attribute !== 'undefined') new_col.attribute = row.attribute;
                    if(row.data_type) new_col.data_type = row.data_type;
                    if(row.is_key) new_col.is_key = row.is_key;
                    if(row.is_key2) new_col.is_key2 = row.is_key2;
                    new_col.data_format = row.data_format;
                    root.columns.push(new_col);
                });
            },
            header_generate:function(){
                var root = this.root;
                var col_text = '<tr role="row"><th style="width:30px">#</th>';                
                root.columns.forEach(function(row){
                    var col_content =row.label;
                    var lattribute = '';
                    var lstyle='padding-right:20px;';
                    $.each(row.attribute,function(lattr_key,lattr_val){
                        if(lattr_key !=='style'){
                            lattribute+= lattr_key+'=\''+lattr_val+'\'';
                        }
                        else{
                            lstyle+=lattr_val;
                        }
                    });
                    var th_id = root.html.div_id.replace("#","")+'_'+row.name.toLowerCase();
                    col_text +='<th id = "'+th_id+'" class="sorting" style=\''+lstyle+'\' '+lattribute+'>'+col_content+'</th>';
                    var id ="#"+th_id; 
                    $('body').off('click',id);
                    $('body').on('click',id,function(){ 
                        <?php echo $id ?>.methods.sort_set(row.name);
                        if(<?php echo $id ?>.methods.sort_get().indexOf(" asc ")!=-1) $(id).attr("class","sorting_asc");
                        else $(id).attr("class","sorting_desc");
                    });
                })
                if(root.controls.length>0){
                    col_text+='<th style="text-align:center">Action</th>';
                }
                col_text+='</tr>';
                $(root.html.thead_id).html(col_text);
                
            },
            body_generate:function(data){
                var root = this.root;
                var body_text='';
                var row_num = ((root.page.active-1)*(root.page.records_per_page));
                data.forEach(function(row){
                    row_num+=1;
                    var content = '';
                    var content_row_num = '';
                    var content_row='';
                    var controls='';
                    var lis_key_exists = false;
                    root.columns.forEach(function(col){
                        var value = ""; 
                        var row_attrib = '';
                        $.each(col.row_attrib, function(attr_key, attr_val){
                            row_attrib+= ' '+attr_key+' = "'+attr_val+'"';
                        });
                        if(typeof row[col.name] !== 'undefined'){
                            if(row[col.name] === null) row[col.name] = '';
                            $.each(col.data_format, function(df_key, df_val){
                                if(df_val === 'thousand_separator') row[col.name] = APP_CONVERTER.thousand_separator(row[col.name]);
                            });
                            if(col.is_key){
                                value = '<a href="'+root.base_href+'/'+row[root.key_column]+'">'+row[col.name]+"</a>";
                                lis_key_exists = true;
                            }
                            else if (col.is_key2) value = '<a href="'+root.base_href2+'/'+row[root.key_column2]+'">'+row[col.name]+"</a>";
                            else value = '<span>'+row[col.name]+'</span>';
                        }
                        content += '<td '+row_attrib+' col_name="'+col.name+'">'+value+'</td>';
                    });
                    
                    if(lis_key_exists){
                        content_row_num = '<td col_name="row_num">'+row_num+'</td>';
                    }
                    else{
                        if(typeof (row[root.key_column]) !== 'undefined'){
                            content_row_num = '<td col_name="row_num">'+'<a href="'+root.base_href+'/'+row[root.key_column]+'">'+row_num+"</a>"+'</td>';
                        }
                        else{
                            content_row_num = '<td col_name="row_num">'+row_num+'</td>';
                        }
                    }
                    
                    if(root.controls.length>0){
                        controls='<td  style="text-align:center" col_name="controls"><div class="btn-group">';
                        var btn= '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">';
                        btn+= '<span class="caret"> </span>';
                        btn+= '<span class="sr-only"> Toggle Dropdown </span>';
                        btn+= '</button>';
                        controls+=btn;
                        controls+='<ul class="dropdown-menu pull-right" role="menu">';
                        root.controls.forEach(function(control){
                            if(control.confirmation){
                                var value = control.base_url+'/'+row[root.key_column];
                                var id = root.html.div_id.replace('#','');
                                var href = '<a component-id="'+id+'_button" \n\
                                    href="#" value="'+value+'" data-toggle="modal" \n\
                                    data-target="#modal_confirmation" \n\
                                    trigger_modal="true">'+control.label+'</a>';
                                controls+='<li>'+href+'</li>';
                            }
                            else{
                                
                                var href = control.base_url+'/'+row[root.key_column];
                                
                                controls+='<li><a href="'+href+'" style="text-align:center" >'+control.label+'</a></li>';
                            }
                        });
                        controls+='</ul></div></td>';
                        
                    }
                    
                    content_row ='<tr>'+content_row_num+content+controls+'</tr>';   
                    body_text +=content_row;
                });                
                $(root.html.tbody_id).html(body_text);
                
                $(root.html.tbody_id).change();
            },
            footer_generate:function(total_rows,page){
                var root = this.root;
                var record_page = root.page.records_per_page;
                
                var row_first = 0;
                var row_last = 0;
                var page_shown_max = root.page.page_shown_max;    
                //init
                for(var i = 1;i<=page_shown_max;i++){ 
                    $(root.html.pagination_id+'_'+i.toString()).addClass('hide');
                }
                 $(root.html.pagination_id+'_next').addClass('disabled');
                 $(root.html.pagination_id+'_prev').addClass('disabled');
                 $(root.html.pagination_id+'_first').addClass('disabled');
                 $(root.html.pagination_id+'_last').addClass('disabled');
                if(total_rows>0){
                    //generate info                
                    row_first = ((page-1)*record_page)+1;
                    row_last = ((page)*record_page);
                    row_last = row_last>total_rows?total_rows:row_last;
                    //end of info    

                    //generate pagination
                    var page_curr = page;
                    var page_first = 1;
                    var page_last = total_rows%record_page == 0?total_rows/record_page:Math.floor(total_rows/record_page)+1;
                    
                    if(page_curr <= 3){
                        if(page_last>page_shown_max) page_last = page_shown_max;                    
                    }
                    else if (page_curr>=(page_last-3)){
                        if(page_last>5){
                            page_first = (page_last - page_shown_max)+1;
                        }
                        else{
                            page_first = 1;
                        }
                    }
                    else{
                        page_first = page_curr-2;
                        page_last = page_curr+2;
                    }
                    
                    if(page_curr!== 1) $(root.html.pagination_id+'_prev').removeClass('disabled');
                    if(page_curr!== 1) $(root.html.pagination_id+'_first').removeClass('disabled');
                    if(page_curr!== page_last) $(root.html.pagination_id+'_next').removeClass('disabled');
                    if(page_curr!== page_last) $(root.html.pagination_id+'_last').removeClass('disabled');
                    
                    var page_counter = 1;                
                    for(var i = page_first;i<=page_last;i++){
                        var page_temp = $(root.html.pagination_id+'_'+page_counter);
                        page_temp.val(i);
                        if(i == page_curr){
                            page_temp.addClass('active'); 
                            page_temp.removeClass('hide');
                        }
                        else{
                            page_temp.removeClass('active');
                            page_temp.removeClass('hide');
                        }
                        page_temp.html('<a href="#">'+i.toString()+'</a>');
                        page_counter+=1;

                    }
                    //end of pagination
                }
                
                var info_text = 'showing '+row_first+' to '+row_last+' of '+total_rows; 
                $(root.html.info_id).html(info_text);
            },
            additional_filter_get:function(){
                result = {};
                root = this.root;
                $.each(root.additional_filter, function(key, val){
                    var ltype = 'input';
                    if(typeof val.type !== 'undefined') ltype = ltype;
                    
                    switch(ltype){
                        case 'input':
                            result[val.field]=$("#"+val.id).val();
                            break;
                        case 'select2':
                            result[val.field]=$("#"+val.id).select2('val');
                            break;
                    }
                });
                return result;
            },
            data_show:function(page){
                var root = this.root;
                var json_data = {data:"",page:"",records_page:"",sort_by:"",additional_filter:[]};
                json_data.data = root.filter.trim();
                json_data.page = page;
                json_data.records_page = $(root.html.records_page_id).val();
                json_data.sort_by = this.sort_get();
                json_data.additional_filter = root.methods.additional_filter_get();
                this.header_generate();
                $(root.html.loading_id).addClass('loading-img');
                $(root.html.loading_overlay_id).addClass('overlay');
                if(root.ajax_url!==''){
                    var response = APP_DATA_TRANSFER.ajaxPOST(root.ajax_url,json_data);
                    root.page.active = page;
                    var total_rows = response.header.total_rows;
                    var total_page = 0;
                    if(response.header.total_rows>0){
                        total_page = total_rows % root.page.records_per_page === 0?total_rows/root.page.records_per_page:Math.floor(total_rows/root.page.records_per_page)+1;
                        root.page.total_page = total_page;
                    }

                    this.body_generate(response.data);
                    this.footer_generate(response.header.total_rows,page);
                }
                setTimeout(function(){
                    $(root.html.loading_id).removeClass('loading-img');
                    $(root.html.loading_overlay_id).removeClass('overlay');
                },500);
                <?php if($screen_refresh){ ?>
                screen_refresh();
                <?php }?>
            }
            
       }
       
    };
    
    var <?php echo $id ?>_col = <?php echo json_encode($columns); ?>;
    
    var <?php echo $id ?>_control = <?php echo json_encode($controls); ?>;

    <?php echo $id ?>.init();
    <?php echo $id ?>.ajax_url = '<?php echo $lookup_url ?>';
    <?php echo $id ?>.base_href = '<?php echo $base_href ?>';
    <?php echo $id ?>.base_href2 = '<?php echo $base_href2 ?>';
    <?php echo $id ?>.key_column='<?php echo $key_column ?>';
    <?php echo $id ?>.key_column2='<?php echo $key_column2 ?>';
    <?php echo $id ?>.methods.columns_set(<?php echo $id ?>_col);    
    <?php echo $id ?>.methods.controls_set(<?php echo $id ?>_control);
    <?php echo $id ?>.methods.config_set('<?php echo $id ?>');
    <?php 
    if (count($filters)>0){ 
        foreach($filters as $filter){
    ?>
        <?php echo $id ?>.additional_filter.push({id:"<?php echo $filter['id'] ?>",field:"<?php echo $filter['field']?>",type:"<?php echo $filter['type'] ?>"});
    <?php     
        }
    } 
    ?>
    <?php echo $id ?>.methods.data_show(1);
    for(var i = 1;i<=5;i++){
        
        $(<?php echo $id ?>.html.pagination_id+'_'+i.toString()).click(function(e){
            e.preventDefault();
            <?php echo $id ?>.methods.data_show($(this).val());                    
        });
    }
    
    
    $(<?php echo $id ?>.html.filter_id).on('keypress',function(key){
        
        if(key.keyCode == 13){
               
            <?php echo $id ?>.filter = $(<?php echo $id ?>.html.filter_id).val();
            <?php echo $id ?>.methods.data_show(1);
            key.preventDefault();
        }
    });
    
    $(<?php echo $id ?>.html.records_page_id).on('change',function(e){
        e.preventDefault();
        <?php echo $id ?>.page.records_per_page = $(<?php echo $id ?>.html.records_page_id).val();
        <?php echo $id ?>.methods.data_show(1);
    });
    
    $(<?php echo $id ?>.html.pagination_id+'_next').on('click',function(e){
        e.preventDefault();
        if(! $(this)[0].classList.contains('disabled')){
            var next_page = <?php echo $id ?>.page.active+1;
            <?php echo $id ?>.methods.data_show(next_page);
        }
    });
    
    $(<?php echo $id ?>.html.pagination_id+'_last').on('click',function(e){
        e.preventDefault();
        if(! $(this)[0].classList.contains('disabled')){
            var last_page = <?php echo $id ?>.page.total_page;
            <?php echo $id ?>.methods.data_show(last_page);
        }
    });
    
    $(<?php echo $id ?>.html.pagination_id+'_prev').on('click',function(e){
        e.preventDefault();
        if(! $(this)[0].classList.contains('disabled')){
            var prev_page = <?php echo $id ?>.page.active-1;
            <?php echo $id ?>.methods.data_show(prev_page);
        }
    });
    
    $(<?php echo $id ?>.html.pagination_id+'_first').on('click',function(e){
        e.preventDefault();
        if(! $(this)[0].classList.contains('disabled')){
            <?php echo $id ?>.methods.data_show(1);
        }
    });
    
    
    $("a").click(function(){
        if($(this).attr("trigger_modal")){
            var id = <?php echo $id ?>.html.div_id.replace('#',"")+"_button";
            if($(this).attr("component-id") == id){
                var href = $(this).attr("value");
                var form = $("#modal_confirmation_form");
                form.attr("action",href);
            }
        }
    });
    
    
</script>