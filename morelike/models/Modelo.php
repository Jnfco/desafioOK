<?php
// cambiar el extends de CIModel a CI_Model
class Modelo extends CI_Model{
    //cambiar atributo de logininra de ruty a rut
    function loginIntra($rut,$clave){
        $this->db->select("*");
        $this->db->where("rut",$rut);
        $this->db->where("clave",md5($clave));
        $this->db->where("estado",0);
        $res = $this->db->get("usuario")->num_rows();
        if($res > 0){
            return true;
        }
        return false;
    }
    function buscaUsuario($rut){
        $this->db->select("*");
        $this->db->where("rut",$rut);
        $res = $this->db->get("usuario")->result();
        return $res;
    }
    function buscaInfoPersona($rut){
        $this->db->select("*");
        $this->db->where("rut",$rut);
        $res = $this->db->get("usuario")->result();
        $super =0;
        foreach ($res as $row) {
            $super = $row->rol;
        }
        // descomentar if de condicion de tipo de usuario ya que venia comentado
        if($super == 0){
            $sql = "select usuario.id, usuario.nombre, usuario.rut, usuario.acceso, usuario.rol, centro.id as idce, centro.nombre as nombreCentro from usuario
                join usce on usuario.id = usce.idus
                join centro on centro.id = usce.idce where usuario.rut = '".$rut."' order by centro.nombre";
        }else{
            $sql = "select * from usuario where usuario.rut = '".$rut."';";
        }
        //echo $sql;
        $res = $this->db->query($sql);
        if($res->num_rows() == 0){
            $sql = "select * from usuario where usuario.rut = '".$rut."';";
            $res = $this->db->query($sql);
        }
        $data['acceso'] = Date("Y-m-d H:i:s");
        $this->db->where("rut",$rut);
        $this->db->update("usuario",$data);
        //este metodo no debe ir aqui
        //$this->historialIntranet("Tabla: usuario - Cambio info Usuario - Rut: ".$rut." acceso: ".$data['acceso']);
        //print_r($res);
        return $res;
    }

    // se elimina la función savePaciente ya que en este sistema no se trabaja con pacientes

    function saveProcedimiento($descripcion, $ingreso, $egreso){
        //Falta calcular el saldo...
        //Saldo será la diferencia entre el saldo anterior +Ingreso -Egreso
        $sql = "select saldo from registros order by id desc limit 1";
        $res = $this->db->query($sql);
        $saldo =0;
        foreach ($res->result() as $row) {
            $saldo = $row->saldo;
        }
        $saldo = $saldo + $ingreso - $egreso;
        $data = array("descripcion"=>$descripcion,"fecha"=>Date("Y-m-d H:i:s"),"ingreso"=>$ingreso,"egreso"=>$egreso, "saldo"=>$saldo);
        $this->db->insert("registros",$data);
    }

    //Se agrega la función para eliminar un registro en modelo, la cual realiza la consulta correspondiente para realizar la eliminación según el id del registro seleccionado
    function deleteRegistro($id){
        /*
        $saldoN =0;
        $sql = "select saldo from registros order by id desc limit 1";
        $res = $this->db->query($sql);
        foreach ($res->result()as $row){
            $saldoN =$row->saldo;
        }
        $saldoN = $saldoN -$saldo;*/
        $sql = "select saldo from registros where id < ".$id." order by id desc limit 1";
        $res = $this->db->query($sql);
        $saldo =0;
        foreach ($res->result() as $row) {
            $saldo = $row->saldo;
        }


        //Borrar registro
        $this->db->where("id", $id);
        $this->db->delete("registros");


        //Metodo para modificar el saldo de los registros que le siguen al registro modificado
        $sql = "select * from registros where id > ".$id." order by id";
        $res = $this->db->query($sql);
        foreach($res->result() as $row){
            $saldo = $saldo + $row->ingreso - $row->egreso;
            $data = array("saldo"=>$saldo);
            $this->db->where("id", $row->id);
            $this->db->update("registros", $data);
        }

    }

    //Se agrega la función para realizar la busqueda de los registros con la consulta
    function buscarRegistro($fecInic,$fecTerm){
        
        $sql = "select * from registros where fecha between '".$fecInic. " 00:00' and '" .$fecTerm. " 00:00' order by fecha desc";
        return $this->db->query($sql);
    }

    function editRegistro($descripcion, $ingreso, $egreso, $id){
        //Falta calcular el saldo...
        //Saldo será la diferencia entre el saldo anterior +Ingreso -Egreso
        $sql = "select saldo from registros where id between 1 and ".$id." order by id desc limit 1";
        $res = $this->db->query($sql);
        $saldo =0;
        foreach ($res->result() as $row) {
            $saldo = $row->saldo;
        }
        $saldo = $saldo + $ingreso - $egreso;
        $data = array("descripcion"=>$descripcion,"ingreso"=>$ingreso,"egreso"=>$egreso, "saldo"=>$saldo);
        $this->db->where("id", $id);
        $this->db->update("registros",$data);
        //Metodo para modificar el saldo de los registros que le siguen al registro modificado
        $sql = "select * from registros where id > ".$id." order by id";
        $res = $this->db->query($sql);
        foreach($res->result() as $row){
            $saldo = $saldo + $row->ingreso - $row->egreso;
            $data = array("saldo"=>$saldo);
            $this->db->where("id", $row->id);
            $this->db->update("registros", $data);
        }
    }

