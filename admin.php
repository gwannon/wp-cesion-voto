<?php

//Administrador --------------------- 
add_action( 'admin_menu', 'wp_cesion_voto_plugin_menu' );
function wp_cesion_voto_plugin_menu() {
  add_options_page(__('Cesión de voto', 'wp-cesion-voto'), __('Cesión de voto', 'wp-cesion-voto'), 'manage_options', 'wp-cesion-voto', 'wp_cesion_voto_admin_page');
}

function wp_cesion_voto_admin_page() { 
  $settings = array( 'media_buttons' => true, 'quicktags' => true, 'textarea_rows' => 15 ); ?>
  <h1><?php _e("Configuración de l sistema de Cesión de Votos", 'wp-cesion-voto'); ?></h1>
  <a href="<?php echo get_admin_url(); ?>options-general.php?page=wp-cesion-voto&csv=true" class="button"><?php _e("Exportar a CSV", 'wp-cesion-voto'); ?></a>
  <?php if(isset($_REQUEST['send']) && $_REQUEST['send'] != '') { 
    ?><p style="border: 1px solid green; color: green; text-align: center;"><?php _e("Datos guardados correctamente.", 'wp-cesion-voto'); ?></p><?php
    update_option('_wp_cesion_voto_admin_emails', $_POST['_wp_cesion_voto_admin_emails']);
    update_option('_wp_cesion_voto_hash_text', $_POST['_wp_cesion_voto_hash_text']);
  } ?>
  <form method="post">
    <b><?php _e("Emails a los que avisar de la cesión de votos", 'wp-cesion-voto'); ?> <small>(<?php _e("Separados por comas", 'wp-cesion-voto'); ?>)</small>:</b><br/>
    <input type="text" name="_wp_cesion_voto_admin_emails" value="<?php echo get_option("_wp_cesion_voto_admin_emails"); ?>" style="width: calc(100% - 20px);" /><br/><br/>
    <b><?php _e("Text de encriptación del token", 'wp-cesion-voto'); ?>:</b><br/>
    <input type="text" name="_wp_cesion_voto_hash_text" value="<?php echo get_option("_wp_cesion_voto_hash_text"); ?>" style="width: calc(100% - 20px);" /><br/><br/>

    <br/><br/>
    <input type="submit" name="send" class="button button-primary" value="<?php _e("Guardar", 'wp-cesion-voto'); ?>" />
  </form>
<?php }

//Exportar a CSV ---------------------
function wp_cesion_voto_export_to_CSV() {
  if (isset($_GET['page']) && $_GET['page'] == 'wp-cesion-voto' && isset($_GET['csv']) && $_GET['csv'] == 'true') {
    $f = fopen(__DIR__."/csv/votos.csv", "a+");
    $csv = "";
    while (($datos = fgetcsv($f, 0, ",")) !== FALSE) {
      $csv .= "\"".implode('","', $datos)."\""."\n";
    }
    fclose($f);
		
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Description: File Transfer");
		header("Content-Encoding: UTF-8");
		header("Content-Type: text/csv; charset=UTF-8");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=cesion-de-votos-".date("Y-m-d_His").".csv");
		header("Content-Transfer-Encoding: binary");
		echo $csv;
		die;
  }
}
add_action( 'admin_init', 'wp_cesion_voto_export_to_CSV', 1 );
