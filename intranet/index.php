<?php
include_once '../includes/constants.php';
include_once "../includes/usuario.php";

usuario::esUsuarioLogueado();

$session = $_SESSION;
$inmueble = new inmueble();
$lista_inm = $inmueble->listarInmueblesAutorizados($session['usuario']['id']);

echo $twig->render('intranet/index.html.twig', array(
    'session'   => $session,
    'inmuebles' => $lista_inm['data'],
    'mensajes'  => 0
));