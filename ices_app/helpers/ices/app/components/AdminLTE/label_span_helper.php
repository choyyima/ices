<?php
    
    get_instance()->load->helper('app/components/label_helper');
    get_instance()->load->helper('app/components/span_helper');
    class Label_Span extends Component_Engine{
        
        private $label=null;
        
        private $span=null;
        
        public function __construct(){
            parent::__construct();
            $this->label = new Label();
            $this->span = new Span();
        }
        
        public function label_span_set($method,$data){
            switch($method){
                case 'value':
                    if(isset($data['label'])) $this->label->label_set('value',$data['label']);
                    if(isset($data['span'])) $this->span->span_set('value',$data['span']);
                break;
                
            }
            return $this;
        }
        
        public function html_render_first(){     
            $label = $this->label;
            $span = $this->span;
            $output = '<div class="form-group">';
            $output.=$label->html_render_first();
            $output.=$label->html_render_second();
            $output.=$span->html_render_first();
            $output.=$span->html_render_second();
            $output.= '</div>';
            return $output;
        }
        
        public function html_render_second(){
            $output=""; 
            
            return $output;             
        }
    }
?>
