<?php

Gatuf::loadFunction ('Pato_Utils_numeroLetra');
Gatuf::loadFunction ('Pato_Calendario_getDefault');

class Pato_PDF_Seccion_Acta extends External_FPDF {
	private function addHeader ($seccion, $gpe, $folio, $fecha) {
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		$this->Image (dirname(__FILE__).'/../data/logo-1.jpg', 18, 8, 26, 30);
		
		$this->SetFont('Times', '', 12);
		$this->SetY (16);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Acta  Final  de  Calificaciones', 0, 0, 'C');
		
		$this->SetY (25);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Departamento  de  Servicios  Escolares', 0, 0, 'C');
		
		$this->SetY (35);
		$this->SetX (13);
		$text = sprintf ('Folio: %06d', $folio);
		$this->Cell (191, 6, $text, 0, 0, 'R');
		
		$this->Rect (13, 42, 135, 8);
		
		$this->SetY (42);
		$this->SetX (13);
		$materia = $seccion->get_materia ();
		
		$this->Cell (135, 8, 'Materia: '.$materia->descripcion, 0, 0, 'L');
		$this->SetX (13);
		$this->Cell (135, 8, 'Evaluación: '.$gpe->descripcion, 0, 0, 'R');
		
		$this->SetX (148);
		$this->Cell (56, 8, 'Clave: '.$materia->clave, 1, 0, 'L');
		
		$calendario = new Pato_Calendario ($GLOBALS['CAL_ACTIVO']);
		/* Arreglado */
		$alumnos = $seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC', 'nb' => 1));
		if (count ($alumnos) == 0) {
			$car_desc = '';
		} else {
			$inscripcion = $alumnos[0]->get_inscripcion_for_cal($calendario);
			if ($inscripcion == null) {
				$car_desc = '';
			} else {
				$carrera = $inscripcion->get_carrera ();
				$car_desc = $carrera->descripcion;
			}
		}
		
		$this->SetY (50); $this->SetX (13);
		$this->Cell (135, 8, 'Programa educativo: '.$car_desc, 1);
		
		/* Condicionado a que esto se elimine */
		$this->SetX (148);
		$this->Cell (56, 8, 'Ciclo: '.$calendario->anio.'-'.($calendario->anio + 1), 1, 0, 'L');
		
		$this->SetY (58); $this->SetX (13);
		$this->Cell (42, 8, 'Cuatrimestre: '.$materia->cuatrimestre.'º', 1);
		
		$this->SetX (55);
		$this->Cell (93, 8, 'Fecha: '.$fecha, 1);
		
		$this->SetX (148);
		$this->Cell (56, 8, 'Periodo: '.$calendario->descripcion, 1);
		
		$this->SetFont('Times', 'B', 12);
		$this->SetY (69); $this->SetX (13);
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
	}
	
	function renderActa ($seccion, $gpe, $folio = 1, $timestamp) {
		setlocale (LC_TIME, 'es_MX');
		$fecha = strftime ('%e de %B de %Y', $timestamp);
		//$fecha='';
		/* Renderizar el Acta */
		$this->addHeader ($seccion, $gpe, $folio, $fecha);
		$alumnos = $seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		
		$this->SetFont('Times', '', 12);
		/* Imprimir los alumnos */
		$y = 76;
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
			
			$sql = new Gatuf_SQL ('calendario=%s AND materia=%s AND gpe=%s', array ($GLOBALS['CAL_ACTIVO'], $seccion->materia, $gpe->id));
			$kardexs = $alumno->get_kardex_list (array ('filter' => $sql->gen ()));
			
			if (count ($kardexs) == 0) {
				$this->Cell (24, $altura, '--', 1, 0, 'C');
			} else {
				if ($kardexs[0]->calificacion <= 0) {
					$especiales = array (-3 => 'IN', -2 => 'SD', 0 => 'NA');
					$this->Cell (24, $altura, $especiales[(int) $kardexs[0]->calificacion], 1, 0, 'C');
				} else {
					$this->Cell (24, $altura, $kardexs[0]->calificacion, 1, 0, 'C');
				}
			}
			
			if (count ($kardexs) != 0) {
				$this->SetX (148);
				if ($kardexs[0]->calificacion <= 0) {
					switch ($kardexs[0]->calificacion) {
						case 0:
							$letra = 'No acreditó';
							break;
						case -2:
							$letra = 'Sin derecho';
							break;
						case -3:
							$letra = 'Curso Incompleto';
							break;
					}
				} else {
					$letra = mb_convert_case (Pato_Utils_numeroLetra ($kardexs[0]->calificacion), MB_CASE_TITLE);
				}
				$this->Cell (56, $altura, $letra, 1, 0, 'C');
			} else {
				$this->Rect (148, $y, 56, $altura);
			}
			
			$g++;
			$y = $y + $altura;
			
			if ($g % 46 == 0) {
				/* Cerrar el pie */
				$this->SetY (265);
				$this->SetX (20);
				
				$this->Cell (0, 8, 'F-SE-04-05');
				
				$this->SetX (100);
				$this->Cell (0, 8, 'Revisión: 00');
				
				$this->SetX (150);
				$this->Cell (0, 8, 'Fecha: 6 de marzo de 2013');
				
				$this->addHeader ($seccion, $gpe, $folio, $fecha);
				$y = 76;
				
				$this->SetFont('Times', '', 12);
			}
		}
		
		$y = $y + 4;
		
		$this->SetLineWidth (0.6);
		$this->Rect (13, $y, 191, 9, 'FD');
		
		$this->SetLineWidth (0.2);
		$this->SetY ($y);
		$this->SetX (13);
		$this->Cell (191, 7, 'Tlajomulco de Zuñiga, Jalisco a '.$fecha, 0, 0, 'C');
		
		$this->SetFont('Times', '', 8);
		$this->SetY ($y + 4);
		$this->SetX (13);
		$this->Cell (191, 7, 'Este documento no es válido si presenta raspaduras o enmendaduras', 0, 0, 'C');
		
		$this->SetFont('Times', '', 12);
		
		$y += 13;
		
		$this->Rect (13, $y, 64, 22);
		$this->Rect (77, $y, 63, 22);
		$this->Rect (140, $y, 64, 22);
		
		$this->SetY ($y);
		$this->SetX (13);
		$this->CellSmallCaps (64, 8, 'Profesor', 0, 0, 'C');
		
		$this->SetX (77);
		$this->CellSmallCaps (63, 8, "Dir.  de  Prog.  Educativo", 0, 0, 'C');
		
		$this->SetY ($y);
		$this->SetX (140);
		$this->CellSmallCaps (64, 8, 'Servicios  Escolares', 0, 0, 'C');
		
		$maestro = $seccion->get_maestro ();
		$this->SetY ($y + 14);
		$this->SetX (13);
		$this->Cell (64, 8, $maestro->displaygrado().' '.$maestro->nombre.' '.$maestro->apellido, 0, 0, 'C');
		
		$this->SetX (77);
		$this->Cell (63, 8, '', 0, 0, 'C');
		
		$this->SetX (140);
		$this->Cell (64, 8, 'Lic. M. Estela Padilla de Anda', 0, 0, 'C');
		
		$this->SetY (265);
		$this->SetX (20);
		
		$this->Cell (0, 8, 'F-SE-04-05');
		
		$this->SetX (100);
		$this->Cell (0, 8, 'Revisión: 00');
		
		$this->SetX (150);
		$this->Cell (0, 8, 'Fecha: 6 de marzo de 2013');
	}	
}

