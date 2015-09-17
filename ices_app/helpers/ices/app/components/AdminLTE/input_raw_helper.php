<?php
    
    class Input_Raw extends Component_Engine{
        public $properties = array(
            "label"=>""
            ,"input_type"=>"text"
            ,"input_value"=>""
            ,"input_placeholder"=>""
            ,"input_name"=>""
            ,"input_id"=>""
            ,"span"=>"12"
            ,"i_class"=>""
            ,"input_mask_type"=>""
            ,"input_allow_empty"=>true
            ,"input_icon"=>""
            ,"hide"=>false
            ,'input_class'=>''
            ,'attrib'=>array()
            ,'is_date_picker'=>false
            ,'is_time_picker'=>false
        );
        
        public $div_properties = array(
            'attrib'=>array()
        );
        
        function __construct(){
            parent::__construct();
            $this->properties = json_decode(json_encode($this->properties));
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->properties->input_id = $this->generate_id();
        }
        public function div_set($method="",$data=""){
            switch($method){
                case 'attrib':
                    foreach($data as $key=>$val){
                        $this->div_properties->attrib[$key] = $val;                        
                    }                    
                    break;
            }
            return $this;
        }
        public function input_raw_set($method="",$data=""){
            switch($method){
                case 'id':
                    $this->properties->input_id = $data;
                    break;
                case 'label':
                    $this->properties->label=$data;
                    break;
                case 'span':
                    $this->properties->span=$data;
                    break;
                case 'name':
                    $this->properties->input_name = $data;
                    break;
                case 'input_mask_type':
                    $this->properties->input_mask_type=$data;
                    break;
                case 'value':
                    $this->properties->input_value = $data;
                    break;
                case 'allow_empty':
                    $this->properties->input_allow_empty=$data;
                    break;
                case 'icon':
                    $this->properties->input_icon=$data;
                    break;
                case 'hide':
                    $this->properties->hide=$data;
                    break;
                case 'attrib':
                    foreach($data as $key=>$val){
                        $this->properties->attrib[$key] = $val;                        
                    }          
                    break;
                case 'class':
                    $this->properties->input_class=$data;
                    break;
                case 'is_date_picker':
                    $this->properties->is_date_picker=$data;
                    break;
                case 'is_time_picker':
                    $this->properties->is_time_picker=$data;
                    break;
                case 'type':
                    $this->properties->input_type=$data;
                    break;
                
            }
            return $this;
        }
        
        public function html_render_first(){            
            $output = "";
            $icon = "";
            $input_attrib = "";
            $div_attrib = "";
            foreach($this->properties->attrib as $key=>$val){
                $input_attrib.=$key.'="'.$val.'"';
            }
            foreach($this->div_properties->attrib as $key=>$val){
                $div_attrib.=$key.'="'.$val.'"';
            }
            
            $input_mask="";
            if($this->properties->input_mask_type == 'date-picker'){
                $input_mask = 'data-inputmask="\'alias\': \'yyyy-mm-dd\'" data-mask';
            }
            else if($this->properties->input_mask_type == 'phone-mobile'){
                $input_mask = 'data-inputmask="\'mask\': \'(99) 99-999-99999\'" data-mask';
            }
            else if($this->properties->input_mask_type == 'phone-home'){
                $input_mask = 'data-inputmask="\'mask\': \'(99) 99-999-9999\'" data-mask';
            }

            $input = '<input type="'.$this->properties->input_type.'" class="'.$this->properties->input_class
                    .' '.($this->properties->is_time_picker?'timepicker':'').'" 
                        id="'.$this->properties->input_id.'"
                        placeholder="'.$this->properties->input_placeholder.'"
                        name = "'.$this->properties->input_name.'"    
                        value = "'.$this->properties->input_value.'"
                        input_allow_empty = '.($this->properties->input_allow_empty?'true':'false').'
                        autocomplete="off"
                        '.$input_mask.'
                        '.$input_attrib.'
                         / > ';
            if($this->properties->is_time_picker){
                $input = '<div class="bootstrap-timepicker">'.$input.'</div>';
            }
            
            switch($this->properties->input_mask_type){
                case 'date-picker':
                    $icon = "fa fa-calendar";                    
                    break;
                case 'phone-home':
                    $icon="fa fa-phone";
                    break;
                case 'phone-mobile':
                    $icon.="fa fa-mobile-phone";
                    break;
                case 'code':
                    $icon.="fa fa-info";
                    break;
                case 'user':
                    $icon.="fa fa-user";
                    break;
                case 'location':
                    $icon.="fa fa-location-arrow";
                    break;
                case 'facebook':
                    $icon.="fa fa-facebook";
                    break;
                case 'email':
                    $icon.="fa fa-envelope";
                    break;
                default:
                    $icon.="fa";
                    break;                    
            }
            
            if(strlen($this->properties->input_icon)>0) $icon = $this->properties->input_icon;
            $output = $input;
            $this->generate_additional_script();
            //var_dump($output);die();
            return $output;
        }
        
        protected function generate_additional_script(){
            // $this->additional_script.='$("[data-mask]").inputmask();';
            if(!$this->properties->input_allow_empty){
                $this->additional_script.='
                    $("#'.$this->properties->input_id.'").blur(function(){
                        if($(this).attr("input_allow_empty") == "false"){
                            if( $.trim($(this).val()) == ""){
                                $("#'.$this->properties->input_id.'_badge").removeClass("hide");
                            }
                            else{
                                $("#'.$this->properties->input_id.'_badge").addClass("hide");
                            }
                        }
                    });
                ';
            }
            if($this->properties->is_date_picker){
                $this->additional_script.='$("#'.$this->properties->input_id.'").datepicker({
                    format: "yyyy-mm-dd"
                    ,autoclose:true
                    ,forceParse:true
                });
                ';
            }
            else if($this->properties->is_time_picker){
                $this->additional_script.='
                $("#'.$this->properties->input_id.'").timepicker({
                    showInputs:false
                    ,template:false
                    ,showMeridian:false
                    ,defaultTime:\'current\'
                });';
            }
        }
        
        public function html_render_second(){
            $output=""; 
            
            return $output;             
        }
    }
?>
