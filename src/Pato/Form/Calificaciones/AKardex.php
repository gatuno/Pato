<?php

class Pato_Form_Calificaciones_AKardex extends Gatuf_Form {
	function initFields ($extra = array ()) {
		$cal_ops = array ();
		
		foreach (Gatuf::factory ('Pato_Calendario')->getList () as $cal) {
			$cal_ops[$cal->descripcion] = $cal->clave;
		}
		
		$this->fields['calendario'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Calendario',
				'initial' => $extra['cal_activo'],
				'help_text' => 'El calendario a procesar',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $cal_ops,
				),
		));
		
		$gpe_ops = array ();
		
		foreach (Gatuf::factory ('Pato_GPE')->getList () as $gpe) {
			$gpe_ops[$gpe->descripcion] = $gpe->id;
		}
		
		$this->fields['modalidad'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Modalidad',
				'initial' => '',
				'help_text' => 'La forma de evaluación a procesar',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $gpe_ops,
				),
		));
	}
	
	public function save ($commit = true) {
		$datos = array ('cal' => $this->cleaned_data['calendario'], 'gpe' => $this->cleaned_data['modalidad']);
		
		return $datos;
	}
}
