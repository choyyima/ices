<?php
    get_instance()->load->helper('app/components/custom_component');
    class Custom_Component extends Component_Engine{
        
        private $custom_component_properties=array(
            'src'=>''
            ,'load_view'=>true
            ,'innerHTML'=>''
            ,'param'=>array()
        );
        
        public function __construct(){
            parent::__construct();
            $this->custom_component_properties = json_decode(json_encode($this->custom_component_properties));

        }
        
        public function custom_component_set($method,$data){
            switch($method){
                case 'innerHTML':
                    $this->custom_component_properties->innerHTML = $data;
                    break;
            }
            return $this;
        }
        
        public function load_view($data){
            $this->custom_component_properties->load_view = $data;
            return $this;
        }
        
        public function src_set($data){
            $this->custom_component_properties->src = $data;
            return $this;
        }
        
        public function param_set($data){
            $this->custom_component_properties->param = $data;
            return $this;
        }
        
        public function html_render_first(){     
            $output='';
            $props = $this->custom_component_properties;
            if($props->load_view){
                $output.= get_instance()->load->view($props->src,$props->param,TRUE);
            }
            else{
                $output.=$props->innerHTML;
            }
            
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $param = array();
            return $output;
             
        }
    }
?>
