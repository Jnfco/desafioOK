<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Principal extends CI_Controller {
	var $cantidadBlog=5;
    public function __construct() {
		parent::__construct();
		//Cambiar el nombre de moelo a modelo
        $this->load->model('Modelo');
        $this->load->helper('url');
        //$cantidadBlog = 2;
        header("Content-Type: text/html; charset=utf-8");
        header("Accept-Encoding: gzip | compress | deflate | br| identity| * ");
	}
	//Cambiar el nombre de la funcion de indes a index
	public function index(){
        $this->load->view("ingreso",array("error"=>""));
    }
	function loginIntra(){
		$this->load->view("ingreso",array("error"=>""));
	}
	function loginIntra2(){
		$rut 	= $this->input->post("rut");
		$clave 	= $this->input->post("clave");
		if($this->Modelo->loginIntra($rut,$clave)){//Falta en el modelo
			$infor = $this->Modelo->buscaInfoPersona($rut); //Falta en el modelo
			$nombre = "";
			$acceso = "";
			$id = 0;
			$super =0;
			$areas = array(); $roles = array(); $idAreas = array();
			$i=0;
			//print_r($infor->result());
			foreach ($infor->result() as $row) {
				$nombre = $row->nombre;
				$acceso = $row->acceso;
				$super 	= $row->rol;
				$id 	= $row->id;

				if($infor->num_rows()>=1){
					if(isset($row->nombreCentro) && isset($row->idce) && isset($row->rol)){
						$areas[$i] = $row->nombreCentro;
						$idAreas[$i] = $row->idce;
						$i++;
					}
				}
			}
			$data   =   array(
            	'logged_in' => TRUE,
            	'rut' 		=> $rut,
            	'id' 		=> $id,
            	'acceso'	=> $acceso,
            	'nombre'	=> $nombre,
            	'areas' 	=> $areas,
            	'idAreas' 	=> $idAreas,
            	'super' 	=> $super
        	);
        	$this->session->set_userdata($data);
			echo '{"res":"0"}';
		}else{
			$data   =   array(
	            'logged_in' => FALSE
	        );
			$this->session->set_userdata($data);
			echo '{"res":"1"}';
		}
	}
	function intranet(){
		if($this->session->userdata("logged_in")==TRUE){
			$this->load->view("index");
			$this->load->view("footer2");
		}else{
			$this->loginIntra();
		}
	}

	// se cambia el nombre de log_out a logout
	function logout(){
		$this->session->sess_destroy();
		redirect(base_url()."Intranet");
	}
	function newArea(){
		$res['areas'] = $this->Modelo->listarAreas();
		$this->load->view("newArea",$res);
	}
	function addNewArea(){
		$area = $this->input->post("area");
		$direccion = $this->input->post("direccion");
		$op   = $this->input->post("op");
		$id   = $this->input->post("id");
		if(strlen(trim($area))>0):
			$res['error'] = $this->Modelo->addNewArea($area,$direccion,$op,$id);
			$res['links'] = $this->Modelo->buscaLinks()->result();
		else:
			$res['error'] = true;
		endif;
		echo json_encode($res);
	}
	function cambiarEstadoArea(){
		$estado = $this->input->post("estado");
		$id 	= $this->input->post("id");
		$this->Modelo->cambiarEstadoArea($estado,$id);
	}
	function nuevoProcedimiento(){
		//Buscar los últimos procedimientos almacenados...
		$result = $this->Modelo->buscarUltimosRegistros();
		$res['data'] = $result->result();
		$res['cant'] = $result->num_rows();
		$ultimo =0;
 		foreach ($result->result() as $row) {
			$ultimo = $row->id;
		}
		$res['ultimo'] =$ultimo;
		$this->load->view("nuevoProcedimiento",$res);
	}
	function saveProcedimiento(){
		$descripcion = $this->input->post("descripcion");
		$ingreso 	 = $this->input->post("ingreso");
		$egreso 	 = $this->input->post("egreso");
		$this->Modelo->saveProcedimiento($descripcion,$ingreso,$egreso);
	}
	function editRegistro(){
		$descripcion = $this->input->post("descripcion");
		$ingreso 	 = $this->input->post("ingreso");
		$egreso 	 = $this->input->post("egreso");
		$id 	 = $this->input->post("id");
		$this->Modelo->editRegistro($descripcion,$ingreso,$egreso, $id);
	}

	//Se crea la función para eliminar un registro la cual llama al modelo
	function deleteRegistro(){
		$id = $this->input->post("id");
		$this->Modelo->deleteRegistro($id);
	}

	//Se agrega la funcion de buscar registro la cual llama al modelo para la consulta
	function buscarRegistro(){
		$fecInic = $this->input->post("fecInic");
		$fecTerm = $this->input->post("fecTerm");
		$result = $this->Modelo->buscarRegistro($fecInic,$fecTerm);
		$res['data'] = $result ->result();
		$res['cant'] = $result->num_rows();
		$ultimo =0;
		foreach ($result -> result() as $row){
			$ultimo =$row->id;
		}
		$res['ultimo']=$ultimo;
		echo json_encode($res);
	}

	function traeMasRegistros(){
		$desde = $this->input->post("desde");
		$result = $this->Modelo->buscarUltimosRegistrosDesde($desde);
		$res['data'] = $result->result();
		$res['cant'] = $result->num_rows();
		$ultimo =0;
 		foreach ($result->result() as $row) {
			$ultimo = $row->id;
		}
		$res['ultimo'] =$ultimo;
		echo json_encode($res);
	}
// Se elimina la función saveImagen ya que en este sistema no se trabaja con imagenes
//Se elimina la funcion buscarPacienteRut, ya que no se está trabajando con pacientes en este sistema
//Se elimina la función traePacientes ya que en este sistema no se utiliza la gestión de pacientes
//Se elimina la función buscarFichasPacientes ya que en este sistema no se manejan las fichas ni pacientes

	function newUser(){
		$res['users'] = $this->Modelo->listarUsers();
		$this->load->view("newUser",$res);
	}
	function addNewUser(){
		$rut 			= $this->input->post("rut");
		$nombre 		= $this->input->post("nombre");
		$clave 			= $this->input->post("clave");
		$fNac 			= $this->input->post("fNac");
		$especialidad 	= $this->input->post("especialidad");
		$cargo 			= $this->input->post("cargo");
		$op = $this->input->post("op");
		$id = $this->input->post("id");
		if(strlen(trim($rut))>0 && strlen(trim($nombre))>0 && strlen(trim($clave))>0):
			$res['error'] = $this->Modelo->addNewUser($rut, $nombre, $clave,$fNac,$especialidad,$cargo,$op,$id);
		else:
			$res['error'] = true;
		endif;
		echo json_encode($res);
	}
	function buscaUsuario(){
		$rut = $this->input->post("rut");
		$res = $this->Modelo->buscaUsuario($rut);
		echo json_encode($res);
	}
	function cambiarEstadoUser(){
		$estado = $this->input->post("estado");
		$id 	= $this->input->post("id");
		$this->Modelo->cambiarEstadoUser($estado,$id);
	}
	function newLink(){
		$res['links'] 		= $this->Modelo->listarLinks();
		$res['usuarios'] 	= $this->Modelo->listarUsersActivos();
		$res['areas'] 		= $this->Modelo->listarAreasActivas();
		$this->load->view("newLink",$res);
	}
	function addNewLink(){
		$usuario 	=$this->input->post("usuario");
		$area 		=$this->input->post("area");
		$op 		=$this->input->post("op");
		$rol 		=$this->input->post("rol");
		$id 		=$this->input->post("id");
		$res['error'] = $this->Modelo->addNewLink($usuario,$area,$op,$rol,$id);
		echo json_encode($res);
	}
	function cambiarEstadoLink(){
		$estado = $this->input->post("estado");
		$id 	= $this->input->post("id");
		$this->Modelo->cambiarEstadoLink($estado,$id);
	}
	function deleteLink(){
		$id = $this->input->post("id");
		$this->Modelo->deleteLink($id);
	}
	function entrarArea(){
		$idArea 	= $this->input->post("area");
		$nombreArea = $this->input->post("nombre");

		$data['area'] 	= $nombreArea;
		$data['id']   	= $idArea;

		//Debo buscar el listado de archivos que se han cargado en esa area.
		$this->load->view("entrarArea",$data);
	}
	function subirFichero(){
		$idarea 	 	= $this->input->post("idarea");
        $cadenaArchivos = $this->input->post("cadenaArchivos");
        //No recuerdo para que era el parámentro OP
		//$op 			= $this->input->post("op");
		$fecha 			= Date("Y-m-d");
		$user 			= $this->session->userdata("rut");
		$ubicacion 		= "uploads/".$idarea."/";
		$this->Modelo->subirFichero($idarea,$cadenaArchivos,$fecha,$user,$ubicacion,'all');
	}
	function buscaFicherosSubidos(){
		$idarea  = $this->input->post("idarea");
		$data['rol'] = $this->input->post("rolarea");
		//Cambiar la busqueda de los ficheros, es "all" o iguales al rut del usuario conectado.
		$data['res'] = $this->Modelo->buscaFicherosSubidos($idarea);
		$this->load->view("listadoFicheros",$data);
	}
	function eliminarFichero(){
		$id 		= $this->input->post("id");
		$ubicacion 	= $this->input->post("ubicacion");
		$area 		= $this->input->post("area");
		$nombre 	= $this->input->post("nombre");
		$this->Modelo->eliminarFichero($id,$ubicacion,$nombre);
	}
	function cambiarEstadoFile(){
		$estado = $this->input->post("estado");
		$id 	= $this->input->post("id");
		$this->Modelo->cambiarEstadoFile($estado,$id);
	}

	//Se elimina la funcion extract, esto es debido a que esta función se encarga de subir archivos y en este sistema esto no se utiliza

	function validaClave0(){
		$claveVieja = $this->input->post("claveVieja");
		$res = $this->Modelo->loginIntra($this->session->userdata("rut"),$claveVieja);
		echo json_encode(array("res"=>$res));
	}
	function cambiarClave(){
		$clave = $this->input->post("clave");
		$this->Modelo->cambiarClave($clave);
	}

	//Se elimina la función leerDocumento, ya que en este sistema no se utiliza la lectura de documentos
	// Se elimina la funcion buscarLeidos, ya que en el sistema no se utiliza la lectura de archivos

	//Se agrega la funcion para llamar a la vista de reportes

	function newReport(){

		$result = $this->Modelo->buscarUltimosRegistros();
		$res['usuarios'] = $this->Modelo->listarUsersActivos();
		$res['data'] = $result->result();
		$res['total'] = $this->Modelo->buscarUltimoSaldo()->result();
		$this->load->view("newReport",$res);

	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */