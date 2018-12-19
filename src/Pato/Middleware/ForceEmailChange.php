<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Middleware_ForceEmailChange {
	function process_request (&$request) {
		if ($request->user->isAnonymous ()) {
			return false;
		}
		
		if (preg_match('#^/mail/change#', $request->query)) {
			return false;
		}
		
		if ($request->user->force_mail_change) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Usuario::emailChange', array (), array ('_redirect_after' => $request->uri));
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return false;
	}
}
