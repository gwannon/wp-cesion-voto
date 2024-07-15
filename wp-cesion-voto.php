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
      $message = __("Estimado Sr %s<br/><br/>La representación de sus acciones ha sido delegada satisfactoriamente a %s.<br/><br/>Muchas gracias", 'wp-cesion-voto');

      if(isset($_POST['Lecanda'])) {
        echo "<p class='ok'>".sprintf($message, $user[0], "D. Fernando Lecanda Garamendi")."</p>";
        wp_mail($user[3], __("AMPLIACIÓN DE CAPITAL DE JOLASETA SA", 'wp-cesion-voto'), sprintf($message, $user[0], "D. Fernando Lecanda Garamendi"), $headers);
        $user[] = "D. Fernando Lecanda Garamendi";
        jolasetaInsertVote($user);
      } else if(isset($_POST['Castellanos'])) {
        echo "<p class='ok'>".sprintf($message, $user[0], "D. Diego Castellanos Maruri")."</p>";
        wp_mail($user[3], __("AMPLIACIÓN DE CAPITAL DE JOLASETA SA", 'wp-cesion-voto'), sprintf($message, $user[0], "D. Fernando Lecanda Garamendi"), $headers);
        $user[] = "D. Diego Castellanos Maruri";
        jolasetaInsertVote($user);
      }
    } else echo "<p class='error'>".__("Ya has delegado tu voto con anterioridad.", 'wp-cesion-voto')."</p>";
  } else if(isset($_GET['hash']) && $_GET['hash'] != '') {
    $showform = false;
    $user = jolasetaGetByHash($_GET['hash']);
    if($user) {
      if(!jolasetaHasVoted($_GET['hash'])) {
        echo sprintf(__("Yo, %s, con DNI/NIE %s delego %s lorem ipsum dolor sit amet, consectetur adipiscing elit, ...", 'wp-cesion-voto') , $user[0], $user[2], ($user[4] > 1 ? "mis ".$user[4]." votos" : "mi único voto")); ?>
        <hr/>
        <form method="post" action="<?=get_the_permalink();?>">
          <input type="hidden" name="hash" value="<?=strip_tags($user[5])?>" />
          <input type="submit" name="Lecanda" value="<?php printf(__("Delego mi voto a %s", 'wp-cesion-voto'), "D. Fernando Lecanda Garamendi"); ?>">
          <br/><input type="submit" name="Castellanos" value="<?php printf(__("Delego mi voto a %s", 'wp-cesion-voto'), "D. Diego Castellanos Maruri"); ?>">
          <?php /* <br/><a href="#">DELEGO MI VOTO A OTRO ACCIONISTA DE MI ELECCIÓN</a>*/ ?>
        <form>
        <?php
      } else echo "<p class='error'>".__("Ya has delegado tu voto con anterioridad.", 'wp-cesion-voto')."</p>";
    } else echo "<p class='error'>".__("Validación incorrecta.", 'wp-cesion-voto')."</p>";
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
            $message = sprintf(__("Estimado Sr %s<br/><br/>
            Gracias por tramitar la delegación de la representación de sus acciones.<br/><br/>
            Mediante este <a href='%s'>enlace único</a> Ud. podrá delegar la representación de sus acciones a D. Fernando Lecanda Garamendi, con DNI 16036142J, Presidente del Consejo de Administración de Jolaseta S.A; o bien a D. Diego Castellanos Maruri, con DNI 16049155P, Presidente del Real Club Jolaseta.<br/><br/>
            En el caso de desear delegar la representación de sus acciones a otro accionista de su elección, puede descargar el siguiente <a href='#'>formulario de delegación</a>, cumplimentándolo debidamente y entregándolo en la Administración del Club o bien enviándolo por correo electrónico a <a href='mailto:registro@jolaseta.com'>registro@jolaseta.com</a>.<br/><br/>
            Muchas gracias", 'wp-cesion-votos'), $user[0], $url);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($_POST['email'], __("AMPLIACIÓN DE CAPITAL DE JOLASETA SA", 'wp-cesion-voto'), $message, $headers);

            
            echo "<p class='advise'>".__("Hemos enviado un email a tu correo con instrucciones para hacer la cesión de votos.", 'wp-cesion-voto')."</p>";
          } else {
            echo "<p class='error'>".__("Ya has delegado tu voto con anterioridad.", 'wp-cesion-voto')."</p>";
            $showform = false;
          }
        } else echo "<p class='error'>".__("No hemos encontrado tus datos en nuestra base de datos.", 'wp-cesion-voto')."</p>";
      } else echo "<p class='error'>".__("Documento de identificación incorrecto", 'wp-cesion-voto')."</p>";
    } else echo "<p class='error'>".__("Email incorrecto", 'wp-cesion-voto')."</p>";
  }

  if($showform) { ?>
    <p><?=$content;?></p>
    <form method="post">
      <label>Email: <br/>
        <input type="email" name="email" value=""></label><br/>
      <label>DNI/NIE: <br/>
        <input type="text" name="dni" value=""></label><br/>
      <input type="submit" name="enviar" value="Enviar">
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

  </style>
  <?php return ob_get_clean();
}
add_shortcode('cesion-voto', 'wp_cesion_voto_shortcode');