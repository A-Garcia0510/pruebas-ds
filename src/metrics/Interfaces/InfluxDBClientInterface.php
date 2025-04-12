<?php
// src/Metrics/Interfaces/InfluxDBClientInterface.php
namespace Metrics\Interfaces;

interface InfluxDBClientInterface {
    public function writeData(string $measurement, array $fields, array $tags = []);
    public function query(string $query);
    public function isConnected(): bool;
    public function getLastError(): string; // Método añadido para obtener el último error
}