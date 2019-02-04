<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Pato_Views_Index {
	function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('pato/index.html', array ('page_title' => 'Portada'), $request);
	}
}
