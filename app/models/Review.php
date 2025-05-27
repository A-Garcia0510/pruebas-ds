<?php
namespace App\Models;

use App\Core\Database\DatabaseInterface;
use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseConfiguration;

class Review {
    private $db;
    
    public function __construct() {
        $config = new DatabaseConfiguration(
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_USER'] ?? 'root',
            $_ENV['DB_PASS'] ?? '',
            $_ENV['DB_NAME'] ?? 'ethos_bd'
        );
        $this->db = new MySQLDatabase($config);
    }
    
    public function create($productoId, $usuarioId, $contenido, $calificacion) {
        $sql = "INSERT INTO product_reviews (producto_ID, usuario_ID, contenido, calificacion) 
                VALUES (?, ?, ?, ?)";
        return $this->db->query($sql, [$productoId, $usuarioId, $contenido, $calificacion]);
    }
    
    public function getByProduct($productoId, $estado = 'aprobada') {
        $sql = "SELECT pr.*, u.nombre, u.apellidos 
                FROM product_reviews pr 
                JOIN Usuario u ON pr.usuario_ID = u.usuario_ID 
                WHERE pr.producto_ID = ? AND pr.estado = ? 
                ORDER BY pr.fecha_creacion DESC";
        return $this->db->fetchAll($sql, [$productoId, $estado]);
    }
    
    public function getPendingReviews() {
        $sql = "SELECT pr.*, u.nombre, u.apellidos, p.nombre_producto 
                FROM product_reviews pr 
                JOIN Usuario u ON pr.usuario_ID = u.usuario_ID 
                JOIN Producto p ON pr.producto_ID = p.producto_ID 
                WHERE pr.estado = 'pendiente' 
                ORDER BY pr.fecha_creacion DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getReportedReviews() {
        $sql = "SELECT pr.*, u.nombre, u.apellidos, p.nombre_producto,
                COUNT(rr.reporte_ID) as reportes_count 
                FROM product_reviews pr 
                JOIN Usuario u ON pr.usuario_ID = u.usuario_ID 
                JOIN Producto p ON pr.producto_ID = p.producto_ID 
                LEFT JOIN review_reports rr ON pr.review_ID = rr.review_ID 
                WHERE rr.estado = 'pendiente' 
                GROUP BY pr.review_ID 
                ORDER BY reportes_count DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function updateStatus($reviewId, $estado, $moderadorId, $comentario = null) {
        $this->db->beginTransaction();
        try {
            // Actualizar estado de la reseña
            $sql = "UPDATE product_reviews SET estado = ? WHERE review_ID = ?";
            $this->db->query($sql, [$estado, $reviewId]);
            
            // Si la reseña es aprobada, eliminar los reportes asociados
            if ($estado === 'aprobada') {
                $sql = "DELETE FROM review_reports WHERE review_ID = ?";
                $this->db->query($sql, [$reviewId]);
            }
            
            // Registrar en el log de moderación
            $sql = "INSERT INTO review_moderation_log (review_ID, moderador_ID, accion, comentario) 
                    VALUES (?, ?, ?, ?)";
            $this->db->query($sql, [$reviewId, $moderadorId, $estado, $comentario]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function report($reviewId, $usuarioId, $razon) {
        $sql = "INSERT INTO review_reports (review_ID, usuario_ID, razon) 
                VALUES (?, ?, ?)";
        return $this->db->query($sql, [$reviewId, $usuarioId, $razon]);
    }
    
    public function getAverageRating($productoId) {
        $sql = "SELECT AVG(calificacion) as promedio 
                FROM product_reviews 
                WHERE producto_ID = ? AND estado = 'aprobada'";
        $result = $this->db->fetchOne($sql, [$productoId]);
        return round($result['promedio'], 1);
    }

    /**
     * Busca una reseña por su ID.
     * 
     * @param int $reviewId ID de la reseña
     * @return array|null Retorna la reseña o null si no se encuentra
     */
    public function findReviewById($reviewId) {
        $sql = "SELECT pr.*, u.nombre, u.apellidos 
                FROM product_reviews pr 
                JOIN Usuario u ON pr.usuario_ID = u.usuario_ID 
                WHERE pr.review_ID = ?";
        return $this->db->fetchOne($sql, [$reviewId]);
    }

    /**
     * Elimina una reseña por su ID.
     * 
     * @param int $reviewId ID de la reseña
     * @return bool Retorna true si se eliminó, false en caso contrario
     */
    public function deleteReview($reviewId) {
        $this->db->beginTransaction();
        try {
            // Primero eliminamos los registros de moderación
            $sql = "DELETE FROM review_moderation_log WHERE review_ID = ?";
            $this->db->query($sql, [$reviewId]);
            
            // Luego eliminamos los reportes asociados
            $sql = "DELETE FROM review_reports WHERE review_ID = ?";
            $this->db->query($sql, [$reviewId]);
            
            // Finalmente eliminamos la reseña
            $sql = "DELETE FROM product_reviews WHERE review_ID = ?";
            $result = $this->db->query($sql, [$reviewId]);
            
            if ($result) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error al eliminar reseña: " . $e->getMessage());
            throw $e;
        }
    }
} 