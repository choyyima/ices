<?php
    get_instance()->load->helper('app/components/custom_component');
    class Ul extends Component_Engine{
        
        private $ul_properties=array(
            "id"=>''
            ,"class"=>""
            ,"style"=>""
        );
        
        public function __construct(){
            parent::__construct();
            $this->ul_properties = json_decode(json_encode($this->ul_properties));
            
        }
        
        public function ul_set($method, $data){
            switch($method){
                case 'id':
                    $this->ul_properties->id = $data; 
                    break;
                case 'class':
                    $this->ul_properties->class = $data;
                    break;
                    
            }
            return $this;
        }
        
        public function ul_class($data){
            $this->ul_properties->class = $data;
            return $this;
        }
        
        public function ul_style($data){
            $this->ul_properties->style =$data;
            return $this;
        }
        
        public function html_render_first(){     
            $output='
                <ul id="'.$this->ul_properties->id.'" class="'.$this->ul_properties->class.'"  
                    style="'.$this->ul_properties->style.'" 
                >
            ';            
            
            return $output;
        }
        
        public function html_render_second(){
            $output="</ul>"; 
            $param = array();
            return $output;
             
        }
    }
?>
