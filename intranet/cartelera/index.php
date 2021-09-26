<?php
include_once '../../includes/constants.php';
include_once "../../includes/usuario.php";

usuario::esUsuarioLogueado();

$session    = $_SESSION;
$inmueble   = new inmueble();
$lista_inm  = $inmueble->listarInmueblesAutorizados($session['usuario']['id']);

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';


switch ($accion) {

    case 'eliminar':
        $data       = Array();
        $tabla      = $_GET['tabla'];
        $cartelera  = new cartelera();
        $cartelera->tabla = $tabla;
        
        if (is_numeric($_GET['id'])) {
            
            $id     = $_GET['id'];
            $data['eliminar'] = 1;
            $result = $cartelera->borrar($id);
            if ($result['suceed']) {
                $registro = $result['stats']['affected_rows'];
                $result['mensaje'] = "($registro) Publicación eliminada con éxito";
            } else {
                $result['mensaje'] = 'Ocurrió un error durante el proceso. No se pudo eliminar la publicaicón';
            }
        }

    case 'listar':
        $id = isset($_GET['id']) ? $_GET['id'] : "inmueble";
        $cartelera = new cartelera();
        switch ($id) {
            case "general":
                $cartelera->tabla = "cartelera_general";
                $publicaciones = $cartelera->listar();
                $titulo = "Cartelera General";
                break;
            case "publica":
                $cartelera->tabla = "cartelera_publica";
                $publicaciones = $cartelera->listar();
                $titulo = "Cartelera Publica";
                break;
            default:
                $cartelera->tabla = "cartelera_inmueble";
                $titulo = "Cartelera Condominio";
                $publicaciones = $cartelera->listar();
                break;
        }
        
        echo $twig->render('intranet/cartelera/publicaciones.html.twig', array(
            'session'       => $session,
            'publicaciones' => $publicaciones['data'],
            'titulo'        => $titulo,
            'tipo'          => $id,
            'tabla'         => $cartelera->tabla,
            'resultado'     => $result
        ));
        break;

    case 'guardar':
        $result = Array();
        $data = Array();
        if (strlen($_POST['contenido']) < 15) {
            $result['suceed'] = false;
            $result['mensaje'] = "Debe agregar el contenido a la publicación.";
        } else {
            $data = $_POST;
            $cartelera = new cartelera();
            $cartelera->tabla = $_POST['tabla'];
            unset($data['tabla']);
            $data['fecha_hasta'] = Misc::format_mysql_date($data['fecha_hasta']);
            $data['fecha']       = Misc::format_mysql_date($data['fecha']);
            $result = $cartelera->insertar($data);
            $data['contenido'] = utf8_encode($data['contenido']);
            if ($result['suceed']) {
                $result['mensaje'] = "Publicación registrada con éxito";
            }
        }
        echo json_encode($result);
        break;

    default :
        echo $twig->render('intranet/cartelera/index.html.twig', array(
            'session'       => $session,
            'inmuebles'     => $lista_inm['data'],
            'mensajes'      => 0,
            'tabla'         => 'cartelera_inmueble'
        ));
        break;
}