<?php

Gatuf::loadFunction ('Admision_DB_getDB');

class Admision_CupoCarrera extends Gatuf_Model {
	/* Manejador de la tabla de cupos para las carreras */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'cupo_carreras';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'carrera' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Carrera',
			),
			'convocatoria' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Admision_Convocatoria',
			),
			'cupo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => true,
			),
		);
		
		$this->_con = Admision_DB_getDB ();
	}
}
