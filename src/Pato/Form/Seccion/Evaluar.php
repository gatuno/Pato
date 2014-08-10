<?php

class Pato_Form_Seccion_Evaluar extends Gatuf_Form {
	private $porcentaje, $seccion;
	public function initFields($extra=array()) {
		$this->porcentaje = $extra['porcentaje'];
		$this->seccion = $extra['seccion'];
		
		$alumnos = $this->seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		
		$choices = array ('No aprobatorias' => array ('IN' => -3, 'SD' => -2, 'NA' => 0), 'Aprobatorias' => array ('7.0' => 7.0, '7.5' => 7.5, '8.0' => 8.0, '8.5' => 8.5, '9.0' => 9.0, '9.5' => 9.5, '10' => 10));
		
		$eval_id = $this->porcentaje->evaluacion;
		/* Revisar por SD e IN en las asistencias */
		foreach ($alumnos as $alumno) {
			$sql = new Gatuf_SQL ('nrc=%s AND alumno=%s AND evaluacion=%s', array ($this->seccion->nrc, $alumno->codigo, $eval_id));
			
			$boleta = Gatuf::factory ('Pato_Boleta')->getOne ($sql->gen ());
			
			$this->fields[$alumno->codigo.'_'.$eval_id] = new Gatuf_Form_Field_Float (
				array (
					'required' => true,
					'label' => (string) $alumno,
					'initial' => ($boleta === null) ? -1 : $boleta->calificacion,
					'help_text' => '',
					'widget_attrs' => array (
						'choices' => $choices,
					),
					'widget' => 'Gatuf_Form_Widget_SelectInput',
			));
		}
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$alumnos = $this->seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		$eval = new Pato_Evaluacion ($this->porcentaje->evaluacion);
		
		foreach ($alumnos as $alumno) {
			$sql = new Gatuf_SQL ('nrc=%s AND alumno=%s AND evaluacion=%s', array ($this->seccion->nrc, $alumno->codigo, $eval->id));
			
			$boleta = Gatuf::factory ('Pato_Boleta')->getOne ($sql->gen ());
			
			if ($boleta === null) {
				$boleta = new Pato_Boleta ();
				$boleta->nrc = $this->seccion;
				$boleta->evaluacion = $eval;
				$boleta->alumno = $alumno;
				
				$boleta->calificacion = $this->cleaned_data[$alumno->codigo.'_'.$eval->id];
				
				$boleta->create ();
			} else {
				$boleta->calificacion = $this->cleaned_data[$alumno->codigo.'_'.$eval->id];
				
				$boleta->update ();
			}
		}
	}
}
