<?php
header ('Content-type: text/html; charset=utf-8');

include_once 'includes/db.php';
include_once 'includes/file.php';
include_once 'includes/inmueble.php';
include_once 'includes/junta_condominio.php';
include_once 'includes/propietario.php';
include_once 'includes/propiedades.php';
include_once 'includes/factura.php';

$db = new db();
const ENCODING = 'UTF-8';

$tablas = [
    "factura_detalle", 
    "facturas", 
    "propiedades", 
    "propietarios", 
    "junta_condominio", 
    "inmueble", 
    "inmueble_deuda_confidencial",
    "grupo",
    "grupo_propietario",
    "fondos_movimiento",
    "cancelacion_gastos"
];

if (isset($_GET['codinm'])) {
    $codinm = $_GET['codinm'];
    $db->exec_query("delete from factura_detalle where id_factura in (select numero_factura from facturas wher id_inmueble='{$codinm}')");
    $db->exec_query("delete from facturas where id_inmueble='{$codinm}'");
    $db->exec_query("delete from propietarios where cedula in (select cedula from propiedades where id_inmueble='{$codinm}')");
    $db->exec_query("delete from junta_condominio where id_inmueble='{$codinm}'");
    $db->exec_query("delete from propiedades where id_inmueble='{$codinm}'");
    $db->exec_query("delete from inmueble where id='{$codinm}'");
    $db->exec_query("delete from inmueble_deuda_confidencial where id_inmueble='{$codinm}'");
    $db->exec_query("delete from grupo where id_inmueble='{$codinm}'");
    $db->exec_query("delete from grupo_propietario where id_inmueble='{$codinm}'");
    $db->exec_query("delete from fondos_movimiento where id_fondo in (select id from fondos where id_inmueble='{$codinm}')");
    $db->exec_query("delete from cancelacion_gastos where id_inmueble='{$codinm}'");
    
    $mensaje = "Actualización inmueble ".$codinm."<br>";
} else {
    $mensaje = "Proceso de Actualización Ejecutado<br />";
    foreach ($tablas as $tabla) {
        $r = $db->exec_query("truncate table " . $tabla);
        echo "limpiar tabla: " .$tabla."<br />";
    }
}
echo "Procesar archivos....<br>";
$mensaje.='Procesar archivos....<br>';
$archivo = ACTUALIZ . ARCHIVO_INMUEBLE;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$inmueble = new inmueble();
$mensaje.= "Inmueble (".count($lineas).")<br />";
echo "Inmueble (".count($lineas).")<br />";
foreach ($lineas as $linea) {
    
    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        
        $registro = [
            "id"                => $registro[0],            
            "nombre_inmueble"   => $registro[1],
            "deuda"             => $registro[2],
            "fondo_reserva"     => $registro[3],
            "beneficiario"      => $registro[4],
            "banco"             => '',
            "numero_cuenta"     => '',
            "supervision"       => '0',
            "RIF"               => $registro[5],
            "meses_mora"        => $registro[6],
            "porc_mora"         => $registro[7],
            "moneda"            => $registro[8],
            "unidad"            => $registro[9],
            "facturacion_usd"   => $registro[10],
            'tasa_cambio'       => $registro[11],
            'redondea_usd'      => $registro[12]    
        ];
        
        $r = $inmueble->insertar($registro);
        
        if($r["suceed"]==FALSE){
            echo ARCHIVO_INMUEBLE."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
            echo $r['query'];
            die();
        }   
    }
}

$archivo = ACTUALIZ . ARCHIVO_CUENTAS;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$inmueble = new inmueble();
$mensaje.= "Cuentas Inmuebles (" . count($lineas) . ")<br />";
echo "Cuentas Inmuebles (" . count($lineas) . ")<br />";

