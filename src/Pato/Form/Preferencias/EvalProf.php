<?php

class Pato_Form_Preferencias_EvalProf extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$abierto = $gconf->getVal ('evaluacion_profesores_abierta', false);
		
		$this->fields['abierta'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Evaluación a profesores abierta',
				'initial' => $abierto,
				'help_text' => 'Indica si los alumnos pueden o no evaluar a sus profesores',
		));
		
		$cal_conf = $gconf->getVal ('evaluacion_profesores_cal', '');
		
		$choices = array ();
		
		foreach (Gatuf::factory ('Pato_Calendario')->getList (array ('filter' => 'oculto=0', 'order' => 'clave DESC')) as $cal) {
			$choices[$cal->descripcion] = $cal->clave;
		}
		
		$this->fields['calendario'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Calendario',
				'initial' => $cal_conf,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'help_text' => 'Indica en qué calendario deben evaluar a los profesores',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
	}
	
	public function save ($commit = true) {
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		if ($this->cleaned_data['abierta']) {
			$eval = true;
		} else {
			$eval = false;
		}
		$gconf->setVal ('evaluacion_profesores_abierta', $eval);
		
		if ($eval) {
			$cal = $this->cleaned_data['calendario'];
			
			$gconf->setVal ('evaluacion_profesores_cal', $cal);
		} else {
			$gconf->setVal ('evaluacion_profesores_cal', '');
		}
	}
}
