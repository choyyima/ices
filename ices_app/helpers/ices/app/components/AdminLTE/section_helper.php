<?php
    
    class Section extends Component_Engine{
        
        private $section_properties=array(
            "id"=>""
            //,"span"=>""
            ,"class"=>""
        );
        
        public function __construct(){
            parent::__construct();
            $this->section_properties = json_decode(json_encode($this->section_properties));
            $this->section_properties->id = $this->generate_id();
        }
        
        public function section_set($method,$data){
            $props = $this->section_properties;
            switch($method){
                /*
                case 'span':
                    $props->span = $data;
                    break;
                 * */
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
            $section_props = $this->section_properties;
            //$span_class = strlen($section_props->span)>0?'col-xs-'.$section_props->span:'';
            $span_class = '';
            $output = "";
            $output.='<section 
                id = "'.$section_props->id.'" 
                class="'.$section_props->class.' '.$span_class.'"
            >';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='</section>';
            return $output;
             
        }
    }
?>
