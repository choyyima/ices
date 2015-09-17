<?php $lib_root = get_instance()->config->base_url()."libraries/"; 

?>    

    </aside><!-- /.right-side -->
    </div><!-- ./wrapper -->
</body>
<script src="<?php echo $lib_root ?>js/jquery.min.js"></script>
<script src="<?php echo $lib_root ?>js/jquery-ui.js"></script>
<script src="<?php echo $lib_root ?>js/plugins/input-mask/jquery.inputmask.js"></script>
<script src="<?php echo $lib_root ?>js/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="<?php echo $lib_root ?>js/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<script src="<?php echo $lib_root ?>js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/AdminLTE/app.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/ices/ices.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/AdminLTE/app_ext.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/plugins/timepicker/bootstrap-timepicker.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/plugins/select2/select2.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/plugins/scrollTo/jquery.scrollTo.js" type="text/javascript"></script>
<script src="<?php echo $lib_root ?>js/plugins/datetimepicker/jquery.datetimepicker.js" type="text/javascript"></script>

<?php /*
<script src="<?php echo $lib_root ?>js/jquery.autocomplete.js" type="text/javascript"></script>
 */ ?>

<script type="text/javascript">
var screen_refresh=function(){
                $('html').attr('style','min-height:"0px"');
                $('body').attr('style','min-height:"0px"');
                $('#div_wrapper').attr('style','min-height:"0px"');
            }    
APP_PATH.base_url = '<?php echo ICES_Engine::$app['app_base_url'];?>';

</script>


