<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Pato_Views_Alumno {
	public function index ($request, $match) {
		$alumno =  new Pato_Alumno ();
		
		$pag = new Gatuf_Paginator ($alumno);
		$pag->action = array ('Pato_Views_Alumno::index');
		$pag->summary = 'Lista de los alumnos';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_FKLink', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron alumnos';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/index.html',
		                                         array('page_title' => 'Alumnos',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public $agregarAlumno_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarAlumno ($request, $match) {
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Agregar ($request->POST);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verAlumno', array ($alumno->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_Agregar (null);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/agregar-alumno.html',
		                                         array ('page_title' => 'Nuevo alumno',
		                                                'form' => $form),
		                                         $request);
	}
	
	
	public function verAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$alumno->getUser ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/ver-alumno.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno),
                                                 $request);
	}

	public function verGruposAlumno($request, $match) {
		$alumno = new Calif_Alumno ();
		
		if (false === ($alumno->get ($match[1] ) ) ) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$alumno->getUser ();
		$secciones = $alumno->get_grupos_list(array ('view' => 'paginador'));
		if ($secciones->count () == 0) $secciones = array ();
		
		$grupos = array ();
		foreach (Gatuf::factory('Calif_GrupoEvaluacion')->getList() as $g){
			$grupos[$g->id] = $g;
		}
		
		$evaluaciones = array ();
		foreach (Gatuf::factory('Calif_Evaluacion')->getList() as $e){
			$evaluaciones[$e->id] = $e;
		}
		
		$calificaciones = array ();
		$promedios = array ();
		$porcentajes = array ();
		$calif_model = new Calif_Calificacion ();
		$materia_model = new Calif_Materia ();
		$array_eval = array(-1 => 'NP', -2 => 'SD');
		
		foreach ($secciones as $seccion) {
			$materia_model->get ($seccion->materia);
			$porcentajes[$seccion->nrc] = array ();
			
			foreach ($grupos as $geval) {
				$prom = Calif_Calificacion::getPromedio ($alumno->codigo, $seccion->nrc, $geval->id);
				
				if ($prom !== false) {
					$promedios[$seccion->nrc][$geval->id] = $prom;
					$sql = new Gatuf_SQL ('grupo=%s', $geval->id);
					$porcentajes[$seccion->nrc][$geval->id] = $materia_model->get_calif_porcentaje_list (array ('filter' => $sql->gen ()));
				}
			}
			$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
			
			$calificaciones[$seccion->nrc] = array ();
			foreach ($alumno->get_calif_calificacion_list (array ('filter' => $sql->gen ())) as $calif) {
				if ($calif->valor === null) {
					$calificaciones[$seccion->nrc][$calif->evaluacion] = null;
				} else if (array_key_exists ($calif->valor, $array_eval)) {
					$calificaciones[$seccion->nrc][$calif->evaluacion] = $array_eval[$calif->valor];
				} else {
					$calificaciones[$seccion->nrc][$calif->evaluacion] = $calif->valor.'%';
				}
			}
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/alumno/ver-grupos.html',
		                                         array('page_title' => 'Alumno '.$alumno->nombre.' '.$alumno->apellido,
		                                               'alumno' => $alumno,
		                                               'secciones' => $secciones,
		                                               'promedios' => $promedios,
		                                               'calificaciones' => $calificaciones,
		                                               'porcentajes' => $porcentajes,
		                                               'grupos' => $grupos,
		                                               'evaluaciones' => $evaluaciones),
                                                 $request);
	}

	public $actualizarAlumno_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarAlumno ($request, $match) {
		$alumno = new Pato_Alumno ();
		
		if (false === $alumno->get ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$alumno->getUser ();
		$extra = array ('alumno' => $alumno);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Alumno_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$alumno = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Alumno::verAlumno', array ($alumno->codigo));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Alumno_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/alumno/edit-alumno.html',
		                                         array ('page_title' => 'Actualizar alumno',
		                                                'alumno' => $alumno,
		                                                'form' => $form),
		                                         $request);
	}
}
