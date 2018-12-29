<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Calendario {
	public $index_precond = array ('Gatuf_Precondition::adminRequired');
	public function index ($request, $match) {
		$calendarios = Gatuf::factory ('Pato_Calendario')->getList ();
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		$activo = $gconf->getVal ('calendario_activo', null);
		$sig = $gconf->getVal ('calendario_siguiente', null);
		
		if ($activo != null) {
			$actual = new Pato_Calendario ($activo);
		} else {
			$actual = null;
		}
		
		if ($sig != null) {
			$siguiente = new Pato_Calendario ($sig);
		} else {
			$siguiente = null;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/index.html',
		                                         array ('page_title' => 'Calendarios',
		                                                'calendarios' => $calendarios,
		                                                'actual' => $actual,
		                                                'siguiente' => $siguiente),
		                                         $request);
	}
	
	public $agregarCalendario_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarCalendario ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calendario_Agregar ($request->POST);
			
			if ($form->isValid ()) {
				$cal = $form->save ();
				
				$request->session->setData ('CAL_ACTIVO', $cal->clave);
				Gatuf_Log::info (sprintf ('El calendario %s fué creado por el usuario %s (%s)', $cal->clave, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calendario::ver', $cal->clave);
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calendario_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/agregar-calendario.html',
		                                         array ('page_title' => 'Nuevo calendario',
		                                                'form' => $form),
		                                         $request);
	}
	
	public function cambiarCalendario ($request, $match) {
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			$success_url = Gatuf_HTTP_URL_urlForView ('Pato_Views::index');
		}
		
		if ($request->method == 'POST') {
			/* Si el formulario valida, cambiar el calendario */
			$form = new Pato_Form_Calendario_Seleccionar ($request->POST);
			
			if ($form->isValid ()) {
				$cal = $form->save ();
				
				$request->session->setData ('CAL_ACTIVO', $cal->clave);
			}
		}
		
		return new Gatuf_HTTP_Response_Redirect ($success_url);
	}
	
	public $ver_precond = array ('Gatuf_Precondition::adminRequired');
	public function ver ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if (false === $calendario->get ($match[1])) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		$activo = $gconf->getVal ('calendario_activo', null);
		$sig = $gconf->getVal ('calendario_siguiente', null);
		
		$es_activo = false;
		if ($activo != null) {
			$actual = new Pato_Calendario ($activo);
			if ($actual->clave == $calendario->clave) $es_activo = true;
		}
		
		$es_siguiente = false;
		if ($sig != null) {
			$siguiente = new Pato_Calendario ($sig);
			if ($siguiente->clave == $calendario->clave) $es_siguiente = true;
		}
		
		/* Calcular los días festivos */
		$sql = new Gatuf_SQL ('inicio > %s AND fin < %s', array ($calendario->inicio, $calendario->fin));
		$festivos = Gatuf::factory ('Pato_DiaFestivo')->getList (array ('filter' => $sql->gen (), 'order' => 'inicio ASC'));
		$hoy = date("Y-m-d");
		
		/* Mostrar algunas de sus configuraciones */
		$configs = array ();
		$configs['suficiencias'] = $gconf->getVal ('suficiencias_abierta_'.$calendario->clave, false);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/ver.html',
		                                         array ('page_title' => 'Calendario '.$calendario->descripcion,
		                                                'calendario' => $calendario,
		                                                'es_activo' => $es_activo,
		                                                'es_siguiente' => $es_siguiente,
		                                                'festivos' => $festivos,
		                                                'hoy' => $hoy,
		                                                'configs' => $configs),
		                                         $request);
	}
	
	public $configurar_precond = array ('Gatuf_Precondition::adminRequired');
	public function configurar ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if (false === $calendario->get ($match[1])) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Calendario_Preferencias ($request->POST, array ('cal' => $calendario));
			
			if ($form->isValid ()) {
				$form->save ();
				
				$request->user->setMessage (1, 'Preferencias del calendario guardadas');
				Gatuf_Log::info (sprintf ('Ajustes en el calendario %s fueron efectuados por el usuario %s (%s)', $calendario->clave, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calendario::ver', $calendario->clave);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Calendario_Preferencias (null, array ('cal' => $calendario));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/configurar.html',
		                                         array ('page_title' => 'Configurar calendario '.$calendario->descripcion,
		                                                'calendario' => $calendario,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $cambiarActual_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambiarActual ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if (false === $calendario->get ($match[1])) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->method == 'POST') {
			/* Viene de regreso, aplicar y redirigir */
			$gconf = new Gatuf_GSetting ();
			$gconf->setApp ('Patricia');
			$gconf->setVal ('calendario_activo', $calendario->clave);
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calendario::ver', $calendario->clave);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/confirmar-actual.html',
		                                         array ('page_title' => 'Cambiar calendario por defecto',
		                                                'calendario' => $calendario),
		                                         $request);
	}
	
	public $cambiarSiguiente_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambiarSiguiente ($request, $match) {
		$calendario = new Pato_Calendario ();
		
		if (false === $calendario->get ($match[1])) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->method == 'POST') {
			/* Viene de regreso, aplicar y redirigir */
			$gconf = new Gatuf_GSetting ();
			$gconf->setApp ('Patricia');
			$gconf->setVal ('calendario_siguiente', $calendario->clave);
			
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Calendario::ver', $calendario->clave);
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/calendario/confirmar-siguiente.html',
		                                         array ('page_title' => 'Cambiar calendario siguiente',
		                                                'calendario' => $calendario),
		                                         $request);
	}
}
