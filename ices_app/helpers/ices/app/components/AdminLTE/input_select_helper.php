<?php
    
    class Input_Select extends Component_Engine{
        
        private $div_properties=array(
            'class'=>'form-group'
            ,'id'=>''
            ,'attrib'=>array()
            ,'hide'=>false
            ,'style'=>''
        );
        
        private $input_select_properties=array(
            "id"=>""
            ,"name"=>""
            ,"class"=>""
            ,'label'=>''
            ,'icon'=>''
            ,'icon_label'=>''
            ,"data"=>array()
            ,'ajax_url'=>""
            ,'min_length'=>'1'
            ,'value'=>array("id"=>"","text"=>"")
            ,'attrib'=>array()
            ,'checkbox'=>false
            ,'allow_empty'=>true
            ,'hide_all'=>false
            ,'no_label'=>false
            ,'disable_all'=>false
            ,'disable_all_type'=>'select2'
            ,'is_module_status'=>false
            ,'module_prefix_id'=>null
            ,'module_primary_data_key'=>null
            ,'module_status_field'=>null
            ,'module_view_url'=>null
        );
        
        
        
        public function __construct(){
            parent::__construct();
            $this->input_select_properties = json_decode(json_encode($this->input_select_properties));
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->input_select_properties->id = $this->generate_id();
            $this->div_properties->id = $this->generate_id();
        }
        
        public function div_set($method,$data){
            switch($method){
                case 'class':
                    $this->div_properties->class=$data;
                    break;
                case 'id':
                    $this->div_properties->id = $data;
                    break;
                case 'attrib':
                    foreach($data as $key=>$val){
                        $this->div_properties->attrib[$key] = $val;                        
                    }
                    break;
                case 'style':
                    $this->div_properties->style = $data;
                    break;
                case'hide':
                    $this->div_properties->hide = $data;
                    break;
            }
            return $this;
        }
        
        public function input_select_set($method,$data){
            $props = $this->input_select_properties;
            switch($method){
                case 'class':
                    $props->class=$data;
                    break;
                case 'id':
                    if(strlen($data)>0) $props->id=$data;
                    break;
                case 'name':
                    if(strlen($data)>0) $props->name=$data;
                    break;
                case 'label':
                    $props->label = $data;
                    break;
                case 'data_add':
                    foreach($data as $row){
                        $opt = $row;
                        $opt['text'] = isset($row['data'])?($row['data']):(isset($row['text'])?$row['text']:'');
                        $opt = json_decode(json_encode($opt));
                        $props->data[]=$opt;
                    }
                    break;
                case 'icon':
                    $props->icon = $data;
                    break;
                case 'icon_label':
                    $props->icon_label = $data;
                    break;
                case 'ajax_url':
                    $props->ajax_url = $data;
                    break;
                case 'min_length':
                    $props->min_length=$data;
                    break;
                case 'value':
                    $props->value = $data;
                    break;
                case 'attrib':
                    foreach($data as $key=>$val){
                        $this->input_select_properties->attrib[$key] = $val;                        
                    }                    
                    break;
                    
                case 'checkbox':
                    $this->input_select_properties->checkbox = $data;
                    break;
                case 'allow_empty':
                    $this->input_select_properties->allow_empty = $data;
                    break;
                case 'hide_all':
                    $this->input_select_properties->hide_all = $data;
                    break;
                case 'no_label':
                    $this->input_select_properties->no_label = $data;
                    break;
                case 'disable_all':
                    $this->input_select_properties->disable_all = $data;
                    break;
                case 'is_module_status':
                    $this->input_select_properties->is_module_status = $data;
                    $this->input_select_properties->allow_empty = false;
                    break;
                case 'module_prefix_id':
                    $this->input_select_properties->module_prefix_id = $data;
                    break;
                case 'module_primary_data_key':
                    $this->input_select_properties->module_primary_data_key = $data;
                    break;
                case 'module_status_field':
                    $this->input_select_properties->module_status_field = $data;
                    break;
                case 'module_view_url':
                    $this->input_select_properties->module_view_url = $data;
                    break;

            }
            return $this;
        }
        
        public function additional_script_render(){
            $input_select_props = $this->input_select_properties;
            $raw_data = '';
            
            $js = '';
            $ajax_js = '';
            
            if(strlen($input_select_props->ajax_url)==0){
                foreach($input_select_props->data as $row){
                    if($raw_data == '')
                        $raw_data.=json_encode($row);
                    else        
                        $raw_data.=','.json_encode($row);
                }
            }
            
            $param = array(
                'input_selector_id'=>$input_select_props->id
                ,'id' => $input_select_props->id
                ,'ajax_url' => $input_select_props->ajax_url
                ,'raw_data'=>$raw_data
                ,'min_length'=>$input_select_props->min_length
                ,'value'=>$input_select_props->value
                ,'allow_empty'=>$input_select_props->allow_empty
            );
            $js = str_replace(array("<script>","</script>"),"",get_instance()->load->view($this->path['app_base_dir'].'templates/'.ICES_Engine::$app['app_theme'].'/components/input_select_js'
                        ,$param 
                        ,TRUE)); 
            $this->additional_script.=$js;
            if($this->input_select_properties->is_module_status){
                if($this->input_select_properties->module_prefix_id === null){
                    $this->input_select_properties->module_prefix_id =
                        substr($this->input_select_properties->id,
                            0,
                            (strlen(str_replace('status','',$this->input_select_properties->id))/2)-1
                        );
                    
                }
                
                if($this->input_select_properties->module_primary_data_key === null){
                    $this->input_select_properties->module_primary_data_key =
                        $this->input_select_properties->module_prefix_id;                    
                }
                
                if($this->input_select_properties->module_status_field === null){
                    $this->input_select_properties->module_status_field =
                        $this->input_select_properties->module_primary_data_key.'_status';                    
                }
                
                if($this->input_select_properties->module_view_url === null){
                    $this->input_select_properties->module_view_url =
                        $this->input_select_properties->module_prefix_id.'_index_url+"view/"';
                }
                else{
                    $this->input_select_properties->module_view_url = '"'.
                        $this->input_select_properties->module_view_url.'"';
                }
                
                $module_prefix_id = $this->input_select_properties->module_prefix_id;
                $module_primary_data_key = $this->input_select_properties->module_primary_data_key;
                $module_status_field = $this->input_select_properties->module_status_field;
                $module_view_url = $this->input_select_properties->module_view_url;
                
                $this->additional_script.=''
                    
                    .'$("#'.$input_select_props->id.'").on("change",function(e){
                        if(typeof e.removed !== "undefined"){
                            $(this).attr("old_val",e.removed.id);
                        }
                        var lold_val = $(this).attr("old_val");
                        var lstatus = $(this).select2("val");
                        if(lstatus ==="X"){'."\n"
                            
                            .'if(lold_val !== ""){'
                                .'var lparent_pane = '.$module_prefix_id.'_parent_pane;'."\n"
                                .'modal_confirmation_cancel_input_select_id = "'.$this->input_select_properties->id.'";'
                                .'modal_confirmation_cancel_module_prefix_id = "#'.$module_prefix_id.'";'."\n"
                                .'modal_confirmation_cancel_primary_data_key = "'.$module_primary_data_key.'";'."\n"
                                .'modal_confirmation_cancel_module_status_field = "'.$module_status_field.'";'."\n"
                                .'modal_confirmation_cancel_parent = $(lparent_pane).attr("class").indexOf("modal-body")!==-1?'."\n"
                                .'$(lparent_pane).closest(".modal"):$(lparent_pane);'
                                .'var l_id = $("#'.$module_prefix_id.'_id").val();'."\n"
                                .'var lmethod = $(this).select2("data").method;'."\n"
                                .'modal_confirmation_cancel_ajax_url = '.$module_prefix_id.'_index_url+lmethod+"/"+l_id;'."\n"
                                .'modal_confirmation_cancel_view_url = '.$module_view_url.";\n"
                                .'$("#modal_confirmation_cancel").modal("show");'."\n"
                                .'if($(lparent_pane).attr("class").indexOf("modal-body")!==-1){
                                    modal_confirmation_cancel_view_url = "";
                                    modal_confirmation_cancel_after_submit = function(){
                                        window.location.href = APP_WINDOW.current_url();
                                    }
                                 }'
                            ."}"
                            .'else{'
                                .'$("#'.(str_replace('status','cancellation_reason',$input_select_props->id)).'").closest(".form-group").show();'
                            .'}'
                        
                        .'}'
                        .' else{ '
                            .'$("#'.(str_replace('status','cancellation_reason',$input_select_props->id)).'").closest(".form-group").hide();'
                        .'}'
                    .' });'
                .'';
            }
        }       

        
        public function html_render_first(){    
            $input_select_props = $this->input_select_properties;
            $div_props = $this->div_properties;
            $input_select_attrib = '';
            $div_attrib = '';
            
            foreach($input_select_props->attrib as $key=>$val){
                $input_select_attrib.=$key.'="'.$val.'"';
            }
            
            foreach($div_props->attrib as $key=>$val){
                $div_attrib.=$key.'="'.$val.'"';
            }
            
            $output = "";
            $checkbox = $input_select_props->checkbox?'<input id="'.$input_select_props->id.'_checkbox" type="checkbox">':'';
            $output.='

                    <div class="'.$this->div_properties->class.' 
                        '.($this->input_select_properties->hide_all?'hide_all':'').'" 
                        style="'.($this->div_properties->hide?' display:none ':'').$this->div_properties->style.'"
                        id="'.$this->div_properties->id.'" '.$div_attrib.'>
                        '.$checkbox.'
                            '.($this->input_select_properties->no_label?
                                '':'<label>'.$input_select_props->label.'</label>').' 
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="'.$input_select_props->icon.'">'.$input_select_props->icon_label.'</i>
                            </span>
                            <input 
                                id = "'.$input_select_props->id.'" 
                                name="'.$input_select_props->name.'"
                                class="'.$input_select_props->class.' 
                                    '.($input_select_props->disable_all?'disable_all':'').'"
                                type="hidden"
                                '.$input_select_attrib.'
                                autocomplete="off" 
                                disable_all_type="'.$input_select_props->disable_all_type.'"
                                old_val = ""
                            >
                            
                        </div>
                    </div>
 
            ';
            
            if($input_select_props->is_module_status){
                
                $output.='
                    <div class="'.$this->div_properties->class.' hide_all'
                        .'" 
                        style="display:none"
                        >'
                        
                        .($this->input_select_properties->no_label?
                            '':'<label>Cancellation Reason</label>').' 
                        
                            
                            <textarea '
                                .'id = "'.str_replace('status','cancellation_reason',$input_select_props->id).'" 
                                name="'.str_replace('status','cancellation_reason',$input_select_props->name).'"
                                class="form-control disable_all'.'"
                                type="hidden"
                                '.$input_select_attrib.'
                                 rows="5"
                                disable_all_type="common"
                            ></textarea>
                            
                        
                    </div>
                ';
            }
            
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            
            return $output;
             
        }
    }
?>
