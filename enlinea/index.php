<?php
include_once '../includes/constants.php';
include_once '../includes/propietario.php';
include_once '../includes/file.php';

propietario::esPropietarioLogueado();


$archivo = '../'.ACTUALIZ . ARCHIVO_ACTUALIZACION;
$fecha_actualizacion = JFile::read($archivo);

$session = $_SESSION;
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
            "session" => $session,
            "fecha_actualizacion" => $fecha_actualizacion
            ));
        break;
}