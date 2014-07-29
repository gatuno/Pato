<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Seccion {
	public function index ($request, $match) {
		$seccion = new Pato_Seccion ();
		$filtro = array();

		/* Enlaces extras */
		$pag = new Gatuf_Paginator ($seccion);
		$pag->model_view = 'paginador';
		$pag->action = array ('Pato_Views_Seccion::index');
		$sql = new Gatuf_SQL ();
		
		/* Aplicando filtrado por carrera y/o departamento
		if ($request->method == 'POST') {
			$form = new Calif_Form_Seccion_Filtrar ($request->POST, array ('logged' => !$request->user->isAnonymous ()));
			if ($form->isValid ()) {
				$filtrado = $form->save ();	
				$data = substr($filtrado, 2);
				if ($filtrado[0] == 'c') {
					$this->porCarrera ($request, array (0 => '', 1 => $data));
				} else if ($filtrado[0] == 'a') {
					$this->porAsignadas ($request, array ());
				} else if ($filtrado[0] == 'n') {
					$this->porNoAsignadas ($request, array ());
				} else if ($filtrado[0] == 's') {
					$this->porSuplente ($request, array ());
				}
			}
		} else {
			$form = new Calif_Form_Seccion_Filtrar (null, array ('logged' => !$request->user->isAnonymous ()));
		}*/
		
		/* Verificar filtro por Carrera
		$car = $request->session->getData('filtro_seccion_asignada_carrera', null);
		$noasig = $request->session->getData('filtro_seccion_asignada_no', false);
		$asig = $request->session->getData('filtro_seccion_asignada', false);
		$suplente = $request->session->getData('filtro_seccion_suplente', false);
		if ($asig === true) {
			$filtro['a'] = 'Secciones asignadas';
			
			$sql->Q ('asignacion IS NOT NULL');
		} else if ($noasig === true) {
			$filtro['n'] = 'Secciones no solicitadas';
			
			$sql->Q ('asignacion IS NULL');
		} else if (!is_null ($car)) {
			$carrera = new Calif_Carrera ($car);
			$filtro['c'] = 'Secciones asignadas a la carrera "'.$carrera->descripcion.'"';
			
			$sql->Q ('asignacion=%s', $car);
		}
		
		if ($suplente === true) {
			$filtro['s'] = 'Con suplente asignado';
			$sql->Q ('suplente IS NOT NULL');
		} */
		
		//$pag->forced_where = $sql;
		$pag->summary = 'Lista de secciones';
		$list_display = array (
			array ('nrc', 'Gatuf_Paginator_FKLink', 'NRC'),
			array ('materia', 'Gatuf_Paginator_FKLink', 'Materia'),
			array ('seccion', 'Gatuf_Paginator_FKLink', 'Sección'),
			array ('maestro_apellido', 'Gatuf_Paginator_FKLink', 'Maestro'),
		);
		
		$pag->items_per_page = 30;
		$pag->no_results_text = 'No se encontraron secciones';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('nrc', 'materia', 'seccion', 'materia_desc', 'maestro_nombre', 'maestro_apellido', 'suplente'),
			array ('nrc', 'materia', 'seccion', 'maestro_apellido','suplente')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/index.html',
		                                          array ('paginador' => $pag,
		                                                 /*'filtro'=>$filtro,
		                                                 'form' => $form,*/
		                                                 'page_title' => 'Secciones'),
		                                          $request);
	}

	public function porCarrera ($request, $match) {
		$carrera = new Calif_Carrera ();
		
		if (false === ($carrera->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$request->session->setData('filtro_seccion_asignada_carrera',$match[1]);
		$request->session->setData('filtro_seccion_asignada_no', false);
		$request->session->setData('filtro_seccion_asignada', false);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}

	public function porNoAsignadas ($request, $match) {
		$request->session->setData('filtro_seccion_asignada_carrera',null);
		$request->session->setData('filtro_seccion_asignada_no', true);
		$request->session->setData('filtro_seccion_asignada', false);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function porAsignadas ($request, $match) {
		$request->session->setData('filtro_seccion_asignada_carrera',null);
		$request->session->setData('filtro_seccion_asignada_no', false);
		$request->session->setData('filtro_seccion_asignada', true);
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function porSuplente ($request, $match) {
		$request->session->setData ('filtro_seccion_suplente', true);
		
		$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::index');
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public function eliminarFiltro($request, $match){
		if ($match[1] == 'a') {
			$request->session->setData('filtro_seccion_asignada', false);
		} else if($match[1] == 'c'){
			$request->session->setData('filtro_seccion_asignada_carrera',null);
		} else if ($match[1] == 'n') {
			$request->session->setData('filtro_seccion_asignada_no', false);
		} else if ($match[1] == 's') {
			$request->session->setData ('filtro_seccion_suplente', false);
		}
		
		$url = Gatuf_HTTP_URL_urlForView('Calif_Views_Seccion::index');
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}

	public function verNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$materia = $seccion->get_materia ();
		$maestro = $seccion->get_maestro ();
		if ($seccion->suplente != null) {
			$suplente = $seccion->get_suplente ();
		} else {
			$suplente = null;
		}
		
		//$horarios = $seccion->get_pato_horario_list (array ('view' => 'paginador'));
		$horarios = array ();
		/*$alumnos = $seccion->get_grupos_list (array ('order' => 'apellido ASC, nombre ASC'));
		if (count ($alumnos) == 0) $alumnos = array ();
		
		// Llenar arreglo $evaluacion[Grupos_Evaluaciones->id][Porcentajes->evaluacion]=Evaluaciones->descripcion
		$todas_evaluaciones = array ();
		
		foreach (Gatuf::factory('Calif_Evaluacion')->getList() as $e){
			$todas_evaluaciones[$e->id] = $e;
		}
		
		$porcentajes = array ();
		$grupo_evals = (array) Gatuf::factory('Calif_GrupoEvaluacion')->getList();
		foreach ($grupo_evals as $key => $geval) {
			$sql = new Gatuf_SQL ('grupo=%s', array($geval->id));
			$porcentajes[$geval->id] = (array) $materia->get_calif_porcentaje_list (array ('filter' => $sql->gen()));
		
			if (count ($porcentajes[$geval->id]) == 0) {
				unset ($grupo_evals[$key]);
			}
		}
		$calificacion = array();
		$promedios = array ();
		$promedios_eval = array();
		if (count ($grupo_evals) != 0) {
			// Llenar Arreglo $calificacion[calificaciones->alumno][calificaciones->evaluacion]=calificaciones->valor; 
			$array_eval = array(-1 => 'NP', -2 => 'SD');
			$sql = new Gatuf_SQL ('nrc=%s', $seccion->nrc);
			$where = $sql->gen ();
		
			foreach ($alumnos as $alumno) {
				$calificacion[$alumno->codigo] = array ();
				$promedios[$alumno->codigo] = array ();
				foreach ($alumno->get_calif_calificacion_list(array ('filter' => $where)) as $t_calif) {
					if (array_key_exists($t_calif->valor , $array_eval)) {
						$t_calif->valor = $array_eval[$t_calif->valor];
					} else if($t_calif->valor) {
						$t_calif->valor .='%';
					}
					$calificacion[$t_calif->alumno][$t_calif->evaluacion] = $t_calif->valor;
				}
				foreach ($grupo_evals as $geval) {
					$promedios[$alumno->codigo][$geval->id] = Calif_Calificacion::getPromedio ($alumno->codigo, $seccion->nrc, $geval->id);
				}
			}
		
			// LLenar arreglo con los promedios de la
			$promedio_model = new Calif_Promedio ();
			
			foreach ($grupo_evals as $geval) {
				foreach ($porcentajes[$geval->id] as $eval) {
					$promedio_model->getPromedioEval ($seccion->nrc, $eval->evaluacion);
					$promedios_eval[$eval->evaluacion] = $promedio_model->promedio.'%';
				}
			}
		} else {
			$grupo_evals = array ();
		}*/
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/ver-seccion.html',
		                                          array ('page_title' => 'NRC '.$seccion->nrc,
		                                                 'seccion' => $seccion,
		                                                 'materia' => $materia,
		                                                 'maestro' => $maestro,
		                                                 'suplente' => $suplente,
		                                                 'horarios' => $horarios,
		                                                 /*'alumnos' => $alumnos,
		                                                 'evals' => $todas_evaluaciones,
		                                                 'porcentajes' => $porcentajes,
		                                                 'grupo_evals' => $grupo_evals,
		                                                 'calificacion' => $calificacion,
		                                                 'promedios' => $promedios,
		                                                 'promedios_eval' => $promedios_eval,*/
		                                                 ),
		                                          $request);
	}
	
	public $agregarNrc_precond = array ('Gatuf_Precondition::adminRequired');
	public function agregarNrc ($request, $match) {
		$extra = array ('user' => $request->user);
		
		/*if ($request->user->isJefe ()) { */
			/* Formulario completo para los administradores, o la otra condición */
			if ($request->method == 'POST') {
				$form = new Pato_Form_Seccion_Agregar ($request->POST, $extra);
				
				if ($form->isValid()) {
					$seccion = $form->save ();
					
					$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($seccion->nrc));
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
			} else {
				if (isset ($request->REQUEST['materia'])) {
					$materia = new Pato_Materia ();
					if (false === ($materia->get($request->REQUEST['materia']))) {
						$extra['materia'] = '';
					} else {
						$extra['materia'] = $materia->clave;
					}
				}
				$form = new Pato_Form_Seccion_Agregar (null, $extra);
			}
			
			return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/agregar-seccion.html',
			                                         array ('page_title' => 'Crear sección',
			                                                'form' => $form),
			                                         $request);
		//} else {
			/* El caso de los coordinadores */
			/*if ($request->method == 'POST') {
				$form = new Calif_Form_Seccion_AgregarMini ($request->POST, $extra);
				
				if ($form->isValid ()) {
					$seccion = $form->save ();
					
					$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
					return new Gatuf_HTTP_Response_Redirect ($url);
				}
			} else {
				if (isset ($request->REQUEST['materia'])) {
					$materia = new Calif_Materia ();
					if (false === ($materia->get($request->REQUEST['materia']))) {
						$extra['materia'] = '';
					} else {
						$extra['materia'] = $materia->clave;
					}
				}
				$form = new Calif_Form_Seccion_AgregarMini (null, $extra);
			}
			
			return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/agregar-mini.html',
			                                         array ('page_title' => $title,
			                                                'form' => $form),
			                                         $request);
		}*/
	}
	
	public $actualizarNrc_precond = array ('Gatuf_Precondition::adminRequired');
	public function actualizarNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$extra = array ('seccion' => $seccion);
		
		if ($request->method == 'POST') {
			$form = new Pato_Form_Seccion_Actualizar ($request->POST, $extra);
			
			if ($form->isValid()) {
				$seccion = $form->save ();
				
				$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Pato_Form_Seccion_Actualizar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/edit-seccion.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'seccion' => $seccion,
		                                                'form' => $form),
		                                         $request);
	}
	
	public $eliminarNrc_precond = array ('Gatuf_Precondition::adminRequired');
	public function eliminarNrc ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		
		if ($request->method == 'POST') {
			/* Eliminar todos los alumnos de este grupo */
			$related = $seccion->get_alumnos_list();
			foreach ($related as $rel) {
				$seccion->delAssoc($rel);
			}
			
			/* Eliminar todas los horarios dde esta sección */
			//$horas = $seccion->get_pato_horario_list ();
			
			foreach ($horas as $hora) {
				$hora->delete ();
			}
			
			$seccion->delete ();
			$url = Gatuf_HTTP_URL_urlForView ('Pato_Views_Seccion::index');
			
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/eliminar-seccion.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'seccion' => $seccion),
		                                         $request);
	}
	
	public function verAlumnos ($request, $match) {
		$seccion = new Pato_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		$alumnos = $seccion->get_alumnos_list ();
		
		return Gatuf_Shortcuts_RenderToResponse ('pato/seccion/ver-alumnos.html',
		                                         array ('page_title' => 'NRC '.$seccion->nrc,
		                                                'alumnos' => $alumnos,
		                                                'seccion' => $seccion),
		                                         $request);
	}
	
	public $reclamarNrc_precond = array ('Calif_Precondition::coordinadorRequired');
	public function reclamarNrc ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
		
		$carrera_a_reclamar = new Calif_Carrera ();
		
		if (false === ($carrera_a_reclamar->get ($match[2]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		
		/* Verificar que la materia pertenezca a la carrera */
		$materia = new Calif_Materia ($seccion->materia);
		
		$carreras = $materia->get_carreras_list ();
		
		$permiso = false;
		
		foreach ($carreras as $c) {
			if ($carrera_a_reclamar->clave == $c->clave) $permiso = true;
		}
		
		if ($permiso == false) {
			$request->user->setMessage (2, sprintf ('No puede reclamar esta sección. La materia "%s" no pertenece a la carrera %s', $materia->descripcion, $carrera_a_reclamar->descripcion));
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		 /* Verificar que el maestro sea coordinador de la carrera que quiere reclamar */
		if (!$request->user->hasPerm ('SIIAU.coordinador.'.$carrera_a_reclamar->clave)) {
			$request->user->setMessage (2, 'No puede reclamar secciones para '.$carrera_a_reclamar->clave.'. Usted no es coordinador de esta carrera');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Si ya está asignado, marcar error */
		if (!is_null ($seccion->asignacion)) {
			$request->user->setMessage (2, 'La sección ya ha sido reclamada por '.$seccion->asignacion);
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		/* Ahora, intentar asignar el nrc */
		$seccion->asignacion = $carrera_a_reclamar;
		
		if ($seccion->updateAsignacion () === true) {
			$request->user->setMessage (1, 'La sección '.$seccion->nrc.' ha sido marcada para la carrera '.$carrera_a_reclamar->clave);
		} else {
			$request->user->setMessage (2, 'La sección '.$seccion->nrc.' no pudo ser reclamada. Por favor intentelo otra vez');
		}
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $liberarNrc_precond = array ('Calif_Precondition::coordinadorRequired');
	public function liberarNrc ($request, $match) {
		$seccion = new Calif_Seccion ();
		
		if (false === ($seccion->get ($match[1]))) {
			throw new Gatuf_HTTP_Error404 ();
		}
		$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', $seccion->nrc);
		
		if (is_null ($seccion->asignacion)) {
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		
		if (!$request->user->hasPerm ('SIIAU.coordinador.'.$seccion->asignacion)) {
			$request->user->setMessage (2, 'No puede liberar la sección '.$seccion->nrc.', usted no es el coordinador que la solicitó.');
		} else {
			$seccion->liberarAsignacion ();
			$request->user->setMessage (1, 'La sección '.$seccion->nrc.' ha sido liberada.');
		}
		
		return new Gatuf_HTTP_Response_Redirect ($url);
	}
	
	public $matricular_precond = array ('Gatuf_Precondition::adminRequired');
	public function matricular ($request, $match){
		$seccion =  new Calif_Seccion ();
		if (false === ($seccion->get($match[1]))) {
			throw new Gatuf_HTTP_Error404();
		}
		$title = 'Matricular Alumno a Seccion '.$seccion->nrc;
		$extra = array ('nrc' => $seccion);
		if ($request->method == 'POST') {
			$form = new Calif_Form_Seccion_Matricular ($request->POST, $extra);
			if ($form->isValid ()) {
				$form->save ();
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Seccion::verNrc', array ($seccion->nrc));
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Seccion_Matricular (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/seccion/matricular.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
}
