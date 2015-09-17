<?php
    
    class Table extends Component_Engine{
        public $control= array();
        public $div_properties = array(
          "id"=>''  
          ,'label'=>''
          ,'hide'=>false
        );
        public $table_properties = array(
            "id"=>""
            ,"raw_data"=>array()
            ,"columns"=>array() // has name and label
            ,"base_href"=>""
            ,"base_href_attr"=>array()
            ,"data_key"=>""
            ,"controls"=>array()
            ,"is_data_table"=>false
            ,'class'=>'table table-bordered table-hover lte-box-shadow'
            ,'footer'=>''
            ,'hide_all'=>false
        );
        
        
        
        function __construct(){
            parent::__construct();
            $this->table_properties = json_decode(json_encode($this->table_properties));
            $this->table_properties->id = $this->generate_id();
            $this->div_properties = json_decode(json_encode($this->div_properties));
            $this->div_properties->id = $this->generate_id();
        }

        public function footer_set($data){
            $this->table_properties->footer = $data;
            return $this;
        }
        
        public function div_set($method,$data){
            switch($method){
                case 'id':
                    $this->div_properties->id = $data;
                    break;
                case 'label':
                    $this->div_properties->label = $data;
                    break;
                case 'hide':
                    $this->div_properties->hide = $data;
                    break;
            }
            return $this;
        }
        
        public function table_set($method,$data){
            switch($method){
                case 'id':
                    $this->table_properties->id = $data;
                    break;
                case 'data':
                    $this->table_properties->raw_data = $data;
                    break;
                case 'columns':
                    $data_temp = array(
                        "label"=>$data['label']
                        ,'attribute'=>''
                        ,"name"=>$data['name']
                        ,"element"=>array(
                            "tag"=>""
                            ,"attribute"=>""
                        )
                        ,"is_key"=>false
                        ,'col_attrib'=>array()
                        ,'header_class'=>''
                    );
                    if(isset($data['attribute'])) $data_temp['attribute'] = $data['attribute'];
                    if(isset($data['is_key'])) $data_temp['is_key'] = $data['is_key'];
                    if(isset($data['element_tag'])) $data_temp['element']['tag'] = $data['element_tag'];
                    if(isset($data['element_attribute'])) $data_temp['element']['attribute'] = $data['element_attribute'];
                    if(isset($data['col_attrib'])) $data_temp['col_attrib']=$data['col_attrib'];
                    if(isset($data['header_class'])) $data_temp['header_class']=$data['header_class'];
                    $this->table_properties->columns[] = json_decode(json_encode($data_temp));
                    break;
                case 'base href':
                    $this->table_properties->base_href=$data;
                    break;
                case 'base href attr':
                    $this->table_properties->base_href_attr=$data;
                    break;
                case 'data key':
                    $this->table_properties->data_key=$data;
                    break;
                case 'control':
                    if(isset($data['name']) && isset($data['href'])){
                        $control = array(
                            "name"=>$data['name']
                            ,"href"=>$data['href']
                            ,"confirmation"=>false
                        );
                        if(isset($data['confirmation'])) $control['confirmation'] = $data['confirmation'];
                        $this->table_properties->controls[]=(object)$control;
                    }
                    break;
                case 'is_data_table':
                    $this->table_properties->is_data_table = $data; //can be text or html
                    break;
                case 'class':
                    $this->table_properties->class = $data;
                    break;
                case 'hide_all':
                    $this->table_properties->hide_all = $data;
                    break;
                
            }
            return $this;
        }
        
        public function columns_generate(){
            $result = "";
            
            foreach($this->table_properties->columns as $col){
                $col_attrib = '';
                foreach($col->col_attrib as $key=>$val){
                    $col_attrib.= $key.'="'.$val.'" ';
                }
                $result.='<th col_name="'.$col->name.'" '.$col_attrib.' class="'.$col->header_class.'">'.$col->label.'</th>';
            }
            if(count($this->table_properties->controls)>0){
                $result.='<th style="text-align:center">Action</th>';
            }
            return $result;
        }
        
        private function content_item_generate($row, $col){
            $result='';
            $content = '';
            $key_name = $this->table_properties->data_key;
            $base_url = $this->table_properties->base_href;    
            $base_href_attr = '';
            
            
            foreach($this->table_properties->base_href_attr as $key=>$val){
                $base_href_attr.= $key.'="'.$val.'"';
            }
            
            if($col->is_key){
                    $content.='<a href="'.$base_url.$row[$key_name].'" '.$base_href_attr.'>'.$row[$col->name]."</a>";                        
            }
            else{
                
                $content.=$row[$col->name];  
                
            }
            
            if(strlen($col->element->tag)>0){
                $tag = $col->element->tag;
                $attrib = $col->element->attribute;
                if($tag == 'input'){
                    if(strpos($attrib,'type="checkbox"') !== false){
                        $result='<'.$tag.' '.$attrib.' '.$content.' >';
                    }
                    else{
                        $result='<'.$tag.' '.$attrib.' value='.$content.' >';
                    }
                }
                else{
                    $result='<'.$tag.' '.$attrib.'>'.$content.'</'.$tag.'>';
                }
            }
            else $result = $content;
                       

            return $result;
        }
        
        private function control_generate($row){
            $result = "";
            $key_name = $this->table_properties->data_key;
            foreach($this->table_properties->controls as $control){                        
                $href = get_instance()->config->base_url().$control->href.$row[$key_name];
                if($control->confirmation){
                    $result.=' <li>
                        <a href="#" style="text-align:center" 
                            data-toggle="modal" 
                            data-target="#modal_confirmation" 
                            component-id="'.$this->table_properties->id.'_button" 
                            value="'.$href.'"
                            trigger_modal = true
                        >'.$control->name.'</a></li>';
                }
                else {
                    $result.=' <li><a href="'.$href.'" style="text-align:center" >'.$control->name.'</a></li>';
                }
            }
            
            return $result;
        }
        
        public function content_generate(){
            $result = "";
            $key_name = $this->table_properties->data_key;
            foreach($this->table_properties->raw_data as $row){
                $result.="<tr>";
                foreach($this->table_properties->columns as $col){
                    //generate content
                    $content = "";
                    if(isset($row[$col->name])){
                        $content = $this->content_item_generate($row,$col);
                    }
                    $result.='
                        <td '.$col->attribute.' col_name="'.$col->name.'" >'.$content.'</td>
                    ';
                }
                if(count($this->table_properties->controls)>0){
                    // generate control
                    $control = $this->control_generate($row);
                    $result.='
                        <td style="text-align:center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu pull-right" role="menu">
                                    '.$control.'
                                </ul>
                            </div>
                        </td>
                    ';
                }
                $result.="</tr>";
            }
            
            return $result;
        }
        
        public function html_render_first(){
            $columns=$this->columns_generate();
            $content=$this->content_generate();
            $tbl_class = $this->table_properties->class;
            $output = '
            <div class=""><label>'.$this->div_properties->label.'</label>
            <div class="table-responsive" id="'.$this->div_properties->id.'">
                <table id="'.$this->table_properties->id.'" class="'.$tbl_class.'" style="font-size:14px">
                    <thead>
                        <tr>'.$columns.'</tr>
                    </thead>
                    <tbody>'.$content.'</tbody>
                    '.$this->table_properties->footer.'
                    
            ';
            $output.='
                </table>
            </div>
            </div>
            ';
            if(count($this->table_properties->raw_data)>0  
                && $this->table_properties->is_data_table){
                $this->additional_script.= ' $("#'.$this->table_properties->id.'").dataTable();';
            }
            
            
            $this->additional_script.='
                $(".dataTables_paginate").parent().parent().before("<div class=\"form-group\">&nbsp</div>")
                $("a").click(function(){
                    if($(this).attr("trigger_modal")){
                        if($(this).attr("component-id") == "'.$this->table_properties->id.'_button"){
                            var $href = $(this).attr("value");
                            var $form = $("#modal_confirmation_form");
                            $form.attr("action",$href);
                            
                        }
                    }
                });
            ';
            
            if($this->table_properties->hide_all){
                $this->additional_script.=''
                    .'$("#'.$this->table_properties->id.'").closest(".form-group").addClass("hide_all");'
                .'';
            }
            
            return $output;
        }
        
        public function html_render_second(){
            return '';             
        }
    }
?>
