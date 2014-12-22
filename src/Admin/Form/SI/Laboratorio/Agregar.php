<?php

class Admin_Form_SI_Laboratorio_Agregar extends Gatuf_Form {
	public function initFields ($extra=array()) {
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => '',
				'help_text' => 'Nombre del laboratorio',
				'max_length' => 64,
				'widget_attrs' => array (
					'maxlength' => 64,
				),
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception ('Cannot save the model from an invalid form.');
		}
		
		$lab = new Admin_SI_Laboratorio ();
		
		$lab->setFromFormData ($this->cleaned_data);
		if ($commit) $lab->create();
		
		return $lab;
	}
}
