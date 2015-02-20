<?php

class Admision_Form_Aspirante_Seleccionar extends Gatuf_Form {
	public function initFields ($extra=array()) {
		$this->fields['aspirante'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Aspirante',
				'initial' => '',
				'help_text' => 'El codigo, nombre o apellidos del aspirante.',
				'widget_attrs' => array(
					'json' => Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::buscarJSON'),
					'min_length' => 2,
				),
				'widget' => 'Gatuf_Form_Widget_AutoCompleteInput',
		));
	}
	
	public function clean_aspirante () {
		$codigo = $this->cleaned_data['aspirante'];
		
		$aspirante = new Admision_Aspirante ();
		if (false === ($aspirante->get ($codigo))) {
			throw new Gatuf_Form_Invalid ('Datos erróneos');
		}
		
		return $codigo;
	}
		
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		return new Admision_Aspirante ($this->cleaned_data['aspirante']);
	}
}
