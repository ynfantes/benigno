<?php
header( 'Content-type: text/html; charset=utf-8' );
include_once '../../includes/constants.php';
$path= getcwd();
$dir = opendir($path);
$factura = new factura();
$e = 0;
$n = 0;
// Leo todos los ficheros de la carpeta
while ($elemento = readdir($dir)){
    // Tratamos los elementos . y .. que tienen todas las carpetas
    if( $elemento != "." && $elemento != ".." && $elemento != "mantenimiento.php" && $elemento != "index.php"){
        
        // Si es una carpeta
        if(!is_dir($path.$elemento) ){
            
            if (!$factura->avisoExisteEnBaseDeDatos($elemento)) {
                unlink($path."/".$elemento);
                //echo $path."/".$elemento."<br>";
                $n++;
            } else {
                $e++;
            }
            
            //if ($n == 20)                break;
        }
    }
}
echo "\n";
echo "$n archivos NO están en la base de datos.\n";
echo "$e archivos SI están en la base de datos";