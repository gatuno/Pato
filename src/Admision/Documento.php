<?php
Gatuf::loadFunction ('Admision_DB_getDB');

class Admision_Documento extends Gatuf_Model {
	/* Manejador de la tabla Documentos de admisión */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'documentos';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'descripcion' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 300,
			),
			'alumnos' =>
			array (
			       'type' => 'Gatuf_DB_Field_Manytomany',
			       'blank' => false,
			       'model' => 'Pato_Alumno',
			       'relate_name' => 'documentos',
			),
		);
		
		$this->default_order = 'descripcion ASC';
		
		$this->_con = Admision_DB_getDB ();
	}
}