foreach ($lineas as $linea) {
    $id = '';
    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        $id=$registro[0];
        $registro = Array(
            "numero_cuenta" => $registro[1],
            "banco" => $registro[2]);


        $r = $inmueble->actualizar("'".$id."'",$registro);
        if ($r["suceed"] == FALSE) {
            //echo ARCHIVO_INMUEBLE."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
            echo $r['query'];
            die();
        }

    }
}

$archivo = ACTUALIZ . ARCHIVO_JUNTA_CONDOMINIO;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$junta_condominio = new junta_condominio();
echo "Junta Condominio (".count($lineas).")<br />";
$mensaje.= "Junta Condominio (".count($lineas).")<br />";
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        $registro = Array("id_cargo" => $registro[1],
            "id_inmueble" => $registro[0],
            "cedula" => $registro[2]);
        
        $r = $junta_condominio->insertar($registro);
        
        if($r["suceed"]==FALSE){
            echo ARCHIVO_JUNTA_CONDOMINIO."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
        }
    }
}

$archivo = ACTUALIZ . ARCHIVO_PROPIETARIOS;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$propietario = new propietario();
echo "Propietarios (".count($lineas).")<br />";
$mensaje.= "Propietarios (".count($lineas).")<br />";
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);

    if ($registro[0] != "") {
        
       $registro = Array(
            'nombre' => mb_convert_encoding($registro[0], ENCODING),
            'clave' => $registro[1],
            'email' => $registro[2],
            'cedula' => $registro[3],
            'telefono1' => $registro[4],
            'telefono2' => $registro[5],
            'telefono3' => $registro[6],
            'direccion' => mb_convert_encoding($registro[7], ENCODING),
            'recibos' => $registro[8]
           );
       
       $r = $propietario->insertar($registro);
       
       if ( !$r["suceed"] ){
            echo "<b>Archivo Propietario: ".$r['stats']['errno']."-".$r['stats']['error']."</b><br />";
            die($r['query']);
        }
            /*}
        }*/
    }
}

$archivo = ACTUALIZ . ARCHIVO_PROPIEDADES;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$propiedades = new propiedades();
echo "Propiedades (".count($lineas).")<br />";
$mensaje.= "Propiedades (".count($lineas).")<br />";
foreach ($lineas as $linea) {


    $registro = explode("\t", $linea);

    if ($registro[0] != "") {
        $registro = Array(
        'cedula'            => $registro[0],
        'id_inmueble'       => $registro[1],
        'apto'              => $registro[2],
        'alicuota'          => $registro[3],
        'meses_pendiente'   => $registro[4],
        'deuda_total'       => str_replace("\r", "", $registro[5]),
        'deuda_usd'         => str_replace("\r", "", $registro[6]));
        
        $r = $propiedades->insertar($registro);
        if($r["suceed"]==FALSE){
            echo "<b>Archivo Propiedades: ".$r['stats']['errno']."-".$r['stats']['error']."</b><br />";
            die($r['query']);
        }
    }
}

$archivo = ACTUALIZ . ARCHIVO_FACTURA;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$facturas = new factura();
echo "Facturas (".count($lineas).")<br />";
$mensaje.= "Facturas (".count($lineas).")<br />";
foreach ($lineas as $linea) {
    
    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {
        
        $registro = Array(
            'id_inmueble'   => $registro[0],
            'apto'          => $registro[1],
            'numero_factura'=> $registro[2],
            'periodo'       => $registro[3],
            'facturado'     => $registro[4],
            'abonado'       => $registro[5],
            'fecha'         => $registro[6],
            'facturado_usd' => $registro[7]
                );
        
        $r = $facturas->insertar($registro);
                
        if(!$r["suceed"]){
            die($r['stats']['errno']."<br />".$r['stats']['error']);
        }
    }
}

