<?php

class Admision_Form_Aspirante_SeleccionarConvocatoria extends Gatuf_Form {
	public function initFields($extra=array()) {
		$choices = array ();
		foreach (Gatuf::factory ('Admision_Convocatoria')->getList () as $convocatoria) {
			/* Revisar que esta convocatoria esté abierta */
			$cupos = $convocatoria->get_admision_cupocarrera_list (array ('count' => true));
			if ($cupos == 0) continue;
			$hora = gmdate ('Y/m/d H:i');
			$unix_time = strtotime ($hora);
		
			$unix_inicio = strtotime ($convocatoria->apertura);
			$unix_fin = strtotime ($convocatoria->cierre);
		
			if ($unix_time >= $unix_inicio && $unix_time <= $unix_fin) {
				/* La convocatoria está abierta */
				$choices[$convocatoria->descripcion] = $convocatoria->id;
			}
		}
		
		$this->fields['convocatoria'] = new Gatuf_Form_Field_Integer (
			array(
				'required' => true,
				'label' => 'Convocatoria',
				'initial' => '',
				'help_text' => 'FIXME: Poner texto aquí',
				'choices' => $choices,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function save ($commit=true) {
		return new Admision_Convocatoria ($this->cleaned_data['convocatoria']);
	}
}
