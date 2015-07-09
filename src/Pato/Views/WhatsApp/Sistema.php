<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_WhatsApp_Sistema {
	public $enviar_precond = array ('Gatuf_Precondition::adminRequired');
	public function enviar ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_WhatsApp_Enviar ($request->POST);
			
			if ($form->isValid ()) {
				$texto = $form->save ();
				
				/* Recuperar todos los perfiles que tengan un whatsapp válido y verificado */
				$sql = 'whatsapp IS NOT NULL AND whatsapp != "" AND whatsapp_verificado = 1';
				
				$perfiles = Gatuf::factory ('Pato_PerfilAlumno')->getList (array ('filter' => $sql));
				
				$m = new Pato_Mensaje_WhatsApp ();
				$m->mensaje = $texto;
				foreach ($perfiles as $p) {
					$m->numero = $p->whatsapp;
					
					$m->create ();
				}
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_WhatsApp_Sistema::enviado');
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_WhatsApp_Enviar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/whatsapp/enviar.html',
		                                         array ('page_title' => 'Enviar mensaje de WhatsApp a los alumnos',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $enviado_precond = array ('Gatuf_Precondition::adminRequired');
	public function enviado ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/whatsapp/enviado.html',
		                                         array ('page_title' => 'WhatsApp masivo enviado'),
		                                         $request);
	}
}
