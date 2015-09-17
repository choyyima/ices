<?php
    
    class Button_Group extends Component_Engine{
        
        private $button_group_properties=array(
            "id"=>""
            ,"value"=>""
            ,'div_class'=>'btn-group'
            ,"class"=>"btn btn-default dropdown-toggle"
            ,"icon"=>""
            ,"href"=>""
            ,"style"=>""
            ,"confirmation"=>false
            ,"confirmation_msg"=>"Are you sure want to perform this action?"
            ,'attrib'=>array()
            ,'disable_after_click'=>true
            ,'script'=>''
            ,'type'=>'button'
            ,'item_list'=>array()
        );
        
        function __construct(){
            parent::__construct();
            $this->button_group_properties = json_decode(json_encode($this->button_group_properties));
            $this->button_group_properties->id = $this->generate_id();
        }
        
        public function button_group_set($method,$data){
            $btn_props = $this->button_group_properties;
            switch($method){
                case 'id':                    
                    if(strlen($data)>0) $btn_props->id=$data;
                    break;
                case 'class':                 
                    switch($data){
                        case 'primary': $btn_props->class='btn btn-primary'; break;
                        case 'danger': $btn_props->class='btn btn-danger'; break;
                        case 'info': $btn_props->class='btn btn-info'; break;
                        default: $btn_props->class=$data;break;
                    };
                    break;
                case 'div_class':
                    $btn_props->div_class=$data;
                    break;
                case 'value':
                    $btn_props->value = $data;
                    break;
                case 'icon':
                    $btn_props->icon = $data;
                    break;
                case 'href':
                    $btn_props->href = $data;
                    break;
                case 'style':
                    $btn_props->style = $data;
                    break;
                case 'confirmation':
                    $btn_props->confirmation = $data;
                    break;
                case 'confirmation msg':
                    $btn_props->confirmation_msg = $data;
                    break;
                case 'attrib':
                    foreach($data as $key=>$val){
                        $btn_props->attrib[$key] = $val;                        
                    }                    
                    break;
                case 'disable_after_click':
                    $btn_props->disable_after_click = $data;
                    break;
                case 'script':
                    $btn_props->script = $data;
                    break;
                case 'type':
                    $btn_props->type = $data;
                    break;
                case 'item_list_add':
                    $temp_list = array(
                        'id'=>$data['id'],
                        'label'=>$data['label'],                        
                        'class'=>$data['class'],  
                    );                    
                    $btn_props->item_list[] = $temp_list;
                    break;
                    
            };
            return $this;
        }
        
        
        private function item_generate(){
            $result = "";
            ;
            foreach($this->button_group_properties->item_list as $item){                        
                
                $result.=' <li><a href="#" id="'.$item['id'].'" style="text-align:center" >'.
                    '<i class="'.$item['class'].'"></i>'.
                    $item['label'].                    
                    '</a></li>';
                
            }
            
            return $result;
        }
        
        public function html_render_first(){            
            $output = "";
            $btn_group_props = $this->button_group_properties;
            
            $btn_group_attrib = '';
            
            foreach($btn_group_props->attrib as $key=>$val){
                $btn_group_attrib.=$key.'="'.$val.'"';
            }
            
            $output.='
                <div class="'.$btn_group_props->div_class.'">
                <button id = "'.$btn_group_props->id.'" type="button" 
                    class="'.$btn_group_props->class.'" data-toggle="dropdown">
                    <i class="'.$btn_group_props->icon.'"></i>
                    <span class="">'.$btn_group_props->value.'</span>
                    <span class="caret"></span>
                    
                </button>
                <ul class="dropdown-menu pull-right" role="menu">  
                '.$this->item_generate().'
            ';
            return $output;
        }
        
        public function html_render_second(){
            $output="
                </ul>
                </div> 
                "; 
            $this->generate_additional_script();
            return $output;
             
        }
        
        protected function generate_additional_script(){
            
            
        }
    }
?>
