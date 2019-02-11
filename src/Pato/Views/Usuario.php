<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Usuario {
	public $passwordChange_precond = array ('Gatuf_Precondition::loginRequired');
	public function passwordChange ($request, $match) {
		$extra = array();
		$usuario = $extra['usuario'] = $request->user;
		
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			if (get_class ($usuario) == 'Pato_Alumno'){
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($usuario->codigo));
			} else {
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($usuario->codigo));
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
		                                                'form' => $form,
		                                                '_redirect_after' => $success_url),
		                                         $request);
	}
	
	public $emailChange_precond = array ('Gatuf_Precondition::loginRequired');
	public function emailChange ($request, $match) {
		$extra = array();
		$usuario = $extra['usuario'] = $request->user;
		
		if ($request->user->force_mail_change == false) {
			if(get_class ($usuario) == 'Pato_Alumno'){
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($usuario->codigo));
			} else {
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($usuario->codigo));
			}
			
			return new Gatuf_HTTP_Response_Redirect ($success_url);
		}
		
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			if($usuario->type == 'a'){
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verPerfil', array ($usuario->codigo));
			} else {
				$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Maestro::verMaestro', array ($usuario->codigo));
			}
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Usuario_Email ($request->POST, $extra);
			
			if ($form->isValid()) {
				$usuario = $form->save ();
				
				$usuario->setMessage (1, 'Correcto actualizado correctamente');
				
				return new Gatuf_HTTP_Response_Redirect ($success_url);
			}
		} else {
			$form = new Pato_Form_Usuario_Email (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/user/cambiar-email.html',
		                                         array ('page_title' => 'Cambiar correo',
		                                                'form' => $form,
		                                                '_redirect_after' => $success_url),
		                                         $request);
	}
}
