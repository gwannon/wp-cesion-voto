<?php


if (($handle = fopen("./accionistas-sin-limpiar.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
    if (filter_var($data[3], FILTER_VALIDATE_EMAIL)) {
      $data[2] = mb_strtoupper($data[2]);
      if(checkDNINIE($data[2])) {
        if($data[1] == 0) $data[1] = "";
        $data[3] = mb_strtolower($data[3]);
        echo implode(",", $data)."\n";
      }
    }
  }
  fclose($handle);
}



function checkDniNie($value)
{
    $pattern = "/^[XYZ]?\d{5,8}[A-Z]$/";
    $dni = strtoupper($value);
    if(preg_match($pattern, $dni))
    {
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
          //echo 'Wrong ID, the letter of the NIF does not correspond';
          return false;
        } else {
          //echo 'Correct ID';
          return true;
        }
    }else{
        //echo 'Wrong ID, invalid format';
        return false;
    }
}
