<?php

class Pato_Form_Usuario_Password extends Gatuf_Form {
	private $user;
	
	public function initFields ($extra = array ()) {
		$this->user = $extra['usuario'];
		
		$this->fields['actual'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Contraseña actual',
				'help_text' => 'Su actual contraseña',
				'widget' => 'Gatuf_Form_Widget_PasswordInput',
				'widget_attrs' => array (
					'size' => 30,
				),
		));
		
		$this->fields['nueva'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Nueva contraseña',
				'help_text' => 'La nueva contraseña',
				'widget' => 'Gatuf_Form_Widget_PasswordInput',
				'widget_attrs' => array (
					'size' => 30,
				),
		));

		$this->fields['repite'] = new Gatuf_Form_Field_Varchar (
			array (
				'required' => true,
				'label' => 'Confirmar contraseña',
				'help_text' => 'Por favor escriba de nuevo la contraseña',
				'widget' => 'Gatuf_Form_Widget_PasswordInput',
				'widget_attrs' => array (
					'size' => 30,
				),
		));
	}
	
	public function clean () {
		$actual = $this->cleaned_data['actual'];
		$nueva = $this->cleaned_data['nueva'];
		$repite = $this->cleaned_data['repite'];
		
		if (!$this->user->checkPassword($actual) ) {
			throw new Gatuf_Form_Invalid ('Su contraseña actual no es correcta');
		}
		
		if ($nueva != $repite) {
			throw new Gatuf_Form_Invalid ('La nueva contraseña no coincide en ambos campos');
		}
		
		if (strlen ($nueva) < 6) {
			throw new Gatuf_Form_Invalid ('Su nueva contraseña debe contener al menos 6 caracteres');
		}
		
		$bloqueadas = Gatuf::config ('blocked_passwords', array ());
		$bloqueadas[] = $this->user->codigo;
		
		if (in_array ($nueva, $bloqueadas)) {
			$this->cleaned_data['nueva'] = '';
			$this->cleaned_data['repite'] = '';
			throw new Gatuf_Form_Invalid ('La nueva contraseña es insegura. Por favor escribe una nueva contraseña');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save a invalid form');
		}
		
		$this->user->setPassword($this->cleaned_data['nueva']);
		
		if ($commit) {
			$this->user->update();
		}
		return $this->user;
	}
}
