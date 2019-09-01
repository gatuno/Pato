<?php

Gatuf::loadFunction ('Pato_Utils_numeroLetra');
Gatuf::loadFunction ('Pato_Calendario_getDefault');

class Pato_PDF_Seccion_Acta extends External_FPDF {
	private function addHeader ($seccion, $gpe, $folio, $fecha) {
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		$this->AliasNbPages();
		
		$this->Image (dirname(__FILE__).'/../data/logo-1.jpg', 18, 8, 26, 30);
		
		$this->SetFont('Arial', '', 10);
		$this->SetY (16);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Acta  Final  de  Calificaciones', 0, 0, 'C');
		
		$this->SetY (25);
		$this->SetX (53);
		$this->CellSmallCaps (151, 6, 'Departamento  de  Servicios  Escolares', 0, 0, 'C');
		
		$this->SetY (35);
		//$this->SetX (13);
		$text = sprintf ('Folio: %06d', $folio);
		$this->Cell (191, 6, $text, 0, 0, 'R');
		
		$this->Rect (13, 42, 135, 8);
		
		$this->SetY (42);
		$this->SetX (13);
		$materia = $seccion->get_materia ();
		$this->Cell (90, 8, 'MATERIA: '.$materia->descripcion, 0, 0, 'L');
		
		$this->SetX (148);
		$this->Cell (56, 8, 'CLAVE: '.$materia->clave, 1, 0, 'L');
		
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
		$this->Cell (135, 8, 'PROGRAMA EDUCATIVO: '.$car_desc, 1);
		
		/* Condicionado a que esto se elimine */
		$this->SetX (148);
		$this->Cell (56, 8, 'EVALUACION: '.$gpe->descripcion, 1, 0, 'L');
		
		$this->SetY (58); $this->SetX (13);
		$this->Cell (42, 8, 'CUATRIMESTRE:: '.$materia->cuatrimestre.'o', 1);
		
		$this->SetX (55);
		$this->Cell (93, 8, 'FECHA: '.$fecha, 1);
		
		$this->SetX (148);
		$this->Cell (56, 8, 'PERIODO: '.$calendario->descripcion, 1);
		
		$this->SetFont('Arial', '', 10);
		$this->SetY (69); $this->SetX (13);
		$this->SetFillColor (186);
		
		$this->Cell (13, 7, 'No', 1, 0, 'C', true);
		
		$this->SetX (22);
		$this->Cell (20, 7, 'Matricula', 1, 0, 'C', true);
		
		$this->SetX (42);
		$this->Cell (36, 7, 'Carrera', 1, 0, 'C', true);

		$this->SetX (78);
		$this->Cell (69, 7, 'Nombre del Alumno', 1, 0, 'C', true);
		
		$this->SetX (147);
		$this->Cell (18, 7, 'Cal. Num.', 1, 0, 'C', true);
		
		$this->SetX (165);
		$this->Cell (39, 7, 'Calificación Letra', 1, 0, 'C', true);
	}
	
