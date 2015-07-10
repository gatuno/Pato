<?php

class Pato_Form_Utils_Agenda extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices = array ();
		
		foreach (Gatuf::factory ('Pato_Calendario')->getList (array ('filter' => 'oculto=0', 'order' => 'clave DESC')) as $cal) {
			$choices[$cal->descripcion] = $cal->clave;
		}
		
		$this->fields['calendario'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Calendario',
				'initial' => Pato_Calendario_getDefault (),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
		
		$carreras = array ('Todas' => 'NULL');
		foreach (Gatuf::factory ('Pato_Carrera')->getList () as $c) {
			$carreras[$c->descripcion] = $c->clave;
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Carrera',
				'initial' => 'NULL',
				'help_text' => '',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $carreras,
				),
		));
		
		$this->fields['inicio'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => true,
				'label' => 'Apertura',
				'initial' => '',
				'help_text' => 'Fecha y hora de la apertura',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
		
		$this->fields['fin'] = new Gatuf_Form_Field_Datetime (
			array(
				'required' => true,
				'label' => 'Cierre',
				'initial' => '',
				'help_text' => 'Fecha y hora del cierre',
				'widget' => 'Gatuf_Form_Widget_DatetimeJSInput',
		));
	}
	
	public function save ($commit = true) {
		$data = array ('inicio' => $this->cleaned_data['inicio'], 'fin' => $this->cleaned_data['fin'], 'calendario' => $this->cleaned_data['calendario']);
		
		if ($this->cleaned_data['carrera'] == 'NULL') {
			$data['carrera'] = null;
		} else {
			$data['carrera'] = $this->cleaned_data['carrera'];
		}
		
		return $data;
	}
}
