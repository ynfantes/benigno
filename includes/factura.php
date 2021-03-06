<?php
/**
 * Clase que mantiene la tabla factura
 *
 * @autor   Edgar Messia
 * @static  
 * @package     Valoriza2.Framework
 * @subpackage	FileSystem
 * @since	1.0
 */
class factura extends db implements crud {
    
    const tabla = "facturas";
    
    public function actualizar($id, $data){
        return db::update(self::tabla, $data, array("id" => $id));
    }

    public function borrar($id){
        return db::delete(self::tabla, array("id" => $id));
    }

    /**
     * Inserta un regristro en la tabla factura
     *
     * @param	Array	$data	Arreglo con la data
     * 
     * @return	Array	Retorna arreglo con parámetos del resultado
     * @since	1.0
     */
    public function insertar($data){
        return db::insert(self::tabla, $data);
    }

    public function insertar_detalle_factura($data) {
        return db::insert("factura_detalle",$data);
    }
    
    public function listar(){
       return db::select("*", self::tabla);
    }
    
    public function ver($id){
        return db::select("*",self::tabla,array("id"=>$id));
    }

    public function borrarTodo() {
        return db::delete(self::tabla);
    }
    
    public static function estadoDeCuenta($inmueble, $apto) {
//        $consulta = "select ".self::tabla.".*,(select count(*) from 
//                pago_detalle inner join pagos on pagos.id = pago_detalle.id_pago 
//                where id_factura = facturas.numero_factura) as pagado from ".self::tabla.
//                " where id_inmueble='".$inmueble."' and apto='".$apto.
//                "' order by periodo ASC";
        $consulta = "select * from ".self::tabla.
                " where id_inmueble='".$inmueble."' and apto='".$apto.
                "' and facturado > abonado order by periodo ASC";
        
        return db::query($consulta);
    }
    
    public function facturaPerteneceACliente($factura,$cedula) {
        
        $query = "select propiedades.* from propiedades join 
                facturas on facturas.apto = propiedades.apto and 
                facturas.id_inmueble = propiedades.id_inmueble 
                where facturas.numero_factura='".$factura."'";
        $result = $this->dame_query($query);
        if ($result['suceed']==true) {
            return $result['data'][0]['cedula']==$cedula;
        } else {
            return false;
        }
    }
}

?>
