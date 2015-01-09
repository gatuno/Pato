<?php

class Pato_Form_Estatus_CambioCarrera extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices = array ();
		
		foreach (Gatuf::factory ('Pato_Calendario')->getList (array ('filter' => 'oculto=0', 'order' => 'clave DESC')) as $cal) {
			$choices[$cal->descripcion] = $cal->clave;
		}
		
		$this->fields['egreso'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Egreso',
				'initial' => Pato_Calendario_getDefault (),
				'help_text' => 'Señale el último calendario que cursó en su antigua carrera',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
		
		$carreras = array ();
		foreach (Gatuf::factory ('Pato_Carrera')->getList () as $c) {
			$carreras[$c->descripcion] = $c->clave;
		}
		
		$this->fields['carrera'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nueva carrera',
				'initial' => '',
				'help_text' => 'Nueva carrera a la que está inscrito',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $carreras,
				),
		));
		
		$this->fields['turno'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nuevo turno',
				'initial' => 'M',
				'help_text' => 'Muy errático este campo, poco relevante. Solo se presenta para los fines estadísticos. Para todo lo demás se IGNORA',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => array ('Matutino' => 'M', 'Vespertino' => 'V'),
				),
		));
	}
	
	public function save ($commit = true) {
		return $this->cleaned_data;
	}
}
