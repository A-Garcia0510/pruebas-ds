<?php
// Instala la biblioteca con Composer: composer require influxdata/influxdb-client-php
require 'autoload.php';

use InfluxDB2\Client;
use InfluxDB2\Point;

// Configuración de InfluxDB
$url = 'http://localhost:8086';
$token = 'zSj-WseDxXqhiDEXqRvYkkPhmQYR6zw7XAuew3OsyckgttlQ0TuvMHeYi95exf_XlvZOOClyTySJzDt8mBZ3Rw==';
$bucket = 'metricas_carga';
$org = 'WorKoout';

// Crear cliente InfluxDB
$client = new Client([
    "url" => $url,
    "token" => $token,
    "bucket" => $bucket,
    "org" => $org,
    "precision" => InfluxDB2\Model\WritePrecision::S
]);

// Escribir datos
$writeApi = $client->createWriteApi();

// Crear un punto de datos
$point = Point::measurement('tiempo_respuesta')
    ->addTag('pagina', 'inicio.php')
    ->addField('valor', rand(100, 500))
    ->addField('usuarios_activos', 150)
    ->time(time());

// Escribir el punto en InfluxDB
$writeApi->write($point);
$writeApi->close();
?>