<?php

Gatuf::loadFunction ('Pato_Utils_numeroLetra');
Gatuf::loadFunction ('Pato_Calendario_getDefault');

class Pato_PDF_Alumno_Boleta extends External_FPDF {
	private function renderAt ($alumno, $y) {
		$this->Image (dirname(__FILE__).'/../data/logo-1.jpg', 14, $y + 10, 20, 23);
		
		$this->SetY ($y + 18);
		$this->SetX (36);
		$this->CellSmallCaps (160, 6, 'Boleta  de  Calificaciones', 0, 0, 'C');
		
		$this->SetY ($y + 36);
		$this->SetX (14);
		$this->Cell (132, 5, 'Nombre del alumno: '.$alumno->apellido.' '.$alumno->nombre, 0, 0, 'L');
		
		$this->SetX (146);
		$this->Cell (50, 5, 'Matrícula: '.$alumno->codigo);
		
		$this->SetY ($y + 41);
		$this->SetX (14);
		
		$ins = $alumno->get_current_inscripcion ();
		if ($ins == null) {
			$this->Cell (132, 5, 'Programa educativo: ', 0, 0, 'L');
		} else {
			$carrera = $ins->get_carrera ();
			$this->Cell (132, 5, 'Programa educativo: '.$carrera->descripcion, 0, 0, 'L');
		}
		
		$this->SetX (146);
		$this->Cell (50, 5, 'Cuatrimestre: ', 0, 0, 'L');
		
		$calendario = new Pato_Calendario (Pato_Calendario_getDefault ());
		$this->SetY ($y + 46);
		$this->SetX (14);
		
		$this->Cell (172, 5, 'Ciclo escolar: '.$calendario->descripcion, 0, 0, 'L');
		
		$this->SetY ($y + 56);
		$this->SetX (14);
		
		$this->SetFont('Times', 'b', 12);
		$this->Cell (132, 6, 'Nombre de la materia', 1, 0, 'C');
		
		$this->SetX (146);
		$this->Cell (50, 6, 'Calificación', 1, 0, 'C');
		
		$this->SetFont('Times', '', 12);
		$secciones = $alumno->get_grupos_list ();
		
		$gy = $y + 62;
		$suma = $sumadas = 0;
		foreach ($secciones as $seccion) {
			$materia = $seccion->get_materia ();
			
			$sql = new Gatuf_SQL ('nrc=%s AND alumno=%s AND evaluacion=1', array ($seccion->nrc, $alumno->codigo));
			$boleta = Gatuf::factory ('Pato_Boleta')->getOne ($sql->gen ());
			
			$this->SetY ($gy);
			$this->SetX (14);
			$this->Cell (132, 4, $materia->descripcion, 1, 0, 'L');
			
			$this->SetX (146);
			if ($boleta->calificacion <= 0) {
				if ($boleta->calificacion == 0) {
					$this->Cell (50, 4, 'NA', 1, 0, 'C');
				}
			} else {
				$this->Cell (50, 4, $boleta->calificacion, 1, 0, 'C');
				$suma += $boleta->calificacion;
				$sumadas++;
			}
			
			$gy += 4;
		}
		
		$this->SetY ($gy);
		$this->SetX (14);
		$this->Cell (132, 6, 'Promedio de las materias aprobadas', 1, 0, 'L');
		
		$this->SetX (146);
		$this->SetFont('Times', 'b', 12);
		
		if ($sumadas == 0) {
			$this->Cell (50, 6, '0', 1, 0, 'C');
		} else {
			$promedio = number_format ($suma / $sumadas, 2);
			
			$this->Cell (50, 6, $promedio, 1, 0, 'C');
		}
		
		$gy += 10;
		
		$this->SetFont('Times', 'b', 10);
		$this->SetY ($gy);
		$this->SetX (14);
		
		$this->Cell (172, 4, 'Carretera Tlajomulco Santa Fé k.m. 3.5 #595, Lomas de Tejeda, Tlajomulco de Zuñiga, Jalisco C.P. 45640', 0, 0, 'C');
		$this->SetY ($gy + 4);
		$this->SetX (14);
		$this->Cell (172, 4, 'Teléfonos 30409916 y 30409918', 0, 0, 'C');
		
		$this->SetFont('Times', '', 12);
		
		$this->SetY ($y + 122);
		$this->SetX (14);
		$this->Cell (60, 6, 'F-SE-04-04', 0, 0, 'L');
		
		$this->SetX (95);
		$this->Cell (60, 6, 'Revisión: 0');
		
		$this->SetX (146);
		$this->Cell (50, 6, 'Fecha: 6 de marzo de 2013', 0, 0, 'R');
	}
	
	function renderBoleta ($alumno) {
		$this->SetFont('Times', '', 12);
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		$this->renderAt ($alumno, 0);
		$this->renderAt ($alumno, 140);
		$this->Line (0, 140, 216, 140);
	}
}
