<?php

class Pato_Form_Planeacion_AgregarUnidad extends Gatuf_Form {
	private $materia, $maestro;
	public function initFields ($extra = array ()) {
		$this->materia = $extra['materia'];
		$this->maestro = $extra['maestro'];
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Unidad de aprendizaje',
				'help_text' => 'Nombre de la unidad de aprendizaje. No es necesario poner "Unidad 1"',
				'initial' => '',
				'max_length' => 300,
				'widget_attrs' => array (
					'size' => 40,
				),
		));

		$this->fields['resultado'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Resultado de aprendizaje',
				'help_text' => 'Describa el resultado de esta unidad de aprendizaje.',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$unidad = new Pato_Planeacion_Unidad ();
		$unidad->setFromFormData ($this->cleaned_data);
		$unidad->maestro = $this->maestro;
		$unidad->materia = $this->materia;
		
		if ($commit) $unidad->create ();

		return $unidad;
	}
}
