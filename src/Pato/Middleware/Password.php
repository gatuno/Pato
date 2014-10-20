<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Middleware_Password {
	function process_request (&$request) {
		if ($request->user->isAnonymous ()) {
			return false;
		}
		
		if (preg_match('#^/password#', $request->query)) {
			return false;
		}
		
		if (preg_match('#^/login#', $request->query)) {
			return false;
		}
		
		if (preg_match('#^/logout#', $request->query)) {
			return false;
		}
		
		/* Si la contraseña del alumno o profesor es igual a su nombre de usuario, es obligatorio que la cambie */
		if ($request->user->checkPassword ($request->user->login)) {
			$request->user->setMessage (2, 'Tu contraseña es insegura, por motivos seguridad es obligatorio cambiarla');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Usuario::passwordChange');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Si la contraseña del alumno o profesor es igual a 12345, es obligatorio que la cambie */
		if ($request->user->checkPassword ('12345') || $request->user->checkPassword ('123') || $request->user->checkPassword ('1234') || $request->user->checkPassword ('123456') || $request->user->checkPassword ('1234567') || $request->user->checkPassword ('12345678') || $request->user->checkPassword ('123456789')) {
			$request->user->setMessage (2, 'Tu contraseña es insegura, por motivos seguridad es obligatorio cambiarla');
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Usuario::passwordChange');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return false;
	}
}
