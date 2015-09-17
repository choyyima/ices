<?php
    
    class Img extends Component_Engine{
        
        private $img_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>""
            ,"src"=>""
            ,"alt"=>""
            ,"style"=>""
        );
        
        public function __construct(){
            parent::__construct();
            $this->img_properties = json_decode(json_encode($this->img_properties));
            $this->img_properties->id = $this->generate_id();
        }
        
        public function img_set($method,$data){
            $props = $this->img_properties;
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
                case 'src':
                    $props->src=$data;
                    break;
                case 'alt':
                    $props->alt=$data;
                    break;
                case 'style':
                    $props->style=$data;
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){     
            $img_props = $this->img_properties;
            $span_class = strlen($img_props->span)>0?'col-xs-'.$img_props->span:'';
            
            $output = "";
            $output.='<img 
                id = "'.$img_props->id.'" 
                class="'.$img_props->class.' '.$span_class.'"
                src="'.$img_props->src.'"
                alt="'.$img_props->alt.'"
                style=\''.$img_props->style.'\'
            >';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='</img>';
            return $output;
             
        }
    }
?>
