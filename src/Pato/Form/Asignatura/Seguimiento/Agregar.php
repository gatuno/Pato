<?php

class Pato_Form_Asignatura_Seguimiento_Agregar extends Gatuf_Form {
	private $plan;
	
	public function initFields ($extra = array ()) {
		$this->plan = $extra['plan'];
		
		$this->fields['realizada'] = new Gatuf_Form_Field_Date (
			array (
				'required' => true,
				'label' => 'Fecha realizada',
				'initial' => '',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_DateJSInput',
		));
		
		$this->fields['resultado'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Resultado',
				'initial' => '',
				'help_text' => '¿¿¿???',
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
		
		$this->fields['notas'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Notas',
				'initial' => '',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 80,
					'rows' => 10,
				),
		));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$seg = new Pato_Asignatura_Seguimiento ();
		
		$seg->plan = $this->plan;
		$seg->setFromFormData ($this->cleaned_data);
		
		if ($commit) $seg->create ();
		
		return $seg;
	}
}
