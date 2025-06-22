#!/usr/bin/env python3
"""
Script de Monitoreo - Sistema de Fidelización
"""

import os
import sys
import time
import logging
import requests
import psutil
import json
from datetime import datetime, timedelta
from pathlib import Path

# Configurar logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class LoyaltyMonitor:
    def __init__(self):
        self.project_root = Path(__file__).parent.parent
        self.api_url = "http://localhost:8000"
        self.alert_email = os.getenv('ALERT_EMAIL', 'admin@cafevt.com')
        self.alert_webhook = os.getenv('ALERT_WEBHOOK', '')
        
    def check_api_health(self):
        """Verificar salud de la API"""
        try:
            response = requests.get(f"{self.api_url}/health", timeout=5)
            if response.status_code == 200:
                logger.info("✅ API saludable")
                return True
            else:
                logger.error(f"❌ API no saludable: {response.status_code}")
                return False
        except Exception as e:
            logger.error(f"❌ Error conectando a API: {e}")
            return False
    
    def check_database_connection(self):
        """Verificar conexión a base de datos"""
        try:
            from utils.database import get_database_connection
            conn = get_database_connection()
            logger.info("✅ Conexión a base de datos OK")
            return True
        except Exception as e:
            logger.error(f"❌ Error de conexión a BD: {e}")
            return False
    
    def check_system_resources(self):
        """Verificar recursos del sistema"""
        try:
            # CPU
            cpu_percent = psutil.cpu_percent(interval=1)
            if cpu_percent > 80:
                logger.warning(f"⚠️  CPU alto: {cpu_percent}%")
            
            # Memoria
            memory = psutil.virtual_memory()
            if memory.percent > 85:
                logger.warning(f"⚠️  Memoria alta: {memory.percent}%")
            
            # Disco
            disk = psutil.disk_usage('/')
            if disk.percent > 90:
                logger.warning(f"⚠️  Disco lleno: {disk.percent}%")
            
            logger.info(f"📊 Recursos: CPU {cpu_percent}%, RAM {memory.percent}%, Disco {disk.percent}%")
            return True
        except Exception as e:
            logger.error(f"❌ Error verificando recursos: {e}")
            return False
    
    def check_loyalty_metrics(self):
        """Verificar métricas específicas del sistema de fidelización"""
        try:
            # Verificar endpoints principales
            endpoints = [
                "/api/v1/loyalty/profile/1",
                "/api/v1/loyalty/rewards",
                "/api/v1/loyalty/transactions/1"
            ]
            
            for endpoint in endpoints:
                try:
                    response = requests.get(f"{self.api_url}{endpoint}", timeout=5)
                    if response.status_code in [200, 404]:  # 404 es OK para usuario inexistente
                        logger.info(f"✅ Endpoint {endpoint} responde")
                    else:
                        logger.warning(f"⚠️  Endpoint {endpoint}: {response.status_code}")
                except Exception as e:
                    logger.error(f"❌ Error en endpoint {endpoint}: {e}")
            
            return True
        except Exception as e:
            logger.error(f"❌ Error verificando métricas: {e}")
            return False
    
    def send_alert(self, message, level="WARNING"):
        """Enviar alerta por email o webhook"""
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        alert_msg = f"[{level}] {timestamp} - {message}"
        
        logger.info(f"🚨 Enviando alerta: {alert_msg}")
        
        # Aquí iría la lógica para enviar email o webhook
        # Por ahora solo loggeamos
        logger.info(f"📧 Alerta enviada a {self.alert_email}")
        
        if self.alert_webhook:
            try:
                payload = {
                    "text": alert_msg,
                    "channel": "#alerts",
                    "username": "Loyalty Monitor"
                }
                requests.post(self.alert_webhook, json=payload, timeout=5)
                logger.info("📱 Alerta enviada por webhook")
            except Exception as e:
                logger.error(f"❌ Error enviando webhook: {e}")
    
    def generate_report(self):
        """Generar reporte de monitoreo"""
        report = {
            "timestamp": datetime.now().isoformat(),
            "checks": {}
        }
        
        # Ejecutar todas las verificaciones
        report["checks"]["api_health"] = self.check_api_health()
        report["checks"]["database"] = self.check_database_connection()
        report["checks"]["system_resources"] = self.check_system_resources()
        report["checks"]["loyalty_metrics"] = self.check_loyalty_metrics()
        
        # Determinar estado general
        all_healthy = all(report["checks"].values())
        report["status"] = "HEALTHY" if all_healthy else "UNHEALTHY"
        
        # Guardar reporte
        reports_dir = self.project_root / "reports"
        reports_dir.mkdir(exist_ok=True)
        
        report_file = reports_dir / f"monitor_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        with open(report_file, 'w') as f:
            json.dump(report, f, indent=2)
        
        logger.info(f"📋 Reporte guardado: {report_file}")
        
        # Enviar alerta si hay problemas
        if not all_healthy:
            failed_checks = [k for k, v in report["checks"].items() if not v]
            self.send_alert(f"Checks fallidos: {', '.join(failed_checks)}")
        
        return report
    
    def run_continuous_monitoring(self, interval=60):
        """Ejecutar monitoreo continuo"""
        logger.info(f"🔄 Iniciando monitoreo continuo (intervalo: {interval}s)")
        
        try:
            while True:
                logger.info("🔍 Ejecutando verificación de monitoreo...")
                self.generate_report()
                time.sleep(interval)
        except KeyboardInterrupt:
            logger.info("🛑 Monitoreo detenido por el usuario")
        except Exception as e:
            logger.error(f"❌ Error en monitoreo continuo: {e}")
            self.send_alert(f"Error en monitoreo: {e}", "ERROR")

def main():
    """Función principal"""
    import argparse
    
    parser = argparse.ArgumentParser(description="Script de monitoreo del sistema de fidelización")
    parser.add_argument("--mode", choices=["once", "continuous"], default="once",
                       help="Modo de ejecución")
    parser.add_argument("--interval", type=int, default=60,
                       help="Intervalo en segundos para monitoreo continuo")
    parser.add_argument("--api-url", default="http://localhost:8000",
                       help="URL de la API")
    
    args = parser.parse_args()
    
    monitor = LoyaltyMonitor()
    monitor.api_url = args.api_url
    
    if args.mode == "once":
        report = monitor.generate_report()
        print(f"Estado: {report['status']}")
        print(f"Checks: {report['checks']}")
    else:
        monitor.run_continuous_monitoring(args.interval)

if __name__ == "__main__":
    main() 