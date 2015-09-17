<?php
    
    class Span extends Component_Engine{
        
        private $span_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>""
            ,'value'=>""
        );
        
        public function __construct(){
            parent::__construct();
            $this->span_properties = json_decode(json_encode($this->span_properties));
            $this->span_properties->id = $this->generate_id();
        }
        
        public function span_set($method,$data){
            $props = $this->span_properties;
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
            }
            return $this;
        }
        
        public function html_render_first(){     
            $span_props = $this->span_properties;
            $span_class = strlen($span_props->span)>0?'col-xs-'.$span_props->span:'';
            
            $output = "";
            $output.='<span 
                id = "'.$span_props->id.'" 
                class="'.$span_props->class.' '.$span_class.'"
            >'.str_replace(' ','&nbsp',$span_props->value);
            return $output;
        }
        
        public function html_render_second(){
            $output="</span>"; 
            return $output;
             
        }
    }
?>
