<?php

Gatuf::loadFunction ('Pato_Utils_numeroLetra');
Gatuf::loadFunction ('Pato_Calendario_getDefault');

class Pato_PDF_Seccion_Acta extends External_FPDF {
	function renderPreacta ($seccion) {
		$this->SetFont('Times', '', 12);
		$fecha = '22 de Agosto de 2014';
		
		/* Renderizar el Pre Acta */
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		/* Rectángulo de arriba */
		$this->Rect (15, 10, 190, 60);
		
		$this->SetFont('Times', '', 16);
		$this->SetY (20);
		$this->SetX (150);
		$this->Cell (0, 0, 'Calificación Final', 0, 0, 'L');
		
		$this->SetFont('Times', '', 12);
		$this->SetY (28);
		$this->SetX (150);
		$this->Cell (0, 0, 'Evaluación Ordinaria', 0, 0, 'L');
		
		/* Carrera */
		$this->SetY (42);
		$this->SetX (18);
		$this->Cell (0, 0, 'Programa educativo:', 0, 0, 'L');
		
		$alumnos = $seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		$inscripciones = $alumnos[0]->get_inscripciones_list ();
		$carrera = $inscripciones[0]->get_carrera ();
		$this->SetX (58);
		$this->Cell (100, 0, $carrera->descripcion, 0, 0, 'L');
		
		/* El cuatrimestre */
		$this->SetY (47);
		$this->SetX (18);
		$this->Cell (0, 0, 'Cuatrimestre:', 0, 0, 'L');
		
		$materia = $seccion->get_materia ();
		$this->SetX (58);
		$this->Cell (100, 0, $materia->cuatrimestre.'º', 0, 0, 'L');
		
		/* El grupo */
		$this->SetX (160);
		$this->Cell (0, 0, 'Grupo:', 0, 0, 'L');
		
		$this->SetX (175);
		$this->Cell (0, 0, substr ($seccion->seccion, strlen ($seccion->seccion) - 1), 0, 0, 'L');
		
		/* El Profesor */
		$this->SetY (52);
		$this->SetX (18);
		$this->Cell (0, 0, 'Profesor:', 0, 0, 'L');
		
		if ($seccion->suplente) {
			$profesor = $seccion->get_suplente ();
		} else {
			$profesor = $seccion->get_maestro ();
		}
		$this->SetX (58);
		$this->Cell (100, 0, $profesor->nombre.' '.$profesor->apellido, 0, 0, 'L');
		
		/* Materia */
		$this->SetY (57);
		$this->SetX (18);
		$this->Cell (0, 0, 'Materia:', 0, 0, 'L');
		
		$this->SetX (58);
		$this->Cell (100, 0, $materia->descripcion, 0, 0, 'L');
		
		$this->SetY (62);
		$this->SetX (18);
		$this->Cell (0, 0, 'Fecha de Entrega:', 0, 0, 'L');
		
		$this->SetX (58);
		$this->Cell (100, 0, $fecha, 0, 0, 'L');
		
		$this->SetFont('Times', 'B', 12);
		/* Empezar el cuadriculado de las calificaciones */
		$this->Rect (15, 75, 8, 6);
		$this->Rect (23, 75, 24, 6);
		$this->Rect (47, 75, 62, 6);
		$this->SetY (75);
		$this->SetX (109);
		$this->Cell (96, 6, 'Calificación', 1, 0, 'C');
		
		$this->SetY (81);
		$this->SetX (15);
		$this->Cell (8, 6, 'No.', 1, 0, 'C');
		
		$this->SetY (81);
		$this->SetX (23);
		$this->Cell (24, 6, 'Matricula', 1, 0, 'C');
		
		$this->SetY (81);
		$this->SetX (47);
		$this->Cell (62, 6, 'Nombre del alumno', 1, 0, 'C');
		
		$this->SetY (81);
		$this->SetX (109);
		$this->Cell (22, 6, '% Asis', 1, 0, 'C');
		
		$this->SetY (81);
		$this->SetX (131);
		$this->Cell (22, 6, 'No.', 1, 0, 'C');
		
		$this->SetY (81);
		$this->SetX (153);
		$this->Cell (52, 6, 'Letra', 1, 0, 'C');
		
		$this->SetFont('Times', '', 12);
		$y = 87;
		$g = 1;
		$altura = 4;
		foreach ($alumnos as $alumno) {
			$this->SetY ($y);
			$this->SetX (15);
			$this->Cell (8, $altura, $g, 1, 0, 'C');
			
			$this->SetY ($y);
			$this->SetX (23);
			$this->Cell (24, $altura, $alumno->codigo, 1, 0, 'C');
			
			$this->SetY ($y);
			$this->SetX (47);
			$this->Cell (62, $altura, $alumno->apellido.' '.$alumno->nombre, 1, 0, 'L');
			
			$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($alumno->codigo, $seccion->nrc));
			/* Recuperar las asistencias */
			$asistencia = Gatuf::factory ('Pato_Asistencia')->getOne ($sql->gen ());
			
			$this->SetY ($y);
			$this->SetX (109);
			if ($asistencia === null) {
				$this->Cell (22, $altura, '--', 1, 0, 'C');
			} else {
				$this->Cell (22, $altura, $asistencia->asistencia, 1, 0, 'C');
			}
			
			/* Recuperar la calificación final */
			$this->SetY ($y);
			$this->SetX (131);
			
			/* FIXME: Esto está mal porque asumo que solo habrá una calificación en boleta
			 * Debe tomar la calificación final que debe ser la de Kardex */
			$boleta = Gatuf::factory ('Pato_Boleta')->getOne ($sql->gen ());
			
			if ($boleta === null) {
				$this->Cell (22, $altura, '--', 1, 0, 'C');
			} else {
				if ($boleta->calificacion < 0) {
					$especiales = array (-3 => 'IN', -2 => 'SD');
					$this->Cell (22, $altura, $especiales[(int) $boleta->calificacion], 1, 0, 'C');
				} else {
					$this->Cell (22, $altura, $boleta->calificacion, 1, 0, 'C');
				}
			}
			
			if ($boleta != null) {
				$this->SetY ($y);
				$this->SetX (153);
				if ($boleta->calificacion < 0) {
					switch ($boleta->calificacion) {
						case -2:
							$letra = 'Sin derecho';
							break;
						case -3:
							$letra = 'Curso Incompleto';
							break;
					}
				} else {
					$letra = mb_convert_case (Pato_Utils_numeroLetra ($boleta->calificacion), MB_CASE_TITLE);
				}
				$this->Cell (52, $altura, $letra, 1, 0, 'C');
			} else {
				$this->Rect (153, $y, 52, $altura);
			}
			
			$g++;
			$y = $y + $altura;
		}
		
