<?php
    
    get_instance()->load->helper('app/components/button_helper');
    class Button_Toggle extends Component_Engine{
        
        private $button_toggle_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>""
            ,"buttons"=>array()
        );
        
        public function __construct(){
            parent::__construct();
            $this->button_toggle_properties = json_decode(json_encode($this->button_toggle_properties));
            $this->button_toggle_properties->id = $this->generate_id();
        }
        
        public function button_toggle_set($method,$data){
            $props = $this->button_toggle_properties;
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
                case 'button_add':
                    $btn = new Button();
                    if(isset($data['id'])) $btn->button_set('id', $data['id']);
                    if(isset($data['value'])) $btn->button_set('value',$data['value']);
                    if(isset($data['icon'])) $btn->button_set('icon',$data['icon']);
                    if(isset($data['class'])) $btn->button_set('class', $data['class']);
                    $props->buttons[]=$btn;
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){     
            $button_toggle_props = $this->button_toggle_properties;
            $span_class = strlen($button_toggle_props->span)>0?'col-xs-'.$button_toggle_props->span:'';
            // btn btn-default btn-xs-5 active
            $output = "";
            $output.='<div class="btn-group '.$this->button_toggle_properties->class.'" data-toggle="btn-toggle">';
            foreach($this->button_toggle_properties->buttons as $btn){
                $output.=$btn->html_render_first();
                $output.=$btn->html_render_second();
            }

            $output.='</div>';
            return $output;
        }
        
        public function html_render_second(){
            $output="";             
            return $output;
             
        }
    }
?>
