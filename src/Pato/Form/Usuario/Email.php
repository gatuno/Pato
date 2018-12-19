<?php

class Pato_Form_Usuario_Email extends Gatuf_Form {
	private $usuario;
	
	public function initFields ($extra = array ()) {
		$this->user = $extra['usuario'];
		
		$this->fields['email'] = new Gatuf_Form_Field_Email (
			array (
				'required' => true,
				'label' => 'Correo',
				'initial' => '',
				'help_text' => 'Un correo válido.',
		));
	}
	
	public function clean () {
		$sql = new Gatuf_SQL ('email=%s', $this->cleaned_data ['email']);
		$count = Gatuf::factory ('Pato_User')->getCount(array('filter' => $sql->gen()));
		
		if ($count > 0) {
			throw new Gatuf_Form_Invalid ('El correo ya está en uso. Por favor elige uno diferente');
		}
		
		return $this->cleaned_data;
	}
	
	public function save ($commit = true) {
		if (!$this->isValid ()) {
			throw new Exception ('Cannot save a invalid form');
		}
		
		$this->user->email = $this->cleaned_data['email'];
		$this->user->force_mail_change = 0;
		
		if ($commit) {
			$this->user->update();
		}
		
		return $this->user;
	}
}
