<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Admin_Views_SI_Laboratorio {
	public $index_precond = array ('Pato_Precondition::maestroRequired');
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
	
	public $registrar_precond = array ('Gatuf_Precondition::loginRequired');
	public function registrar ($request, $match) {
		$laboratorio = new Admin_SI_Laboratorio ();
		
		if (false === ($laboratorio->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		//$sql = Gatuf_SQL (
		$registros = $laboratorio->get_admin_si_laboratorioingreso_list (array ('order' => 'id DESC', 'nb' => 5));
		
		$form = new Pato_Form_SeleccionarAlumno (null);
		
		return Gatuf_Shortcuts_RenderToResponse ('admin/si/laboratorio/registrar.html',
		                                         array ('page_title' => 'SI - Laboratorio '.$laboratorio->nombre,
		                                                'laboratorio' => $laboratorio,
		                                                'registros' => $registros,
		                                                'form' => $form),
		                                         $request);
	}
	
	public function registrarEntrada ($request, $match) {
		$laboratorio = new Admin_SI_Laboratorio ();
		
		if (false === ($laboratorio->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				$registro = new Admin_SI_LaboratorioIngreso ();
				
				$registro->laboratorio = $laboratorio;
				$registro->alumno = $alumno;
				$registro->hora = gmdate ('Y-m-d H:i:s');
				$registro->tipo = 'e';
				
				$registro->create ();
			}
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Admin_Views_SI_Laboratorio::registrar', $laboratorio->id);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function registrarSalida ($request, $match) {
		$laboratorio = new Admin_SI_Laboratorio ();
		
		if (false === ($laboratorio->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_SeleccionarAlumno ($request->POST);
			
			if ($form->isValid ()) {
				$alumno = $form->save ();
				
				$registro = new Admin_SI_LaboratorioIngreso ();
				
				$registro->laboratorio = $laboratorio;
				$registro->alumno = $alumno;
				$registro->hora = gmdate ('Y-m-d H:i:s');
				$registro->tipo = 's';
				
				$registro->create ();
			}
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Admin_Views_SI_Laboratorio::registrar', $laboratorio->id);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
}
