<?php

class Pato_Form_Asignatura_Planeacion_Agregar extends Gatuf_Form {
	private $nrc;
	
	public function initFields ($extra = array ()) {
		$this->nrc = $extra['seccion'];
		
		$this->fields['programada'] = new Gatuf_Form_Field_Date (
			array (
				'required' => true,
				'label' => 'Fecha programada',
				'initial' => '',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$this->fields['unidad'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Unidad de aprendizaje',
				'initial' => '',
				'help_text' => 'Nombre de la unidad de aprendizaje que está programando',
				'max_length' => 300,
				'widget_attrs' => array (
					'size' => 30,
				),
		));
		
		$this->fields['resultado'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Resultado de aprendizaje',
				'initial' => '',
				'help_text' => '¿¿¿???',
				'max_length' => 300,
				'widget_attrs' => array (
					'size' => 30,
				),
		));
		
		$this->fields['estrategia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Estrategia de aprendizaje',
				'initial' => '',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
		
		$this->fields['evidencia'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Evidencia',
				'initial' => '',
				'help_text' => '',
				'max_length' => 300,
				'widget_attrs' => array (
					'size' => 30,
				),
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$plan = new Pato_Asignatura_Planeacion ();
		
		$plan->nrc = $this->nrc;
		$plan->setFromFormData ($this->cleaned_data);
		
		if ($commit) $plan->create ();
		
		return $plan;
	}
}
