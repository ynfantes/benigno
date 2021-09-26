<?php
include_once '../includes/constants.php';
include_once '../includes/propietario.php';
include_once '../includes/file.php';

propietario::esPropietarioLogueado();


$archivo             = '../'.ACTUALIZ . ARCHIVO_ACTUALIZACION;
$session             = $_SESSION;
$cartelera           = new cartelera();
$propiedad           = new propiedades();
$publicaciones       = $cartelera->listar();
$propiedades         = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
$fecha_actualizacion = JFile::read($archivo);


if ($propiedades['suceed'] == true) {

    $cartelera_inmueble  = Array();    
    $cartelera->tabla="cartelera_inmueble";
    foreach ($propiedades['data'] as $propiedad) {
        $resultado = $cartelera->listarCarteleraInmueble($propiedad['id_inmueble']);
        if ($resultado['suceed'] && count($resultado['data'])>0) {
            
            array_push($cartelera_inmueble, $resultado['data']);
            $inm = $propiedad['id_inmueble'];
            
        }
    }

}
//$propiedad = new propiedades();
//$inmueble = new inmueble();
//$semafono = Array();
//$propiedades = $propiedad->propiedadesPropietario($_SESSION['usuario']['cedula']);
//
//if ($propiedades['suceed'] == true) {
////     foreach ($propiedades['data'] as $propiedad) {
////         $id_grupo = $inmueble->obtenerIdGrupo($propiedad['id_inmueble'],$propiedad['apto']);
////         $r = $inmueble->obtenerSemaforo($id_grupo);
////         if ($r['suceed'] && count($r['data'])>0) {
////             $semaforo[] = $r['data'];
////         }
////     }
//}

switch ($accion) {
    default :
        echo $twig->render('enlinea/index.html.twig', array(
            'session'              => $session,
            'fecha_actualizacion'  => $fecha_actualizacion,
            'cartelera_inmueble'   => $cartelera_inmueble,
            'inm'                  => $inm
            ));
        break;
}