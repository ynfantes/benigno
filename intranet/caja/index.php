<?php
include_once '../../includes/constants.php';
include_once "../../includes/usuario.php";
include_once '../../includes/file.php';

usuario::esUsuarioLogueado();

$session        = $_SESSION;
$inmueble       = new inmueble();
$lista_inm      = $inmueble->listarInmueblesAutorizados($session['usuario']['id']);
$archivo        = '../../'.ACTUALIZ . ARCHIVO_ACTUALIZACION;
$fecha_actualizacion = JFile::read($archivo);
$accion         = isset($_GET['accion']) ? $_GET['accion'] : "";
$id             = isset($_GET['id']) ? $_GET['id'] : -1;

switch ($accion) {

// <editor-fold defaultstate="collapsed" desc="salir">
    case "salir":
        
        $user_logout = new usuario();
        $user_logout->logout();
        break; // </editor-fold>

// <editor-fold defaultstate="collapsed" desc="consultar">
    case "consultar":
        
        if (isset($_POST['inmueble']) && isset($_POST['apto'])) {
            $facturas       = new factura();
            $inmueble       = new inmueble();
            $propietario    = new propietario();
            $propiedades    = new propiedades();
            $honorarios     = 0;
            $honorarios_usd = 0;
            $deuda          = 0;

            $inm       = $inmueble->ver($_POST['inmueble']);
            $data_inm  = $inm['data'][0];

            $propiedad = $propiedades->verPropiedad($_POST['inmueble'], $_POST['apto']);
            
            $lista_pro = $propietario->listarPropietariosPorInmueble($_POST['inmueble']);
            
            $prop       = $propietario->obtenerPropietario($_POST['inmueble'], $_POST['apto']);

            $factura    = $facturas->estadoDeCuenta($_POST['inmueble'], $_POST['apto']);
            
            if ($factura['suceed'] == true) {

                for ($index = 0; $index < count($factura['data']); $index++) {

                    $filename = "../../enlinea/avisos/" . $factura['data'][$index]['numero_factura'] . ".pdf";
                    $factura['data'][$index]['aviso'] = file_exists($filename);
                    
                    $r = pago::facturaPendientePorProcesar(
                            $factura['data'][$index]['periodo'], 
                            $factura['data'][$index]['id_inmueble'], 
                            $factura['data'][$index]['apto']
                           );
                    
                    if ($r['suceed'] && count($r['data'])>0) {
                        
                        $factura['data'][$index]['pagado'] = 1;
                        $factura['data'][$index]['pagado_detalle'] = "<i class='fa fa-calendar-o'></i> ".
                                Misc::date_format($r['data'][0]['fecha']).
                                "<br>".strtoupper($r['data'][0]['tipo_pago']);
                        
                        if (!$r['data'][0]['numero_documento']==='') {
                            $factura['data'][$index]['pagado_detalle'].=" - Ref: ".$r['data'][0]['numero_documento'];
                        }
                        $factura['data'][$index]['pagado_detalle'].="<br>Monto: ".number_format($r['data'][0]['monto'],2,",",".");
                    } else {
                        $factura['data'][$index]['pagado'] = 0;
                        $factura['data'][$index]['pagado_detalle']='';
                    }
                    $deuda += ($factura['data'][$index]['facturado'] - $factura['data'][$index]['abonado']);
                }

                /* Calculamos honorarios, si aplica */
                if ($data_inm['meses_mora'] > 0 && $data_inm['porc_mora'] > 0 && $propiedad['row']['meses_pendiente'] > $data_inm['meses_mora']) {
                    
                    $honorarios = $deuda * $data_inm['porc_mora'] / 100;
                    if ($data_inm['tasa_cambio']) $honorarios_usd = round($honorarios / $data_inm['tasa_cambio'],2);
                }

                $cuenta = [
                    'apto'          => $_POST['apto'],
                    'codinm'        => $_POST['inmueble'],
                    'honorarios'    => $honorarios,
                    'honorarios_usd'=> $honorarios_usd,
                    'inmueble'      => $data_inm,
                    'propiedad'     => $propiedad['row'],
                    'propietario'   => $prop,
                    'recibos'       => $factura['data'],
                ];

            }
        }
        $lista_inm = $inmueble->listar();
        $archivo = '../../'.ACTUALIZ . ARCHIVO_ACTUALIZACION;
        $fecha_actualizacion = JFile::read($archivo);
        
        echo $twig->render('intranet/caja/index.html.twig', array(
            "session"       => $session,
            "cuentas"       => $cuenta,
            "inmuebles"     => $lista_inm['data'],
            "propietarios"  => $lista_pro['data'],
            "actualizacion" => $fecha_actualizacion
        ));
        break; // </editor-fold>

// <editor-fold defaultstate="collapsed" desc="lista propietarios por inmueble">
    case "listarPropietariosPorInmueble" :
        if (isset($_POST['id_inmueble'])) {
            $pro = new propietario();
            $r = $pro->listarPropietariosPorInmueble($_POST['id_inmueble']);
            if ($r['suceed'] && count($r['data']) > 0) {
                $jsonTable = json_encode($r['data']);
                echo $jsonTable;
            }
          }
        break; // </editor-fold>

// <editor-fold defaultstate="collapsed" desc="default">
    default :
        echo $twig->render('intranet/caja/index.html.twig', array(
            'session'       => $session,
            'inmuebles'     => $lista_inm['data'],
            'mensajes'      => 0,
            'actualizacion' => $fecha_actualizacion
        ));
        break; 
// </editor-fold>

}