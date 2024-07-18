<?php 

/**
 * Plugin Name: WP Jolaseta Cesión voto
 * Plugin URI:  https://github.com/gwannon/wp-cesion-voto
 * Description: Plugin de WordPress para ceder tu voto a Jolaseta
 * Version:     1.0
 * Author:      Gwannon
 * Author URI:  https://github.com/gwannon/
 * License:     GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-cesion-voto
 *
 * PHP 8.1
 * WordPress 6.5.3
 */

define("HASHTEXT", get_option("_wp_cesion_voto_hash_text"));
define("ADMINEMAILS", get_option("_wp_cesion_voto_admin_emails"));

//Cargamos el multi-idioma
function wp_cesion_voto_plugins_loaded() {
  load_plugin_textdomain('wp-cesion-voto', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
}
add_action('plugins_loaded', 'wp_cesion_voto_plugins_loaded', 0 );

/* ----------- Includes ------------ */
include_once(plugin_dir_path(__FILE__).'lib.php');
include_once(plugin_dir_path(__FILE__).'admin.php');


/* ----------- Códigos cortos ------ */
function wp_cesion_voto_shortcode($params = array(), $content = null) {
  ob_start();
  $showform = true;

  if((isset($_POST['Lecanda']) || isset($_POST['Castellanos']) ) && isset($_POST['hash']) && $_POST['hash'] != '') {
    $showform = false;
    if(!jolasetaHasVoted($_POST['hash'])) {
      $user = jolasetaGetByHash($_POST['hash']);
      $headers = array('Content-Type: text/html; charset=UTF-8');
      $message = __("Estimado Sr/Sra %s<br/><br/>La representación de sus acciones ha sido delegada satisfactoriamente a %s.<br/><br/>Muchas gracias", 'wp-cesion-voto');

      if(isset($_POST['Lecanda'])) {
        echo "<p class='ok'>".sprintf($message, $user[0], __("D. Fernando Lecanda Garamendi", 'wp-cesion-voto'))."</p>";
        $message = sprintf($message, $user[0], __("D. Fernando Lecanda Garamendi", 'wp-cesion-voto'));
        $message = str_replace("[MESSAGE]", $message, file_get_contents(__DIR__."/email.html"));
        wp_mail($user[3], __("AMPLIACIÓN DE CAPITAL DE JOLASETA SA", 'wp-cesion-voto'), $message, $headers);
        $user[] = "D. Fernando Lecanda Garamendi";
        jolasetaInsertVote($user);
      } else if(isset($_POST['Castellanos'])) {
        echo "<p class='ok'>".sprintf($message, $user[0], __("D. Diego Castellanos Maruri", 'wp-cesion-voto'))."</p>";
        $message = sprintf($message, $user[0], __("D. Diego Castellanos Maruri", 'wp-cesion-voto'));
        $message = str_replace("[MESSAGE]", $message, file_get_contents(__DIR__."/email.html"));
        wp_mail($user[3], __("AMPLIACIÓN DE CAPITAL DE JOLASETA SA", 'wp-cesion-voto'), $message, $headers);
        $user[] = "D. Diego Castellanos Maruri";
        jolasetaInsertVote($user);
      }
    } else echo "<p class='error'>".__("Lo sentimos, ya ha delegado la representación de sus acciones con anterioridad.", 'wp-cesion-voto')."</p>";
  } else if(isset($_GET['hash']) && $_GET['hash'] != '') {
    $showform = false;
    $user = jolasetaGetByHash($_GET['hash']);
    if($user) {
      if(!jolasetaHasVoted($_GET['hash'])) { ?>
        <h3 style="font-weight: bold;"><?php _e("PODER DE REPRESENTACIÓN ESPECIAL PARA LA JUNTA GENERAL DE JOLASETA, S.A.", 'wp-cesion-voto'); ?></h3>
        <p><?=sprintf(__("<b>%s</b>, mayor de edad, con DNI/NIE nº <b>%s</b>, en su condición de accionista de JOLASETA, S.A., delega su representación para la Junta General Extraordinaria de accionistas de JOLASETA, S.A., que está prevista que se celebre en el domicilio social el día 25 de septiembre de 2024, a las 13 horas en primera convocatoria, y al día siguiente, 26 de septiembre de 2024, en el mismo lugar y hora, en segunda convocatoria, para tratar de los asuntos incluidos en el siguiente orden del día:", 'wp-cesion-voto') , $user[0], $user[2]); ?></p>
        <p><?php _e("Primero.-Ampliación de capital en la cantidad de 6.310,63 euros mediante aportaciones dinerarias y consiguiente modificación del artículo 5º de los Estatutos sociales.", 'wp-cesion-voto'); ?></p>
        <p><?php _e("Segundo.- Delegación de facultades.", 'wp-cesion-voto'); ?></p>
        <p><?php _e("Tercero.- Aprobación del acta.", 'wp-cesion-voto'); ?></p>
        <p><?php _e("En virtud del presente apoderamiento, el representante queda facultado para ejercitar, sin limitación, todos los derechos que asisten al poderdante en el seno de dicha Junta General, entre otros, y sin carácter limitativo (i) aceptar la celebración de la Junta, así como los puntos del orden del día; (ii) tratar los puntos contenidos en el antedicho orden del día, así como cualesquiera otros que, conforme a la legislación vigente, puedan ser incluidos en el mismo por acuerdo de los socios; y (iii) ejercer el derecho de voto, pudiendo verificarse éste en la forma que el representante estime conveniente.
", 'wp-cesion-voto'); ?></p>
        <p><b><?php _e("Delega su representación en favor de:", 'wp-cesion-voto'); ?></b></p>
        <form class="delego" method="post" action="<?=get_the_permalink();?>">
          <input type="hidden" name="hash" value="<?=strip_tags($user[5])?>" />
          <input type="submit" name="Lecanda" value="<?php _e("D. Fernando Lecanda Garamendi, con DNI 16036142J, Presidente del Consejo de Administración de Jolaseta S.A.", 'wp-cesion-voto'); ?>">
          <br/><input type="submit" name="Castellanos" value="<?php _e("D. Diego Castellanos Maruri, con DNI 16049155P, Presidente del Real Club Jolaseta", 'wp-cesion-voto'); ?>">
        <form>
        <p><?php echo sprintf(__("Y para que surta los efectos oportunos, suscribe el presente documento en Getxo, a %d de %s de 2024.", 'wp-cesion-voto'),  date_i18n("d"),  date_i18n("F") ); ?></p>
        <?php
      } else echo "<p class='error'>".__("Lo sentimos, ya ha delegado la representación de sus acciones con anterioridad.", 'wp-cesion-voto')."</p>";
    } else echo "<p class='error'>".__("Lo sentimos, el código de validación es incorrecto.", 'wp-cesion-voto')."</p>";
  }

  if(isset($_POST['enviar']) && $_POST['enviar'] != '') {
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
      if(jolasetaCheckDniNie(mb_strtoupper($_POST['dni']))) {
        $user = jolasetaGetByEmailDni($_POST['email'], mb_strtoupper($_POST['dni']));
        if($user) {
          $hash = hash('ripemd160', $user[2].$user[3].HASHTEXT);
          if(!jolasetaHasVoted($hash)) {
            $showform = false;

            $url = get_the_permalink()."?hash=".$hash;
            $message = sprintf(__("Estimado Sr/Sra %s<br/><br/>Gracias por tramitar la delegación de la representación de sus acciones.<br/><br/>Mediante este <a href='%s'>enlace único</a> Ud. podrá delegar la representación de sus acciones a D. Fernando Lecanda Garamendi, con DNI 16036142J, Presidente del Consejo de Administración de Jolaseta S.A; o bien a D. Diego Castellanos Maruri, con DNI 16049155P, Presidente del Real Club Jolaseta.<br/><br/>En el caso de desear delegar la representación de sus acciones a otro accionista de su elección, puede descargar el siguiente <a href='https://jolaseta.com/wp-content/uploads/2024/07/IMPRESO-PODER-REPRESENTACION.pdf'>formulario de delegación</a>, cumplimentándolo debidamente y entregándolo en la Administración del Club o bien enviándolo por correo electrónico a <a href='mailto:registro@jolaseta.com'>registro@jolaseta.com</a>.<br/><br/>Muchas gracias", 'wp-cesion-votos'), $user[0], $url);
            $message = str_replace("[MESSAGE]", $message, file_get_contents(__DIR__."/email.html"));
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($_POST['email'], __("AMPLIACIÓN DE CAPITAL DE JOLASETA SA", 'wp-cesion-voto'), $message, $headers);

            
            echo "<p class='advise'>".__("Hemos enviado un email a su correo con instrucciones para delegar la representación de sus acciones.", 'wp-cesion-voto')."</p>";
          } else {
            echo "<p class='error'>".__("Lo sentimos, ya ha delegado la representación de sus acciones con anterioridad.", 'wp-cesion-voto')."</p>";
            $showform = false;
          }
        } else echo "<p class='error'>".__("Lo sentimos, no hemos encontrado sus datos en nuestra base de datos.", 'wp-cesion-voto')."</p>";
      } else echo "<p class='error'>".__("Lo sentimos, el documento de identificación es incorrecto.", 'wp-cesion-voto')."</p>";
    } else echo "<p class='error'>".__("Lo sentimos, el mail es incorrecto.", 'wp-cesion-voto')."</p>";
  }

  if($showform) { ?>
    <?=apply_filters("the_content", $content);?>
    <form method="post" class="logeo">
      <label><b><?php _e("Email", 'wp-cesion-voto'); ?>:</b><br/>
        <input type="email" name="email" value="" required></label><br/>
      <label><b><?php _e("DNI/NIE", 'wp-cesion-voto'); ?>:</b><br/>
        <input type="text" name="dni" value="" required></label><br/><br/>
      <input type="submit" name="enviar" value="<?php _e("Enviar", 'wp-cesion-voto'); ?>">
    </form>
  <?php } ?>
  <style>
    p.error {
      color: red;
      padding: 5px;
      border: 1px solid red;
      font-weight: 700;
    }

    p.ok {
      color: green;
      padding: 5px;
      border: 1px solid green;
      font-weight: 700;
    }

    p.advise {
      color: orange;
      padding: 5px;
      border: 1px solid orange;
      font-weight: 700;
    }

    div.delego {
      font-size: 30px;
      font-weight: 700;
      text-align: center;
      padding: 10px 10px 30px 10px;
    }

    form.delego input[type=submit] {
      width: 100%;
      margin-bottom: 20px;
      white-space: normal;
    }

  </style>
  <?php return ob_get_clean();
}
add_shortcode('cesion-voto', 'wp_cesion_voto_shortcode');