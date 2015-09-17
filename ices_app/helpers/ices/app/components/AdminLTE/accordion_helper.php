<?php
    
    class Accordion extends Component_Engine{
        
        private $accordion_properties=array(
            "id"=>""
            ,"span"=>""
            ,"class"=>""
            ,'href'=>''
            ,'header'=>array(
                'id'=>''
                ,'text'=>''
            )
            
           
        );
        
        public function __construct(){
            parent::__construct();
            $this->accordion_properties = json_decode(json_encode($this->accordion_properties));
            $this->accordion_properties->id = $this->generate_id();
        }
        
        public function accordion_set($method,$data){
            $props = $this->accordion_properties;
            switch($method){
                case 'href':
                    $props->href = $data;
                    break;
                case 'header':
                    $data_temp = array(
                        "id"=>$this->generate_id()
                        ,"text"=>''
                    );
                    $data_temp = json_decode(json_encode($data_temp));
                    if(isset($data['id'])) $data_temp->id = $data['id'];
                    if(isset($data['text'])) $data_temp->text = $data['text'];
                    $props->header = $data_temp;
                    break;
                
            }
            return $this;
        }
        
        public function html_render_first(){     
            $accordion_props = $this->accordion_properties;

            $output = '
            <div class="panel box box-primary">
                <div class="box-header">
                    <h4 class="box-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#'.$accordion_props->header->id.'">
                            '.$accordion_props->header->text.'
                        </a>
                    </h4>
                </div>
                <div id="'.$accordion_props->header->id.'" class="panel-collapse collapse">
                    <div class="box-body">
                        
            ';
            
            return $output;
        }
        
        public function html_render_second(){
            $output="
                    </div>
                </div>
            </div>

            "; 
            return $output;
             
        }
    }
?>
