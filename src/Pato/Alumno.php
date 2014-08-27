<?php

class Pato_Alumno extends Gatuf_Model {
	/* Manejador de la tabla Alumnos */
	public $_model = __CLASS__;
	
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
			       'size' => 1,
			       'default' => 'M'
			),
		);
		
		$this->default_order = 'apellido ASC, nombre ASC';
	}
	
	function postSave ($create = false) {
		if ($create) {
			/* Crear el usuario correspondiente */
			$user = new Pato_User ();
			$user->login = $this->codigo;
			$user->type = 'a'; /* Alumno */
			$user->administrator = false;
			$user->password = Gatuf_Utils::getPassword (8);
			if (isset ($this->_data['email'])) {
				$user->email = $this->email;
				/* TODO: Mandar el correo de bienvenida con la contraseña */
			} else {
				$user->email = '';
			}
			
			$user->create ();
			$this->user = $user;
		}
	}
	
	function getUser () {
		$sql = new Gatuf_SQL ('login=%s', $this->codigo);
		$this->user = Gatuf::factory ('Pato_User')->getOne (array ('filter' => $sql->gen ()));
	}
	
	public function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verAlumno', array ($this->codigo)).'">'.$this->codigo.'</a>';
	}
	
	public function get_current_inscripcion () {
		/* Recoger todas las inscripciones y regresar única activa */
		$inscripciones = $this->get_inscripciones_list (array ('filter' => 'egreso IS NULL', 'order' => 'clave DESC'));
		
		if (count ($inscripciones) == 0) return null;
		foreach ($inscripciones as $ins) {
			$estatus = $ins->get_estatus ();
			
			if ($estatus->activo) return $ins;
		}
		
		return null;
	}
	
	function __toString () {
		return $this->apellido.' '.$this->nombre.' ('.$this->codigo.')';
	}
}
