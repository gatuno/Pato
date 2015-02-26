<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Agenda {
	public $agenda_precond = array ('Gatuf_Precondition::loginRequired');
	public function agenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if (!$request->user->administrator) {
			if ($request->user->login != $alumno->codigo) {
				throw new Gatuf_HTTP_Error404 ();
			}
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		$ins = $alumno->get_current_inscripcion ();
		$abierta = false;
		$agenda = null;
		if ($ins != null) {
			if (count ($list) != 0) {
				$agenda = $list[0];
				$unix_inicio = strtotime ($agenda->inicio);
				$unix_fin = strtotime ($agenda->fin);
				if ($unix_time > $unix_inicio && $unix_time < $unix_fin) {
					$abierta = true;
				}
			}
		}
		
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $calendario,
		                                                'abierta' => $abierta,
		                                                'hora' => $hora,
		                                                'agenda' => $agenda,
		                                                'inscripcion' => $ins),
		                                         $request);
	}
	
	public $crearAgenda_precond = array ('Gatuf_Precondition::adminRequired');
	public function crearAgenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) != 0) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('alumno' => $alumno);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_CrearAgenda ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$agenda = $form->save ();
				Gatuf_Log::info (sprintf ('La agenda para el alumno %s ha sido creada por el usuario %s (%s)', $alumno->codigo, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_CrearAgenda (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/crear-agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $calendario,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $cambiarAgenda_precond = array ('Gatuf_Precondition::adminRequired');
	public function cambiarAgenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$extra = array ('agenda' => $list[0]);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_CambiarAgenda ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$agenda = $form->save ();
				Gatuf_Log::info (sprintf ('La agenda para el alumno %s ha sido actualizada por el usuario %s (%s)', $alumno->codigo, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
				
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_CambiarAgenda (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/cambiar-agenda.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $request->calendario,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $eliminarAgenda_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarAgenda ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$list[0]->delete ();
		
		Gatuf_Log::info (sprintf ('La agenda para el alumno %s ha sido eliminada por el usuario %s (%s)', $alumno->codigo, $request->user->login, $request->user->id));
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $registro_precond = array ('Gatuf_Precondition::loginRequired');
	public function registro ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->user->type != 'a' || $request->user->login != $alumno->codigo) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		$request->session->setData ('CAL_ACTIVO', $calendario->clave);
		
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
			return new Gatuf_HTTP_Response_Redirect ($url);
		} else {
			$agenda = $list[0];
		}
		
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		$unix_inicio = strtotime ($agenda->inicio);
		$unix_fin = strtotime ($agenda->fin);
		
		if ($unix_time < $unix_inicio || $unix_time > $unix_fin) {
			$request->user->setMessage (3, 'Tu agenda aún no permite el registro');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Registro ($request->POST);
			
			if ($form->isValid ()) {
				$nrcs = $form->save ();
				
				Gatuf::loadFunction ('Pato_Procedimiento_matricular');
				
				$seccion = new Pato_Seccion ();
		
				foreach ($nrcs as $nrc_nuevo) {
					/* Intentar matricular el alumno en los nrc */
					if (false === ($seccion->get ($nrc_nuevo))) {
						$request->user->setMessage (3, 'El NRC '.$nrc_nuevo.' no existe');
						continue;
					}
					
					$resp = Pato_Procedimiento_matricular ($seccion, $alumno, true, true);
					
					if ($resp !== true) {
						$request->user->setMessage (2, 'El NRC '.$seccion->nrc.' no se pudo matricular por la siguiente razón: '.$resp);
					}
				}
			}
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::registro', $alumno->codigo);
			return new Gatuf_HTTP_Response_Redirect ($url);
		} else {
			$form = new Pato_Form_Alumno_Registro (null);
		}
		
		$secciones_al = $alumno->get_grupos_list ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/registro.html',
		                                         array ('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                                'alumno' => $alumno,
		                                                'calendario' => $calendario,
		                                                'secciones' => $secciones_al,
		                                                'form' => $form,
		                                                'agenda' => $agenda),
		                                         $request);
	}
	
	public $registroEliminar_precond = array ('Gatuf_Precondition::loginRequired');
	public function registroEliminar ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($request->user->type != 'a' || $request->user->login != $alumno->codigo) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$gconf = new Gatuf_GSetting ();
		$gconf->setApp ('Patricia');
		
		$calendario = new Pato_Calendario ($gconf->getVal ('calendario_siguiente'));
		$GLOBALS['CAL_ACTIVO'] = $calendario->clave;
		
		$seccion = new Pato_Seccion ();
		if (false === ($seccion->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::agenda', $alumno->codigo);
		$list = $alumno->get_agenda_list ();
		if (count ($list) == 0) {
			return new Gatuf_HTTP_Response_Redirect ($url);
		} else {
			$agenda = $list[0];
		}
		
		$hora = gmdate ('Y/m/d H:i');
		$unix_time = strtotime ($hora);
		
		$unix_inicio = strtotime ($agenda->inicio);
		$unix_fin = strtotime ($agenda->fin);
		
		if ($unix_time < $unix_inicio || $unix_time > $unix_fin) {
			$request->user->setMessage (3, 'Tu agenda aún no permite el registro');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$alumno->delAssoc ($seccion);
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Agenda::registro', $alumno->codigo);
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
}
