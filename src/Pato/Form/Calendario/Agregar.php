<?php

class Pato_Form_Calendario_Agregar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'max_length' => 6,
				'widget_attrs' => array (
					'maxlength' => 6,
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Descripción del calendario',
				'initial' => '',
				'max_length' => 20,
				'widget_attrs' => array (
					'maxlength' => 20,
				),
		));
	}
	
	public function clean () {
		/* Verificar que no exista otro calendario con la misma clave */
		$sql = new Gatuf_SQL ('clave=%s', $this->cleaned_data['clave']);
		
		$l = Gatuf::factory ('Pato_Calendario')->getList (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($l > 0) {
			throw new Gatuf_Form_Invalid ('Ya existe otro calendario con la misma clave');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the form in a valid state');
		}
		
		$calendario = new Pato_Calendario ();
		
		$calendario->setFromFormData ($this->cleaned_data);
		
		if ($commit) $calendario->create ();
		
		return $calendario;
	}
}
