<?php

class Pato_Form_Edificio_Agregar extends Gatuf_Form {

	public function initFields($extra=array()) {
		
		$this->fields['clave'] = new Gatuf_Form_Field_Varchar (
			array(
				'required' => true,
				'label' => 'Clave',
				'initial' => '',
				'help_text' => 'La clave del edificio',
				'max_length' => 5,
				'min_length' => 3,
				'widget_attrs' => array(
					'maxlength' => 5,
				),
		));
		
		$this->fields['descripcion'] = new Gatuf_Form_Field_Varchar(
			array(
				'required' => true,
				'label' => 'Edificio',
				'initial' => '',
				'help_text' => 'El nombre completo del edificio',
				'max_length' => 100,
				'widget_attrs' => array(
					'maxlength' => 100,
					'size' => 30,
				),
			
		));
	}
	
	public function clean_clave () {
		$clave = mb_strtoupper($this->cleaned_data['clave']);
		
		if (!preg_match ("/^[a-zA-Z]+\d*$/", $clave)) {
			throw new Gatuf_Form_Invalid('La clave del edificio se forma de letras seguidas de números.');
		}

		$sql = new Gatuf_SQL('clave=%s', array($clave));
        $l = Gatuf::factory('Pato_Edificio')->getList(array('filter'=>$sql->gen(),'count' => true));
        if ($l > 0) {
            throw new Gatuf_Form_Invalid('Este edificio ya existe');
        }
        
        return $clave;
	}
	
	public function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save the model from an invalid form.');
		}
		
		$edificio = new Pato_Edificio ();
		
		$edificio->setFromFormData ($this->cleaned_data);
		if ($commit) $edificio->create();
		
		return $edificio;
	}
}
