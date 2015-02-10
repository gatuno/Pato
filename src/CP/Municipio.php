<?php
Gatuf::loadFunction ('CP_DB_getDB');

class CP_Municipio extends Gatuf_Model {
	/* Manejador de la tabla de municipios */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'municipios';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'estado' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_Estado',
			),
			'numero' =>
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
		);
		
		$this->_con = CP_DB_getDB ();
	}
}
