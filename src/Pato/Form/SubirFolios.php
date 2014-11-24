<?php
function Pato_Form_SubirFolios_dontmove ($field) {
	/* No mover el archivo */
}

class Pato_Form_SubirFolios extends Gatuf_Form {
	public function initFields ($extra = array ()) {
		$choices = array ();
		
		foreach (Gatuf::factory ('Pato_Calendario')->getList (array ('order' => 'clave DESC')) as $cal) {
			$choices[$cal->descripcion] = $cal->clave;
		}
		
		$this->fields['calendario'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Calendario',
				'initial' => Pato_Calendario_getDefault (),
				'widget' => 'Gatuf_Form_Widget_SelectInput',
				'widget_attrs' => array (
					'choices' => $choices,
				),
		));
		
		$this->fields['archivo'] = new Gatuf_Form_Field_File (
			array (
				'required' => true,
				'label' => 'Archivo',
				'help_text' => 'El archivo a subir',
				'move_function' => 'Pato_Form_SubirFolios_dontmove',
				'move_function_params' => array(),
				'max_size' => 10485760,
			));
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save the form in a valid state');
		}
		
		$data = array ('archivo' => $this->data['archivo']['tmp_name'], 'calendario' => $this->cleaned_data['calendario']);
		
		return $data;
	}
}
