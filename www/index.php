<?php

require dirname(__FILE__).'/../src/Pato/conf/path.php';

# Cargar Gatuf
require 'Gatuf.php';

# Inicializar las configuraciones
Gatuf::start(dirname(__FILE__).'/../src/Pato/conf/pato.php');

Gatuf_Despachador::loadControllers(Gatuf::config('pato_views'));

Gatuf_Despachador::despachar(Gatuf_HTTP_URL::getAction());
