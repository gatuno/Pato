<?php

class Pato_Form_SeleccionarEvaluacion extends Gatuf_Form {
	public function initFields($extra=array()) {
		$choices = array ();
		
		foreach (Gatuf::factory ('Pato_Evaluacion')->getList () as $eval) {
			$choices[$eval->descripcion] = $eval->id;
		}
		
		$this->fields['evaluacion'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Forma de evaluación',
				'initial' => '',
				'help_text' => 'La forma de evaluación que quiere revisar',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function save ($commit = true) {
		return new Pato_Evaluacion ($this->cleaned_data['evaluacion']);
	}
}
