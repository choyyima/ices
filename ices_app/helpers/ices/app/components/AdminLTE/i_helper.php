<?php
    get_instance()->load->helper('app/components/custom_component');
    class I extends Component_Engine{
        
        private $i_properties=array(
            "class"=>""
        );
        
        public function __construct(){
            parent::__construct();
            $this->i_properties = json_decode(json_encode($this->i_properties));
            
        }
        
        public function i_class($data){
            $this->i_properties->class = $data;
            return $this;
        }
        
        public function html_render_first(){     
            $output='<i class="'.$this->i_properties->class.'">';            
            
            return $output;
        }
        
        public function html_render_second(){
            $output="</i> "; 
            $param = array();
            return $output;
             
        }
    }
?>
