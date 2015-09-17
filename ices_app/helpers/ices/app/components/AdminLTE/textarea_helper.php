<?php
    
    class TextArea extends Component_Engine{
        public $textarea_properties = array(
            "label"=>""
            ,"textarea_value"=>""
            ,"textarea_placeholder"=>""
            ,"textarea_name"=>""
            ,"textarea_id"=>""
            ,"textarea_allow_empty"=>true
            ,"textarea_icon"=>""
            ,'textarea_class'=>'form-control'
            ,'textarea_rows'=>"5"
            ,'attrib'=>array()
            ,'hide_all'=>false
            ,'disable_all'=>false
        );
        
        public $div_properties = array(
            "class"=>""
            ,"id"=>""
            ,'hide'=>false
        );
        
        
        function __construct(){
            parent::__construct();
            $this->textarea_properties = json_decode(json_encode($this->textarea_properties));
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->textarea_properties->textarea_id = $this->generate_id();
            $this->div_properties->id = $this->generate_id();
        }
        
        public function div_set($method = "", $data=""){
            switch($method){
                case 'id':
                    $this->div_properties->id = $data;
                    break;
                case 'class':
                    $this->div_properties->class = $data;
                    break;
                case 'hide':
                    $this->div_properties->hide = $data;
                    break;
            }
            return $this;
        }       
                
        public function textarea_set($method="",$data=""){
            switch($method){
                case 'id':
                    $this->textarea_properties->textarea_id = $data;
                    break;
                case 'label':
                    $this->textarea_properties->label=$data;
                    break;
                case 'name':
                    $this->textarea_properties->textarea_name = $data;
                    break;
                case 'value':
                    $this->textarea_properties->textarea_value = $data;
                    break;
                case 'attrib':
                    foreach($data as $key=>$val){
                        $this->textarea_properties->attrib[$key] = $val;                        
                    }                    
                    break;
                case 'class':
                    $this->textarea_properties->textarea_class=$data;
                    break;
                case 'placeholder':
                    $this->textarea_properties->textarea_placeholder=$data;
                    break;
                case 'hide_all':
                    $this->textarea_properties->hide_all = $data;
                    break;
                case 'disable_all':
                    $this->textarea_properties->disable_all = $data;
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){            
            $output = "";
            $icon = "";
            $textarea_attrib = "";
            $div_attrib = "";
            foreach($this->textarea_properties->attrib as $key=>$val){
                $textarea_attrib.=$key.'="'.$val.'"';
            }

            $textarea = '<textarea class="'.$this->textarea_properties->textarea_class.'
                         '.($this->textarea_properties->disable_all?'disable_all':'').'" 
                        id="'.$this->textarea_properties->textarea_id.'"
                        placeholder="'.$this->textarea_properties->textarea_placeholder.'"
                        name = "'.$this->textarea_properties->textarea_name.'"    
                        
                        rows="'.$this->textarea_properties->textarea_rows.'"
                        disable_all_type="common"    
                        autocomplete="off"
                        '.$textarea_attrib.'
                            
                        ></textarea>';
            $output.='
                    <div id="'.$this->div_properties->id.'" 
                        class="form-group  '.$this->div_properties->class.' 
                            '.($this->textarea_properties->hide_all?'hide_all':'').'"
                        style="'.($this->div_properties->hide?' display:none ':'').'"
                        >
                        <label>'.$this->textarea_properties->label.'</label>&nbsp
                        <small id= "'.$this->textarea_properties->textarea_id.'_badge" class="badge bg-yellow hide">Must be filled out</small>        
                        '.$textarea.'  
                   </div>
                ';
            
            $this->generate_additional_script();
            
            return $output;
        }
        
        protected function generate_additional_script(){
           $this->additional_script='
               $("#'.$this->textarea_properties->textarea_id.'").val('.json_encode($this->textarea_properties->textarea_value).');
            ';
        }
        
        public function html_render_second(){
            $output=""; 
            return $output;             
        }
    }
?>
