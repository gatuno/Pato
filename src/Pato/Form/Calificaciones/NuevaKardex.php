<?php

class Pato_Form_Calificaciones_NuevaKardex extends Gatuf_Form {
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
		
		$modalidad = '';
		if (isset ($extra['gpe'])) $modalidad = $extra['gpe'];
		
		$this->fields['gpe'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Modalidad',
				'initial' => $modalidad,
				'help_text' => 'La forma de evaluación a procesar',
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $gpe_ops,
				),
		));
		
		$materia = '';
		if (isset ($extra['materia'])) $materia = $extra['materia'];
		
		$choices = array ();
		foreach (Gatuf::factory ('Pato_Carrera')->getList () as $car) {
			$choices[$car->descripcion] = array ();
			foreach ($car->get_materias_list () as $m) {
				$choices[$car->descripcion][$m->clave . ' - ' . $m->descripcion] = $m->clave;
			}
		}
		
		$this->fields['materia'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Materia',
				'initial' => $materia,
				'help_text' => 'El nombre completo de la materia',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_DobleInput',
		));
		
		$this->fields['alumno'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => true,
				'label' => 'Alumno',
				'initial' => '',
				'help_text' => 'El codigo, nombre o apellidos del alumno a matricular',
				'widget_attrs' => array(
					'json' => Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::buscarJSON'),
					'min_length' => 2,
				),
				'widget' => 'Gatuf_Form_Widget_AutoCompleteInput',
		));
		
		$choices = array ('No aprobatoria' => array ('NA' => 0), 'Aprobatorias' => array ('7.0' => 7.0, '7.5' => 7.5, '8.0' => 8.0, '8.5' => 8.5, '9.0' => 9.0, '9.5' => 9.5, '10' => 10));
		
		$this->fields['calificacion'] = new Gatuf_Form_Field_Float (
			array (
				'required' => true,
				'label' => 'Calificación',
				'initial' => 0,
				'help_text' => '',
				'widget_attrs' => array (
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function clean_alumno () {
		$codigo = $this->cleaned_data['alumno'];
		
		$alumno = new Pato_Alumno ();
		if (false === ($alumno->get ($codigo))) {
			throw new Gatuf_Form_Invalid ('Alumno inexistente');
		}
		
		return $codigo;
	}
	
	public function clean () {
		$sql = new Gatuf_SQL ('alumno=%s AND calendario=%s AND materia=%s AND gpe=%s', array ($this->cleaned_data['alumno'], $this->cleaned_data['calendario'], $this->cleaned_data['materia'], $this->cleaned_data['gpe']));
		
		$count = Gatuf::factory ('Pato_Kardex')->getList (array ('count' => true, 'filter' => $sql->gen ()));
		
		if ($count != 0) {
			throw new Gatuf_Form_Invalid ('El alumno ya tiene una calificación en Kardex para esta materia, calendario y modalidad. Haga una corrección de kardex');
		}
		
		$sql = new Gatuf_SQL ('alumno=%s AND aprobada=1 AND materia=%s', array ($this->cleaned_data['alumno'], $this->cleaned_data['materia']));
		
		$count = Gatuf::factory ('Pato_Kardex')->getList (array ('count' => true, 'filter' => $sql->gen ()));
		
		if ($count != 0) {
			throw new Gatuf_Form_Invalid ('El alumno ya aprobó la materia '.$this->cleaned_data['materia'].'. Por lo tanto, no puede aprobar dos veces la misma materia, ¿o sí?');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		$kardex = new Pato_Kardex ();
		
		$kardex->setFromFormData ($this->cleaned_data);
		
		if ($this->cleaned_data['calificacion'] >= 7) {
			$kardex->aprobada = true;
		}
		
		if ($commit) {
			$kardex->create ();
		}
		
		return $kardex;
	}
}
