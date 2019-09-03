<?php

class Pato_CorreoPendiente extends Gatuf_Model {
	/* Manejador de la tabla de correos para envio posterior */
	public $_model = __CLASS__;
	
	function init () {
		$this->_a['table'] = 'correo_pendiente';
		$this->_a['model'] = __CLASS__;
		$this->primary_key = 'id';
		
		$this->_a['cols'] = array (
			'id' =>
			array (
			       'type' => 'Gatuf_DB_Field_Sequence',
			       'blank' => true,
			),
			'destinatario' =>
			array (
			       'type' => 'Gatuf_DB_Field_Email',
			       'blank' => false,
			       'size' => 64,
			),
			'asunto' =>
			array (
			       'type' => 'Gatuf_DB_Field_Varchar',
			       'blank' => false,
			       'size' => 200,
			),
			'cuerpo_txt' =>
			array (
			       'type' => 'Gatuf_DB_Field_Text',
			       'blank' => false,
			),
			'cuerpo_html' =>
			array (
			       'type' => 'Gatuf_DB_Field_Text',
			       'blank' => false,
			),
			'estado' =>
			array (
			       'type' => 'Gatuf_DB_Field_Integer',
			       'blank' => false,
			       'default' => 1,
			),
		);
	}
	
	public function block_for_sending () {
		$con = $this->_con = &Gatuf::db($this);
		
		$req = 'UPDATE '.$this->getSqlTable().' SET '."\n";
		$req .= 'estado = 2'."\n";
		$req .= ' WHERE '.$this->primary_key.' = '.$this->_toDb($this->_data[$this->primary_key], $this->primary_key).' AND estado = 1';
		
		$con->execute ($req);
		
		$affected = $con->getAffectedRows ();
		
		if ($affected == 0) {
			return false;
		}
		
		$this->_data['estado'] = 2;
		
		return true;
	}
}
