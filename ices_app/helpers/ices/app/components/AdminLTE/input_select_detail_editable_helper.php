<?php
    get_instance()->load->helper('app/components/component_engine');
    class Input_Select_Detail_Editable extends Component_Engine{
        
        private $path = array(
            "view"=>'templates/app/components/input_select_detail_editable/'
        );
        
        private $div_properties = array(
            'id'  =>''
            ,'hide'=>false
        );
        
        private $input_select_properties=array(
            "id"=>""
            ,"name"=>""
            ,"class"=>""
            ,'label'=>''
            ,'icon'=>''
            ,"data"=>array()
            ,'ajax_url'=>""
            ,'min_length'=>'1'
            ,'value'=>array("id"=>"","data"=>"")
            ,'attrib'=>array()
            ,'disable_all'=>false
            ,'hide_all'=>false
        );
        
        private $detail_properties = array(
            "id"=>''
            ,"rows"=>array()
            ,'ajax_url'=>''
            ,'button_new_id'=>''
            ,'button_edit_id'=>''
            ,'button_new'=>false
            ,'button_edit'=>false
            ,'button_new_target'=>''
            ,'button_edit_target'=>''
        );
        
        
        
        public function __construct(){
            parent::__construct();
            $this->input_select_properties = json_decode(json_encode($this->input_select_properties));
            $this->detail_properties = json_decode(json_encode($this->detail_properties));
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->path = json_decode(json_encode($this->path));
            
            $this->input_select_properties->id = $this->generate_id();
            $this->detail_properties->id = $this->generate_id();
            $this->div_properties->id = $this->generate_id();
        }
        
        public function div_set($method,$data){
            switch($method){
                case 'id':
                    $this->div_properties->id = $data;
                    break;
                case'hide':
                    $this->div_properties->hide = $data;
                    break;
            }
            return $this;
        }
        
        public function detail_editable_set($method,$data){
            $tbl_props = $this->detail_properties;
            switch($method){
                case 'rows':
                    foreach($data as $row){
                        $data_temp = array(
                            'id'=>isset($row['id'])? $row['id']:$this->generate_id()
                            ,"name"=>$row['name']   
                            ,"label"=>$row['label']
                            ,"type"=>$row['type']
                            ,'attribute'=>isset($row['attribute'])?$row['attribute']:''
                        );
                        if(strlen($data_temp['id']) == 0) $data_temp['id'] = 'input_select_detail_editable_'.$this->generate_id();
                        $tbl_props->rows[]=$data_temp;
                    }
                    break;
                case 'id':
                    $tbl_props->id = $data;
                    break;
                case 'ajax_url':
                    $tbl_props->ajax_url = $data;
                    break;
                case 'button_new':
                    $tbl_props->button_new = $data;
                    break;
                case 'button_edit':
                    $tbl_props->button_edit = $data;
                    break;
                case 'button_new_target':
                    $tbl_props->button_new_target = $data;
                    break;
                case 'button_edit_target':
                    $tbl_props->button_edit_target = $data;
                    break;
                case 'button_new_id':
                    $tbl_props->button_new_id = $data;
                    break;
                case 'button_edit_id':
                    $tbl_props->button_edit_id = $data;
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
                case 'ajax_url':
                    $props->ajax_url = $data;
                    break;
                case 'min_length':
                    $props->min_length=$data;
                    break;
                case 'value':
                    $val =array("id"=>"","text"=>"");
                    if(isset($data['id'])) $val['id']=$data['id'];
                    $val['text']=isset($data['data'])?$data['data']:(isset($data['text'])?$data['text']:'');
                    $props->value = $val;
                    break;   
                case 'attrib':
                    foreach($data as $key=>$val){
                        $this->input_select_properties->attrib[$key] = $val;                        
                    }                    
                    break;
                case 'disable_all':
                    $this->input_select_properties->disable_all = $data;
                    break;
                case 'hide_all':
                    $this->input_select_properties->hide_all = $data;
                    break;
                    
            }
            return $this;
        }
        
        public function additional_script_render(){
            $input_select_props = $this->input_select_properties;
            $detail_props = $this->detail_properties;
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
                ,'detail_rows'=>$detail_props->rows
                ,'detail_id'=>$detail_props->id
                ,'detail_ajax_url'=>$detail_props->ajax_url
                ,'allow_empty'=>false
                
            );
            
            
            $js= str_replace(array("<script>","</script>"),""
                    ,get_instance()->load->view($this->path->view.'input_select_detail_editable_js'
                        ,$param 
                        ,TRUE)
                    ); 
            
            $js.= str_replace(array("<script>","</script>"),"",get_instance()->load->view('templates/app/components/input_select_js'
                        ,$param 
                        ,TRUE)); 
            
            
            $this->additional_script.=$js;
            
        }       

        
        public function html_render_first(){     
            $input_select_props = $this->input_select_properties;
            
            $param = array(
                "detail_rows"=>$this->detail_properties->rows
                ,"detail_id"=>$this->detail_properties->id  
                ,'button_new'=>$this->detail_properties->button_new
                ,'button_edit'=>$this->detail_properties->button_edit
                ,'button_new_target'=>$this->detail_properties->button_new_target
                ,'button_edit_target'=>$this->detail_properties->button_edit_target
                ,'button_new_id'=>$this->detail_properties->button_new_id
                ,'button_edit_id'=>$this->detail_properties->button_edit_id
                ,'disable_all'=>$this->input_select_properties->disable_all
            );
            
            $input_select_attrib = '';
            
            foreach($input_select_props->attrib as $key=>$val){
                $input_select_attrib.=$key.'="'.$val.'"';
            }
            
            $detail = '';
            $detail = get_instance()->load->view($this->path->view.'input_select_detail_editable',$param,TRUE);
            $output = "";
            $output.='

                    <div class="form-group '.($this->div_properties->hide?' hide ':'')
                            .($this->input_select_properties->hide_all?'hide_all ':'')
                        .'"'
                        .' id="'.$this->div_properties->id.'">
                        <label>'.$input_select_props->label.'</label>
                        <div class="input-group" style="margin-bottom:6px">
                            <span class="input-group-addon">
                                <i class="'.$input_select_props->icon.'"></i>
                            </span>
                            <input 
                                id = "'.$input_select_props->id.'" 
                                name="'.$input_select_props->name.'"
                                class="'.$input_select_props->class.' '
                                    .($input_select_props->disable_all?'disable_all':'')
                                .'"
                                disable_all_type="select2"
                                type="hidden"
                                autocomplete="off"
                                '.$input_select_attrib.'
                            >
                            </input>
                        </div>
                        '.$detail.'
                    </div>
                    
 
            ';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            
            return $output;
             
        }
    }
?>
