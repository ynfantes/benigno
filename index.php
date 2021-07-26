<?php
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
include_once 'includes/constants.php';
$mantenimiento=false;
$avance=0;
$propietarios = new propietario();
$propiedades = new propiedades();
$facturas = new factura();
$result = $propietarios->listar();
if (!$result['suceed'] || empty($result['data'])) {
    $mantenimiento=TRUE;
} else {
    $result = $propiedades->listar();
    if (!$result['suceed'] || empty($result['data'])) {
        $mantenimiento=TRUE;
        
    } else {
        $result = $facturas->listar();
        if (!$result['suceed'] || empty($result['data'])) {
            $mantenimiento=TRUE;
        }
    }    
}

if ($mantenimiento) {
    $mail = new mailto(SMTP);
    $min = date("i");
    $avance = $min * 100 / 60;
    $mensaje = 'Se requiere una sincronización de la página <strong>URGENTE!</strong><br><br>';
    $mensaje.= 'Hasta que sincronice, el servicio se encuenta en mantenimiento: '.ROOT.'<br><br>';
    $mensaje.= 'Un usuario está tratando de acceder al servicio en este momento.';
    //enviamos una comunicacion al administrador del sistema
    if (PRODUCCION) {
        $mail->enviar_email(TITULO.' [Mantenimiento]',$mensaje,'','angelica.benigno@gmail.com');
    }
    echo $twig->render('mantenimiento.html.twig',array("avance" => $avance));
} else {
    echo $twig->render('login.html.twig');
}