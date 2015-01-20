<?php

class Pato_Log_Kardex extends Gatuf_Model {
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'log_kardex';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'usuario' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_User',
			),
			'kardex' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'blank' => false,
			       'model' => 'Pato_Kardex',
			       'relate_name' => 'correcciones',
			),
			'vieja' =>
			array (
			       'type' => 'Gatuf_DB_Field_Float',
			       'blank' => false,
			       'is_null' => true,
			       'decimal_places' => 1,
			       'max_digits' => 5,
			),
			'nueva' =>
			array (
			       'type' => 'Gatuf_DB_Field_Float',
			       'blank' => false,
			       'is_null' => true,
			       'decimal_places' => 1,
			       'max_digits' => 5,
			),
			'timestamp' =>
			array (
			       'type' => 'Gatuf_DB_Field_Datetime',
			       'blank' => false,
			),
		);
	}
	
	function preSave ($create = true) {
		if ($create) {
			/* Tomar el timestamp actual */
			$this->timestamp = gmdate ('Y-m-d H:i:s');
		}
	}
}
