<?php

class Pato_Precondition {
	static public function selfAlumno ($request, $alumno) {
		$res = Gatuf_Precondition::loginRequired ($request);
		
		if (true !== $res) {
			return $res;
		}
		
		if (get_class ($request->user) == 'Pato_Alumno' && $request->user->codigo == $alumno->codigo) {
			return true;
		}
		
		return new Gatuf_HTTP_Response_Forbidden ($request);
	}
	
	static public function selfAlumnoOrHasPerm ($request, $alumno, $perm) {
		$res = Gatuf_Precondition::loginRequired ($request);
		
		if (true !== $res) {
			return $res;
		}
		
		if (get_class ($request->user) == 'Pato_Alumno' && $request->user->codigo == $alumno->codigo) {
			return true;
		}
		
		if ($request->user->hasPerm ($perm)) {
			return true;
		}
		
		return new Gatuf_HTTP_Response_Forbidden ($request);
	}
	
	static public function hasAnyPerm ($request, $perms) {
		$res = Gatuf_Precondition::loginRequired($request);
		if (true !== $res) {
			return $res;
		}
		foreach ($perms as $perm) {
			if ($request->user->hasPerm($perm)) {
				return true;
			}
		}
		return new Gatuf_HTTP_Response_Forbidden($request);
	}
	
	static public function maestroRequired ($request) {
		$res = Gatuf_Precondition::loginRequired($request);
		if (true !== $res) {
			return $res;
		}
		
		if (get_class ($request->user) == 'Pato_Maestro') {
			return true;
		}
		
		return new Gatuf_HTTP_Response_Forbidden ($request);
	}
}
