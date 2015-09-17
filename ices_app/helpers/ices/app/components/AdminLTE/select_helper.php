<?php
    
    class Select extends Component_Engine{
        
        private $select_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>"form-control"
            ,'options'=>array()
            ,'label'=>''
            ,'icon'=>''
            ,'name'=>''
        );
        
        
        
        public function __construct(){
            parent::__construct();
            $this->select_properties = json_decode(json_encode($this->select_properties));
            $this->select_properties->id = $this->generate_id();
        }
        
        public function options_add($data){
            $props = $this->select_properties;
            foreach($data as $row){
                $opt = array(
                    "id"=>$this->generate_id()
                    ,"value"=>""       
                    ,"label"=>""
                );

                if(isset($row['id'])) $opt['id'] = $row['id'];
                if(isset($row['value'])) $opt['value'] = $row['value'];
                if(isset($row['label'])) $opt['label'] = $row['label'];
                $opt = json_decode(json_encode($opt));
                $props->options[]=$opt;
            }
            return $this;
        }
        
        public function select_set($method,$data){
            $props = $this->select_properties;
            switch($method){
                case 'span':
                    $props->span = $data;
                    break;
                case 'class':
                    $props->class=$data;
                    break;
                case 'id':
                    $props->id=$data;
                    break;
                case 'label':
                    $props->label = $data;
                    break;
                case 'options_add':
                    foreach($data as $row){
                        $opt = array(
                            "id"=>$this->generate_id()
                            ,"value"=>""       
                            ,"label"=>""
                        );
                    
                        if(isset($row['id'])) $opt['id'] = $row['id'];
                        if(isset($row['value'])) $opt['value'] = $row['value'];
                        if(isset($row['label'])) $opt['label'] = $row['label'];
                        $opt = json_decode(json_encode($opt));
                        $props->options[]=$opt;
                    }
                    break;
                case 'icon':
                    $props->icon = $data;
                    break;
                case 'name':
                    $props->name = $data;
                    break;
            }
            return $this;
        }
        
        public function options_generate(){
            $result = "";
            foreach($this->select_properties->options as $opt){
                $result.='<option id="'.$opt->id.'" value="'.$opt->value.'" class="form-control">';
                $result.=$opt->label;
                $result.="</option>";
            }

            return $result;
        }
        
        public function html_render_first(){     
            $select_props = $this->select_properties;
            $span_class = strlen($select_props->span)>0?'col-xs-'.$select_props->span:'';
            
            $options = $this->options_generate();
            
            $output = "";
            $output.='
                <div class="row">
                    <div class="form-group col-xs-12">
                        <label>'.$select_props->label.'</label>
                        <div class="">
                            <select 
                                id = "'.$select_props->id.'" 
                                class=" '.$select_props->class.' '.$span_class.'"
                                name = "'.$select_props->name.'"
                                style="max-width:400px;"
                            >
                                '.$options.'
                            </select>
                        </div>
                    </div>
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
