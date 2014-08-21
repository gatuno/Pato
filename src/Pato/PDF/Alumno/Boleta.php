<?php

Gatuf::loadFunction ('Pato_Utils_numeroLetra');
Gatuf::loadFunction ('Pato_Calendario_getDefault');

class Pato_PDF_Alumno_Boleta extends External_FPDF {
	private function renderAt ($alumno, $y) {
		$this->Image (dirname(__FILE__).'/../data/logo-1.jpg', 18, $y + 10, 30, 35);
	}
	
	function renderBoleta ($alumno) {
		$this->AddPage();
		$this->SetAutoPageBreak (false);
		
		$this->renderAt ($alumno, 0);
		$this->renderAt ($alumno, 140);
	}
}
