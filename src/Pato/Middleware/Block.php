<?php

class Pato_Middleware_Block
{

    /**
     * Process the request.
     *
     * @param Pluf_HTTP_Request The request
     * @return bool false
     */
    function process_request(&$request)
    {
            $res = new Gatuf_HTTP_Response('Servidor en mantenimiento'."\n\n".'Estamos actualizando el sistema para dar un mejor servicio. Por favor, intenta ingresar en 1 hora.', 'text/plain');
            $res->status_code = 503;
            return $res;
    }
}
