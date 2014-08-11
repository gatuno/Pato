<?php

class Pato_Precondition {
	static public function coordinadorRequired ($request) {
		$res = Gatuf_Precondition::loginRequired($request);
		if (true !== $res) {
			return $res;
		}
		
		if ($request->user->administrator) {
			return true;
		}
		
		$perms = $request->user->getAllPermissions ();
		
		$coord = preg_grep ('/Patricia.coordinador.*/', $perms);
		
		if (count ($coord) > 0) {
			return true;
		}
		
		return new Gatuf_HTTP_Response_Forbidden ($request);
	}
}