    // Se eliminan las funciones buscarPacienteRut , buscarPacientes y buscarFichasPacientes, debido a que en este sistema no se trabaja con pacientes

    function listarAreas(){
        $this->db->select("*");
        $this->db->order_by("estado");
        $this->db->order_by("nombre");
        return $this->db->get("centro")->result();
    }
    function listarAreasActivas(){
        $this->db->select("*");
        $this->db->where("estado",0);
        return $this->db->get("centro")->result();
    }
    function addNewArea($area,$direccion,$op,$id){
        if($op==0){
            //echo "QL";
            $sql = "select * from centro where centro.nombre = '".$area."'";
            $res = $this->db->query($sql)->num_rows();
            if($res > 0 ){
                return true;
            }else{
                $data['nombre'] = $area;
                $data['direccion'] = $direccion;
                $this->db->insert("centro",$data);
                $this->addNewLink($this->session->userdata("id"),$this->db->insert_id(),0,0);
                $this->historialIntranet("Tabla: centro - Insercion centro - nombre: ".$area);
                return false;
            }
        }else{
            $sql = "select * from centro where centro.nombre = '".$area."' and id !=".$id;
            $res = $this->db->query($sql)->num_rows();
            if($res > 0 ){
                return true;
            }else{
                $data['nombre'] = $area;
                $data['direccion'] = $direccion;
                $this->db->where("id",$id);
                $this->db->update("centro",$data);
                $this->historialIntranet("Tabla: centro - Cambio nombre - Id: ".$id." nombre: ".$area);
                return false;
            }
        }
    }
    function cambiarEstadoArea($estado,$id){
        $data['estado'] = $estado;
        $this->db->where("id",$id);
        $this->db->update('centro',$data);
        $this->historialIntranet("Tabla: centro - Cambio de Estado - Id: ".$id." estado: ".$estado);

        $this->db->where("idce",$id);
        $this->db->update('usce',$data);
        $this->historialIntranet("Tabla: ua - Cambio de Estado - Id: ".$id." estado: ".$estado);
    }
    function listarUsers(){
        $this->db->select("*");
        $this->db->where("rol",0);
        $this->db->order_by("nombre");
        return $this->db->get("usuario")->result();
    }
    function listarUsersActivos(){
        $this->db->select("*");
        //$this->db->where("rol",0);
        $this->db->where("estado",0);
        $this->db->order_by("nombre");
        return $this->db->get("usuario")->result();
    }
    function addNewUser($rut, $nombre, $clave,$fNac,$especialidad,$cargo, $op, $id){
        //si op = 0 es Insert de nuevo usuario.. si op = 1 es update.
        if($op == 0){
            $sql = "select * from usuario where rut = '".$rut."'";
            $res = $this->db->query($sql)->num_rows();
            if($res > 0 ){
                return true;
            }else{
                $data['rut'] = $rut;
                $data['nombre'] = $nombre;
                $data['clave'] = md5($clave);
                $data['fnac'] = $fNac;
                $data['especialidad'] = $especialidad;
                $data['rol']    = $cargo;
                $this->db->insert("usuario",$data);
                $this->historialIntranet("Tabla: usuario - Insercion de User - Rut: ".$rut." Nombre: ".$nombre);

                return false;
            }
        }else{
            //Funcion engañosa, dado a que necesitamos editar el usuario existente, entonces preguntamos por rut y id, siendo el id un dato autoincrement el cual actua como pseudo clave primaria.
            //Valido que el nuevo usuario no esté repetido con su rut
            $sql = "select * from usuario where rut = '".$rut."' and id =".$id;
            $res = $this->db->query($sql)->num_rows();
            if($res <= 0 ){//Significa que hay otro usuario anterior con el mismo rut
                return true;
            }else{
                //Solo debo actualizar la clave si es que el usuario ingresó una nueva clave, por lo que deberé comparar el hash que viene con el que ya está en la base de datos.
                //Faltaba la funcion para transformar la clave a hash.
                $sql = "select * from usuario where clave = '".md5($clave)."' and id =".$id;
                $res1 = $this->db->query($sql)->num_rows();
                if($res1 == 0){
                    $data['clave'] = md5($clave);
                }
                $data['rut'] = $rut;
                $data['nombre'] = $nombre;
                //Se agregan los campos faltantes para editar, fecha nacimiento, especialidad y rol
                $data['fnac'] = $fNac;
                $data['especialidad'] = $especialidad;
                $data['rol']    = $cargo;
                if($op == 2){
                    $data['estado']    = 1;

                }
                $this->db->where("id",$id);
                $this->db->update("usuario",$data);
                $this->historialIntranet("Tabla: usuario - Cambio de User - Id: ".$id." Nombre: ".$nombre);
                return false;
            }
        }
    }
    function cambiarEstadoUser($estado,$id){
        $data['estado'] = $estado;
        $this->db->where("id",$id);
        $this->db->update('usuario',$data);
        $this->historialIntranet("Tabla: usuario - Cambio de Estado User - Id: ".$id." Estado: ".$estado);
    }
    function listarLinks(){
        //se cambia la consulta en usce.idce por usce.id
        $sql ="select usuario.id as idu, usuario.nombre, usuario.rut, usuario.estado as estadousuario, centro.id as idc, centro.nombre, centro.estado as estadocentro, usce.estado as estadousce, usce.id as idusce from usce join usuario on usuario.id = usce.idus join centro on centro.id = usce.idce order by usuario.nombre, centro.nombre";
        //$sql = "select usuario.id as idu, usuario.nombre, usuario.rut, usuario.estado as estadousuario, centro.id as idc, centro.nombre, centro.estado as estadocentro, usce.estado as estadousce, usce.idce from usce join usuario on usuario.id = usce.idus join centro on centro.id = usce.idce order by usuario.nombre, centro.nombre";
        return $this->db->query($sql)->result();
    }
    function buscaLinks(){
        $sql = "select * from usce join centro on centro.id = usce.idce where idus = ".$this->session->userdata("id")." and centro.estado=0 order by centro.nombre asc";
        $res = $this->db->query($sql);
        return $res;
    }
    function addNewLink($usuario,$area,$op,$id){
        if($op==0){
            $sql = "select * from usce where usce.idus = ".$usuario." and usce.idce = ".$area;
            $res = $this->db->query($sql);
            if($res->num_rows() == 0){
                $data['idce'] = $area;
                $data['idus'] = $usuario;
                $data['fecha'] = Date("Y-m-d");
                $this->db->insert("usce",$data);
                $this->historialIntranet("Tabla: usce - Insercion de Link - Area: ".$area." Usuario: ".$usuario);
                return false;
            }else{
                return true;
            }
        }else{
            //Se cambia en la consulta de usce.ida a usce.idce
            $sql = "select * from usce where usce.idus = ".$usuario." and usce.idce = ".$area." and usce.id !=".$id;
            $res = $this->db->query($sql);
            if($res->num_rows() == 0){
                $data['idce'] = $area;
                $data['idus'] = $usuario;
                $data['rol'] = $rol;
                $this->db->where("id",$id);
                $this->db->update("usce",$data);
                $this->historialIntranet("Tabla: usce - Cambio de Link - Area: ".$area." Usuario: ".$usuario." Rol: ".$rol);
                return false;
            }else{
                return true;
            }
        }
    }
    function cambiarEstadoLink($estado,$id){
        $data['estado'] = $estado;
        $this->db->where("id",$id);
        $this->db->update('usce',$data);
        $this->historialIntranet("Tabla: usce - Cambio Estado de Link ".$id." Estado: ".$estado);
    }
    function deleteLink($id){
        $this->db->where("id",$id);
        $this->db->delete("usce");
        $this->historialIntranet("Tabla: ua - Eliminacion de Link ".$id);
    }

