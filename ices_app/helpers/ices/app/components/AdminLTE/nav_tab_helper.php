<?php
    
    class Nav_Tab extends Component_Engine{
        
        private $nav_tab_properties = array(
                "id"=>""
                ,"items"=>array()
                
        );
               
        public function __construct(){
            parent::__construct();
            $this->nav_tab_properties = json_decode(json_encode($this->nav_tab_properties));
        }
        
        public function nav_tab_set($method,$data){
            $props = $this->nav_tab_properties;
            switch($method){
                case 'items_add':
                    $item = array(
                        "id"=>""
                        ,"value"=>""
                        ,"class"=>""
                    );
                    
                    if(isset($data['id'])) if(strlen($data['id'])>0) $item['id'] = $data['id'];   
                    if(isset($data['value'])) if(strlen($data['value'])>0) $item['value'] = $data['value'];
                    if(isset($data['class'])) if(strlen($data['class'])>0) $item['class'] = $data['class'];
                    $item = json_decode(json_encode($item));
                    $props->items[] = $item;
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){            
            $nav_header = "";
            $nav_props = $this->nav_tab_properties;
            foreach($nav_props->items as $item){
                $nav_header.='<li class ="'.$item->class.'" >';
                $nav_header.='<a href="'.$item->id.'" data-toggle="tab">'.$item->value.'</a>';
                $nav_header.='</li>';
            }
            
            $output = "";
            $output.='
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">                    
            ';
            $output.=$nav_header;
            $output.='</ul> <div class="tab-content">';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='</div></div>';
            return $output;
             
        }
    }
?>
