<?php

//LIB
function jolasetaGetByEmailDni($email, $dni) {
  if (($handle = fopen(__DIR__."/csv/accionistas.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
      if ($data[3] == $email && $data[2] == $dni) {
        fclose($handle);
        return $data;
      }
    }
    fclose($handle);
  }
  return false;
}

function jolasetaInsertVote($user) {
  $user[] = date("Y-m-d H:i:s");
  if (($handle = fopen(__DIR__."/csv/votos.csv", "a+")) !== FALSE) {
    fputcsv($handle, $user);
    jolasetaNoticeVote($user);
    fclose($handle);
  }
}

function jolasetaNoticeVote($user) {
  $message = "Nombre: ".$user[0]."\n";
  $message .= "Teléfono: ".$user[1]."\n";
  $message .= "DNI/NIE: ".$user[2]."\n";
  $message .= "EMail: ".$user[3]."\n";
  $message .= "Acciones: ".$user[4]."\n";
  $message .= "Voto: ".$user[6]."\n";
  $message .= "Fecha: ".$user[7]."\n";
  foreach(explode(",", ADMINEMAILS) as $admin_email) {
    wp_mail($admin_email, __("Aviso de cesión de voto Jolaseta", 'wp-cesion-voto'), $message);
  }
}

function jolasetaHasVoted($hash) {
  if (($handle = fopen(__DIR__."/csv/votos.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
      $current_hash = hash('ripemd160', $data[2].$data[3].HASHTEXT);
      if ($current_hash == $hash) {
        fclose($handle);
        return true;
      }
    }
    fclose($handle);
  }
  return false;
}

function jolasetaGetByHash($hash) {
  if (($handle = fopen(__DIR__."/csv/accionistas.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
      $current_hash = hash('ripemd160', $data[2].$data[3].HASHTEXT);
      if ($current_hash == $hash) {
        fclose($handle);
        $data[] = $hash;
        return $data;
      }
    }
    fclose($handle);
  }
  return false;
}

function jolasetaCheckDniNie($value) {
  $pattern = "/^[XYZ]?\d{5,8}[A-Z]$/";
  $dni = strtoupper($value);
  if(preg_match($pattern, $dni)) {
    $number = substr($dni, 0, -1);
    $number = str_replace('X', 0, $number);
    $number = str_replace('Y', 1, $number);
    $number = str_replace('Z', 2, $number);
    $dni = substr($dni, -1, 1);
    $start = $number % 23;
    $letter = 'TRWAGMYFPDXBNJZSQVHLCKET';
    $letter = substr('TRWAGMYFPDXBNJZSQVHLCKET', $start, 1);
    if($letter != $dni)
    {
      return false;
    } else {
      return true;
    }
  } else {
    return false;
  }
}