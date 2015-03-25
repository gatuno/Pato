<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Admin_Views_Biblioteca_Equipo {
	public $index_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admin.biblioteca-ver-equipo'));
	public function index ($request, $match) {
		$equipos = Gatuf::factory ('Admin_Biblioteca_Equipo')->getList (array ('order' => array ('tipo ASC', 'nombre ASC')));
		
		return Gatuf_Shortcuts_RenderToResponse ('admin/biblioteca/equipo/index.html',
		                                         array ('page_title' => 'Biblioteca - Equipo',
		                                                'equipos' => $equipos),
		                                         $request);
	}
	
	public $agregar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admin.biblioteca-agregar-equipo'));
	public function agregar ($request, $match) {
		$extra = array ();
		if (isset ($request->GET['biblioteca'])) {
			$extra['biblioteca'] = (int) $request->GET['biblioteca'];
		}
		
		if ($request->method == 'POST') {
			$form = new Admin_Form_Biblioteca_Equipo_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$equipo = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admin_Views_Biblioteca_Equipo::ver', $equipo->id);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admin_Form_Biblioteca_Equipo_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admin/biblioteca/equipo/agregar.html',
		                                         array ('page_title' => 'Biblioteca - Agregar equipo',
		                                                'form' => $form),
		                                         $request);
	}
	
	public $ver_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admin.biblioteca-ver-equipo'));
	public function ver ($request, $match) {
		$equipo = new Admin_Biblioteca_Equipo ();
		
		if (false === ($equipo->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($equipo->prestado ()) {
			$prestamos = $equipo->get_prestamos_list (array ('filter' => 'regreso IS NULL'));
			$prestamo = $prestamos[0];
		} else {
			$prestamo = null;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admin/biblioteca/equipo/ver.html',
		                                         array ('page_title' => 'Biblioteca - Ver equipo',
		                                                'equipo' => $equipo,
		                                                'prestamo' => $prestamo),
		                                         $request);
	}
	
	public $prestar_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admin.biblioteca-prestar-equipo'));
	public function prestar ($request, $match) {
		$biblioteca = new Admin_Biblioteca ();
		
		if (false === ($biblioteca->get ($match[1]))) {
			return new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('biblioteca' => $biblioteca, 'user' => $request->user);
		
		if (isset ($request->GET['equipo'])) {
			$extra['equipo'] = (int) $request->GET['equipo'];
		}
		
		if ($request->method == 'POST') {
			$form = new Admin_Form_Biblioteca_Equipo_Prestar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$prestamo = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admin_Views_Biblioteca_Equipo::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admin_Form_Biblioteca_Equipo_Prestar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admin/biblioteca/equipo/prestar.html',
		                                         array ('page_title' => 'Biblioteca - Prestar equipo',
		                                                'biblioteca' => $biblioteca,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $regresarPorEquipo_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admin.biblioteca-prestar-equipo'));
	public function regresarPorEquipo ($request, $match) {
		$equipo = new Admin_Biblioteca_Equipo ();
		
		if (false === ($equipo->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$equipo->prestado ()) {
			$request->user->setMessage (3, 'El equipo no se encuetra prestado');
			
			$url = Gatuf_HTTP_URL_urlForView ('Admin_Views_Biblioteca_Equipo::ver', $equipo->id);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$prestamos = $equipo->get_prestamos_list (array ('filter' => 'regreso IS NULL'));
		
		$url = Gatuf_HTTP_URL_urlForView ('Admin_Views_Biblioteca_Equipo::regresarPorPrestamo', $prestamos[0]->id);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $regresarPorPrestamo_precond = array (array ('Gatuf_Precondition::hasPerm', 'Admin.biblioteca-prestar-equipo'));
	public function regresarPorPrestamo ($request, $match) {
		$prestamo = new Admin_Biblioteca_Prestamo ();
		
		if (false === ($prestamo->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($prestamo->regreso !== null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$equipos = $prestamo->get_equipos_list ();
		$extra = array ('prestamo' => $prestamo);
		
		if ($request->method == 'POST') {
			$form = new Admin_Form_Biblioteca_Equipo_Regresar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$prestamo = $form->save (false);
				
				$prestamo->usuario_regreso = $request->user;
				$prestamo->regreso = gmdate ('Y-m-d H:i:s');
				
				$prestamo->update ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Admin_Views_Biblioteca_Equipo::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Admin_Form_Biblioteca_Equipo_Regresar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('admin/biblioteca/equipo/regresar.html',
		                                         array ('page_title' => 'Biblioteca - Regresar equipo',
		                                                'form' => $form,
		                                                'equipos' => $equipos,
		                                                'prestamo' => $prestamo),
		                                         $request);
	}
}
