<?php

class Pato_Form_Calificaciones_Correccion extends Gatuf_Form {
	private $kardex;
	function initFields ($extra = array ()) {
		$this->kardex = $extra['kardex'];
		
		$choices = array ('No aprobatoria' => array ('NA' => 0), 'Aprobatorias' => array ('7.0' => 7.0, '7.5' => 7.5, '8.0' => 8.0, '8.5' => 8.5, '9.0' => 9.0, '9.5' => 9.5, '10' => 10));
		
		$this->fields['calificacion'] = new Gatuf_Form_Field_Float (
			array (
				'required' => true,
				'label' => 'Nueva calificación',
				'initial' => $this->kardex->calificacion,
				'help_text' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function clean () {
		if ($this->cleaned_data['calificacion'] == $this->kardex->calificacion) {
			throw new Gatuf_Form_Invalid ('Parece que eligió la misma calificación, ¿seguro de que es una corrección?');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data;
	}
}
