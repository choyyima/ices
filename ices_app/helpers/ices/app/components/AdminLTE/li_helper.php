<?php
    get_instance()->load->helper('app/components/custom_component');
    class Li extends Component_Engine{
        
        private $li_properties=array(
            "class"=>""
        );
        
        public function __construct(){
            parent::__construct();
            $this->li_properties = json_decode(json_encode($this->li_properties));
            
        }
        
        public function li_class($data){
            $this->li_properties->class = $data;
            return $this;
        }
        
        public function html_render_first(){     
            $output='<li class="'.$this->li_properties->class.'">';            
            
            return $output;
        }
        
        public function html_render_second(){
            $output="</li>"; 
            $param = array();
            return $output;
             
        }
    }
?>
