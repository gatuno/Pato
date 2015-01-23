<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Usuario {
	public $agregarPermiso_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarPermiso($request, $match) {
		$usuario = new Pato_User ();
		
		if (false === ($usuario->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($usuario->type == 'a') {
			/* Por el momento los alumnos no tienen permisos */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('user' => $usuario);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Usuario_Permisos ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
			}
		}
		
		if ($usuario->type == 'm') {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::permisos', $usuario->login);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
	}

	//public $eliminarPermiso_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarPermiso($request, $match) {
		$usuario = new Pato_User ();
		$permiso = new Gatuf_Permission ();
		
		if (false === ($usuario->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($usuario->type == 'a') {
			/* Por el momento los alumnos no tienen permisos */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (false === $permiso->get ($match[2])) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$usuario->delAssoc($permiso);
		
		if ($usuario->type == 'm') {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::permisos', $usuario->login);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
	}
	
	public $agregarGrupo_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarGrupo($request, $match) {
		$usuario = new Pato_User ();
		
		if (false === ($usuario->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($usuario->type == 'a') {
			/* Por el momento los alumnos no tienen permisos */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('user' => $usuario);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Usuario_Grupos ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$form->save ();
			}
		}
		
		if ($usuario->type == 'm') {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::permisos', $usuario->login);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
	}
	
	public $eliminarGrupo_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarGrupo($request, $match) {
		$usuario = new Pato_User ();
		$grupo = new Gatuf_Group ();
		
		if (false === ($usuario->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($usuario->type == 'a') {
			/* Por el momento los alumnos no tienen permisos */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (false === $grupo->get ($match[2])) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$usuario->delAssoc($grupo);
		
		if ($usuario->type == 'm') {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::permisos', $usuario->login);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
	}

	public $passwordChange_precond = array ('Gatuf_Precondition::loginRequired');
	public function passwordChange ($request, $match) {
		$extra = array();
		$usuario = $extra['usuario'] = $request->user;
		
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			if($usuario->type == 'a'){
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($usuario->login));
			} else {
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($usuario->login));
			}
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Usuario_Password ($request->POST, $extra);
			
			if ($form->isValid()) {
				$usuario = $form->save ();
				
				$usuario->setMessage (1, 'Contraseña cambiada correctamente');
				
				return new Gatuf_HTTP_Response_Redirect ($success_url);
			}
		} else {
			$form = new Pato_Form_Usuario_Password (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/user/cambiar-password.html',
		                                         array ('page_title' => 'Cambiar Contraseña',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $passwordReset_precond = array ('Gatuf_Precondition::adminRequired');
	public function passwordReset ($request, $match) {
		$sql = new Gatuf_SQL ('login=%s', $match[1]);
		
		$user = Gatuf::factory ('Pato_User')->getOne ($sql->gen ());
		
		if ($user === null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($user->login == $request->user->login) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Usuario::passwordChange');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($user->type == 'a'){
			$url_af = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($user->login));
		} else {
			$url_af = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($user->login));
		}
		
		if (!$user->active) {
			$request->user->setMessage (3, 'El usuario se encuentra inactivo');
			return new Gatuf_HTTP_Response_Redirect ($url_af);
		}
		
		$return_url = Gatuf_HTTP_URL_urlForView ('Pato_Views::passwordRecoveryInputCode');
		$tmpl = new Gatuf_Template('pato/user/recuperarcontra-email.txt');
		$cr = new Gatuf_Crypt (md5(Gatuf::config('secret_key')));
		$code = trim ($cr->encrypt($user->email.':'.$user->id.':'.time()), '~');
		$code = substr (md5 (Gatuf::config ('secret_key').$code), 0, 2).$code;
		$url = Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views::passwordRecovery', array ($code), array (), false);
		$urlic = Gatuf::config ('url_base').Gatuf_HTTP_URL_urlForView ('Pato_Views::passwordRecoveryInputCode', array (), array (), false);
		$context = new Gatuf_Template_Context (
		               array ('url' => Gatuf_Template::markSafe ($url),
		                      'urlik' => Gatuf_Template::markSafe ($urlic),
		                      'user' => $user,
		                      'key' => Gatuf_Template::markSafe ($code)));
		$email = new Gatuf_Mail (Gatuf::config ('from_email'), $user->email, 'Recuperar contraseña - Sistema Patricia');
		$email->setReturnPath (Gatuf::config ('bounce_email', Gatuf::config ('from_email')));
		$email->addTextMessage ($tmpl->render ($context));
		$email->sendMail ();
		
		$request->user->setMessage (1, sprintf ('Se ha enviado un correo a "%s" para resetear la contraseña. Expira en 12 horas', $user->email));
		return new Gatuf_HTTP_Response_Redirect ($url_af);
	}
}
