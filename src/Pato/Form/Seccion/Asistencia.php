<?php

class Pato_Form_Seccion_Asistencia extends Gatuf_Form {
	private $seccion;
	public function initFields($extra=array()) {
		$this->seccion = $extra['seccion'];
		
		$alumnos = $this->seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		
		foreach ($alumnos as $alumno) {
			$sql = new Gatuf_SQL ('nrc=%s AND alumno=%s', array ($this->seccion->nrc, $alumno->codigo));
			$asistencia = Gatuf::factory ('Pato_Asistencia')->getOne ($sql->gen ());
			
			$this->fields[$alumno->codigo] = new Gatuf_Form_Field_Integer (
				array (
					'required' => true,
					'label' => (string) $alumno,
					'initial' => ($asistencia === null) ? '' : $asistencia->asistencia,
					'help_text' => '',
					'min' => 0,
					'max' => 100,
					'widget_attrs' => array (
						'size' => 3,
					),
			));
		}
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$alumnos = $this->seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		
		foreach ($alumnos as $alumno) {
			$sql = new Gatuf_SQL ('nrc=%s AND alumno=%s', array ($this->seccion->nrc, $alumno->codigo));
			$asistencia = Gatuf::factory ('Pato_Asistencia')->getOne ($sql->gen ());
			
			if ($asistencia === null) {
				$asistencia = new Pato_Asistencia ();
				$asistencia->nrc = $this->seccion;
				$asistencia->alumno = $alumno;
				
				$asistencia->asistencia = $this->cleaned_data[$alumno->codigo];
				
				$asistencia->create ();
			} else {
				$asistencia->asistencia = $this->cleaned_data[$alumno->codigo];
				
				$asistencia->update ();
			}
		}
	}
}
