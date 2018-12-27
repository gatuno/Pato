<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Planeacion {
	public $index_precond = array ('Pato_Precondition::maestroRequired');
	public function index ($request, $match, $which) {
		if ($which == 'myself') {
			$maestro = $request->user->extra;
		} else {
			$maestro = new Pato_Maestro ();
			/* FIXME: Revisar aquí si tiene el permiso para revisar las planeaciones de otros profesores */
			if (false === ($maestro->get ($match[1]))) {
				throw new Gatuf_HTTP_Error404();
			}
		}
		$secciones = $maestro->get_primario_list (array ('view' => 'mats_cant'));
		
		$materias = array ();
		$cants = array ();
		foreach ($secciones as $s) {
			$m = $s->get_materia ();
			$m->cant_grupos = $s->cant_grupos;
			$materias[] = $m;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/index.html',
		                                          array ('page_title' => 'Planeación',
		                                                 'materias' => $materias,
		                                                 'maestro' => $maestro),
		                                          $request);
	}
	
	public $verMateria_precond = array ('Pato_Precondition::maestroRequired');
	public function verMateria ($request, $match, $which) {
		$materia = new Pato_Materia ();
		
		if ($which == 'myself') {
			$maestro = $request->user->extra;
		
			if (false === ($materia->get($match[1]))) {
				throw new Gatuf_HTTP_Error404();
			}
		} else {
			$maestro = new Pato_Maestro ();
			/* FIXME: Revisar aquí si tiene el permiso para revisar las planeaciones de otros profesores */
			if (false === ($maestro->get ($match[1]))) {
				throw new Gatuf_HTTP_Error404();
			}
			
			if (false === ($materia->get($match[2]))) {
				throw new Gatuf_HTTP_Error404();
			}
		}
		
		$sql = new Gatuf_SQL ('materia=%s', $materia->clave);
		$secciones = $maestro->get_primario_list (array ('filter' => $sql->gen ()));
		
		if (count ($secciones) == 0) {
			/* oops, el maestro no tiene grupos de esta materia. */
			if ($maestro->codigo == $request->user->extra->codigo) {
				$url = Gatuf_HTTP_URL_urlForView ('planeacion_propia');
			} else {
				$url = Gatuf_HTTP_URL_urlForView ('planeacion_otros');
			}
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		$sql = new Gatuf_SQL ('materia=%s AND maestro=%s', array ($materia->clave, $maestro->codigo));
		$unidades = Gatuf::factory ('Pato_Planeacion_Unidad')->getList (array ('filter' => $sql->gen ()));
		
		$seguimientos = array ();
		$temas = array ();
		foreach ($unidades as $unidad) {
			$temas[$unidad->id] = $unidad->get_temas_list ();
			
			foreach ($temas[$unidad->id] as $tema) {
				$seguimientos[$tema->id] = array ();
				$segs = $tema->get_seguimientos_list ();
				foreach ($segs as $s) {
					$seguimientos[$tema->id][$s->nrc] = $s;
				}
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/materia.html',
		                                          array ('page_title' => 'Planeación',
		                                                 'materia' => $materia,
		                                                 'secciones' => $secciones,
		                                                 'unidades' => $unidades,
		                                                 'temas' => $temas,
		                                                 'maestro' => $maestro,
		                                                 'seguimientos' => $seguimientos),
		                                          $request);
	}
	
	public $agregarUnidad_precond = array ('Pato_Precondition::maestroRequired');
	public function agregarUnidad ($request, $match) {
		$materia = new Pato_Materia ();
		
		$maestro = $request->user->extra;
		
		if (false === ($materia->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$sql = new Gatuf_SQL ('materia=%s', $materia->clave);
		$cant = $maestro->get_primario_list (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($cant == 0) {
			/* oops, el maestro no tiene grupos de esta materia. */
			$url = Gatuf_HTTP_URL_urlForView ('planeacion_propia');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Planeacion_AgregarUnidad ($request->POST, array ('maestro' => $maestro, 'materia' => $materia));
			
			if ($form->isValid()) {
				$unidad = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Planeacion::seleccionarUnidad', array (), array ('unidad' => $unidad->id));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Planeacion_AgregarUnidad (null, array ('maestro' => $maestro, 'materia' => $materia));
		}
		
		$sql = new Gatuf_SQL ('materia=%s AND maestro=%s', array ($materia->clave, $maestro->codigo));
		$existentes = Gatuf::factory ('Pato_Planeacion_Unidad')->getList (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($existentes > 0) {
			$form_sel = new Pato_Form_Planeacion_SeleccionarUnidad (null, array ('maestro' => $maestro, 'materia' => $materia));
		} else {
			$form_sel = null;
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/agregar_unidad.html',
		                                          array ('page_title' => 'Planeación - Agregar unidad',
		                                                 'materia' => $materia,
		                                                 'form' => $form,
		                                                 'form_sel' => $form_sel),
		                                          $request);
	}
	
	public $seleccionarUnidad_precond = array ('Pato_Precondition::maestroRequired');
	public function seleccionarUnidad ($request, $match) {
		$unidad = new Pato_Planeacion_Unidad ();
		
		if (!isset ($request->REQUEST['unidad'])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if (false === ($unidad->get($request->REQUEST['unidad']))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Planeacion::agregarTema', array ($unidad->id));
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $agregarTema_precond = array ('Pato_Precondition::maestroRequired');
	public function agregarTema ($request, $match) {
		$unidad = new Pato_Planeacion_Unidad ();
		
		if (false === ($unidad->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($unidad->maestro != $request->user->extra->codigo) {
			return new Gatuf_HTTP_Response_Forbidden ($request);
		}
		
		$materia = $unidad->get_materia ();
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Planeacion_AgregarTema ($request->POST, array ('unidad' => $unidad));
			
			if ($form->isValid()) {
				$tema = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('planeacion_materia_propia', array ($materia->clave));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Planeacion_AgregarTema (null, array ('unidad' => $unidad));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/agregar_tema.html',
		                                          array ('page_title' => 'Planeación - Agregar tema',
		                                                 'materia' => $materia,
		                                                 'unidad' => $unidad,
		                                                 'form' => $form),
		                                          $request);
	}
	
	public $borrarTema_precond = array ('Pato_Precondition::maestroRequired');
	public function borrarTema ($request, $match) {
		$tema = new Pato_Planeacion_Tema ();
		
		if (false === ($tema->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$unidad = $tema->get_unidad ();
		
		if ($unidad->maestro != $request->user->extra->codigo) {
			return new Gatuf_HTTP_Response_Forbidden ($request);
		}
		
		$materia = $unidad->get_materia ();
		
		$segs = $tema->get_seguimientos_list (array ('count' => true));
		
		if ($segs > 0) {
			/* TODO: Poner mensaje aquí */
			$url = Gatuf_HTTP_URL_urlForView ('planeacion_materia_propia', array ($materia->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$tema->delete ();
			
			$url = Gatuf_HTTP_URL_urlForView ('planeacion_materia_propia', array ($materia->clave));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/eliminar_tema.html',
		                                          array ('page_title' => 'Planeación - Eliminar tema',
		                                                 'materia' => $materia,
		                                                 'unidad' => $unidad,
		                                                 'tema' => $tema),
		                                          $request);
	}
	
	public $seguimiento_precond = array ('Pato_Precondition::maestroRequired');
	public function seguimiento ($request, $match) {
		$tema = new Pato_Planeacion_Tema ();
		
		if (false === ($tema->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$unidad = $tema->get_unidad ();
		
		if ($unidad->maestro != $request->user->extra->codigo) {
			return new Gatuf_HTTP_Response_Forbidden ($request);
		}
		
		$nrc = new Pato_Seccion ();
		
		if (false === ($nrc->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		if ($nrc->maestro != $unidad->maestro) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Revisar si ya existe un seguimiento */
		$sql = new Gatuf_SQL ('nrc=%s AND tema=%s', array ($nrc->nrc, $tema->id));
		$segs = Gatuf::factory ('Pato_Planeacion_Seguimiento')->getList (array ('filter' => $sql->gen (), 'count' => true));
		
		if ($segs > 0) {
			$url = Gatuf_HTTP_URL_urlForView ('planeacion_materia_propia', array ($unidad->materia));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Planeacion_AgregarSeguimiento ($request->POST, array ('tema' => $tema, 'seccion' => $nrc));
			
			if ($form->isValid()) {
				$seguimiento = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('planeacion_materia_propia', array ($nrc->materia));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Planeacion_AgregarSeguimiento (null, array ('tema' => $tema, 'seccion' => $nrc));
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/planeacion/agregar_seguimiento.html',
		                                          array ('page_title' => 'Planeación - Realizar seguimiento',
		                                                 'materia' => $unidad->get_materia(),
		                                                 'unidad' => $unidad,
		                                                 'tema' => $tema,
		                                                 'seccion' => $nrc,
		                                                 'form' => $form),
		                                          $request);
	}
}