//$archivo = ACTUALIZ . ARCHIVO_FACTURA_DETALLE;
//$contenidoFichero = JFile::read($archivo);
//$lineas = explode("\r\n", $contenidoFichero);
//echo "procesar archivo Detalla Factura (".count($lineas).")<br />";
//$mensaje.="procesar archivo Detalle Factura (".count($lineas).")<br />";
//foreach ($lineas as $linea) {
//
//    $registro = explode("\t", $linea);
//
//
//    if ($registro[0] != "") {
//
//        $registro = Array(
//            "id_factura" => $registro[0],
//            "detalle" => utf8_encode($registro[1]),
//            "codigo_gasto" => $registro[2],
//            "comun" => $registro[3],
//            "monto" => str_replace("\r","",$registro[4])
//                );
//        
//        $r = $facturas->insertar_detalle_factura($registro);
//        
//        if($r["suceed"]==FALSE){
//            die($r['stats']['errno']."-".$r['stats']['error'].'<br/>'.$r['query'].'<br/>');
//        }
//    }
//}// </editor-fold>

$archivo = ACTUALIZ . ARCHIVO_EDO_CTA_INM;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
echo "Estado de cuenta inmueble (".count($lineas).")<br />";
$mensaje.="Estado de cuenta inmueble (".count($lineas).")<br />";
foreach ($lineas as $linea) {


    $registro = explode("\t", $linea);


    if ($registro[0] != "") {

        $registro = Array(
            "id_inmueble" => $registro[0],
            "apto" => $registro[1],
            "propietario" => utf8_encode($registro[2]),
            "recibos" => $registro[3],
            "deuda" => str_replace("\r", "", $registro[4]),
            "deuda_usd" => str_replace("\r", "", $registro[5])
        );


        $r = $inmueble->insertarEstadoDeCuentaInmueble($registro);


        if ($r["suceed"] == FALSE) {
            die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
        }
    }
}


if (GRAFICO_FACTURACION == 1) {
    $archivo = ACTUALIZ . "FACTURACION_MENSUAL.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "Grafico facturacion mensual (" . count($lineas) . ")<br />";
    $mensaje.="Gráfico facturación mensual (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_inmueble" => $registro[0],
                "periodo" => $registro[1],
                "facturado" => $registro[2]
            );

            $r = $inmueble->insertarFacturacionMensual($registro);

            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}

if (GRAFICO_COBRANZA == 1) {
    $archivo = ACTUALIZ . "COBRANZA_MENSUAL.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "Gráfico cobranza mensual (" . count($lineas) . ")<br />";
    $mensaje.="Gráfico facturación mensual (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_inmueble" => $registro[0],
                "periodo" => $registro[1],
                "monto" => $registro[2]
            );

            $r = $inmueble->insertarCobranzaMensual($registro);

            if ($r["suceed"] == FALSE) {
                die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}


if (GRUPOS == 1) {
    $archivo = ACTUALIZ . "GRUPO.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "Grupos (" . count($lineas) . ")<br />";
    $mensaje.="Grupos (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id" => $registro[0],
                "id_inmueble" => $registro[1],
                "descripcion" => $registro[2]
            );

            $r = $inmueble->insertarGrupo($registro);

            if ($r["suceed"] == FALSE) {
                echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }


    $archivo = ACTUALIZ . "GRUPO_PROPIETARIO.txt";
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "Grupo propietario (" . count($lineas) . ")<br />";
    $mensaje.="Grupo propietario (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "id_grupo" => $registro[0],
                "id_inmueble" => $registro[1],
                "apto" => $registro[2]
            );

            $r = $inmueble->insertarGrupoPropietario($registro);

            if ($r["suceed"] == FALSE) {
                echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}

