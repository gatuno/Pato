<?php

class Pato_Form_Materia_AgregarEval extends Gatuf_Form {
	private $materia;
	
	public function initFields($extra=array()) {
		$choices = array ();
		$this->materia = $extra['materia'];
		
		foreach ($this->materia->getNotEvals ($extra['gp']) as $eval) {
			$choices [$eval->descripcion] = $eval->id;
		}
		
		$this->fields['evaluacion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Forma de evaluacion',
				'initial' => '',
				'help_text' => 'La forma de evaluación que aplica sobre esta materia',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['porcentaje'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => true,
				'label' => 'Porcentaje',
				'initial' => '20',
				'help_text' => 'La ponderación que recibe esta forma de evaluación',
				'min' => 1,
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$porcentaje = new Pato_Porcentaje ();
		$porcentaje->materia = $this->materia;
		$porcentaje->setFromFormData ($this->cleaned_data);
		
		$porcentaje->create ();
		
		return $porcentaje;
	}
}

