<?php

class Pato_Alumno extends Gatuf_Model {
	/* Manejador de la tabla Alumnos */
	public $_model = __CLASS__;
	public $session_key = '_GATUF_Gatuf_User_auth';
	public $administrator = false; /* FIXME: QUITAR ESTO */
	
	function init() {
		$this->_a['table'] = 'alumnos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'codigo';
		
		$this->_a['cols'] = array (
			'codigo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 8,
			),
			'nombre' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 70,
			),
			'apellido' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 140,
			),
			'sexo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'default' => 'M',
			       'size' => 1,
			),
			'email' =>
			array (
			       'type' => 'Gatuf_DB_Field_Email',
			       'blank' => false,
			),
			'password' =>
			array (
			       'type' => 'Gatuf_DB_Field_Password',
			       'blank' => false,
			       'size' => 150,
			),
			'active' =>
			array (
			       'type' => 'Gatuf_DB_Field_Boolean',
			       'default' => true,
			       'blank' => true,
			),
			'last_login' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => true,
			       'editable' => false,
			       'is_null' => true,
			       'default' => null,
			),
		);
		
		$this->default_order = 'apellido ASC, nombre ASC';
	}
	
	function postSave ($create = false) {
		/* TODO: Enviar el correo con la contraseña del usuario */
		if ($create) {
			$this->setPassword (Gatuf_Utils::getRandomString (8));
		}
	}
	
	function setPassword ($password) {
		$salt = Gatuf_Utils::getRandomString(5);
		$this->password = 'sha1:'.$salt.':'.sha1($salt.$password);
		return true;
	}
	
	function checkPassword ($password) {
		if ($this->password == '') {
			return false;
		}
		list ($algo, $salt, $hash) = explode(':', $this->password);
		if ($hash == $algo($salt.$password)) {
			return true;
		} else {
			return false;
		}
	}
	
	function isAnonymous () {
		return ('' === $this->codigo);
	}
	
	function getAllPermissions ($force=false) {
		return array ();
	}
	
	function hasPerm ($perm, $obj = null) {
		return false;
	}
	
	function hasAppPerms ($app) {
		return false;
	}
	
	function setMessage ($type, $message) {
		if ($this->isAnonymous ()) {
			return false;
		}
		
		$m = new Pato_MessageA ();
		$m->message = $message;
		$m->type = $type;
		$m->user = $this;
		
		return $m->create ();
	}
	
	function getAndDeleteMessages () {
		if ($this->isAnonymous ()) {
			return false;
		}
		$messages = new ArrayObject ();
		$ms = $this->get_messages_list ();
		foreach ($ms as $m) {
			$messages[] = array ('message' => $m->message, 'type' => $m->type);
			$m->delete ();
		}
		
		return $messages;
	}
	
	function get_type () {
		return 'a';
	}
	
	public function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($this->codigo)).'">'.$this->codigo.'</a>';
	}
	
	public function get_current_inscripcion () {
		/* Recoger todas las inscripciones y regresar única activa */
		$inscripciones = $this->get_inscripciones_list (array ('filter' => 'egreso IS NULL', 'order' => 'ingreso DESC'));
		
		if (count ($inscripciones) == 0) return null;
		foreach ($inscripciones as $ins) {
			//$estatus = $ins->get_estatus ();
			
			//if ($estatus->activo) return $ins;
			return $ins;
		}
		
		return null;
	}
	
	public function get_inscripcion_for_cal ($calendario) {
		/* Buscar la inscripcion para el alumno que está en el periodo indicado */
		$inscripciones = $this->get_inscripciones_list ();
		
		foreach ($inscripciones as $ins) {
			$cal = $ins->get_ingreso ();
			
			if ($calendario->anio < $cal->anio) continue;
			if ($calendario->anio == $cal->anio && $calendario->letra < $cal->letra) {
				/* No es la inscripción que busco */
				continue;
			}
			
			if ($ins->egreso != null) {
				/* También revisar la salida */
				$cal = $ins->get_egreso ();
				
				if ($calendario->anio > $cal->anio) continue;
				if ($calendario->anio == $cal->anio && $calendario->letra > $cal->letra) {
					/* No es la inscripción que busco */
					continue;
				}
			}
			
			return $ins;
		}
		
		return null;
	}
	function __toString () {
		return $this->apellido.' '.$this->nombre.' ('.$this->codigo.')';
	}
}
