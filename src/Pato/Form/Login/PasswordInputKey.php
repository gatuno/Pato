<?php

Gatuf::loadFunction ('Gatuf_HTTP_URL_urlForView');

class Pato_Form_Login_PasswordInputKey extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['key'] = new Gatuf_Form_Field_Varchar(
		                               array('required' => true,
		                                     'label' => 'Código de verificación',
		                                     'initial' => '',
		                                     'widget_attrs' => array (
		                                         'size' => 50,
		                                     ),
		));
	}
	
	public function clean_key () {
		$this->cleaned_data ['key'] = trim ($this->cleaned_data['key']);
		
		if (false === ($cres = self::checkKeyHash ($this->cleaned_data['key']))) {
			throw new Gatuf_Form_Invalid ('El código de verificación no es válido. Prueba a copiarlo y pegarlo directamente desde el correo de verificación');
		}
		
		self::getKeyUser ($cres);
		
		return $this->cleaned_data['key'];
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception('Cannot save an invalid form.');
		}
		return Gatuf_HTTP_URL_urlForView ('Pato_Views_Login::passwordRecovery', array ($this->cleaned_data['key']));
	}
	
	public static function getKeyUser ($recover_data) {
		if ($recover_data[0] == 'a') {
			$guser = new Pato_Alumno ();
		} else if ($recover_data[0] == 'm') {
			$guser = new Pato_Maestro ();
		}
		
		$sql = new Gatuf_SQL ('email=%s AND codigo=%s', array ($recover_data[1], $recover_data[2]));
		$user = $guser->getList (array ('filter' => $sql->gen ()));
		
		if (count ($user) != 1) {
			throw new Gatuf_Form_Invalid ('El código de verificación no es válido. Prueba a copiarlo y pegarlo directamente desde el correo de verificación');
		}
		
		if ((time() - $recover_data[3]) > 43200) {
			throw new Gatuf_Form_Invalid ('Lo sentimos, el código de verificación ha expirado, por favor intentalo de nuevo. Por razones de seguridad, los códigos de verificación son sólo válidas por 12 horas');
		}
		
		if (!$user[0]->active) {
			throw new Gatuf_Form_Invalid ('Esta cuenta no está activa. Por favor contacta al administrador');
		}
		
		return $user[0];
	}
	
	public static function checkKeyHash ($key) {
		$hash = substr ($key, 0, 2);
		$encrypted = substr ($key, 2);
		if ($hash != substr(md5(Gatuf::config('secret_key').$encrypted), 0, 2)) {
			return false;
		}
		$cr = new Gatuf_Crypt (md5(Gatuf::config('secret_key')));
		$f = explode (':', $cr->decrypt($encrypted), 4);
		if (count ($f) != 4) {
			return false;
		}
		return $f;
	}
}
