<?php

class Admin_Form_Biblioteca_Equipo_Agregar extends Gatuf_Form {
	public function initFields ($extra=array()) {
		$opc_biblio = array ();
		
		foreach (Gatuf::factory ('Admin_Biblioteca')->getList () as $biblioteca) {
			$opc_biblio[$biblioteca->nombre] = $biblioteca->id;
		}
		
		$this->fields['biblioteca'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Biblioteca',
				'initial' => (isset ($extra['biblioteca']) ? $extra['biblioteca'] : 0),
				'help_text' => 'La biblioteca en la que el equipo da servicio',
				'choices' => $opc_biblio,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$opc_tipo = array ('Cañon' => 1, 'Extensión' => 2, 'Laptop' => 3, 'Bocinas' => 4);
		
		$this->fields['tipo'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Tipo',
				'initial' => 0,
				'help_text' => 'El tipo',
				'choices' => $opc_tipo,
				'widget' => 'Gatuf_Form_Widget_SelectInput',
		));
		
		$this->fields['nombre'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nombre',
				'initial' => '',
				'help_text' => 'Nombre para identificar al equipo',
				'max_length' => 50,
				'widget_attrs' => array (
					'maxlength' => 50,
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => false,
				'label' => 'Descripción',
				'initial' => '',
				'help_text' => 'Una breve descripción del equipo',
				'widget' => 'Gatuf_Form_Widget_TextareaInput',
				'widget_attrs' => array (
					'cols' => 40,
					'rows' => 4,
				),
		));
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception ('Cannot save the model from an invalid form.');
		}
		
		$equipo = new Admin_Biblioteca_Equipo ();
		
		$equipo->setFromFormData ($this->cleaned_data);
		$equipo->activo = true;
		if ($commit) $equipo->create();
		
		return $equipo;
	}
}
