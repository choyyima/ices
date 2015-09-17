<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    class Lang {
        //public static $translate = true;
        
        public static $lang_list=array(
            //common
            'has not been deposited'=>'Belum Disetor',
            'product result'=>'Produk Hasil',
            'lack'=>'Kekurangan',
            'has been paid'=>'Telah Dibayar',
            'has been used'=>'Telah Digunakan',
            'confirmation'=>'Konfirmasi',
            'plan'=>'Rencana',
            'being used'=>'Terpakai',
            'leftover'=>'Sisa',
            'scrap'=>'Sisa',
            'start'=>'mulai',
            'end'=>'selesai',
            'ordered'=>'dipesan',
            'approved'=>'diterima',
            'rejected'=>'ditolak',
            'result'=>'Hasil',
            'one'=>'Satu',            
            'list'=>'Daftar'
            ,'date'=>'Tanggal'
            ,'new'=>'Baru'
            ,'add'=>'Tambahkan'
            ,'reference'=>'Referensi'
            ,'modified date'=>'Tanggal Modifikasi'
            ,'type'=>'Tipe'
            ,'code'=>'Kode'
            ,'name'=>'Nama'
            ,'address'=>'Alamat'
            ,'phone'=>'Phone'
            ,'address'=>'Alamat'
            ,'city'=>'Kota'
            ,'country'=>'Negara'
            ,'product'=>'Barang'
            ,'unit'=>'Satuan'
            ,'cancellation reason'=>'Alasan Pembatalan'
            ,'failed'=>'gagal'
            ,'use'=>'gunakan'
            ,'store empty'=>'Toko Kosong'
            ,'empty'=>'Kosong'
            ,'receipt number'=>'Nomor Resi'
            ,'receiver name'=>'Nama Penerima'
            ,'expedition name'=>'Nama Ekspedisi'
            ,'driver name'=>'Nama Supir'
            ,'driver assistant name'=>'Nama Kenet'
            ,'new'=>'Baru'
            ,'does not'=>'Tidak'
            ,'exists'=>'sudah ada'
            ,'at once'=>'Terlebih dahulu'
            ,'cannot be'=>'Tidak dapat'
            ,'the same as'=>'Sama dengan'
            ,'different from'=>'Tidak Sama dengan'
            ,'or'=>'Atau'
            ,'is being processed'=>'Sedang di proses'
            ,'is being refered to'=>'Sedang di gunakan untuk referensi pada '
            ,'is lower than'=>'Lebih kecil dari'
            ,'is greater than'=>'Lebih besar dari'
            ,'is too many'=>'Terlalu banyak'
            ,'unallowed'=>'Tidak diijinkan'
            ,'unreceived'=>'Belum diterima'
            ,'undelivered'=>'Belum terkirim'
            ,'received'=>'Diterima'
            ,'delivered'=>'Terkirim'
            ,'incomplete'=>'Belum lengkap'
            ,'not'=>'Belum'
            ,'mismatch'=>'Berbeda'
            ,'with'=>'dengan'
            ,'ordered qty'=>'Qty Dipesan'
            ,'must be greater than'=>'Harus Lebih dari'
            ,'movement outstanding qty'=>'Qty belum terkirim'
            ,'work order'=>'Surat Perintah Kerja'
            ,'subcon work order'=>'Pengerjaan Subcon'
            ,'and'=>'Dan'
            ,'similar'=>'sama'
            ,'expected'=>'diharapkan'
            ,'expectation'=>'ekspektasi'
            ,'dashboard'=>'Dashboard'
            ,'company'=>'Perusahaan'
            ,'security'=>'Keamanan'
            ,'list'=>'Daftar'
            ,'activity'=>'Aktifitas'
            ,'report'=>'Laporan'
            ,'store'=>'Toko'
            ,'warehouse'=>'Gudang'
            ,'customer'=>'Customer'
            ,'type'=>'Tipe'
            ,'purchase'=>'Pembelian'
            ,'sales'=>'Penjualan'
            ,'product'=>'Produk'
            ,'category'=>'Kategori'
            ,'sub category'=>'Sub Kategori'
            ,'unit'=>'Satuan'
            ,'approval'=>'Approval'
            ,'request form'=>'Form Permohonan'
            ,'receive product'=>'Penerimaan Barang'
            ,'payment'=>'Pembayaran'
            ,'self taken'=>'Diambil Sendiri'
            ,'system investigation report'=>'Berita Acara Pemeriksaan'
            ,'phone number'=>'Nomor Telepon'
            ,'estimation'=>'Estimasi'
            ,'fee'=>'Ongkos'
            ,'recondition'=>'Rekondisi'
            //sales
            ,'sales prospect'=>'Prospek Penjualan'            
            ,'sales invoice'=>'Nota Penjualan'
            ,'expedition weight'=>'Berat Ekspedisi'
            ,'expedition'=>'Ekspedisi'
            ,'estimated delivery cost'=>'Perkiraan Ongkos Kirim'
            ,'delivery cost'=>'Ongkos Kirim'
            ,'delivery cost estimation'=>'Perkiraan Ongkos Kirim'
            ,'checking result form'=>'Form Hasil Pengisian'
            //purchase invoice section
            ,'purchase invoice'=>'Invoice Pembelian'
            
            //intake section
            ,'product intake'=>'Pengambilan Produk'
            ,'product intake final'=>'Pengambilan Produk Final'
            
            //delivery order section
            ,'delivery order'=>'Surat Jalan'
            ,'delivery order final'=>'Surat Jalan Final'
            ,'delivery order final confirmation'=>'Konfirmasi Surat Jalan Final'
            ,'delivery product'=>'Pengiriman Barang'
            ,'delivery product list'=>'Daftar Pengiriman Barang'
            ,'new delivery product'=>'Tambahkan Pengiriman Barang'
            ,'delivery product date'=>'Tanggal Pengiriman Barang'
            ,'delivery product code'=>'Kode Pengiriman Barang'
            ,'from warehouse'=>'Gudang Asal'
            ,'warehouse from'=>'Gudang Asal'
            ,'to warehouse'=>'Gudang Tujuan'
            ,'warehouse to'=> 'Gudang Tujuan'
            
            //receive product section
            ,'receive product'=>'Penerimaan Barang'
            ,'receive product list'=>'Daftar Penerimaan Barang'
            ,'new receive product'=>'Tambahkan Penerimaan Barang'
            ,'receive product date'=>'Tanggal Penerimaan Barang'
            ,'receive product code'=>'Kode Penerimaan Barang'
            ,'from warehouse'=>'Gudang Asal'
            ,'to warehouse'=>'Gudang Tujuan'
            
            //customer
            ,'customer type'=>'Tipe Customer'
            ,'movement'=>'Pergerakan Barang'
            
            //receipt
            ,'receipt list'=>'Daftar Pembayaran'
            ,'unallocated receipt'=>'Receipt Gantung'
            //movement 
            
            //manufacturing
            ,'manufacturing - work order'=>'Manufacturing - Perintah Kerja'
            ,'manufacturing work order'=>'Perintah Kerja'
            ,'manufacturing outstanding qty'=>'Qty Belum Dikerjakan'
            ,'manufacturing - work process'=>'Manufacturing - Pengerjaan'
            ,'manufacturing work process'=>'Pengerjaan'
            ,'manufacturing ordered qty'=>'Qty Dipesan'
            ,'manufacturing available component qty'=>'Qty Komponen Tersedia'
            
            // status
            ,'not done yet'=>'belum selesai'
            ,'not confirmed yet'=>'belum dikonfirmasi'
            //passive word
            
            
        );
        
        public static $prt_lang_list=array(
            //common
            
        );
        
        public static function get($words, $translate=true, $first_capital=true, $after_space_capital=false, $lower_all=false,$skip_grammar=false){
            //<editor-fold defaultstate="collapsed">
            return self::translate(self::$lang_list, $words, $translate, $first_capital, $after_space_capital, $lower_all,$skip_grammar);
            //</editor-fold>
        }
        
        public static function prt_get($words, $translate=true, $first_capital=true, $after_space_capital=false, $lower_all=false,$skip_grammar=false){
            return self::translate(self::$prt_lang_list, $words, $translate, $first_capital, $after_space_capital, $lower_all,$skip_grammar);
        }
        
        public static function translate($word_list, $words, $translate=true, $first_capital=true, $after_space_capital=false, $lower_all=false,$skip_grammar=false){
            //<editor-fold defaultstate="collapsed">
            $result = '';
            if($translate) $translate = ICES_Engine::$app['app_translate'];
            if($translate){
                if(is_string($words)){
                    $result = isset($word_list[strtolower($words)])?$word_list[strtolower($words)]:$words;
                }
                else if(is_array($words)){
                    $adj_arr[] = '';
                    $noun_arr[] = '';
                    $words_arr = $words;
                    if(!$skip_grammar){
                        //<editor-fold defaultstate="collapsed">
                        foreach($words_arr as $word_idx=>$word){
                            $grammar = '';
                            $val = '';
                            $uc_first = true;
                            $uc_words = false;

                            if(is_array($word)){
                                $grammar = isset($word['grammar'])?Tools::_str($word['grammar']):'noun';
                                $val = isset($word['val'])?Tools::_str($word['val']):'';
                                $uc_first = isset($word['uc_first'])?Tools::_bool($word['uc_first']):true;
                                $uc_words = isset($word['uc_words'])?Tools::_bool($word['uc_words']):false;
                                $lower_all = isset($word['lower_all'])?Tools::_bool($word['lower_all']):$lower_all;

                            }
                            else {
                                $grammar = 'noun';
                                $val = Tools::_str($word);
                            }

                            $val=isset($word_list[strtolower($val)])?
                                        $word_list[strtolower($val)]:$val;

                            if($uc_first) $val = ucfirst ($val);
                            else $val = lcfirst ($val);

                            if($uc_words) $val = ucwords($val);

                            if($lower_all) $val = strtolower($val);
                            switch($grammar){
                                case 'adj':
                                    $adj_arr[]= $val;
                                    break;
                                case 'noun':
                                    $noun_arr[]=$val;
                                    break;
                            }
                        }
                        $noun = '';
                        $adj = '';
                        for($i = count($noun_arr)-1;$i>=0;$i--) $noun.=$noun===''?$noun_arr[$i]:' '.$noun_arr[$i];
                        for($i = count($adj_arr)-1;$i>=0;$i--) $adj.=$adj===''?$adj_arr[$i]:' '.$adj_arr[$i];

                        $result = $noun.' '.$adj;
                        //</editor-fold>
                    }
                    else{
                        foreach($words_arr as $word_idx=>$word){
                            $val = '';
                            $uc_first = true;
                            $uc_words = false;
                            $llower_all = $lower_all;

                            if(is_array($word)){
                                $val = isset($word['val'])?Tools::_str($word['val']):'';
                                $uc_first = isset($word['uc_first'])?Tools::_bool($word['uc_first']):true;
                                $uc_words = isset($word['uc_words'])?Tools::_bool($word['uc_words']):false;
                                $llower_all = isset($word['lower_all'])?Tools::_bool($word['lower_all']):$lower_all;

                            }
                            else {
                                $val = Tools::_str($word);
                            }

                            $val=isset($word_list[strtolower($val)])?
                                        $word_list[strtolower($val)]:$val;

                            if($uc_first) $val = ucfirst ($val);
                            else $val = lcfirst ($val);

                            if($uc_words) $val = ucwords($val);

                            if($llower_all) $val = strtolower($val);
                            
                            $result.=$val.' ';
                        }
                    }
                    
                }
            }
            else{
                if(is_string($words)){
                    $result = $words;
                }
                else{
                    foreach($words as $i=>$word){
                        $val = '';
                        if(is_array($word)){
                            $val = isset($word['val'])?Tools::_str($word['val']):'';
                        }
                        else {
                            $val = Tools::_str($word);
                        }
                        
                        $result .= (($result==='')?($val):' '.($val));
                    }
                }
            }
            
            if($first_capital){
                $result = ucfirst($result);
            }
            else{
                $result = lcfirst($result);
            }
            
            if($after_space_capital){
                $result = ucwords($result);
            }
            
            if($lower_all) $result = strtolower($result);
            
            return $result;
            //</editor-fold>
        }
        
    }
    
?>