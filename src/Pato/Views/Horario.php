<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Horario {
	public $agregarHora_precond = array ('Pato_Precondition::coordinadorRequired');
	public function agregarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$materia = $seccion->get_materia ();
		
		/* Revisar que tenga permisos de edición sobre la materia de esta sección */
		$carreras = $materia->get_carreras_list ();
		
		$found = false;
		foreach ($carreras as $carrera) {
			if ($request->user->hasPerm ('Patricia.coordinador.'.$carrera->clave)) {
				$found = true;
				break;
			}
		}
		
		if (!$found) {
			$request->user->setMessage (3, 'Usted no puede agregar horarios a esta sección por falta de permisos');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$als = $seccion->get_alumnos_list (array ('count' => true));
		
		if ($als > 0) {
			if (!$request->user->administrator) {
				$request->user->setMessage (3, 'No puede modificar los horarios de esta sección porque ya hay alumnos inscritos.');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
	                        return new Gatuf_HTTP_Response_Redirect ($url);
			} else {
				$request->user->setMessage (2, 'Modificar el horario de una sección cuando ya tiene alumnos inscritos puede ser fatal y desastrozo. Podría provocar colisiones en los horarios de los alumnos. Preste mucha atención al hacer las modificaciones, Patricia no puede revisar los horarios de todos los alumnos.');
			}
		}
		$extra = array('seccion' => $seccion);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Horario_Agregar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				$horario = $form->save ();
				
				/* Actualizar el mínimo cupo disponible */
				$min = $horario->get_salon ()->cupo;
				
				foreach ($seccion->get_pato_horario_list () as $h) {
					if ($min > $h->get_salon ()->cupo) $min = $h->get_salon ()->cupo;
				}
				
				$seccion->cupo = $min;
				$seccion->update ();
				
				Gatuf_Log::info (sprintf ('La hora %s fué agregada al NRC %s por el usuario %s (%s)', $horario->id, $seccion->nrc, $request->user->login, $request->user->id));
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($horario->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Horario_Agregar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('pato/horario/agregar-horario.html',
		                                         array ('page_title' => 'Agregar nueva hora',
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
		
	}
	
	public $eliminarHora_precond = array ('Pato_Precondition::coordinadorRequired');
	public function eliminarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Revisar que tenga permisos de edición sobre la materia de esta sección */
		$carreras = $seccion->get_materia ()->get_carreras_list ();
		
		$found = false;
		foreach ($carreras as $carrera) {
			if ($request->user->hasPerm ('Patricia.coordinador.'.$carrera->clave)) {
				$found = true;
				break;
			}
		}
		
		if (!$found) {
			$request->user->setMessage (3, 'Usted no puede editar horarios de esta sección por falta de permisos');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$hora = new Pato_Horario ();
		
		if (false === ($hora->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		$als = $seccion->get_alumnos_list (array ('count' => true));

		if ($als > 0) {
			if (!$request->user->administrator) {
				$request->user->setMessage (3, 'No puede modificar los horarios de esta sección porque ya hay alumnos inscritos.');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			} else {
				$request->user->setMessage (2, 'Modificar el horario de una sección cuando ya tiene alumnos inscritos puede ser fatal y desastrozo. Podría provocar colisiones en los horarios de los alumnos. Preste mucha atención al hacer las modificaciones, Patricia no puede revisar los horarios de todos los alumnos.');
			}
		}
		
		if ($request->method == 'POST') {
			/* Adelante, eliminar esta hora */
			Gatuf_Log::info (sprintf ('La hora (%s, %s) ha sido eliminada del NRC %s por el usuario %s (%s)', $hora->id, $hora->hash(), $hora->nrc, $request->user->login, $request->user->id));
			$hora->delete ();
			
			/* Actualizar el mínimo cupo disponible */
			$horas = $seccion->get_pato_horario_list ();
			if (count ($horas) == 0) {
				$seccion->cupo = 0;
			} else {
				$min = $horas[0]->get_salon ()->cupo;
				foreach ($horas as $h) {
					if ($min > $h->get_salon ()->cupo) $min = $h->get_salon ()->cupo;
				}
				$seccion->cupo = $min;
			}
			
			$seccion->update ();
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($seccion->nrc));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/horario/eliminar-horario.html',
		                                         array ('page_title' => 'Eliminar hora',
		                                                'seccion' => $seccion,
		                                                'salon' => $hora->get_salon (),
		                                                'horario' => $hora),
		                                         $request);
	}
	
	public $actualizarHora_precond = array ('Pato_Precondition::coordinadorRequired');
	public function actualizarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		/* Revisar que tenga permisos de edición sobre la materia de esta sección */
		$carreras = $seccion->get_materia ()->get_carreras_list ();
		
		$found = false;
		foreach ($carreras as $carrera) {
			if ($request->user->hasPerm ('Patricia.coordinador.'.$carrera->clave)) {
				$found = true;
				break;
			}
		}
		
		if (!$found) {
			$request->user->setMessage (3, 'Usted no puede eliminar horarios de esta sección por falta de permisos');
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$hora = new Pato_Horario ();
		
		if (false === ($hora->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}
		$als = $seccion->get_alumnos_list (array ('count' => true));

		if ($als > 0) {
			if (!$request->user->administrator) {
				$request->user->setMessage (3, 'No puede modificar los horarios de esta sección porque ya hay alumnos inscritos.');
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', $seccion->nrc);
				return new Gatuf_HTTP_Response_Redirect ($url);
			} else {
				$request->user->setMessage (2, 'Modificar el horario de una sección cuando ya tiene alumnos inscritos puede ser fatal y desastrozo. Podría provocar colisiones en los horarios de los alumnos. Preste mucha atención al hacer las modificaciones, Patricia no puede revisar los horarios de todos los alumnos.');
			}
		}
		
		$extra = array('seccion' => $seccion, 'horario' => $hora);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Horario_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				Gatuf_Log::info (sprintf ('La hora (%s, %s) del NRC %s va a ser actualizada', $hora->id, $horario->hash(), $horario->nrc));
				$horario = $form->save ();
				Gatuf_Log::info (sprintf ('A: %s, por el usuario %s (%s)', $horario->hash(), $request->user->login, $request->user->id));
				
				/* Actualizar el mínimo cupo disponible */
				$min = $horario->get_salon ()->cupo;
				
				foreach ($seccion->get_pato_horario_list () as $h) {
					if ($min > $h->get_salon ()->cupo) $min = $h->get_salon ()->cupo;
				}
				
				$seccion->cupo = $min;
				$seccion->update ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($horario->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Horario_Actualizar (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('pato/horario/edit-horario.html',
		                                         array ('page_title' => 'Actualizar horario',
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
}
