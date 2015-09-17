<?php
    
    class Button extends Component_Engine{
        
        private $button_properties=array(
            'prefix_id'=>'',
            "id"=>""
            ,"value"=>""
            ,"class"=>"btn btn-primary"
            ,"icon"=>""
            ,"href"=>""
            ,"style"=>""
            ,"confirmation"=>false
            ,"confirmation_msg"=>"Are you sure want to perform this action?"
            ,'attrib'=>array()
            ,'disable_after_click'=>true
            ,'script'=>''
            ,'type'=>'button'
            ,'submit_form_ajax'=>false
            
        );
        
        function __construct(){
            parent::__construct();
            $this->button_properties = json_decode(json_encode($this->button_properties));
            $this->button_properties->id = $this->generate_id();
        }
        
        public function button_set($method,$data){
            $btn_props = $this->button_properties;
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
                case 'submit_form_ajax':
                    $btn_props->submit_form_ajax = $data;
                    break;
                case 'prefix_id':
                    $btn_props->prefix_id = $data;
                    break;
                    
            };
            return $this;
        }
        
        
        public function html_render_first(){            
            $output = "";
            $btn_props = $this->button_properties;
            $extra_attrib = '';
            if($btn_props->confirmation){
                $extra_attrib .=' 
                    trigger_modal=true 
                    value= "'.$btn_props->href.'"
                    data-toggle="modal" 
                    data-target="#modal_confirmation"
                    ';
            }
            
            $btn_attrib = '';
            
            foreach($btn_props->attrib as $key=>$val){
                $btn_attrib.=$key.'="'.$val.'"';
            }
            
            $output.='
                <button id="'.$btn_props->id.'" 
                    type="'.$btn_props->type.'"                     
                    class="'.$btn_props->class.'"
                    style="'.$btn_props->style.'"
                    '.$extra_attrib.'
                    '.$btn_attrib.'
                     
                >
                <i class="'.$btn_props->icon.'"></i>&nbsp &nbsp'.$btn_props->value
                .'</button>                     
            ';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $this->generate_additional_script();
            return $output;
             
        }
        
        protected function generate_additional_script(){
            $btn_props = $this->button_properties;
            $navigation_script="";
            if(!$btn_props->confirmation){
                if(strlen($btn_props->href)>0){
                    $navigation_script = 'location.href="'.$btn_props->href.'"';
                }
                $this->additional_script.='
                    $("#'.$btn_props->id.'").on("click",
                        function(){
                            '.($btn_props->disable_after_click?'$(this).addClass("disabled");':'').'
                            
                            '.$navigation_script.'
                            $(this).blur();
                            
                        }
                    );
                ';
            }
            else{
                $this->additional_script.='
                $("button").click(function(){
                    if($(this).attr("trigger_modal")){
                        
                        if($(this).attr("id") == "'.$this->button_properties->id.'"){
                            var $href = $(this).attr("value");
                            var $form = $("#modal_confirmation_form");
                            $form.attr("action",$href);
                            $("#modal_confirmation_msg").text("'.$this->button_properties->confirmation_msg.'");

                        }
                    }
                });
            ';
            }
            
            if($btn_props->submit_form_ajax){
                $prefix_id = $btn_props->prefix_id;
                $this->additional_script.='
                    var lparent_pane = '.$prefix_id.'_parent_pane;
                    $(lparent_pane).find("#'.$btn_props->id.'").off("click");
                    $("#'.$btn_props->id.'").on("click",function(e){
                        e.preventDefault();
                        btn = $(this);
                        btn.addClass("disabled");
                        var lparent_pane = '.$prefix_id.'_parent_pane;
                        modal_confirmation_submit_parent = $(lparent_pane).attr("class").indexOf("modal-body")!==-1?
                            $(lparent_pane).closest(".modal"):null;
                        $("#modal_confirmation_submit").modal("show");
                        $("#modal_confirmation_submit_btn_submit").on("click",function(){
                            '.$prefix_id.'_methods.submit();
                        });
                        $('.$prefix_id.'_window_scroll).scrollTop(0);        
                        setTimeout(function(){btn.removeClass("disabled")},1000);
                    });
                ';
            }
            
            $this->additional_script.=$this->button_properties->script;
        }
    }
?>
