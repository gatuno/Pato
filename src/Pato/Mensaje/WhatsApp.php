<?php

class Pato_Mensaje_WhatsApp extends Gatuf_Model {
	/* Manejador de la tabla de mensajes del whatsapp */
	public $_model = __CLASS__;
	
	function init() {
		$this->_a['table'] = 'mensajes_whats';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
		    'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'numero' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 12,
			),
			'mensaje' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 400,
			),
		);
	}
}
