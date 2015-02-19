<?php
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Admision_Views_Aspirante {
	public function convocatoria ($request, $match) {
		/* Revisar que exista al menos un convocatoria abierta */
		$abierta = false;
		foreach (Gatuf::factory ('Admision_Convocatoria')->getList () as $convocatoria) {
			$hora = gmdate ('Y/m/d H:i');
			$unix_time = strtotime ($hora);
		
			$unix_inicio = strtotime ($convocatoria->apertura);
			$unix_fin = strtotime ($convocatoria->cierre);
		
			if ($unix_time >= $unix_inicio && $unix_time <= $unix_fin) {
				/* La convocatoria está abierta */
				$cupos = $convocatoria->get_admision_cupocarrera_list (array ('count' => true));
				if ($cupos > 0) $abierta = true;
			}
		}
		
		if (!$abierta) {
			return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/convocatoria-cerrada.html',
		                                         array('page_title' => 'Todas las convocatorias cerradas'),
                                                 $request);
		}
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_Aspirante_SeleccionarConvocatoria ($request->POST);
			
			if ($form->isValid ()) {
				$convocatoria = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::registro', $convocatoria->id);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Aspirante_SeleccionarConvocatoria (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/convocatoria.html',
		                                         array('page_title' => 'Seleccionar convocatoria',
		                                               'form' => $form),
                                                 $request);
	}
	
	public function registro ($request, $match) {
		$convocatoria = new Admision_Convocatoria ();
		
		if (false === ($convocatoria->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Revisar que esta convocatoria esté abierta */
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		$unix_inicio = strtotime ($convocatoria->apertura);
		$unix_fin = strtotime ($convocatoria->cierre);
		
		if ($unix_time < $unix_inicio || $unix_time > $unix_fin) {
			/* La convocatoria está cerrada */
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('convocatoria' => $convocatoria);
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_Aspirante_Registro ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$aspirante = $form->save (false);
				
				/* Enviar el correo */
				$tmpl = new Gatuf_Template('admision/aspirante/primer-registro.txt');
				$context = new Gatuf_Template_Context (
				               array ('numero' => $aspirante->id,
				                      'pass' => $aspirante->token));
				$email = new Gatuf_Mail (Gatuf::config ('from_email'), $aspirante->email, 'Bienvenido Aspirante - Continua tu trámite');
				$email->setReturnPath (Gatuf::config ('bounce_email', Gatuf::config ('from_email')));
				$email->addTextMessage ($tmpl->render ($context));
				$email->sendMail ();
				
				$return_url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Aspirante::postRegistro');
				
				return new Gatuf_HTTP_Response_Redirect ($return_url);
			}
		} else {
			$form = new Admision_Form_Aspirante_Registro (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/registro.html',
		                                         array('page_title' => 'Nuevo registro',
		                                               'convocatoria' => $convocatoria,
		                                               'form' => $form),
                                                 $request);
	}
	
	public function postRegistro ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('admision/aspirante/postregistro.html',
		                                         array('page_title' => 'Registro completo'),
                                                 $request);
	}
}
