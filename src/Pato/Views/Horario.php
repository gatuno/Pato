<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Horario {
	public $agregarHora_precond = array (array ('Pato_Precondition::hasAnyPerm', array ('Patricia.editar_secciones_vacio', 'Patricia.admin_secciones')));
	public function agregarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$has_alumnos = false;
		$cant_alumnos = $seccion->get_alumnos_list (array ('count' => true));
		if ($cant_alumnos > 0) {
			$has_alumnos = true;
		}
		
		/* Si la sección tiene alumnos, no puede eliminar esta sección si no tiene el permiso adecuado */
		if ($has_alumnos && !$request->user->hasPerm ('Patricia.admin_secciones')) {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		
		if ($has_alumnos > 0) {
			$request->user->setMessage (2, 'Modificar el horario de una sección cuando ya tiene alumnos inscritos puede ser fatal y desastrozo. Podría provocar colisiones en los horarios de los alumnos. Preste mucha atención al hacer las modificaciones, Patricia no puede revisar los horarios de todos los alumnos.');
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
				
				Gatuf_Log::info (sprintf ('La hora (%s, %s) fué agregada al NRC %s por el usuario %s', $horario->id, $horario->hash (), $seccion->nrc, $request->user->codigo));
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
	
	public $eliminarHora_precond = array (array ('Pato_Precondition::hasAnyPerm', array ('Patricia.editar_secciones_vacio', 'Patricia.admin_secciones')));
	public function eliminarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$has_alumnos = false;
		$cant_alumnos = $seccion->get_alumnos_list (array ('count' => true));
		if ($cant_alumnos > 0) {
			$has_alumnos = true;
		}
		
		/* Si la sección tiene alumnos, no puede eliminar esta sección si no tiene el permiso adecuado */
		if ($has_alumnos && !$request->user->hasPerm ('Patricia.admin_secciones')) {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		
		$hora = new Pato_Horario ();
		
		if (false === ($hora->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}

		if ($has_alumnos > 0) {
			$request->user->setMessage (2, 'Modificar el horario de una sección cuando ya tiene alumnos inscritos puede ser fatal y desastrozo. Podría provocar colisiones en los horarios de los alumnos. Preste mucha atención al hacer las modificaciones, Patricia no puede revisar los horarios de todos los alumnos.');
		}
		
		if ($request->method == 'POST') {
			/* Adelante, eliminar esta hora */
			Gatuf_Log::info (sprintf ('La hora (%s, %s) ha sido eliminada del NRC %s por el usuario %s', $hora->id, $hora->hash(), $hora->nrc, $request->user->codigo));
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
	
	public $actualizarHora_precond = array (array ('Pato_Precondition::hasAnyPerm', array ('Patricia.editar_secciones_vacio', 'Patricia.admin_secciones')));
	public function actualizarHora ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$has_alumnos = false;
		$cant_alumnos = $seccion->get_alumnos_list (array ('count' => true));
		if ($cant_alumnos > 0) {
			$has_alumnos = true;
		}
		
		/* Si la sección tiene alumnos, no puede eliminar esta sección si no tiene el permiso adecuado */
		if ($has_alumnos && !$request->user->hasPerm ('Patricia.admin_secciones')) {
			return new Gatuf_HTTP_Response_Forbidden($request);
		}
		
		$hora = new Pato_Horario ();
		
		if (false === ($hora->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($hora->nrc != $seccion->nrc) {
			throw new Gatuf_HTTP_Error404();
		}

		if ($has_alumnos > 0) {
			$request->user->setMessage (2, 'Modificar el horario de una sección cuando ya tiene alumnos inscritos puede ser fatal y desastrozo. Podría provocar colisiones en los horarios de los alumnos. Preste mucha atención al hacer las modificaciones, Patricia no puede revisar los horarios de todos los alumnos.');
		}
		
		$extra = array('seccion' => $seccion, 'horario' => $hora);
		if ($request->method == 'POST') {
			$form = new Pato_Form_Horario_Actualizar ($request->POST, $extra);
			
			if ($form->isValid ()) {
				Gatuf_Log::info (sprintf ('La hora (%s, %s) del NRC %s va a ser actualizada', $hora->id, $hora->hash(), $hora->nrc));
				$horario = $form->save ();
				Gatuf_Log::info (sprintf ('A: %s, por el usuario %s', $horario->hash(), $request->user->codigo));
				
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
