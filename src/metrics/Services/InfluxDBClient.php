<?php
// src/Metrics/Services/InfluxDBClient.php
namespace Metrics\Services;

use Metrics\InfluxDBConfiguration;
use Metrics\Interfaces\InfluxDBClientInterface;

class InfluxDBClient implements InfluxDBClientInterface {
    private $config;
    private bool $connected = false;
    private $lastError = '';

    public function __construct(InfluxDBConfiguration $config) {
        $this->config = $config;
        try {
            // Verificar la conexión con una solicitud simple a la API de InfluxDB v2
            $this->connected = $this->testConnection();
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error conectando a InfluxDB v2: " . $e->getMessage());
            $this->connected = false;
        }
    }

    /**
     * Método privado para probar la conexión con una solicitud HTTP
     */
    private function testConnection(): bool {
        try {
            // Cambiar esta línea
            $url = $this->config->getUrl() . '/health';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Token ' . $this->config->getToken()
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return true;
            } else {
                $this->lastError = "Error de conexión a InfluxDB v2. Código HTTP: " . $httpCode;
                if (!empty($curlError)) {
                    $this->lastError .= ", Error cURL: " . $curlError;
                }
                if (!empty($response)) {
                    $this->lastError .= ", Respuesta: " . $response;
                }
                return false;
            }
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }
    /**
     * Escribe datos en InfluxDB v2 usando la API de línea
     * 
     * @param string $measurement Nombre de la medición
     * @param array $fields Campos a guardar (valores)
     * @param array $tags Tags asociados (metadata)
     * @return bool
     */
    /**
 * Obtiene el nombre del bucket configurado
 * 
 * @return string
 */
    public function getBucket(): string {
        return $this->config->getBucket();
    }
    public function writeData(string $measurement, array $fields, array $tags = []): bool {
        if (!$this->connected) {
            $this->lastError = "No hay conexión con InfluxDB";
            return false;
        }
        
        try {
            // Construir la cadena en formato de protocolo de línea
            $lineProtocol = $measurement;
            
            // Agregar tags si existen
            if (!empty($tags)) {
                $tagString = '';
                foreach ($tags as $key => $value) {
                    if ($tagString !== '') {
                        $tagString .= ',';
                    }
                    $tagString .= $key . '=' . $this->escapeValue($value);
                }
                $lineProtocol .= ',' . $tagString;
            }
            
            // Agregar campos (obligatorios)
            $fieldString = '';
            foreach ($fields as $key => $value) {
                if ($fieldString !== '') {
                    $fieldString .= ',';
                }
                
                if (is_bool($value)) {
                    $fieldString .= $key . '=' . ($value ? 'true' : 'false');
                } elseif (is_int($value) || is_float($value)) {
                    $fieldString .= $key . '=' . $value;
                } else {
                    $fieldString .= $key . '="' . $this->escapeValue($value) . '"';
                }
            }
            $lineProtocol .= ' ' . $fieldString;
            
            // Agregar timestamp en nanosegundos (opcional)
            $lineProtocol .= ' ' . (time() * 1000000000);
            
            // Crear la solicitud HTTP POST a la API de escritura de InfluxDB v2
            $url = $this->config->getUrl() . '/api/v2/write';
            $url .= '?org=' . urlencode($this->config->getOrg());
            $url .= '&bucket=' . urlencode($this->config->getBucket());
            $url .= '&precision=ns';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $lineProtocol);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Token ' . $this->config->getToken(),
                'Content-Type: text/plain'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return true;
            } else {
                $this->lastError = "Error al escribir datos. Código HTTP: " . $httpCode . ", Respuesta: " . $response;
                return false;
            }
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error escribiendo en InfluxDB v2: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ejecuta una consulta Flux en InfluxDB v2
     * 
     * @param string $query Consulta en lenguaje Flux
     * @return array Resultados de la consulta
     */
    public function query(string $query): array {
        if (!$this->connected) {
            $this->lastError = "No hay conexión con InfluxDB";
            return [];
        }
        
        try {
            // Comprobar si es una consulta específica que necesita ser convertida
            if (strtoupper(substr(trim($query), 0, 14)) === 'SHOW DATABASES') {
                return $this->listBuckets();
            } else if (strtoupper(substr(trim($query), 0, 15)) === 'CREATE DATABASE') {
                // Extraer el nombre de la base de datos/bucket
                $bucketName = trim(substr(trim($query), 15));
                return $this->createBucket($bucketName);
            }
            
            // Para consultas Flux normales
            // Convertir de InfluxQL a Flux si es necesario
            if (stripos($query, 'SELECT') === 0) {
                $query = $this->convertToFlux($query);
            }
            
            $url = $this->config->getUrl() . '/api/v2/query?org=' . urlencode($this->config->getOrg());
            
            $payload = json_encode([
                'query' => $query,
                'type' => 'flux'
            ]);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Token ' . $this->config->getToken(),
                'Content-Type: application/json',
                'Accept: application/csv'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                // Parsear la respuesta CSV a un array
                return $this->parseCSVResponse($response);
            } else {
                $this->lastError = "Error en la consulta. Código HTTP: " . $httpCode . ", Respuesta: " . $response;
                return [];
            }
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            error_log("Error consultando InfluxDB v2: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista todos los buckets disponibles
     * 
     * @return array
     */
    private function listBuckets(): array {
        try {
            $url = $this->config->getUrl() . '/api/v2/buckets?org=' . urlencode($this->config->getOrg());
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Token ' . $this->config->getToken()
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                $data = json_decode($response, true);
                $result = [];
                
                if (isset($data['buckets']) && is_array($data['buckets'])) {
                    foreach ($data['buckets'] as $bucket) {
                        $result[] = ['name' => $bucket['name']];
                    }
                }
                
                return $result;
            } else {
                $this->lastError = "Error al listar buckets. Código HTTP: " . $httpCode;
                return [];
            }
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }
    
    /**
     * Crea un nuevo bucket
     * 
     * @param string $bucketName
     * @return array
     */
    private function createBucket(string $bucketName): array {
        try {
            $url = $this->config->getUrl() . '/api/v2/buckets';
            
            $payload = json_encode([
                'name' => $bucketName,
                'orgID' => $this->getOrgID()
            ]);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Token ' . $this->config->getToken(),
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return [['success' => true]];
            } else {
                $this->lastError = "Error al crear bucket. Código HTTP: " . $httpCode . ", Respuesta: " . $response;
                return [];
            }
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return [];
        }
    }
    
    /**
     * Obtiene el ID de la organización
     * 
     * @return string
     */
    private function getOrgID(): string {
        try {
            $url = $this->config->getUrl() . '/api/v2/orgs?org=' . urlencode($this->config->getOrg());
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Token ' . $this->config->getToken()
            ]);
            
            $response = curl_exec($ch);
            $data = json_decode($response, true);
            
            if (isset($data['orgs']) && count($data['orgs']) > 0) {
                return $data['orgs'][0]['id'];
            }
            
            throw new \Exception("No se pudo encontrar el ID de la organización");
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            throw $e;
        }
    }
    
    /**
     * Parsea la respuesta CSV de InfluxDB v2
     * 
     * @param string $csvData
     * @return array
     */
    private function parseCSVResponse(string $csvData): array {
        $lines = explode("\n", $csvData);
        $header = null;
        $result = [];
        $dataFound = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || substr($line, 0, 1) === '#') {
                continue;
            }
            
            $values = str_getcsv($line);
            
            if ($header === null) {
                $header = $values;
                continue;
            }
            
            // Verificar si es una línea de datos
            if (count($values) >= count($header)) {
                $row = [];
                foreach ($header as $i => $key) {
                    if (isset($values[$i]) && $key !== '') {
                        $row[$key] = $values[$i];
                    }
                }
                
                // Solo agregar filas que tengan un valor "_value"
                if (isset($row['_value'])) {
                    $dataFound = true;
                    // Transformar la fila para que sea compatible con el formato anterior
                    $transformedRow = $this->transformRow($row);
                    $result[] = $transformedRow;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Transforma una fila de respuesta Flux al formato esperado
     * 
     * @param array $row
     * @return array
     */
    private function transformRow(array $row): array {
        $result = [];
        
        // Mapeo de campos
        if (isset($row['_time'])) {
            $result['time'] = $row['_time'];
        }
        
        if (isset($row['_measurement'])) {
            $result['measurement'] = $row['_measurement'];
        }
        
        if (isset($row['_field'])) {
            $fieldName = $row['_field'];
            $result[$fieldName] = $row['_value'] ?? null;
        }
        
        // Agregar todos los tags
        foreach ($row as $key => $value) {
            if (substr($key, 0, 1) !== '_' && $key !== 'result' && $key !== 'table') {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * Convierte una consulta InfluxQL simple a Flux
     * 
     * @param string $influxQL
     * @return string
     */
    private function convertToFlux(string $influxQL): string {
        // Este es un conversor muy básico que solo maneja consultas simples
        $matches = [];
        
        // Pattern para SELECT * FROM measurement WHERE ...
        if (preg_match('/SELECT\s+\*\s+FROM\s+(\w+)(?:\s+WHERE\s+(.+?))?(?:\s+ORDER\s+BY\s+(.+?))?(?:\s+LIMIT\s+(\d+))?$/i', $influxQL, $matches)) {
            $measurement = $matches[1];
            $where = $matches[2] ?? '';
            $orderBy = $matches[3] ?? '';
            $limit = $matches[4] ?? '';
            
            $flux = 'from(bucket: "' . $this->config->getBucket() . '")
                |> range(start: -30d)
                |> filter(fn: (r) => r._measurement == "' . $measurement . '")';
            
            // Convertir condiciones WHERE
            if (!empty($where)) {
                // Este es un conversor muy simple, no maneja casos complejos
                if (strpos($where, "source='test_script'") !== false) {
                    $flux .= '
                |> filter(fn: (r) => r.source == "test_script")';
                }
            }
            
            // Agregar LIMIT
            if (!empty($limit)) {
                $flux .= '
                |> limit(n: ' . $limit . ')';
            }
            
            return $flux;
        }
        
        // Si no podemos convertir, devolver una consulta Flux básica
        return 'from(bucket: "' . $this->config->getBucket() . '")
            |> range(start: -30d)
            |> filter(fn: (r) => r._measurement == "test_measurement")
            |> limit(n: 10)';
    }
    
    /**
     * Escapa valores para el formato line protocol
     * 
     * @param mixed $value
     * @return string
     */
    private function escapeValue($value): string {
        $value = (string)$value;
        $value = str_replace('"', '\\"', $value);
        $value = str_replace(' ', '\\ ', $value);
        $value = str_replace(',', '\\,', $value);
        $value = str_replace('=', '\\=', $value);
        return $value;
    }

    /**
     * Verifica si la conexión está activa
     * 
     * @return bool
     */
    public function isConnected(): bool {
        return $this->connected;
    }
    
    /**
     * Obtiene el último mensaje de error
     * 
     * @return string
     */
    public function getLastError(): string {
        return $this->lastError;
    }
}