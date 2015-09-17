<?php
    
    class Form extends Component_Engine{
        public $control= array();
        public $form_properties = array(
            "span"=>""
            ,"action"=>''
            ,'title'=>''
            ,'footer_enable'=>true
            ,'disable_submit'=>false
            ,'remove_border'=>false
        );
        
        public function form_set($method,$data){
            switch($method){
                case 'span':
                    $this->form_properties['span'] = $data;
                    break;
                case 'action':
                    $this->form_properties['action'] = $data;
                    break;
                case 'title':
                    $this->form_properties['title']=$data;
                    break;
                case 'footer_enable':
                    $this->form_properties['footer_enable'] = $data;
                    break;
                case 'disable_submit':
                    $this->form_properties['disable_submit'] = $data;
                    break;
                case 'remove_border':
                    $this->form_properties['remove_border'] = $data;
                    break;
                    
            }
            return $this;
        }
        
        public function control_set($method,$id="",$class="",$type="button", $href="",$label="",$icon=""){
            switch($method){
                case 'button':
                        $ctrl= array(
                                "tag"=>"button"
                                ,"id"=>$this->generate_id()
                                ,"type"=>$type
                                ,"class"=>"btn btn-primary"
                                ,"label"=>$label
                                ,"href"=>$href
                                ,"icon"=>$icon
                        );

                        if(strlen($id)>0) $ctrl['id']=$id;
                        switch($class){
                            case 'primary': $ctrl['class']='btn btn-primary'; break;
                            case 'danger': $ctrl['class']='btn btn-danger'; break;
                            case 'info': $ctrl['class']='btn btn-info'; break;
                            case 'default': $ctrl['class']='btn btn-default'; break;
                        }
                        
                        $this->control[] = $ctrl;
                    break;
            }
            return $this;
        }
        
        public function html_render_first(){  
            $form_span = strlen($this->form_properties['span'])>0?'col-md-'.$this->form_properties['span']:"";
            $output = "";

            $output.='
                
                    <div class="'.$form_span.'">
                        <div class="'.($this->form_properties['remove_border']?'':'box box-primary').'">
                            <div class="box-header">
                                <h3 class="box-title">'.$this->form_properties['title'].'</h3>
                            </div>
                            <form role="form" action = "'.$this->form_properties['action'].'" method="POST" 
                                '.($this->form_properties['disable_submit']?' onsubmit="return false" ':'').'>
                                <div class="box-body">
            ';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            $output.='
                </div>
                
            ';
            if($this->form_properties['footer_enable']){
                $output.='<div class="box-footer">';
                foreach($this->control as $key=>$val){
                    if($val['tag'] == 'button'){
                        $on_click = "";
                        if($val['type'] == 'submit'){
                            $output.='
                                <button id="'.$val['id'].'" 
                                    class="'.$val['class'].'" 
                                    type="'.$val['type'].'"
                                    ><i class="'.$val['icon'].'"></i> &nbsp &nbsp '.$val['label'].'</button>
                            ';
                        }
                        else if ($val['type'] == 'button'){
                            $output.='
                                <button id="'.$val['id'].'" 
                                    class="'.$val['class'].'" 
                                    type="'.$val['type'].'"
                                    onClick="location.href=\''.$val['href'].'\'; return false;"    
                                    ><i class="'.$val['icon'].'"></i> &nbsp &nbsp '.$val['label'].'</button>
                            ';
                             
                        }
                    }
                }
                $output.='</div>';
            }
                    
            $output.='          
                            </form>
                        </div>
                    </div>
                
            ';
            return $output;
             
        }
    }
?>
