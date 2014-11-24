<?php

class Pato_Form_Calificaciones_AKardexSelectivo extends Gatuf_Form {
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
		
		$choices_m = array ();
		foreach (Gatuf::factory ('Pato_Carrera')->getList () as $car) {
			$choices_m[$car->descripcion] = array ();
			foreach ($car->get_materias_list () as $m) {
				$choices_m[$car->descripcion][$m->clave . ' - ' . $m->descripcion] = $m->clave;
			}
		}
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Materia',
				'initial' => '',
				'help_text' => 'El nombre completo de la materia',
				'widget_attrs' => array(
					'choices' => $choices_m,
				),
				'widget' => 'Gatuf_Form_Widget_DobleInput',
		));
	}
	
	public function save ($commit = true) {
		$datos = array ('cal' => $this->cleaned_data['calendario'], 'gpe' => $this->cleaned_data['modalidad'], 'materias' => array ($this->cleaned_data['materia']));
		
		return $datos;
	}
}
