<?php

class Pato_Form_Solicitud_Suficiencia_Actualizar extends Gatuf_Form {
	private $suficiencia;
	public function initFields ($extra = array ()) {
		$this->suficiencia = $extra['suficiencia'];
		
		$todoslosmaestros = Gatuf::factory ('Pato_Maestro')->getList (
		                    array ('order' => array ('Apellido ASC', 'Nombre ASC')));
		$choices_m = array();
		foreach ($todoslosmaestros as $m) {
			$choices_m[$m->apellido . ' ' . $m->nombre] = $m->codigo;
		}
		
		$this->fields['maestro'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Profesor',
				'initial' => $this->suficiencia->maestro,
				'help_text' => 'Sujeto a aprobación por el director de carrera',
				'widget_attrs' => array (
					'choices' => $choices_m,
				),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['terms'] = new Gatuf_Form_Field_Boolean (
			array (
				'required' => true,
				'label' => 'Acepto los términos y condiciones de las suficiencias.',
				'initial' => '',
		));
	}
	
	public function clean_terms() {
		if (!$this->cleaned_data['terms']) {
			throw new Gatuf_Form_Invalid('Sabemos que es aburrido, pero tienes que aceptar los términos y condiciones de las suficiencias');
		}
		return $this->cleaned_data['terms'];
	}
	
	public function save ($commit = true) {
		$this->suficiencia->setFromFormData ($this->cleaned_data);
		
		if ($commit) {
			$this->suficiencia->update ();
		}
		
		return $this->suficiencia;
	}
}
