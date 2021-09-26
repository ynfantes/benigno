<?php

class cartelera extends db implements crud {
    public $tabla = "cartelera_general";
    
    public function actualizar($id, $data) {
        return $this->update($this->tabla, $data, array("id" => $id));
    }

    public function borrar($id) {
        return $this->delete($this->tabla, array("id" => $id));
    }

    public function borrarTodo() {
        return $this->delete($this->tabla);
    }

    public function insertar($data) {
        return $this->insert($this->tabla, $data);
    }

    public function listar() {
        if ($this->tabla=='cartelera_inmueble') {
            $sql = "select cartelera_inmueble.*,inmueble.nombre_inmueble from cartelera_inmueble join inmueble on 
                    cartelera_inmueble.inmueble = inmueble.id where eliminar=0";
            return $this->query($sql);
            
        } else {
            return $this->select("*", $this->tabla,Array("eliminar"=>0),null,Array("fecha"=>"desc"));
        }
    }
    
    public function listarCarteleraInmueble($inmueble) {    
        return $this->select('*', $this->tabla,Array('eliminar'=>0,'inmueble'=>$inmueble),null,Array('fecha'=>'desc'));
    }
    
    
    public function listarBoletinesInmueble($inmueble) {
        return $this->select("*","boletines",Array("eliminado"=>0,"id_inmueble"=>$inmueble),null,Array("id"=>"desc"));
    }
    public function ver($id) {
        return $this->select("*",$this->tabla,array("id"=>$id));
    }
    
    public function boletinPerteneceAlCondominio($cedula,$boletin_id) {
        
        $query = "select boletines.* from boletines join propiedades on boletines.id_inmueble = propiedades.id_inmueble "
                . "where propiedades.cedula='".$cedula."' and boletines.id=".$boletin_id;
        
        $result = $this->dame_query($query);
        
        if ($result['suceed']) {
            return $result['data'];
        } else {
            return false;
        }
    }
            
}
