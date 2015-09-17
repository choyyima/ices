<?php
    
    class Input_Select_Table extends Component_Engine{
        
        private $path = array(
            "view"=>'templates/app/components/input_select_table/'
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
            ,'hide_all'=>false
        );
        
        private $table_properties = array(
            "id"=>''
            ,"columns"=>array()
            ,'ajax_url'=>''
            ,'column_key'=>''
            ,'allow_duplicate_id'=>false
            ,'selected_value'=>array()
            ,'max_selected_item'=>0
        );
        
        
        
        public function __construct(){
            parent::__construct();
            $this->input_select_properties = json_decode(json_encode($this->input_select_properties));
            $this->table_properties = json_decode(json_encode($this->table_properties));
            $this->path = json_decode(json_encode($this->path));
            
            $this->input_select_properties->id = $this->generate_id();
            $this->table_properties->id = $this->generate_id();
        }
        
        public function table_set($method,$data){
            $tbl_props = $this->table_properties;
            switch($method){
                case 'max_selected_item':
                    $tbl_props->max_selected_item = $data;
                    break;
                case 'columns':
                    foreach($data as $row){
                        $data_temp = array(
                            "name"=>$row['name']
                            ,"label"=>$row['label']
                            ,'type'=>isset($row['type'])?$row['type']:'text'
                            ,'filter'=>isset($row['filter'])?$row['filter']:''
                            ,'value'=>isset($row['value'])?$row['value']:''
                        );
                        $tbl_props->columns[]=$data_temp;
                    }
                    break;
                case 'id':
                    $tbl_props->id = $data;
                    break;
                case 'column_key':
                    $tbl_props->column_key = $data;
                    break;
                case 'ajax_url':
                    $tbl_props->ajax_url = $data;
                    break;
                case 'allow_duplicate_id':
                    $tbl_props->allow_duplicate_id = $data;
                    break;
                case 'selected_value':
                    $tbl_props->selected_value = $data;
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
                case 'hide_all':
                    $props->hide_all = $data;
                    break;

            }
            return $this;
        }
        
        public function additional_script_render(){
            $input_select_props = $this->input_select_properties;
            $tbl_props = $this->table_properties;
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
                ,'tbl_ajax_url'=>$tbl_props->ajax_url
                ,'raw_data'=>$raw_data
                ,'min_length'=>$input_select_props->min_length
                ,'value'=>$input_select_props->value
                ,'allow_empty'=>false
                ,'table_columns'=>$tbl_props->columns
                ,'table_id'=>$tbl_props->id
                ,'table_allow_duplicate_id'=>$tbl_props->allow_duplicate_id
                ,'selected_value'=>$tbl_props->selected_value
                ,'max_selected_item'=>$tbl_props->max_selected_item
                
            );
            $js = str_replace(array("<script>","</script>"),""
                    ,get_instance()->load->view($this->path->view.'input_select_table_js'
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
                "table_columns"=>$this->table_properties->columns
                ,"table_id"=>$this->table_properties->id
                ,'table_column_key'=>$this->table_properties->column_key
            );

            $table = get_instance()->load->view($this->path->view.'input_select_table',$param,TRUE);
            $output = "";
            $output.='

                    <div class="form-group '.($input_select_props->hide_all?'hide_all':'').'">
                        <label>'.$input_select_props->label.'</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="'.$input_select_props->icon.'"></i>
                            </span>
                            <input 
                                id = "'.$input_select_props->id.'" 
                                name="'.$input_select_props->name.'"
                                class="'.$input_select_props->class.' "
                                type="hidden"
                                autocomplete="off"
                            >
                            </input>
                        </div>
                        '.$table.'
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
