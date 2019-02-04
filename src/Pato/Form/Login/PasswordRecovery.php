<?php

class Pato_Form_Login_PasswordRecovery extends Gatuf_Form {
	public function initFields($extra=array()) {
		$this->fields['account'] = new Gatuf_Form_Field_Varchar(
		                               array('required' => true,
		                                     'label' => 'Tu código o correo',
		                                     'help_text' => 'Ingresa tu código o correo para recuperar la contraseña',
		));
	}
	
	public function clean_account () {
		$account = mb_strtolower (trim($this->cleaned_data['account']));
		
		$sql = new Gatuf_SQL ('email=%s OR codigo=%s', array ($account, $account));
		$users_m = Gatuf::factory ('Pato_Maestro')->getList(array ('filter' => $sql->gen()));
		$users_a = Gatuf::factory ('Pato_Alumno')->getList(array ('filter' => $sql->gen()));
		
		$users = array_merge ($users_m->getArrayCopy (), $users_a->getArrayCopy ());
		
		if (count ($users) == 0) {
			throw new Gatuf_Form_Invalid ('Lo sentimos, no podemos encontrar un usuario con este código o correo. Por favor intentalo de nuevo');
		}
		$ok = false;
		foreach ($users as $user) {
			if ($user->active) {
				$ok = true;
				continue;
			}
			
			$ok = false;
		}
		
		if (!$ok) {
			throw new Gatuf_Form_Invalid ('Lo sentimos, no podemos encontrar un usuario con este código o correo. Por favor intentalo de nuevo');
		}
		
		return $account;
	}
	
	function save ($commit=true) {
		if (!$this->isValid()) {
			throw new Exception ('No se puede guardar el formulario en un estado inválido');
		}
		
		$account = $this->cleaned_data['account'];
		
		$sql = new Gatuf_SQL ('email=%s OR codigo=%s', array ($account, $account));
		$users_m = Gatuf::factory ('Pato_Maestro')->getList(array ('filter' => $sql->gen()));
		$users_a = Gatuf::factory ('Pato_Alumno')->getList(array ('filter' => $sql->gen()));
		
		$users = array_merge ($users_m->getArrayCopy (), $users_a->getArrayCopy ());
		
		foreach ($users as $user) {
			if (!$user->active) continue;
			
			self::send_code ($user);
		}
	}
	
	public static function send_code ($user) {
		$tmpl = new Gatuf_Template('pato/user/recuperarcontra-email.txt');
		$cr = new Gatuf_Crypt (md5(Gatuf::config('secret_key')));
		if (get_class ($user) == 'Pato_Alumno') {
			$type = 'a';
		} else if (get_class ($user) == 'Pato_Maestro') {
			$type = 'm';
		}
		$code = trim ($cr->encrypt($type.':'.$user->email.':'.$user->codigo.':'.time()), '~');
		$code = substr (md5 (Gatuf::config ('secret_key').$code), 0, 2).$code;
		$url = Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views_Login::passwordRecovery', array ($code), array (), false);
		$urlic = Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views_Login::passwordRecoveryInputCode', array (), array (), false);
		$context = new Gatuf_Template_Context (
		               array ('url' => Gatuf_Template::markSafe ($url),
		                      'urlik' => Gatuf_Template::markSafe ($urlic),
		                      'user' => $user,
		                      'key' => Gatuf_Template::markSafe ($code)));
		$email = new Gatuf_Mail (Gatuf::config ('from_email'), $user->email, 'Recuperar contraseña - Sistema Patricia');
		$email->setReturnPath (Gatuf::config ('bounce_email', Gatuf::config ('from_email')));
		$email->addTextMessage ($tmpl->render ($context));
		$email->sendMail ();
	}
}
