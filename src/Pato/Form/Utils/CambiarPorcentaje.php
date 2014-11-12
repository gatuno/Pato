<?php

class Pato_Form_Utils_CambiarPorcentaje extends Gatuf_Form {
	public function initFields($extra=array()) {
		$choices = array ();
		foreach (Gatuf::factory ('Pato_GPE')->getList () as $gpe) {
			$choices [$gpe->descripcion] = array ();
			foreach ($gpe->get_pato_evaluacion_list () as $eval) {
				$choices [$gpe->descripcion][$eval->descripcion] = $eval->id;
			}
		}
		
		$this->fields['evaluacion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Forma de evaluacion',
				'initial' => '',
				'help_text' => 'La forma de evaluación para cambiar porcentajes',
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
				'min' => 0,
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$data = array ('evaluacion' => $this->cleaned_data['evaluacion'], 'porcentaje' => $this->cleaned_data['porcentaje']);
		
		return $data;
	}
}

