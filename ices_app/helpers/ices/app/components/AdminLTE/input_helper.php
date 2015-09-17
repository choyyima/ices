<?php
    
    class Input extends Component_Engine{
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
            ,'input_class'=>'form-control'
            ,'attrib'=>array()
            ,'is_date_picker'=>false
            ,'is_time_picker'=>false
            ,'is_numeric'=>false
            ,'hide_all'=>false
            ,'disable_all'=>false
            ,'numeric_min'=>'0'
            ,'maxlength'=>'45'
        );
        
        public $numeric_opt = array('min_val'=>'0');
        
        public $div_properties = array(
            'attrib'=>array()
            ,'id'=>''
        );
        
        function __construct(){
            parent::__construct();
            
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->properties->input_id = $this->generate_id();
            $this->div_properties->id = $this->generate_id();
        }
        public function div_set($method="",$data=""){
            switch($method){
                case 'attrib':
                    $this->div_properties->attrib = $data; 
                    break;
                    
                case 'id':
                    $this->div_properties->id = $data;
                    break;
                case 'hide':
                    $this->properties->hide=$data;
                    break;
            }
            return $this;
        }
        public function input_set($method="",$data=""){
            switch($method){
                case 'id':
                    $this->properties->input_id = $data;
                    $this->security_properties->component->id = $data;
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
                case 'is_numeric':
                    $this->properties->is_numeric=$data;
                    break;
                case 'type':
                    $this->properties->input_type=$data;
                    break;
                case 'hide_all':
                    $this->properties->hide_all = $data;
                    break;
                case 'disable_all':
                    $this->properties->disable_all = $data;
                    break;
                case 'numeric_opt':
                    $this->numeric_opt = $data;
                    break;
                case 'maxlength':
                    $this->properties->maxlength = $data;
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
                $input_mask = 'data-inputmask="\'mask\': \'999-99999999\'" data-mask';
            }
            else if($this->properties->input_mask_type == 'phone-home'){
                $input_mask = 'data-inputmask="\'mask\': \'(99) 99-9999999\'" data-mask';
            }
            else if($this->properties->input_mask_type == 'email'){
                $input_mask = 'data-inputmask="\'mask\': \'(99) 99-9999999\'" data-mask';
            }
            
            $input = '<input type="'.$this->properties->input_type.'" 
                        class="'.$this->properties->input_class.' '.($this->properties->is_time_picker?'timepicker':'').' '.($this->properties->disable_all?'disable_all':'').'" 
                        id="'.$this->properties->input_id.'"
                        placeholder="'.$this->properties->input_placeholder.'"
                        name = "'.$this->properties->input_name.'"    
                        value = "'.$this->properties->input_value.'"
                        input_allow_empty = '.($this->properties->input_allow_empty?'true':'false').'
                        disable_all_type="common"
                        autocomplete="off"
                        maxlength="'.$this->properties->maxlength.'"
                        '.($this->properties->disable_all?'disable_all':'').'
                        
                        '.$input_mask.'
                        '.$input_attrib.'
                        >';
            if($this->properties->is_time_picker){
                $input = '<div class="bootstrap-timepicker">'.$input.'</div>';
            }
            
            $output.='

                <div class="form-group '.($this->properties->hide_all?'hide_all':'').'" id = "'.$this->div_properties->id.'" 
                    style="'.($this->properties->hide?' display:none ':'').'">
                    <label>'.$this->properties->label.'</label>&nbsp
                    <small id= "'.$this->properties->input_id.'_badge" class="badge bg-yellow hide">Must be filled out</small>        
                ';
            
            
            $output.='
                <div class="input-group" >
                    <span class="input-group-addon" '.$div_attrib.'>
                    ';
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
            
            $output.='<i class="'.$icon.'"></i>';
            
                
                
            $output.='</span>'.$input.'</div>';

            
            $this->generate_additional_script();
            
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
            else if ($this->properties->is_numeric){
                $input_opt = '';
                foreach($this->numeric_opt as $key=>$val){
                    $input_opt.=($input_opt === ''?'':',').$key.':"'.$val.'"';
                }
                $this->additional_script.='
                    APP_COMPONENT.input.numeric($("#'.$this->properties->input_id.'")[0],{
                        '.$input_opt.'
                    });
                    $("#'.$this->properties->input_id.'").blur();
                ';
            }
            
            
            if($this->properties->input_mask_type === 'numeric'){
                $this->additional_script.=''
                . 'APP_EVENT.init().component_set("#'.$this->properties->input_id.'")'
                . '.type_set("input").numeric_set().min_val_set("'.$this->properties->numeric_min.'").render()'
                . '';
            }
            
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='</div>';
            return $output;             
        }
    }
?>
