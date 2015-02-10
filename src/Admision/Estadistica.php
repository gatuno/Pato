<?php
Gatuf::loadFunction ('Admision_DB_getDB');

class Admision_Estadistica extends Gatuf_Model {
	/* Manejador de la tabla de estadisticas */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'estadisticas';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'aspirante' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Admision_Aspirante',
			),
			'medio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 120,
			       'blank' => false,
			),
			'informes' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 100,
			       'blank' => false,
			),
			'entrar' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'size' => 100,
			       'blank' => false,
			),
		);
		
		$this->_con = Admision_DB_getDB ();
	}
}
