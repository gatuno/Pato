<?php

Gatuf::loadFunction ('Gatuf_HTTP_URL_urlForView');

class Pato_Form_Login_PasswordReset extends Gatuf_Form {
	protected $user = null;
	
	public function initFields($extra=array()) {
		$this->fields['key'] = new Gatuf_Form_Field_Varchar(
		                           array('required' => true,
		                           'label' => 'El código de verificación',
		                           'initial' => $extra['key'],
		                           'widget' => 'Gatuf_Form_Widget_HiddenInput',
		));
		$this->fields['password'] = new Gatuf_Form_Field_Varchar(
		                           array('required' => true,
		                           'label' => 'Su contraseña',
		                           'initial' => '',
		                           'widget' => 'Gatuf_Form_Widget_PasswordInput',
		                           'help_text' => 'Su contraseña debe ser díficil de encontrar para otras personas.',
		                           'widget_attrs' => array(
		                                             'maxlength' => 50,
		                                             'size' => 15,
		                           ),
		));
		$this->fields['password2'] = new Gatuf_Form_Field_Varchar(
		                           array('required' => true,
		                           'label' => 'Confirme su contraseña',
		                           'initial' => '',
		                           'widget' => 'Gatuf_Form_Widget_PasswordInput',
		                           'widget_attrs' => array(
		                                             'maxlength' => 50,
		                                             'size' => 15,
		                           ),
		));
	}
	
	public function clean () {
		if ($this->cleaned_data['password'] != $this->cleaned_data['password2']) {
			throw new Gatuf_Form_Invalid ('Las contraseñas deben de coincidir');
		}
		
		if (strlen ($this->cleaned_data['password']) < 6) {
			throw new Gatuf_Form_Invalid ('Su nueva contraseña debe contener al menos 6 caracteres');
		}
		
		$bloqueadas = Gatuf::config ('blocked_passwords', array ());
		$bloqueadas[] = $this->user->codigo;
		
		$nueva = $this->cleaned_data['password'];
		
		if (in_array ($nueva, $bloqueadas)) {
			$this->cleaned_data['password'] = '';
			$this->cleaned_data['password2'] = '';
			throw new Gatuf_Form_Invalid ('La nueva contraseña es insegura. Por favor escriba una nueva contraseña');
		}
		
		return $this->cleaned_data;
	}
	
	public function clean_key () {
		$this->cleaned_data ['key'] = trim ($this->cleaned_data['key']);
		
		if (false === ($cres = Pato_Form_Login_PasswordInputKey::checkKeyHash ($this->cleaned_data['key']))) {
			throw new Gatuf_Form_Invalid ('El código de verificación no es válido. Pruebe a copiarlo y pegarlo directamente desde el correo de recuperación');
		}
		
		$this->user = Pato_Form_Login_PasswordInputKey::getKeyUser ($cres);
		
		return $this->cleaned_data['key'];
	}
	
	function save($commit=true) {
		if (!$this->isValid()) {
			throw new Exception ('Cannot save an invalid form');
		}
		
		$this->user->setPassword ($this->cleaned_data['password']);
		if ($commit) {
			$this->user->update ();
			
			$params = array('user' => $this->user);
                       Gatuf_Signal::send('Gatuf_User::passwordUpdated',
                              'Pato_Form_PasswordReset', $params);
               }
		
		return $this->user;
	}
}