	function renderActa ($seccion, $gpe, $folio = 1, $timestamp) {
		setlocale (LC_TIME, 'es_MX');
		$fecha = strftime ('%e de %B de %Y', $timestamp);
		
		/* Renderizar el Acta */
		$this->addHeader ($seccion, $gpe, $folio, $fecha);
		$alumnos = $seccion->get_alumnos_list (array ('order' => 'apellido ASC, nombre ASC'));
		
		$calendario = new Pato_Calendario ($GLOBALS['CAL_ACTIVO']);
		
		$this->SetFont('Arial', '', 10);
		/* Imprimir los alumnos */
		$y = 76;
		$g = 1;
		$altura = 4;
		foreach ($alumnos as $alumno) {
			$inscripcion_alumno = $alumno->get_inscripcion_for_cal ($calendario);
			
			$this->SetY ($y);
			$this->SetX (13);
			$this->Cell (9, $altura, $g, 1, 0, 'C');
			
			$this->SetX (22);
			$this->Cell (20, $altura, $alumno->codigo, 1, 0, 'C');
			
			$this->SetX (42);
			$this->Cell (36, $altura, $inscripcion_alumno->carrera, 1, 0, 'C');

			$this->SetX (78);
			$this->Cell (69, $altura, $alumno->apellido.' '.$alumno->nombre, 1, 0, 'L');
			
			$sql = new Gatuf_SQL ('alumno=%s AND nrc=%s', array ($alumno->codigo, $seccion->nrc));
			/* Recuperar la calificación final */
			$this->SetX (147);
			
			$sql = new Gatuf_SQL ('calendario=%s AND materia=%s AND gpe=%s', array ($GLOBALS['CAL_ACTIVO'], $seccion->materia, $gpe->id));
			$kardexs = $alumno->get_kardex_list (array ('filter' => $sql->gen ()));
			
			if (count ($kardexs) == 0) {
				$this->Cell (18, $altura, '--', 1, 0, 'C');
			} else {
				if ($kardexs[0]->calificacion <= 0) {
					$especiales = array (-3 => 'IN', -2 => 'SD', 0 => 'NA');
					$this->Cell (18, $altura, $especiales[(int) $kardexs[0]->calificacion], 1, 0, 'C');
				} else {
					$this->Cell (18, $altura, $kardexs[0]->calificacion, 1, 0, 'C');
				}
			}
			
			if (count ($kardexs) != 0) {
				$this->SetX (165);
				if ($kardexs[0]->calificacion <= 0) {
					switch ($kardexs[0]->calificacion) {
						case 0:
							$letra = 'No acredito';
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
				$this->Cell (39, $altura, $letra, 1, 0, 'C');
			} else {
				$this->Rect (165, $y, 56, $altura);
			}
			
			$g++;
			$y = $y + $altura;
			
			if ($g % 41 == 0) {
				/* Cerrar el pie */
				$this->SetFont('Arial','I',8);
				$this->SetY ($this->GetY () + 8);
				$this->setx(150);
				$this->Cell(0,10,'Foja: '.$this->PageNo().'/{nb}', 0, 0, 'R');
				
				$this->SetY (265);
				$this->SetX (20);
				
				$this->Cell (0, 8, 'F-SE-04-05');
				
				$this->SetX (100);
				$this->Cell (0, 8, 'Revision: 00');
				
				$this->SetX (150);
				$this->Cell (0, 8, 'Fecha: 6 de marzo de 2013');
				
				$this->addHeader ($seccion, $gpe, $folio, $fecha);
				$y = 76;
				
				$this->SetFont('Arial', '', 10);
			}
		}
		
		$y = $y + 4;
		
		$this->SetLineWidth (0.6);
		$this->Rect (13, $y, 191, 9, 'FD');
		
		$this->SetLineWidth (0.2);
		$this->SetY ($y);
		$this->SetX (13);
		$this->Cell (191, 7, 'Tlajomulco de Zuniga, Jalisco a '.$fecha, 0, 0, 'C');
		
		$this->SetFont('Arial', '', 8);
		$this->SetY ($y + 4);
		$this->SetX (13);
		$this->Cell (191, 7, 'ESTE DOCUMENTO NO ES VALIDO SI PRESENTA RASPADURAS O ENMENDADURAS', 0, 0, 'C');
		
		$this->SetFont('Arial', 'I', 10);
		
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
		
		$this->SetFont('Arial','I',8);
		$this->SetY (260);
		$this->SetX (150);
		$this->Cell (0, 10, 'Foja: '.$this->PageNo ().'/{nb}', 0, 0, 'R');
		
		$this->SetY (265);
		$this->SetX (20);
		$this->Cell (0, 8, 'F-SE-04-05');
		
		$this->SetX (100);
		$this->Cell (0, 8, 'Revision: 00');
		
		$this->SetX (150);
		$this->Cell (0, 8, 'Fecha: 6 de marzo de 2013', 0, 0, 'R');
	}	
}

