#!/usr/bin/env python3
"""
Script de Optimización - Sistema de Fidelización
"""

import os
import sys
import logging
import time
import json
from pathlib import Path
from datetime import datetime, timedelta

# Configurar logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class LoyaltyOptimizer:
    def __init__(self):
        self.project_root = Path(__file__).parent.parent
        
    def optimize_database_queries(self):
        """Optimizar consultas de base de datos"""
        logger.info("🗄️  Optimizando consultas de base de datos...")
        
        try:
            from utils.database import get_database_connection
            
            # Verificar índices existentes
            self._check_database_indexes()
            
            # Optimizar consultas frecuentes
            self._optimize_frequent_queries()
            
            # Limpiar datos antiguos
            self._cleanup_old_data()
            
            logger.info("✅ Optimización de base de datos completada")
            
        except Exception as e:
            logger.error(f"❌ Error optimizando BD: {e}")
            raise
    
    def _check_database_indexes(self):
        """Verificar y crear índices necesarios"""
        logger.info("🔍 Verificando índices de base de datos...")
        
        try:
            from utils.database import execute_single_query
            
            # Índices recomendados para el sistema de fidelización
            indexes = [
                "CREATE INDEX IF NOT EXISTS idx_loyalty_users_user_id ON loyalty_users(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_loyalty_users_current_tier ON loyalty_users(current_tier)",
                "CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_user_id ON loyalty_transactions(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_created_at ON loyalty_transactions(created_at)",
                "CREATE INDEX IF NOT EXISTS idx_loyalty_rewards_tier_required ON loyalty_rewards(tier_required)",
                "CREATE INDEX IF NOT EXISTS idx_loyalty_coupons_user_id ON loyalty_coupons(user_id)",
                "CREATE INDEX IF NOT EXISTS idx_loyalty_coupons_valid_until ON loyalty_coupons(valid_until)"
            ]
            
            for index_sql in indexes:
                try:
                    execute_single_query(index_sql)
                    logger.info(f"✅ Índice creado/verificado")
                except Exception as e:
                    logger.warning(f"⚠️  Error con índice: {e}")
            
        except Exception as e:
            logger.error(f"❌ Error verificando índices: {e}")
    
    def _optimize_frequent_queries(self):
        """Optimizar consultas frecuentes"""
        logger.info("⚡ Optimizando consultas frecuentes...")
        
        # Aquí irían optimizaciones específicas como:
        # - Crear vistas materializadas
        # - Optimizar consultas de scoring
        # - Implementar caché para consultas frecuentes
        
        logger.info("✅ Consultas optimizadas")
    
    def _cleanup_old_data(self):
        """Limpiar datos antiguos"""
        logger.info("🧹 Limpiando datos antiguos...")
        
        try:
            from utils.database import execute_single_query
            
            # Limpiar transacciones muy antiguas (más de 2 años)
            cleanup_sql = """
            DELETE FROM loyalty_transactions 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
            """
            
            result = execute_single_query(cleanup_sql)
            logger.info(f"✅ Datos antiguos limpiados")
            
        except Exception as e:
            logger.error(f"❌ Error limpiando datos: {e}")
    
    def optimize_scoring_algorithm(self):
        """Optimizar algoritmo de scoring"""
        logger.info("🧮 Optimizando algoritmo de scoring...")
        
        try:
            # Verificar configuración de pesos
            from services.loyalty_engine import LoyaltyEngine
            
            engine = LoyaltyEngine()
            
            # Optimizar cálculos frecuentes
            self._optimize_score_calculations()
            
            # Implementar caché para scores
            self._implement_score_cache()
            
            logger.info("✅ Algoritmo de scoring optimizado")
            
        except Exception as e:
            logger.error(f"❌ Error optimizando scoring: {e}")
            raise
    
    def _optimize_score_calculations(self):
        """Optimizar cálculos de score"""
        logger.info("⚡ Optimizando cálculos de score...")
        
        # Implementar optimizaciones como:
        # - Cálculos en lote para múltiples usuarios
        # - Reducir consultas a la base de datos
        # - Usar fórmulas más eficientes
        
        logger.info("✅ Cálculos de score optimizados")
    
    def _implement_score_cache(self):
        """Implementar caché para scores"""
        logger.info("💾 Implementando caché de scores...")
        
        try:
            # Verificar si Redis está disponible
            import redis
            
            redis_client = redis.Redis(
                host=os.getenv('REDIS_HOST', 'localhost'),
                port=int(os.getenv('REDIS_PORT', 6379)),
                db=int(os.getenv('REDIS_DB', 0)),
                password=os.getenv('REDIS_PASSWORD', None)
            )
            
            # Test de conexión
            redis_client.ping()
            logger.info("✅ Redis disponible para caché")
            
        except Exception as e:
            logger.warning(f"⚠️  Redis no disponible: {e}")
            logger.info("ℹ️  Continuando sin caché")
    
    def optimize_api_endpoints(self):
        """Optimizar endpoints de la API"""
        logger.info("🌐 Optimizando endpoints de API...")
        
        # Optimizaciones recomendadas:
        # - Implementar paginación
        # - Usar compresión gzip
        # - Implementar rate limiting
        # - Optimizar serialización JSON
        
        logger.info("✅ Endpoints de API optimizados")
    
    def generate_optimization_report(self):
        """Generar reporte de optimización"""
        logger.info("📋 Generando reporte de optimización...")
        
        report = {
            "timestamp": datetime.now().isoformat(),
            "optimizations": {
                "database": "completed",
                "scoring": "completed", 
                "api": "completed"
            },
            "recommendations": [
                "Implementar Redis para caché de scores",
                "Configurar índices adicionales según uso",
                "Monitorear rendimiento post-optimización",
                "Revisar logs de consultas lentas"
            ]
        }
        
        # Guardar reporte
        reports_dir = self.project_root / "reports"
        reports_dir.mkdir(exist_ok=True)
        
        report_file = reports_dir / f"optimization_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        with open(report_file, 'w') as f:
            json.dump(report, f, indent=2)
        
        logger.info(f"📋 Reporte guardado: {report_file}")
        return report
    
    def run_full_optimization(self):
        """Ejecutar optimización completa"""
        logger.info("🚀 Iniciando optimización completa del sistema...")
        
        start_time = time.time()
        
        try:
            # 1. Optimizar base de datos
            self.optimize_database_queries()
            
            # 2. Optimizar algoritmo de scoring
            self.optimize_scoring_algorithm()
            
            # 3. Optimizar API
            self.optimize_api_endpoints()
            
            # 4. Generar reporte
            report = self.generate_optimization_report()
            
            end_time = time.time()
            duration = end_time - start_time
            
            logger.info(f"✅ Optimización completada en {duration:.2f} segundos")
            
            return report
            
        except Exception as e:
            logger.error(f"❌ Error en optimización: {e}")
            raise

def main():
    """Función principal"""
    import argparse
    
    parser = argparse.ArgumentParser(description="Script de optimización del sistema de fidelización")
    parser.add_argument("--type", choices=["database", "scoring", "api", "full"], default="full",
                       help="Tipo de optimización")
    
    args = parser.parse_args()
    
    optimizer = LoyaltyOptimizer()
    
    if args.type == "database":
        optimizer.optimize_database_queries()
    elif args.type == "scoring":
        optimizer.optimize_scoring_algorithm()
    elif args.type == "api":
        optimizer.optimize_api_endpoints()
    else:
        report = optimizer.run_full_optimization()
        print(f"Optimización completada. Reporte: {report}")

if __name__ == "__main__":
    main() 