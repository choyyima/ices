<?php
    
    class Modal extends Component_Engine{
        
        private $modal_properties = array(
            'id'=>''
            ,'header'=>array(
                'title'=>''
                ,'icon'=>''
            )
            ,'footer'=>array('button' => array())
            ,'width'=>''
            ,'footer_attr'=>array()
        );
        
        function __construct(){
            parent::__construct();
            $this->modal_properties = json_decode(json_encode($this->modal_properties));
            $this->modal_properties->id = 'modal';//$this->generate_id();
        }
        
        public function footer_attr_set($data){
            $this->modal_properties->footer_attr = $data;
            return $this;
        }
        
        public function id_set($data){            
            if(strlen($data)>0) $this->modal_properties->id=$data;
            return $this;
        }
        
        public function width_set($data){
            $this->modal_properties->width = $data;
            return $this;
        }
        
        public function header_set($data=array()){
            if(isset($data['title'])) $this->modal_properties->header->title= $data['title'];
            if(isset($data['icon'])) $this->modal_properties->header->icon= $data['icon'];
            return $this;
        }
        
        public function modal_button_footer_add($btn_id,$btn_type='button',$btn_class,$icon_class,$msg){
            if(strlen($btn_class) == 0) $btn_class = "btn btn-primary pull-left";
            if(strlen($btn_id) == 0){
                $btn_id = 'modal_btn_footer_'.(count($this->modal_properties->footer->button)+1);
            
            }
            $btn = '<button id="'.$btn_id.'" type="
                    '.$btn_type.'" class="
                    '.$btn_class.'">
                    <i class="'.$icon_class.'">
                    </i>
                    '.$msg.
                    '</button> ';
            $this->modal_properties->footer->button[] =$btn;
            return $this;
            
        }
        
        public function html_render_first(){            
            $modal_props = $this->modal_properties;
            $output = "";
            $header = '
                <div class="modal-header">
                    <button id = "'.$this->modal_properties->id.'_btn_close" type="button" class="close" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">'.  App_Icon::html_get($modal_props->header->icon).'<strong> '.$modal_props->header->title.'</strong></h4>
                </div>
            ';
            
            $content = '
                
                <div class="modal-body">
                    
            ';
            
            $output.='
                <div class="modal fade" 
                    id="'.$modal_props->id.'" tabindex="-1" 
                    role="dialog" aria-hidden="true"
                    style=""
                >
                    <div class="modal-dialog" style="width:'.$modal_props->width.'">
                        <div class="modal-content">
                        '.$header.'
                        '.$content.'    
            ';
            return $output;
        }
        
        public function html_render_second(){
            
            $this->generate_additional_script();
            $output="";
            $button_footer = '';
            foreach($this->modal_properties->footer->button as $btn){
                $button_footer .= $btn;
            }
            
            $footer_attr = '';
            foreach($this->modal_properties->footer_attr as $key=>$val){
                $footer_attr.=$key.'="'.$val.'"';
            }
            
            
            $output .= '
                
                            </div>
                        <div class="modal-footer clearfix" '.$footer_attr.'>
                            '.$button_footer.'
                            
                        </div>
                        </div>
                    </div>
                </div>
            ';
            
            
            return $output;
             
        }
        
        protected function generate_additional_script(){
            $this->additional_script = '
                $("#'.$this->modal_properties->id.'_btn_close").on("click",function(){
                    $("#'.$this->modal_properties->id.'").modal("hide");
                });
                
            ';
            
        }
    }
?>
