<?php

class Pato_Form_Calificaciones_Buscar extends Gatuf_Form {
	function initFields ($extra = array ()) {
		$choices = array ('Cualquier materia' => 'NULL');
		foreach (Gatuf::factory ('Pato_Materia')->getList () as $m) {
			$choices[$m->clave . ' - ' . $m->descripcion] = $m->clave;
		}
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => false,
				'label' => 'Materia',
				'initial' => 'NULL',
				'help_text' => 'El nombre completo de la materia',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['alumno'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => false,
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
	
	public function save ($commit = true) {
		return $this->cleaned_data;
	}
}
