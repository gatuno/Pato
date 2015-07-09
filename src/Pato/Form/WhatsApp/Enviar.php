<?php

class Pato_Form_WhatsApp_Enviar extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$this->fields['texto'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Mensaje',
				'initial' => '',
				'help_text' => 'El mensaje no puede exceder de más de 100 caracteres. Cuide su ortografía.',
				'max_length' => 100,
				'widget_attrs' => array (
					'maxlength' => 100,
					'size' => '100',
				),
		));
	}
		
	public function save ($commit = true) {
		return $this->cleaned_data['texto'];
	}
}
