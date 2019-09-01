<?php

class Pato_Form_Seccion_Actualizar extends Gatuf_Form {
	private $seccion;
	
	public function initFields($extra=array()) {
		$this->seccion = $extra['seccion'];
		
		$this->fields['seccion'] = new Gatuf_Form_Field_Varchar(
			array (
				'required' => true,
				'label' => 'Seccion',
				'initial' => $this->seccion->seccion,
				'help_text' => 'La sección, como A01 o B03. Se utiliza "A" para Lomas de Tejeda, "B" para Cajititlán, "X" para extracurriculares, "R" para secciones de revalidación, "S" para secciones de suficiencias, "E" para especiales y "C" Por competencia',
				'max_length' => 15,
				'widget_attrs' => array(
					'maxlength' => 15,
				)
		));
		
		$todoslosmaestros = Gatuf::factory ('Pato_Maestro')->getList (
		                    array ('order' => array ('Apellido ASC', 'Nombre ASC')));
		$choices = array ();
		foreach ($todoslosmaestros as $m) {
			$choices[$m->apellido . ' ' . $m->nombre] = $m->codigo;
		}
		
		$this->fields['cupo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Cupo',
				'initial' => $this->seccion->cupo,
				'help_text' => 'El cupo de este grupo. Recuerde que el cupo se actualiza automáticamente en base a los salones programados para este NRC',
		));
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Integer(
			array(
				'required' => true,
				'label' => 'Profesor',
				'initial' => $this->seccion->maestro,
				'help_text' => 'El profesor de este grupo',
				'widget_attrs' => array(
					'json' => Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::buscarJSON'),
					'min_length' => 2,
				),
				'widget' => 'Gatuf_Form_Widget_AutoCompleteInput',
		));

		$choices = array('No asignado' => 0) + $choices;
		
		$this->fields['suplente'] = new Gatuf_Form_Field_Integer(
			array(
				'required' => false,
				'label' => 'Suplente',
				'initial' => $this->seccion->suplente,
				'help_text' => 'El suplente para este grupo',
				'widget_attrs' => array(
					'choices' => $choices,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
	}
	
	public function clean_seccion () {
		$seccion = mb_strtoupper($this->cleaned_data['seccion']);
		
		if ($this->seccion->seccion == $seccion) return $this->seccion->seccion;
		
		if (!preg_match("/^[ABESRXC]\d+$/", $seccion)) {
			throw new Gatuf_Form_Invalid('La sección de la materia tiene que comenzar con las letras A, B, E, S, R, X, C seguidas de un número.');
		}
		
		$sql = new Gatuf_SQL ('materia=%s AND seccion=%s', array ($this->seccion->materia, $seccion));
		
		$l = Gatuf::factory ('Pato_Seccion')->getList (array ('filter' => $sql->gen (), 'count' => true));
		if ($l > 0) {
			throw new Gatuf_Form_Invalid ('La sección '.$seccion.' para la materia '.$this->seccion->materia.' ya está en uso');
		}
		
		return $seccion;
	}
	
	public function clean_suplente () {
		if ($this->cleaned_data['suplente'] === 0) {
			return null;
		}
		
		return $this->cleaned_data['suplente'];
	}
	
	public function clean () {
		if ($this->cleaned_data['suplente'] != null && $this->cleaned_data['suplente'] == $this->cleaned_data['maestro']) {
			throw new Gatuf_Form_Invalid ('El profesor asignado y el suplente deben ser diferentes');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$this->seccion->setFromFormData ($this->cleaned_data);
		if ($commit) $this->seccion->update();
		
		return $this->seccion;
	}
}
