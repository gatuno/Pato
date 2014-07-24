<?php

class Pato_Maestro extends Gatuf_Model {
	/* Manejador de la tabla Maestros */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'maestros';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'codigo';
		
		$this->_a['cols'] = array (
			'codigo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
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
			'grado' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'default' => 'L',
			       'size' => 1,
			),
			'tiempo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Char',
			       'blank' => false,
			       'size' => 1,
			       'default' => null, // Tiempo Completo, Medio tiempo
			       'is_null' => true,
			),
		);
		
		$this->default_order = 'apellido ASC, nombre ASC';
	}
	
	function getUser () {
		$sql = new Gatuf_SQL ('login=%s', $this->codigo);
		$this->user = Gatuf::factory ('Pato_User')->getOne (array ('filter' => $sql->gen ()));
	}
	
	function displaylinkedcodigo ($extra=null) {
		return '<a href="'.Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($this->codigo)).'">'.$this->codigo.'</a>';
	}
	
	function __toString () {
		return $this->apellido.' '.$this->nombre.' ('.$this->codigo.')';
	}
	
	function displaygrado ($extra = null) {
		switch ($this->grado) {
			case 'I':
				return 'Ing.';
				break;
			case 'L':
				return 'Lic.';
				break;
			case 'D':
				if ($this->sexo == 'F') return 'Dra.';
				else return 'Dr.';
				break;
			case 'M':
				if ($this->sexo == 'F') return 'Mtra.';
				else return 'Mtro.';
				break;
			default:
				throw new Exception ('No implementado');
		}
	}
}
