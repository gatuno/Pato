<?php

class Pato_Form_Solicitud_Suficiencia_Aprobar extends Gatuf_Form {
	private $solicitudes;
	public function initFields ($extra = array ()) {
		$this->solicitudes = $extra['solicitudes'];
		
		$todoslosmaestros = Gatuf::factory ('Pato_Maestro')->getList (
		                    array ('order' => array ('Apellido ASC', 'Nombre ASC')));
		$choices_m = array('Ninguno' => 0);
		foreach ($todoslosmaestros as $m) {
			$choices_m[$m->apellido . ' ' . $m->nombre] = $m->codigo;
		}
		
		$choices_e = array ('Pendiente' => 0, 'Aprobada' => 1, 'Rechazada' => 2);
		
		foreach ($this->solicitudes as $solicitud) {
			$this->fields['maestro_'.$solicitud->id] = new Gatuf_Form_Field_Integer(
				array(
					'required' => false,
					'label' => 'Profesor asignado',
					'initial' => is_null ($solicitud->maestro) ? 0 : $solicitud->maestro,
					'help_text' => 'El profesor que va a aplicar la suficiencia. Elegir sólo si la solicitud es aprobada',
					'widget_attrs' => array(
						'choices' => $choices_m,
					),
					'widget' => 'Gatuf_Form_Widget_SelectInput',
			));
			
			$this->fields['estado_'.$solicitud->id] = new Gatuf_Form_Field_Integer (
				array (
					'required' => false,
					'label' => 'Estado',
					'initial' => $solicitud->estatus,
					'help_text' => '',
					'widget_attrs' => array(
						'choices' => $choices_e,
					),
					'widget' => 'Gatuf_Form_Widget_RadioInput',
			));
		}
	}
	
	public function clean () {
		foreach ($this->solicitudes as $sol) {
			$estado = $this->cleaned_data['estado_'.$sol->id];
			
			if ($estado == 1 && $this->cleaned_data['maestro_'.$sol->id] == 0) {
				throw new Gatuf_Form_Invalid ('Debe elegir un profesor para la solicitud no. '.$sol->id);
			}
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		$maestro = new Pato_Maestro ();
		foreach ($this->solicitudes as $sol) {
			$sol->estatus = $this->cleaned_data['estado_'.$sol->id];
			
			if ($sol->estatus == 1) { /* Solo si está aprobada asignamos profesor */
				$maestro->get ($this->cleaned_data['maestro_'.$sol->id]);
				
				$sol->maestro = $maestro;
			} else {
				$sol->maestro = null;
			}
			
			$sol->update ();
		}
	}
}
