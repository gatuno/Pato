<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Admin_Views_SI_Laboratorio {
	public $index_precond = array ('Gatuf_Precondition::loginRequired');
	public function index ($request, $match) {
		$laboratorios = Gatuf::factory ('Admin_SI_Laboratorio')->getList ();
		return Gatuf_Shortcuts_RenderToResponse ('admin/si/laboratorio/index.html',
		                                         array ('page_title' => 'SI - Laboratorios',
		                                                'laboratorios' => $laboratorios),
		                                         $request);
	}
	
	public $agregar_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregar ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Admin_Form_SI_Laboratorio_Agregar ($request->POST);
			
			if ($form->isValid ()) {
				$laboratorio = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admin_Views_SI_Laboratorio::ver', $laboratorio->id);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admin_Form_SI_Laboratorio_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admin/si/laboratorio/agregar.html',
		                                         array ('page_title' => 'SI - Agregar laboratorio',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $ver_precond = array ('Gatuf_Precondition::loginRequired');
	public function ver ($request, $match) {
		$laboratorio = new Admin_SI_Laboratorio ();
		
		if (false === ($laboratorio->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admin/si/laboratorio/ver.html',
		                                         array ('page_title' => 'SI - Laboratorio '.$laboratorio->nombre,
		                                                'laboratorio' => $laboratorio),
		                                         $request);
	}
}
