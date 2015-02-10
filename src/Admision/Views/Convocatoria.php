<?php
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Admision_Views_Convocatoria {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		/* Listar todas las convocatorias */
		$convocatorias = Gatuf::factory ('Admision_Convocatoria')->getList ();
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/convocatoria/index.html',
		                                         array('page_title' => 'Administrar convocatorias',
		                                               'convocatorias' => $convocatorias),
                                                 $request);
	}
	
	public $agregar_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregar ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Admision_Form_Convocatoria_Agregar ($request->POST);
			
			if ($form->isValid ()) {
				$convocatoria = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Convocatoria::ver', $convocatoria->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Convocatoria_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/convocatoria/agregar.html',
		                                         array('page_title' => 'Nueva convocatoria',
		                                               'form' => $form),
                                                 $request);
	}
	
	public $ver_precond = array ('Gatuf_Precondition::adminRequired');
	public function ver ($request, $match) {
		$convocatoria = new Admision_Convocatoria ();
		
		if (false === ($convocatoria->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$cupos = $convocatoria->get_admision_cupocarrera_list ();
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/convocatoria/ver.html',
		                                         array('page_title' => 'Convocatoria '.$convocatoria->descripcion,
		                                               'convocatoria' => $convocatoria,
		                                               'cupos' => $cupos),
                                                 $request);
	}
	
	public $agregarCupo_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarCupo ($request, $match) {
		$convocatoria = new Admision_Convocatoria ();
		
		if (false === ($convocatoria->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$cupos = $convocatoria->get_admision_cupocarrera_list (array ('count' => true));
		$carreras = Gatuf::factory ('Pato_Carrera')->getList (array ('count' => true));
		
		if ($cupos == $carreras) {
			$request->user->setMessage (2, 'No se pueden agregar más carreras a la convocatoria, ya fueron agregadas todas');
			
			$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Convocatoria::ver', $convocatoria->id);
			return Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$extra = array ('convocatoria' => $convocatoria);
		
		if ($request->method == 'POST') {
			$form = new Admision_Form_Convocatoria_AgregarCupo ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$cupo = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admision_Views_Convocatoria::ver', $convocatoria->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admision_Form_Convocatoria_AgregarCupo (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admision/convocatoria/agregar-carrera.html',
		                                         array('page_title' => 'Agregar carrera a convocatoria',
		                                               'form' => $form,
		                                               'convocatoria' => $convocatoria),
                                                 $request);
	}
}
