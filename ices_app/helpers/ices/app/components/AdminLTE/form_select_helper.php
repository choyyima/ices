<?php
    
    class Form_Select extends Component_Engine{
        
        private $form_select_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>""
            ,'options'=>array()
            ,'label'=>''
            ,'icon'=>''
            ,'name'=>''
            ,'value'=>''
        );
        
        
        
        public function __construct(){
            parent::__construct();
            $this->form_select_properties = json_decode(json_encode($this->form_select_properties));
            $this->form_select_properties->id = $this->generate_id();
        }
        
        public function options_add($data){
            $props = $this->form_select_properties;
            foreach($data as $row){
                $opt = array(
                    "id"=>$this->generate_id()
                    ,"value"=>""       
                    ,"label"=>""
                );

                if(isset($row['id'])) $opt['id'] = $row['id'];
                if(isset($row['value'])) $opt['value'] = $row['value'];
                if(isset($row['label'])) $opt['label'] = $row['label'];
                $opt = json_decode(json_encode($opt));
                $props->options[]=$opt;
            }
            return $this;
        }
        
        public function form_select_set($method,$data){
            $props = $this->form_select_properties;
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
                case 'label':
                    $props->label = $data;
                    break;
                case 'options_add':
                    foreach($data as $row){
                        $opt = array(
                            "id"=>$this->generate_id()
                            ,"value"=>""       
                            ,"label"=>""
                        );
                    
                        if(isset($row['id'])) $opt['id'] = $row['id'];
                        if(isset($row['value'])) $opt['value'] = $row['value'];
                        if(isset($row['label'])) $opt['label'] = $row['label'];
                        $opt = json_decode(json_encode($opt));
                        $props->options[]=$opt;
                    }
                    break;
                case 'icon':
                    $props->icon = $data;
                    break;
                case 'name':
                    $props->name = $data;
                    break;
                case 'value':
                    $props->value = $data;
                    break;
                
            }
            return $this;
        }
        
        public function options_generate(){
            $result = "";
            foreach($this->form_select_properties->options as $opt){
                $selected ='';
                if($opt->value == $this->form_select_properties->value){
                    $selected = 'selected';
                            
                };
                $result.='<option id="'.$opt->id.'" value="'.$opt->value.'" '.$selected.'>';
                $result.=$opt->label;
                $result.="</option>";
            }

            return $result;
        }
        
        public function html_render_first(){     
            $form_select_props = $this->form_select_properties;
            $span_class = strlen($form_select_props->span)>0?'col-xs-'.$form_select_props->span:'';
            
            $options = $this->options_generate();
            
            $output = "";
            $output.='
                
                    <div class="form-group ">
                        <label>'.$form_select_props->label.'</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="'.$form_select_props->icon.'"></i>
                            </span>
                            <select 
                                id = "'.$form_select_props->id.'" 
                                class=" '.$form_select_props->class.' '.$span_class.'"
                                name = "'.$form_select_props->name.'"
                                    
                            >
                                '.$options.'
                            </select>
                        </div>
                    </div>
                   
            ';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            
            return $output;
             
        }
    }
?>
