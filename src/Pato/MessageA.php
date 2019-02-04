<?php

class Pato_MessageA extends Gatuf_Model {
	public $_model = __CLASS__;
	
	public function init () {
		$this->_a['table'] = 'messages_a';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'type' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			),
			'user' =>
			array (
			       'type' => 'Gatuf_DB_Field_Foreignkey',
			       'model' => 'Pato_Alumno',
			       'blank' => false,
			       'relate_name' => 'messages',
			),
			'message' =>
			array (
			       'type' => 'Gatuf_DB_Field_Text',
			       'blank' => false,
			),
		);
	}
	
	function __toString () {
		return $this->message;
	}
}
