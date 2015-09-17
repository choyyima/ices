<?php
    
    class Datetimepicker extends Component_Engine{
        public $properties = array(
            'id'=>'',
            'value'=>'',
            'label'=>'',
            'name'=>'',
            'disable_all'=>'',
            'disable_all_type'=>'common',
            'format'=>'F d, Y H:i',
            'hide_all'=>false,
        );
        
        public $div_properties = array(
            'id'=>''
            ,'hide'=>false
        );
        
        function __construct(){
            parent::__construct();
            $this->properties = json_decode(json_encode($this->properties));
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->properties->id = $this->generate_id();
            $this->div_properties->id = $this->generate_id();
        }
        public function div_set($method="",$data=""){
            switch($method){
                case 'attrib':
                    break;
                case 'id':
                    $this->div_properties->id = $data;
                    break;
                case 'hide':
                    $this->div_properties->hide = $data;
                    break;
            }
            return $this;
        }
        public function datetimepicker_set($method="",$data=""){
            switch($method){
                case 'id':
                    $this->properties->id = $data;
                    break;
                case 'label':
                    $this->properties->label=$data;
                    break;
                case 'value':
                    $this->properties->value = $data;
                    break;
                case 'name':
                    $this->properties->name = $data;
                    break;
                case 'disable_all':
                    $this->properties->disable_all = $data;
                    break;
                case 'format':
                    $this->properties->format = $data;
                    break;
                case 'hide_all':
                    $this->properties->hide_all = $data;
                    break;
                    
            }
            return $this;
        }
        
        public function html_render_first(){            
            $output = "";
            $icon = "";
            $input_attrib = "";
            $div_attrib = "";
            $input = '<input type="text" 
                        name="'.$this->properties->name.'"
                        id="'.$this->properties->id.'"
                        class="form-control '
                            .($this->properties->disable_all?'disable_all ':'')
                            
                        .'"
                        disable_all_type="'.$this->properties->disable_all_type.'"
                        >';
            
            $output.=''
                .'<div class="form-group '
                    .($this->properties->hide_all?'hide_all':'')
                    .'"'
                    .'style="'.($this->div_properties->hide?' display:none ':'')
                    .'"'
                 .'id = "'.$this->div_properties->id.'">'
                    .'<label>'.$this->properties->label.'</label>'
                .'';
            
            
            $output.='
                <div class="input-group" >
                    <span class="input-group-addon">
                    ';
            
            $output.='<i class="fa fa-calendar"></i>';
            $output.='</span>'.$input.'</div>';

            
            $this->generate_additional_script();
            
            return $output;
        }
        
        protected function generate_additional_script(){
            // $this->additional_script.='$("[data-mask]").inputmask();';
            $this->additional_script='';
            $this->additional_script.='
                $("#'.$this->properties->id.'").datetimepicker({value:"'.$this->properties->value.'",'
                    . 'format:"'.$this->properties->format.'"});
                $("#'.$this->properties->id.'").on("blur",function(){
                   '.
                    ($this->properties->value!==''?
                    '  
                   if($(this).val().length<1){
                        $(this).datetimepicker({value:"'.$this->properties->value.'",'
                    . 'format:"'.$this->properties->format.'"});
                    }':''   
                    )
                .' });
            ';
            
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='</div>';
            return $output;             
        }
    }
?>
