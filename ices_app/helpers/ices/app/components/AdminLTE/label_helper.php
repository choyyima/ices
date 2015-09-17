<?php
    
    class Label extends Component_Engine{
        
        private $label_properties=array(
            "id"=>""
            ,"class"=>""
            ,"value"=>""
            ,'attrib'=>array()
        );
        
        public function __construct(){
            parent::__construct();
            $this->label_properties = json_decode(json_encode($this->label_properties));
            $this->label_properties->id = $this->generate_id();
        }
        
        public function label_set($method,$data){
            $props = $this->label_properties;
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
                case 'value':
                    $props->value = $data;
                    break;
                case 'attrib':
                    foreach($data as $key=>$val){
                        $this->label_properties->attrib[$key] = $val;                        
                    }                    
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){     
            $label_props = $this->label_properties;
            $output = "";
            $attrib = '';
            foreach($label_props->attrib as $key=>$val){
                $attrib.=$key.'="'.$val.'"';
            }
            
            $output.='<label 
                id = "'.$label_props->id.'" 
                class="'.$label_props->class.'"
                '.$attrib.'
            > '.str_replace(' ','&nbsp',$label_props->value);
            return $output;
        }
        
        public function html_render_second(){
            $output=" </label> "; 
            return $output;             
        }
    }
?>
