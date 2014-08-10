<?php

class Pato_Form_SeleccionarAlumno extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['alumno'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => true,
				'label' => 'Alumno',
				'initial' => '',
				'help_text' => 'El codigo, nombre o apellidos del alumno a matricular',
				'widget_attrs' => array(
					'json' => Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::buscarJSON'),
					'min_length' => 2,
				),
				'widget' => 'Gatuf_Form_Widget_AutoCompleteInput',
		));
	}
	
	public function clean_alumno () {
		$codigo = $this->cleaned_data['alumno'];
		
		$alumno = new Pato_Alumno ();
		if (false === ($alumno->get ($codigo))) {
			throw new Gatuf_Form_Invalid ('Alumno inexistente');
		}
		
		return $codigo;
	}
		
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		return new Pato_Alumno ($this->cleaned_data['alumno']);
	}
}
