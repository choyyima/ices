<?php
    
    class Hr extends Component_Engine{
        
        private $hr_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>""
        );
        
        public function __construct(){
            parent::__construct();
            $this->hr_properties = json_decode(json_encode($this->hr_properties));
            $this->hr_properties->id = $this->generate_id();
        }
        
        public function hr_set($method,$data){
            $props = $this->hr_properties;
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
            }
            return $this;
        }
        
        public function html_render_first(){     
            $hr_props = $this->hr_properties;
            $span_class = strlen($hr_props->span)>0?'col-xs-'.$hr_props->span:'';
            
            $output = "";
            $output.='<hr 
                id = "'.$hr_props->id.'" 
                class="'.$hr_props->class.' '.$span_class.'"
            >';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='</hr>';
            return $output;
             
        }
    }
?>
