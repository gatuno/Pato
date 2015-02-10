<?php

class Admision_Form_Convocatoria_Agregar extends Gatuf_Form {
	public function initFields($extra=array()) {
		$choices_cal = array ();
		
		foreach (Gatuf::factory ('Pato_Calendario')->getList (array ('order' => 'clave DESC')) as $cal) {
			$choices_cal[$cal->descripcion] = $cal->clave;
		}
		
		$this->fields['calendario'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Calendario',
				'initial' => '',
				'help_text' => 'Calendario en el que van a ingresar los alumnos.',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'choices' => $choices_cal,
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Descripcion',
				'initial' => '',
				'help_text' => 'Nombre de la visible de la convocatoria',
				'widget_attrs' => array (
					'size' => 70,
				),
		));
		
		$this->fields['apertura'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => false,
				'label' => 'Apertura',
				'initial' => '',
				'help_text' => 'La convocatoria no aceptará solicitudes antes de esta fecha.',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
		
		$this->fields['cierre'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => false,
				'label' => 'Cierre',
				'initial' => '',
				'help_text' => 'La convocatoria no aceptará solicitudes después de esta fecha.',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
	}
	
	public function clean () {
		$inicio = $this->cleaned_data ['apertura'];
		$fin = $this->cleaned_data ['cierre'];
		
		if ($inicio > $fin) {
			throw new Gatuf_Form_Invalid ('La fecha y hora de apertura son posteriores a la fecha y hora de cierre');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		$convocatoria = new Admision_Convocatoria ();
		
		$convocatoria->setFromFormData ($this->cleaned_data);
		
		$convocatoria->create ();
		
		return $convocatoria;
	}
}
