<?php
    
    class Table_Input extends Component_Engine{
        public $control= array();
        
        public $main_div_properties = array(
            'id'=>''
            ,'class'=>'form-group'
            ,'hide_all'=>false
        );
        
        public $label_properties = array(
            'id'=>''
            ,'val'=>''
            
        );
        
        public $table_div_properties = array(
            'id'=>''
        );
        
        public $table_properties = array(
            "id"=>""
            ,"columns"=>array() // has name and label
            ,"is_data_table"=>false
            ,'class'=>'table fixed-table' 
            ,'func_row_bind_event'=>''
            ,'new_row'=>true
            ,'row_num'=>true
        );
        
        
        
        
        function __construct(){
            parent::__construct();
            $this->main_div_properties = json_decode(json_encode($this->main_div_properties));
            $this->table_properties = json_decode(json_encode($this->table_properties));
            $this->table_properties->id = $this->generate_id();
            $this->table_div_properties = json_decode(json_encode($this->table_div_properties));
            $this->label_properties = json_decode(json_encode($this->label_properties));
            
        }

        public function main_div_set($method, $data){
            switch($method){
                case 'class':
                    $this->main_div_properties->class = $data;
                    break;
                case 'hide_all':
                    $this->main_div_properties->hide_all = $data;
                    break;
            }
            return $this;
        }
        
        public function footer_set($data){
            $this->table_properties->footer = $data;
            return $this;
        }
        
        public function label_set($method, $data){
            switch($method){
                case 'value':
                    $this->label_properties->val = $data;
                    break;
            }
            return $this;
        }
        
        public function table_input_set($method,$data){
            switch($method){
                case 'id':
                    $this->table_properties->id = $data;
                    break;
                case 'columns':
                    $data_temp =array();
                    
                    $data_temp['col_name'] = isset($data['col_name'])?$data['col_name']:'';
                    $data_temp['col_id_exists'] = isset($data['col_id_exists'])?$data['col_id_exists']:false;
                    
                    //<editor-fold defaultstate="collapsed" desc="TH">
                    $data_temp['th'] = array(
                        'tag'=>'span','col_attr'=>array()
                        ,'attr'=>'','val'=>'','col_style'=>''
                        ,'col_class'=>'','class'=>''
                        ,'style'=>'','visible'=>true
                    );
                    if(isset($data['th']['tag'])) $data_temp['th']['tag'] = $data['th']['tag'];
                    if(isset($data['th']['col_attr'])) $data_temp['th']['col_attr'] = $data['th']['col_attr'];
                    if(isset($data['th']['attr'])) $data_temp['th']['attr'] = $data['th']['attr'];
                    if(isset($data['th']['col_class'])) $data_temp['th']['col_class'] = $data['th']['col_class'];
                    if(isset($data['th']['class'])) $data_temp['th']['class'] = $data['th']['class'];
                    if(isset($data['th']['col_style'])) $data_temp['th']['col_style'] = $data['th']['col_style'];
                    if(isset($data['th']['style'])) $data_temp['th']['style'] = $data['th']['style'];
                    if(isset($data['th']['val'])) $data_temp['th']['val'] = $data['th']['val'];
                    if(isset($data['th']['visible'])) $data_temp['th']['visible'] = $data['th']['visible'];
                    //</editor-fold>
                    
                    //<editor-fold defaultstate="collapsed" desc="TD">
                    $data_temp['td'] = array(
                        'tag'=>'span','col_attr'=>array()
                        ,'attr'=>'','val'=>'','col_style'=>''
                        ,'style'=>'','visible'=>true
                        ,'col_class'=>'','class'=>''
                        ,'type'=>''
                    );
                    if(isset($data['td']['tag'])) $data_temp['td']['tag'] = $data['td']['tag'];
                    if(isset($data['td']['col_attr'])) $data_temp['td']['col_attr'] = $data['td']['col_attr'];
                    if(isset($data['td']['attr'])) $data_temp['td']['attr'] = $data['td']['attr'];
                    if(isset($data['td']['col_class'])) $data_temp['td']['col_class'] = $data['td']['col_class'];
                    if(isset($data['td']['class'])) $data_temp['td']['class'] = $data['td']['class'];
                    if(isset($data['td']['col_style'])) $data_temp['td']['col_style'] = $data['td']['col_style'];
                    if(isset($data['td']['style'])) $data_temp['td']['style'] = $data['td']['style'];
                    if(isset($data['td']['val'])) $data_temp['td']['val'] = $data['td']['val'];
                    if(isset($data['td']['visible'])) $data_temp['td']['visible'] = $data['td']['visible'];
                    //</editor-fold>
                    
                    $this->table_properties->columns[] = json_decode(json_encode($data_temp));
                    break;
                case 'class':
                    $this->table_properties->class = $data;
                    break;
                case 'new_row':
                    $this->table_properties->new_row = $data;
                    break;
                case 'row_num':
                    $this->table_properties->row_num = $data;
                    break;
                
            }
            return $this;
        }
        
        public function html_render_first(){
            $tbl_class = $this->table_properties->class;
            $output = '
            <div 
                class="'.$this->main_div_properties->class.' '.
                        ($this->main_div_properties->hide_all?'hide_all':'')
            .'">'
                . '<label>'.$this->label_properties->val.'</label>
            <div class="table-responsive" id="'.$this->table_div_properties->id.'">
                <table id="'.$this->table_properties->id.'" class="'.$tbl_class.'" style="font-size:14px">
                    <thead></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                    
            ';
            $output.='
                </table>
            </div>
            </div>
            ';
            
            $param = array(
                'table_id'=>$this->table_properties->id
                ,'table_cols'=>$this->table_properties->columns
                ,'new_row'=>$this->table_properties->new_row
                ,'row_num'=>$this->table_properties->row_num
            );

            $this->additional_script.= str_replace(array('<script>','</script>'),''
                ,get_instance()->load->view('templates/app/components/table_input/table_input_js',$param,TRUE)
            );
            
            return $output;
        }
        
        public function html_render_second(){
            return '';             
        }
    }
?>
