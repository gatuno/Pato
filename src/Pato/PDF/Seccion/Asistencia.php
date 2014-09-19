<?php

Gatuf::loadFunction ('Pato_Utils_numeroLetra');
Gatuf::loadFunction ('Pato_Calendario_getDefault');

class Pato_PDF_Seccion_Asistencia extends External_FPDF {
	private function renderHeaderGrupo ($seccion) {
		$this->SetFont('Times', '', 16);
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		$this->Image (dirname(__FILE__).'/../data/logo-1.jpg', 12, 10, 30, 35);
		
		$maestro = $seccion->get_maestro ();
		$materia = $seccion->get_materia ();
		
		$this->SetY (20);
		$this->SetX (38);
		$this->CellSmallCaps (230, 6, 'Lista  de  asistencia', 0, 0, 'C');
		
		$this->SetFont('Times', '', 12);
		$this->SetY (38);
		$this->SetX (50);
		$this->Cell (80, 6, 'Profesor: '.$maestro->nombre.' '.$maestro->apellido, 0, 0, 'L');
		
		$this->SetX (130);
		$this->Cell (110, 6, 'Materia: '.$materia->descripcion.' Sección: '.$seccion->seccion, 0, 0, 'L');
		
		$this->SetX (240);
		$this->Cell (40, 6, 'NRC: '.$seccion->nrc, 0, 0, 'L');
		
		$this->SetY (50);
		$this->SetX (12);
		
		$this->SetFont('Times', 'b', 12);
		$this->Cell (28, 6, 'Matrícula', 1, 0, 'C');
		
		$this->SetX (40);
		$this->Cell (80, 6, 'Nombre del alumno', 1, 0, 'C');
		
		$x = 120;
		for ($g = 0; $g < 31; $g++) {
			$this->SetX ($x);
			$this->Cell (4, 6, '', 1, 0, 'C');
			
			$x += 4;
		}
		$this->SetX ($x);
		$this->Cell (20, 6, 'Total', 1, 0, 'C');
		
		$this->SetFont('Times', '', 12);
	}
	
	private function renderFootGrupo () {
		$calendario = new Pato_Calendario (Pato_Calendario_getDefault ());
		
		$this->SetY ($this->GetY () + 8);
		$this->SetX (12);
		
		$this->Cell (120, 6, 'Cuatrimestre: '.$calendario->descripcion, 0, 0, 'L');
		
		$this->SetX (215);
		$this->Cell (50, 6, 'Página: '.$this->PageNo().'/{nb}', 0, 0, 'R');
		
		/* Pié de página */
		$this->SetY (204);
		
		$this->SetX (12);
		$this->Cell (20, 6, 'F-SE-04-01', 0, 0, 'L');
		
		$this->SetX (120);
		$this->Cell (30, 6, 'Revisión: 0', 0, 0, 'C');
		
		$this->SetX (215);
		$this->Cell (50, 6, 'Fecha: 6 de marzo de 2013', 0, 0, 'R');
	}
	
	function renderGrupo ($seccion) {
		$this->AliasNbPages();
		
		$this->renderHeaderGrupo ($seccion);
		
		$alumnos = $seccion->get_alumnos_list ();
		
		$this->SetFont('Times', '', 12);
		
		$count = 0;
		$y = 56;
		foreach ($alumnos as $alumno) {
			if ($count >= 27) {
				$this->renderFootGrupo ();
				$this->renderHeaderGrupo ($seccion);
				$count = 0;
				$y = 56;
			}
			
			$this->SetY ($y);
			
			$this->SetX (12);
			$this->Cell (28, 5, $alumno->codigo, 1, 0, 'C');
			
			$this->SetX (40);
			$this->Cell (80, 5, $alumno->apellido.' '.$alumno->nombre, 1, 0, 'L');
			
			$x = 120;
			for ($g = 0; $g < 31; $g++) {
				$this->SetX ($x);
				$this->Cell (4, 5, '', 1, 0, 'C');
			
				$x += 4;
			}
		
			$this->SetX ($x);
			$this->Cell (20, 5, '', 1, 0, 'C');
			
			$count++;
			$y += 5;
		}
		$this->renderFootGrupo ();
	}
}

