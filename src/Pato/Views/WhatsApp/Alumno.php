<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_WhatsApp_Alumno {
	public $registrar_precond = array ('Gatuf_Precondition::loginRequired');
	public function registrar ($request, $match) {
		if ($request->user->type != 'a') {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$alumno = $request->user->extra;
		/* Recuperar el perfil del alumno */
		$perfiles = $alumno->get_pato_perfilalumno_list();
		
		if (count ($perfiles) == 0) {
			/* Aún no tiene un perfil, crearlo */
			$perfil = new Pato_PerfilAlumno ();
			
			$perfil->alumno = $alumno;
			$perfil->create ();
		} else {
			$perfil = $perfiles[0];
		}
		
		$extra = array ('perfil' => $perfil);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_WhatsApp_Registrar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$numero = $form->save ();
				
				if ($numero == $perfil->whatsapp) {
					$request->user->setMessage (1, 'El número no ha sido modificado');
				} else {
					$perfil->whatsapp = $numero;
					$perfil->whatsapp_verificado = false;
					
					$perfil->update ();
					
					/* Enviar el mensaje */
					$mensaje = new Pato_Mensaje_WhatsApp ();
					$mensaje->numero = $numero;
					$mensaje->mensaje = 'Bienvenido al sistema de notificaciones de Patricia. Para confirmar tu suscripción, envia la palabra ALTA seguida de un espacio y tu código de alumno. Si no deseas recibir más mensajes, simplemente responde con el texto BAJA';
					
					$mensaje->create ();
				}
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_WhatsApp_Alumno::completar');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_WhatsApp_Registrar (null, $extra);
		}
		
		$context = new Gatuf_Template_Context(array());
		$tmpl = new Gatuf_Template('pato/whatsapp/terminos.html');
		$terms = Gatuf_Template::markSafe($tmpl->render($context));
		return Gatuf_Shortcuts_RenderToResponse ('pato/whatsapp/registrar-alumno.html',
		                                         array ('page_title' => 'Registrar número de WhatsApp',
		                                                'form' => $form,
		                                                'alumno' => $alumno,
		                                                'perfil' => $perfil,
		                                                'terms' => $terms),
		                                         $request);
	}
	
	public $completar_precond = array ('Gatuf_Precondition::loginRequired');
	public function completar ($request, $match) {
		$numero = Gatuf::config ('whatsapp_login');
		
		$real = substr ($numero, 3);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/whatsapp/completar-registro.html',
		                                         array ('page_title' => 'Registrar número de WhatsApp',
		                                                'numero' => $real),
		                                         $request);
	}
}
