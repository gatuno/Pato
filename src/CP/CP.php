<?php
Gatuf::loadFunction ('CP_DB_getDB');

class CP_CP extends Gatuf_Model {
	/* Manejador de la tabla de codigos postales */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'codigos_postales';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'codigo' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'localidad' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 200,
			),
			'asentamiento' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_Asentamiento',
			),
			'municipio' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_Municipio',
			),
			'zona' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'CP_Zona',
			),
		);
		
		$this->_a['idx'] = array (
			'codigo_idx' =>
			array (
				   'col' => 'codigo',
				   'type' => 'index',
			),
		);
		
		$this->_con = CP_DB_getDB ();
	}
	
	public function display_full () {
		$municipio = $this->get_municipio ();
		
		return $this->localidad.' C.P. '.$this->codigo.' '.$municipio->nombre.', '.$municipio->get_estado()->nombre;
	}
}
