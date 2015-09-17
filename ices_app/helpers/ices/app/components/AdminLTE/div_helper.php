<?php
    
    class Div extends Component_Engine{
        
        private $div_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>""
            ,'value'=>''
            ,'attrib'=>array()
        );
        
        public function __construct(){
            parent::__construct();
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->div_properties->id = $this->generate_id();
        }
        
        public function div_set($method,$data){
            $props = $this->div_properties;
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
                        $this->div_properties->attrib[$key] = $val;                        
                    }                    
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){     
            $div_props = $this->div_properties;
            $span_class = strlen($div_props->span)>0?'col-xs-'.$div_props->span:'';
            $attrib='';
            foreach($div_props->attrib as $key=>$val){
                $attrib.=$key.'="'.$val.'"';
            }
            
            $output = "";
            $output.='<div 
                id = "'.$div_props->id.'" 
                class="'.$div_props->class.' '.$span_class.'"
                '.$attrib.'    
            >'.$div_props->value;
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='</div>';
            return $output;
             
        }
    }
?>
