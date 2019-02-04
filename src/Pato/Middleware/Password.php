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
		$bloqueadas = Gatuf::config ('blocked_passwords', array ());
		$bloqueadas[] = $request->user->codigo;
		
		foreach ($bloqueadas as $bloq) {
			if ($request->user->checkPassword ($bloq)) {
				$request->user->setMessage (2, 'Su contraseña es insegura, por motivos seguridad es obligatorio cambiarla');
			
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Usuario::passwordChange', array (), array ('_redirect_after' => $request->uri));
			
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		}
		
		return false;
	}
}
