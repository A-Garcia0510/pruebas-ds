#!/usr/bin/env python3
"""
Script de Backup Automatizado - Sistema de Fidelización
"""

import os
import sys
import subprocess
import logging
import datetime
from pathlib import Path
import gzip
import shutil

# Configurar logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

class LoyaltyBackup:
    def __init__(self):
        self.project_root = Path(__file__).parent.parent
        self.backup_dir = Path("/backups/loyalty")  # Cambiar según configuración
        self.retention_days = 30
        
    def create_backup(self):
        """Crear backup completo de la base de datos"""
        logger.info("💾 Iniciando backup de base de datos...")
        
        # Crear directorio de backup si no existe
        self.backup_dir.mkdir(parents=True, exist_ok=True)
        
        # Generar nombre del archivo de backup
        timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
        backup_file = self.backup_dir / f"loyalty_backup_{timestamp}.sql"
        compressed_file = backup_file.with_suffix('.sql.gz')
        
        try:
            # Obtener configuración de base de datos desde variables de entorno
            db_host = os.getenv('DB_HOST', 'localhost')
            db_port = os.getenv('DB_PORT', '3306')
            db_name = os.getenv('DB_NAME', 'loyalty_system')
            db_user = os.getenv('DB_USER', 'loyalty_user')
            db_password = os.getenv('DB_PASSWORD', '')
            
            # Comando mysqldump
            cmd = [
                'mysqldump',
                f'--host={db_host}',
                f'--port={db_port}',
                f'--user={db_user}',
                '--single-transaction',
                '--routines',
                '--triggers',
                '--events',
                db_name
            ]
            
            if db_password:
                cmd.insert(1, f'--password={db_password}')
            
            logger.info(f"📦 Ejecutando mysqldump para {db_name}...")
            
            # Ejecutar backup
            with open(backup_file, 'w') as f:
                result = subprocess.run(cmd, stdout=f, stderr=subprocess.PIPE, text=True)
            
            if result.returncode == 0:
                logger.info(f"✅ Backup creado: {backup_file}")
                
                # Comprimir backup
                self._compress_backup(backup_file, compressed_file)
                
                # Limpiar archivo sin comprimir
                backup_file.unlink()
                
                # Limpiar backups antiguos
                self._cleanup_old_backups()
                
                logger.info(f"✅ Backup completado: {compressed_file}")
                return str(compressed_file)
            else:
                logger.error(f"❌ Error en backup: {result.stderr}")
                raise RuntimeError("Error ejecutando mysqldump")
                
        except Exception as e:
            logger.error(f"❌ Error creando backup: {e}")
            raise
    
    def _compress_backup(self, source_file, compressed_file):
        """Comprimir archivo de backup"""
        logger.info("🗜️  Comprimiendo backup...")
        
        try:
            with open(source_file, 'rb') as f_in:
                with gzip.open(compressed_file, 'wb') as f_out:
                    shutil.copyfileobj(f_in, f_out)
            
            logger.info(f"✅ Backup comprimido: {compressed_file}")
        except Exception as e:
            logger.error(f"❌ Error comprimiendo backup: {e}")
            raise
    
    def _cleanup_old_backups(self):
        """Limpiar backups antiguos según política de retención"""
        logger.info(f"🧹 Limpiando backups más antiguos de {self.retention_days} días...")
        
        try:
            cutoff_date = datetime.datetime.now() - datetime.timedelta(days=self.retention_days)
            
            for backup_file in self.backup_dir.glob("loyalty_backup_*.sql.gz"):
                file_time = datetime.datetime.fromtimestamp(backup_file.stat().st_mtime)
                
                if file_time < cutoff_date:
                    backup_file.unlink()
                    logger.info(f"🗑️  Eliminado backup antiguo: {backup_file}")
            
            logger.info("✅ Limpieza de backups completada")
        except Exception as e:
            logger.error(f"❌ Error limpiando backups: {e}")
    
    def restore_backup(self, backup_file):
        """Restaurar backup desde archivo"""
        logger.info(f"🔄 Restaurando backup: {backup_file}")
        
        try:
            # Obtener configuración de base de datos
            db_host = os.getenv('DB_HOST', 'localhost')
            db_port = os.getenv('DB_PORT', '3306')
            db_name = os.getenv('DB_NAME', 'loyalty_system')
            db_user = os.getenv('DB_USER', 'loyalty_user')
            db_password = os.getenv('DB_PASSWORD', '')
            
            # Comando mysql para restaurar
            cmd = [
                'mysql',
                f'--host={db_host}',
                f'--port={db_port}',
                f'--user={db_user}',
                db_name
            ]
            
            if db_password:
                cmd.insert(1, f'--password={db_password}')
            
            # Descomprimir si es necesario
            if backup_file.endswith('.gz'):
                import tempfile
                with tempfile.NamedTemporaryFile(mode='w', suffix='.sql', delete=False) as temp_file:
                    with gzip.open(backup_file, 'rt') as f_in:
                        temp_file.write(f_in.read())
                    temp_file_path = temp_file.name
                
                # Restaurar desde archivo temporal
                with open(temp_file_path, 'r') as f:
                    result = subprocess.run(cmd, stdin=f, stderr=subprocess.PIPE, text=True)
                
                # Limpiar archivo temporal
                os.unlink(temp_file_path)
            else:
                # Restaurar directamente
                with open(backup_file, 'r') as f:
                    result = subprocess.run(cmd, stdin=f, stderr=subprocess.PIPE, text=True)
            
            if result.returncode == 0:
                logger.info("✅ Backup restaurado correctamente")
            else:
                logger.error(f"❌ Error restaurando backup: {result.stderr}")
                raise RuntimeError("Error restaurando backup")
                
        except Exception as e:
            logger.error(f"❌ Error en restauración: {e}")
            raise

def main():
    """Función principal"""
    import argparse
    
    parser = argparse.ArgumentParser(description="Script de backup del sistema de fidelización")
    parser.add_argument("--action", choices=["backup", "restore"], default="backup",
                       help="Acción a realizar")
    parser.add_argument("--file", help="Archivo de backup para restaurar")
    parser.add_argument("--retention", type=int, default=30,
                       help="Días de retención para backups")
    
    args = parser.parse_args()
    
    backup_manager = LoyaltyBackup()
    backup_manager.retention_days = args.retention
    
    if args.action == "backup":
        backup_file = backup_manager.create_backup()
        print(f"Backup completado: {backup_file}")
    elif args.action == "restore":
        if not args.file:
            print("❌ Error: Debe especificar un archivo de backup para restaurar")
            sys.exit(1)
        backup_manager.restore_backup(args.file)
        print("✅ Restauración completada")

if __name__ == "__main__":
    main() 