<?php

class Admision_Form_Aspirante_Login extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['aspirante'] = new Gatuf_Form_Field_Integer (
			array (
				'required' => true,
				'label' => 'Número de aspirante',
				'help_text' => 'El número de aspirante que fué enviado a tu correo',
				'initial' => '',
		));
		
		$this->fields['password'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Contraseña',
				'help_text' => 'Escribe la contraseña justo como está en tu correo, respetando mayúsculas y minúsculas',
				'initial' => '',
				'widget' => 'Gatuf_Form_Widget_PasswordInput',
		));
	}
	
	public function clean () {
		$aspirante = new Admision_Aspirante ();
		
		if ($aspirante->get ($this->cleaned_data['aspirante']) === false) {
			/* Usuario inválido */
			throw new Gatuf_Form_Invalid ('El número de aspirante o la contraseña son inválidos');
		}
		
		if ($this->cleaned_data['password'] != $aspirante->token) {
			/* Contraseña inválida */
			throw new Gatuf_Form_Invalid ('El número de aspirante o la contraseña son inválidos');
		}
		
		return $this->cleaned_data;
	}
	public function save ($commit=true) {
		return new Admision_Aspirante ($this->cleaned_data['aspirante']);
	}
}