    //Se eliminan las funciones subirFichero, buscarFicherosSubidos y eliminarFichero, ya que en este sistema no se trabaja con ficheros
   
    function cambiarEstadoFile($estado, $id){
        $data['estado'] = $estado;
        $this->db->where("id",$id);
        $this->db->update('fichero',$data);
        $this->historialIntranet("Tabla: Fichero - Archivo: ".$id." - Cambio de Estado a ".$estado);
    }
    function cambiarClave($clave){
        $data['clave'] = md5($clave);
        $this->db->where("rut",$this->session->userdata("rut"));
        $this->db->update("usuario",$data);
        $this->historialIntranet("Tabla: usuario - Cambio de clave del usuario");
    }
    function historialIntranet($accion){
        $data['user']   = $this->session->userdata("rut");
        $data['fecha']  = Date("Y-m-d H:i:s");
        $data['accion'] = $accion;
        $this->db->insert("historial",$data);
    }

    //Se elimina la función leerDocumento y buscarLeidos, ya que en este sistema no se leen documentos ni se trabajan con estos
    function rutCompleto($rut){
        $sql = "select rut from usuario where rut like '".$rut."%'";
        $res = $this->db->query($sql)->result();
        foreach ($res as $row) {
            return $row->rut;
        }
    }

    function buscarUltimosRegistros(){
        $sql = "select * from registros order by fecha desc limit 10";
        return $this->db->query($sql);
    }
    function buscarUltimosRegistrosDesde($desde){
        $sql = "select * from registros where id <".$desde." order by fecha desc limit 10";
        return $this->db->query($sql);
    }
}
?>
