<?php
    
    class Form_Group extends Component_Engine{
        public $form_properties=array(
            'attrib'=>array()
            ,'hide'=>false
        );
        
        function __construct(){
            parent::__construct();
            $this->form_properties = json_decode(json_encode($this->form_properties));
        }
        
        public function attrib_set($data){
            foreach($data as $key=>$val){
                foreach($data as $key=>$val){
                    $this->form_properties->attrib[$key] = $val;                        
                }                    
            }
            return $this;
        }
        
        public function div_set($method,$data){
            switch($method){
                case 'hide':
                    $this->form_properties->hide = $data;
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){            
            $output = "";
            $attrib = '';
            foreach($this->form_properties->attrib as $key=>$val){
                $attrib.= $key.'="'.$val.'"';
            }
            
            $output.='
                <div class="form-group" '.$attrib.'
                    style="'.($this->form_properties->hide?' display:none ':'').'"
                >
                    
            ';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='</div>';
            return $output;
             
        }
    }
?>
