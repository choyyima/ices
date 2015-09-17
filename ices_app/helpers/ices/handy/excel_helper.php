<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Excel {
        public static $objPHPExcel;
        public static $path=array();
        
        function __construct(){
            get_instance()->load->library('PHPExcel');        
            get_instance()->load->library('PHPExcel/IOFactory');
            self::path_set();
            self::$objPHPExcel =  new PHPExcel();            
            self::$objPHPExcel->setActiveSheetIndex(0);
        }
        
        function path_set(){
            self::$path = array(
                'download'=>'download/',
                'upload'=>'upload/'
            );
            self::$path = json_decode(json_encode(self::$path));
        }
        
        function file_info_set($method,$data){
            switch (strtolower($method)){
                case 'title':
                    self::$objPHPExcel->getProperties()->setTitle($data);
                    break;
                case 'description':
                    self::$objPHPExcel->setDescription($data);
                    break;
            }
        }
        
        function array_to_text($data=array(),$start_cell='A1',$sheet_idx=0){

            // Assign cell values
            self::$objPHPExcel->setActiveSheetIndex($sheet_idx);
            self::$objPHPExcel->getActiveSheet()->fromArray($data, NULL, $start_cell);
            
        }
        
        function array_to_text_smart($data = array(),$start_cell='A1',$sheet_idx=0){
            $column_header = isset($data['column_header'])?$data['column_header']:'';
            $data = isset($data['data'])?$data['data']:'';
            self::$objPHPExcel->setActiveSheetIndex($sheet_idx);
            self::$objPHPExcel->getActiveSheet()->setSelectedCell($start_cell);
            
            $active_cell = self::$objPHPExcel->getActiveSheet()->getActiveCell();
            $col_idx = self::col_index_get($active_cell);
            $row_idx = self::row_index_get($active_cell);
            $alignment_default = array('horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            foreach($column_header as $idx=>$col){
                $header_style = isset($col['style'])?
                    $col['style']:
                    array('font'=>array(),
                        'alignment'=>$alignment_default
                    );
                $header_style['font']['bold'] = true;
                
                $val = $col['val'];
                self::$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(
                    $col_idx,$row_idx,$val
                );
                self::$objPHPExcel->getActiveSheet()->getStyle(self::cell_letter_get($col_idx,$row_idx))->applyFromArray($header_style);

                $col_idx++;
            }
            $row_idx++;
            $col_idx = self::col_index_get($active_cell);
            self::$objPHPExcel->getActiveSheet()->setSelectedCell(self::cell_letter_get($col_idx,$row_idx));
            $active_cell = self::$objPHPExcel->getActiveSheet()->getActiveCell();
            $row_idx = self::row_index_get($active_cell);
            
            
            self::$objPHPExcel->getActiveSheet()->fromArray($data, NULL, self::cell_letter_get($col_idx,$row_idx));
            
            foreach($column_header as $idx=>$col){
                $header_style = isset($col['style'])?
                    $col['style']:
                    array('font'=>array(),
                        'alignment'=>$alignment_default
                    );
                $val = $col['val'];
                
                self::$objPHPExcel->getActiveSheet()->getStyle(
                    self::cell_letter_get($col_idx,$row_idx).':'.self::cell_letter_get($col_idx,$row_idx+count($data))
                )->applyFromArray($header_style);
                
                $col_idx++;
            }
        }
        
        function save($filename,$download=true){
            self::$objPHPExcel->getProperties()->setCreator("SYSTEM");
            self::$objPHPExcel->getProperties()->setLastModifiedBy("SYSTEM");
            $objWriter = IOFactory::createWriter(self::$objPHPExcel, 'Excel5');
            if(!strpos($filename,'.xls')) $filename = $filename.'.xls';            
            
            if($download){
                //header("location: ".get_instance()->config->base_url().$path);
                
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="'.$filename.'"');
                header('Cache-Control: max-age=0');
                if (ob_get_contents()) ob_end_clean();
                $objWriter->save('php://output');                
            }
            else{                
                $path = self::$path->download.$filename;
                $objWriter->save($path);
            }
        }
        
        function col_index_get($cell_string){
            $result = 0;
            $temp = preg_replace('/[^A-Z]/','',$cell_string);
            $result = PHPExcel_Cell::columnIndexFromString($temp) -1;
            return $result;
        }
        
        
        function row_index_get($cell_string){
            $result = 0;
            $temp = preg_replace('/[^0-9]/','',$cell_string);
            $result = $temp;
            return $result;
        }
        
        function cell_letter_get($col_idx,$row_idx){
            $result = '';
            $result.=PHPExcel_Cell::stringFromColumnIndex(Tools::_int($col_idx)).Tools::_str($row_idx);
            return $result;
        }
        
        function column_width_set($col_idx, $width){
            if(strpos(preg_replace('/[A-Z a-z]/','-',$col_idx),'-') !== false){
                $col_idx = self::col_index_get($col_idx);
            }
            self::$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col_idx))
                ->setAutoSize(false);
            self::$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col_idx))
                ->setWidth($width);
        }
    }
?>