<?php

class Admision_Form_Aspirante_SubirFoto extends Gatuf_Form {
	private $aspirante;
	public function initFields($extra=array()) {
		$this->aspirante = $extra['aspirante'];
		
		$upload_path = Gatuf::config ('admision_data_upload');
		$curp_filename = sprintf ('%s/foto_%s_%%s', $this->aspirante->id, $this->aspirante->id);
		$this->fields['attachment_foto'] = new Gatuf_Form_Field_File (
			array (
				'required' => true,
				'label' => 'Foto',
				'help_text' => 'La imagen',
				'move_function_params' => array (
					'upload_path' => $upload_path,
					'upload_path_create' => true,
					'file_name' => $curp_filename,
					'upload_overwrite' => true,
				),
		));
	}
	
	function clean_attachment_foto () {
		// Just png or jpeg/jpg
		if (!preg_match('/\.(png|jpg|jpeg)$/i', $this->cleaned_data['attachment_foto']) && $this->cleaned_data['attachment_foto'] != '') {
			@unlink(Gatuf::config ('admision_data_upload').'/'.$this->cleaned_data['attachment_foto']);
			throw new Gatuf_Form_Invalid('Por razones de seguridad, no puedes subir un archivo con esta extensión. Solo se permiten png y jpg/jpeg');
		}
		return $this->cleaned_data['attachment_foto'];
	}
	
	function save ($commit=true) {
		if ($this->aspirante->foto != '') {
			@unlink(Gatuf::config ('admision_data_upload').'/'.$this->aspirante->foto);
		}
		$this->aspirante->foto = $this->cleaned_data['attachment_foto'];
		
		$this->aspirante->update ();
		
		return $this->aspirante;
	}
}