if (MOVIMIENTO_FONDO == 1) {
    $fondo = new fondo();
    $archivo = ACTUALIZ . ARCHIVO_CUENTAS_DE_FONDO;
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "Cuentas de fondo (" .count($lineas).")<br/>";
    $mensaje.="Cuentas de fondo (".count($lineas).")<br/>";
    foreach ($lineas as $linea) {
       $registro = explode("\t",$linea);
       if ($registro[0]!="") {
            $registro = Array(
               "id_inmueble"    =>  $registro[0],
               "codigo_gasto"   =>  $registro[1],
               "descripcion"    =>  utf8_encode($registro[2]),
               "saldo"          =>  $registro[3],
               "mostrar"        =>  1
            );
            $r = $fondo->insertarRegistroFondo($registro,Array("saldo"=>$registro["saldo"]));
       }
    }
    $archivo = ACTUALIZ . ARCHIVO_MOVIMIENTOS_DE_FONDO;
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "Movimiento de fondo (" .count($lineas).")<br/>";
    $mensaje.="Movimiento de fondo (".count($lineas).")<br/>";
    $id_inmueble = "";
    $codigo_gasto = "";
    foreach ($lineas as $l) {
        
        $movimiento = explode("\t",$l);
        
        if ($movimiento[0]!="") {
            
            if ($id_inmueble != $movimiento[0] || $codigo_gasto != $movimiento[1]) {
                $id_inmueble = $movimiento[0];
                $codigo_gasto = $movimiento[1];
                $r = $fondo->obtenerIdCuentaFondo($id_inmueble, $codigo_gasto);
                if ($r['suceed'] && count($r['data'])>0) {
                    $id = $r['data'][0]['id'];
                } else {
                    $id = 0;
                }
            }
            
            if ($id > 0) {
                $registro = Array(
                    "id_fondo"      => $id,
                    "fecha"         => $movimiento[2],
                    "tipo"          => $movimiento[3],
                    "concepto"      => utf8_encode($movimiento[4]),
                    "debe"          => $movimiento[5],
                    "haber"         => $movimiento[6]
                );

                $r = $fondo->insertarMovimiento($registro);
                if ($r["suceed"] == FALSE) {
                    echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
                }
            }
        }

    }
    
}

$archivo = ACTUALIZ . "CANCELACION_GASTOS.txt";
if (file_exists($archivo)) {
    
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo cancelacion de gastos (".count($lineas).")<br />";
    $mensaje.="procesar archivo cancelacion de gastos (".count($lineas).")<br />";
    $pago = new pago();
    foreach ($lineas as $linea) {

        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = [
                "fecha_movimiento" => $registro[0],
                "monto"            => $registro[1],
                "descripcion"      => mb_convert_encoding($registro[2], ENCODING),
                "id_inmueble"      => $registro[3],
                "id_apto"          => $registro[4],
                "periodo"          => $registro[5],
                "numero_factura"   => str_replace("\r","",$registro[6])            
            ];

            $r = $pago->insertarCancelacionDeGastos($registro);


            if (!$r["suceed"]) {
                echo ($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}

$fecha = JFILE::read(ACTUALIZ."ACTUALIZACION.txt");
echo "****FIN DEL PROCESO DE ACTUALIZACION****<br />";
echo "Fecha: ".$fecha."<br/>";
$mensaje.="Fecha: ".$fecha;
$mail = new mailto();
$mensaje = '<img alt="Tu Condominio Fácil" src="https://tucondominiofacil.com.ve/wp-content/uploads/2017/04/cropped-angelica2-2.png" id="logo" width="130" height="80"><br><br>'
        . '<div style="color:rgb(0,0,0);font-family:Helvetica;font-size:15px!important;font-weight:400;line-height:22px!important;color:#000000">'.$mensaje;
$mensaje.= '</div><div style="display: block;width: 60px;height: 2px;margin:10px 0;background-color:#1e73be ;position: relative;float:left"></div>'
        .'<div style="display: block;width: 60px;height: 2px;margin:10px 0;background-color:#9c9ea1;position: relative;float:left"></div>';

$r = $mail->enviar_email("Actualización página web ".$fecha,$mensaje, "", 'angelica.benigno@gmail.com',"");
        
if ($r=="") {
    echo "Email de confirmación enviado con éxito\n";
} else {
    echo "Falló el envio del emailo de ejecución del proceso\n";
}
echo "Cierre esta ventana para finalizar.";
