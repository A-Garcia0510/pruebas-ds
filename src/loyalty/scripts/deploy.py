#!/usr/bin/env python3
"""
Script de Despliegue Automatizado - Sistema de Fidelización
"""

import os
import sys
import subprocess
import logging
from pathlib import Path

# Configurar logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class LoyaltyDeployer:
    def __init__(self):
        self.project_root = Path(__file__).parent.parent
        self.scripts_dir = self.project_root / "scripts"
        
    def check_environment(self):
        """Verificar que el entorno esté listo para despliegue"""
        logger.info("🔍 Verificando entorno de despliegue...")
        
        # Verificar Python
        if sys.version_info < (3, 8):
            raise RuntimeError("Se requiere Python 3.8 o superior")
        
        # Verificar archivo .env
        env_file = self.project_root / ".env"
        if not env_file.exists():
            logger.warning("⚠️  Archivo .env no encontrado. Copiando desde env.example...")
            self._copy_env_file()
        
        # Verificar dependencias
        self._check_dependencies()
        
        logger.info("✅ Entorno verificado correctamente")
    
    def _copy_env_file(self):
        """Copiar archivo de ejemplo de variables de entorno"""
        example_file = self.project_root / "env.example"
        env_file = self.project_root / ".env"
        
        if example_file.exists():
            import shutil
            shutil.copy(example_file, env_file)
            logger.info("📋 Archivo .env creado desde env.example")
        else:
            logger.error("❌ No se encontró env.example")
    
    def _check_dependencies(self):
        """Verificar que todas las dependencias estén instaladas"""
        logger.info("📦 Verificando dependencias...")
        
        try:
            import fastapi
            import uvicorn
            import sqlalchemy
            import pydantic
            import redis
            logger.info("✅ Dependencias principales verificadas")
        except ImportError as e:
            logger.error(f"❌ Dependencia faltante: {e}")
            self._install_dependencies()
    
    def _install_dependencies(self):
        """Instalar dependencias faltantes"""
        logger.info("📦 Instalando dependencias...")
        
        requirements_file = self.project_root.parent.parent / "requirements.txt"
        if requirements_file.exists():
            subprocess.run([sys.executable, "-m", "pip", "install", "-r", str(requirements_file)], check=True)
            logger.info("✅ Dependencias instaladas")
        else:
            logger.error("❌ No se encontró requirements.txt")
    
    def run_migrations(self):
        """Ejecutar migraciones de base de datos"""
        logger.info("🗄️  Ejecutando migraciones de base de datos...")
        
        # Aquí irían las migraciones específicas del sistema de fidelización
        # Por ahora, solo verificamos la conexión
        try:
            from utils.database import get_database_connection
            conn = get_database_connection()
            logger.info("✅ Conexión a base de datos verificada")
        except Exception as e:
            logger.error(f"❌ Error de conexión a base de datos: {e}")
            raise
    
    def run_tests(self):
        """Ejecutar tests antes del despliegue"""
        logger.info("🧪 Ejecutando tests...")
        
        try:
            result = subprocess.run([
                sys.executable, "-m", "pytest", "tests/", "-v", "--tb=short"
            ], cwd=self.project_root, capture_output=True, text=True)
            
            if result.returncode == 0:
                logger.info("✅ Todos los tests pasaron")
            else:
                logger.error(f"❌ Tests fallaron:\n{result.stdout}\n{result.stderr}")
                raise RuntimeError("Tests fallaron")
        except Exception as e:
            logger.error(f"❌ Error ejecutando tests: {e}")
            raise
    
    def start_server(self, host="0.0.0.0", port=8000, workers=4):
        """Iniciar el servidor FastAPI"""
        logger.info(f"🚀 Iniciando servidor en {host}:{port} con {workers} workers...")
        
        try:
            subprocess.run([
                sys.executable, "-m", "uvicorn", 
                "main:app", 
                "--host", host, 
                "--port", str(port),
                "--workers", str(workers),
                "--log-level", "info"
            ], cwd=self.project_root)
        except KeyboardInterrupt:
            logger.info("🛑 Servidor detenido por el usuario")
        except Exception as e:
            logger.error(f"❌ Error iniciando servidor: {e}")
            raise
    
    def deploy(self, environment="production"):
        """Proceso completo de despliegue"""
        logger.info(f"🚀 Iniciando despliegue en entorno: {environment}")
        
        try:
            # 1. Verificar entorno
            self.check_environment()
            
            # 2. Ejecutar tests
            self.run_tests()
            
            # 3. Ejecutar migraciones
            self.run_migrations()
            
            # 4. Iniciar servidor
            if environment == "production":
                self.start_server(host="0.0.0.0", port=8000, workers=4)
            else:
                self.start_server(host="127.0.0.1", port=8000, workers=1)
                
        except Exception as e:
            logger.error(f"❌ Error en despliegue: {e}")
            sys.exit(1)

def main():
    """Función principal"""
    import argparse
    
    parser = argparse.ArgumentParser(description="Script de despliegue del sistema de fidelización")
    parser.add_argument("--env", choices=["development", "staging", "production"], 
                       default="production", help="Entorno de despliegue")
    parser.add_argument("--host", default="0.0.0.0", help="Host del servidor")
    parser.add_argument("--port", type=int, default=8000, help="Puerto del servidor")
    parser.add_argument("--workers", type=int, default=4, help="Número de workers")
    
    args = parser.parse_args()
    
    deployer = LoyaltyDeployer()
    
    if args.env == "production":
        logger.info("🚀 DESPLIEGUE EN PRODUCCIÓN")
        deployer.deploy("production")
    else:
        logger.info(f"🚀 DESPLIEGUE EN {args.env.upper()}")
        deployer.deploy(args.env)

if __name__ == "__main__":
    main() 