		$this->Rect (15, $y, 8, 6);
		$this->Rect (23, $y, 24, 6);
		
		$this->SetFont('Times', 'B', 12);
		$this->SetY ($y);
		$this->SetX (47);
		$this->Cell (62, 6, 'Promedio del grupo', 1, 0, 'L');
		
		$this->SetFont('Times', '', 12);
		$this->Rect (109, $y, 22, 6);
		$this->Rect (131, $y, 22, 6);
		$this->Rect (153, $y, 52, 6);
		
		/* La parte de abajo con firmas */
		$this->Line (20, 250, 80, 250);
		$this->Line (130, 250, 190, 250);
		
		$this->SetY (250);
		$this->SetX (20);
		
		$this->Cell (60, 6, 'Profesor', 0, 0, 'C');
		
		$this->SetY (250);
		$this->SetX (130);
		
		$this->Cell (60, 6, 'Director de Programa Educativo', 0, 0, 'C');
	}
	
	function renderActa ($seccion) {
		$this->SetFont('Times', '', 12);
		$fecha = '22 de Agosto de 2014';
		
		/* Renderizar el Pre Acta */
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		$this->Image (dirname(__FILE__).'/../data/logo-1.jpg', 18, 10, 30, 35);
		
		$this->SetY (18);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Acta  Final  de  Calificaciones', 0, 0, 'C');
		
		$this->SetY (30);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Departamento  de  Servicios  Escolares', 0, 0, 'C');
		
		$this->Rect (13, 52, 135, 8);
		
		$this->SetY (52);
		$this->SetX (13);
		$materia = $seccion->get_materia ();
		
		$this->Cell (135, 8, 'Materia: '.$materia->descripcion, 0, 0, 'L');
		$this->SetX (13);
		$this->Cell (135, 8, 'Evaluación: Ordinaria', 0, 0, 'R');
		
		$this->SetX (148);
		$this->Cell (56, 8, 'Clave: '.$materia->clave, 1, 0, 'L');
		
		/* FIXME: Esto está mal */
		$alumnos = $seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		$inscripciones = $alumnos[0]->get_inscripciones_list ();
		$carrera = $inscripciones[0]->get_carrera ();
		
		$this->SetY (60); $this->SetX (13);
		$this->Cell (191, 8, 'Programa educativo: '.$carrera->descripcion, 1);
		
		$this->SetY (68); $this->SetX (13);
		$this->Cell (42, 8, 'Cuatrimestre: '.$materia->cuatrimestre.'º', 1);
		
		$this->SetX (55);
		$this->Cell (93, 8, 'Fecha: '.$fecha, 1);
		
		$calendario = new Pato_Calendario (Pato_Calendario_getDefault ());
		$this->SetX (148);
		$this->Cell (56, 8, 'Ciclo: '.$calendario->descripcion, 1);
		
		$this->SetFont('Times', 'B', 12);
		$this->SetY (79); $this->SetX (13);
		$this->SetFillColor (186);
		
		$this->Cell (13, 7, 'Nº', 1, 0, 'C', true);
		
		$this->SetX (26);
		$this->Cell (29, 7, 'Matricula', 1, 0, 'C', true);
		
		$this->SetX (55);
		$this->Cell (69, 7, 'Nombre del Alumno', 1, 0, 'C', true);
		
		$this->SetX (124);
		$this->Cell (24, 7, 'Cal. Num.', 1, 0, 'C', true);
		
		$this->SetX (148);
		$this->Cell (56, 7, 'Calificación Letra', 1, 0, 'C', true);
		
		$this->SetFont('Times', '', 12);
		/* Imprimir los alumnos */
		$y = 86;
		$g = 1;
		$altura = 4;
		foreach ($alumnos as $alumno) {
			$this->SetY ($y);
			$this->SetX (13);
			$this->Cell (13, $altura, $g, 1, 0, 'C');
			
			$this->SetX (26);
			$this->Cell (29, $altura, $alumno->codigo, 1, 0, 'C');
			
			$this->SetX (55);
			$this->Cell (69, $altura, $alumno->apellido.' '.$alumno->nombre, 1, 0, 'L');
			
			$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($alumno->codigo, $seccion->nrc));
			/* Recuperar la calificación final */
			$this->SetX (124);
			
			/* FIXME: Esto está mal porque asumo que solo habrá una calificación en boleta
			 * Debe tomar la calificación final que debe ser la de Kardex */
			$boleta = Gatuf::factory ('Pato_Boleta')->getOne ($sql->gen ());
			
			if ($boleta === null) {
				$this->Cell (24, $altura, '--', 1, 0, 'C');
			} else {
				if ($boleta->calificacion < 0) {
					$especiales = array (-3 => 'IN', -2 => 'SD');
					$this->Cell (24, $altura, $especiales[(int) $boleta->calificacion], 1, 0, 'C');
				} else {
					$this->Cell (24, $altura, $boleta->calificacion, 1, 0, 'C');
				}
			}
			
			if ($boleta != null) {
				$this->SetX (148);
				if ($boleta->calificacion < 0) {
					switch ($boleta->calificacion) {
						case -2:
							$letra = 'Sin derecho';
							break;
						case -3:
							$letra = 'Curso Incompleto';
							break;
					}
				} else {
					$letra = mb_convert_case (Pato_Utils_numeroLetra ($boleta->calificacion), MB_CASE_TITLE);
				}
				$this->Cell (56, $altura, $letra, 1, 0, 'C');
			} else {
				$this->Rect (148, $y, 56, $altura);
			}
			
			$g++;
			$y = $y + $altura;
		}
		
		$y = $y + 4;
		
		$this->SetLineWidth (0.6);
		$this->Rect (13, $y, 191, 17, 'FD');
		
		$this->SetLineWidth (0.2);
		$this->SetY ($y + 3);
		$this->SetX (13);
		$this->Cell (191, 7, 'Tlajomulco de Zuñiga, Jalisco a '.$fecha, 0, 0, 'C');
		
		$this->SetFont('Times', '', 8);
		$this->SetY ($y + 10);
		$this->SetX (13);
		$this->Cell (191, 7, 'Este documento no es válido si presenta raspaduras o enmendaduras', 0, 0, 'C');
		
		$this->SetFont('Times', '', 12);
		
		$y += 21;
		
		$this->Rect (13, $y, 64, 22);
		$this->Rect (77, $y, 63, 22);
		$this->Rect (140, $y, 64, 22);
		
		$this->SetY ($y);
		$this->SetX (13);
		$this->CellSmallCaps (64, 8, 'Profesor', 0, 0, 'C');
		
		$this->SetX (77);
		$this->MultiCellSmallCaps (63, 4, "Director  de\nPrograma  Educativo", 0, 0, 'L');
		
		$this->SetY ($y);
		$this->SetX (140);
		$this->CellSmallCaps (64, 8, 'Servicios  Escolares', 0, 0, 'C');
		
		$maestro = $seccion->get_maestro ();
		$this->SetY ($y + 14);
		$this->SetX (13);
		$this->Cell (64, 8, $maestro->displaygrado().' '.$maestro->nombre.' '.$maestro->apellido, 0, 0, 'C');
		
		$this->SetX (77);
		$this->Cell (63, 8, 'Mtro. Pedro Alonso Mayoral Ruiz', 0, 0, 'C');
		
		$this->SetX (140);
		$this->Cell (64, 8, 'Lic. M. Estela Padilla de Anda', 0, 0, 'C');
		
		$this->SetY (270);
		$this->SetX (20);
		
		$this->Cell (0, 8, 'F-SE-04-05');
		
		$this->SetX (100);
		$this->Cell (0, 8, 'Revisión: 01');
		
		$this->SetX (150);
		$this->Cell (0, 8, 'Fecha: 6 de marzo de 2013');
	}	
}

