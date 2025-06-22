"""
Configuración de base de datos para el sistema de fidelización
"""

import aiomysql
import logging
from typing import Optional
from config import settings

logger = logging.getLogger(__name__)

# Variable global para la conexión
_pool: Optional[aiomysql.Pool] = None

async def init_db():
    """Inicializar la conexión a la base de datos"""
    global _pool
    
    try:
        _pool = await aiomysql.create_pool(
            host=settings.DB_HOST,
            port=settings.DB_PORT,
            user=settings.DB_USER,
            password=settings.DB_PASSWORD,
            db=settings.DB_NAME,
            charset='utf8mb4',
            autocommit=True,
            maxsize=20,
            minsize=5
        )
        logger.info("✅ Pool de conexiones a la base de datos creado")
        
        # Verificar conexión
        async with _pool.acquire() as conn:
            async with conn.cursor() as cursor:
                await cursor.execute("SELECT 1")
                result = await cursor.fetchone()
                if result:
                    logger.info("✅ Conexión a la base de datos verificada")
                    
    except Exception as e:
        logger.error(f"❌ Error al inicializar la base de datos: {e}")
        raise

async def close_db():
    """Cerrar la conexión a la base de datos"""
    global _pool
    
    if _pool:
        _pool.close()
        await _pool.wait_closed()
        logger.info("✅ Pool de conexiones cerrado")

async def get_db():
    """Obtener una conexión de la base de datos"""
    if not _pool:
        raise RuntimeError("Base de datos no inicializada")
    
    return _pool

async def execute_query(query: str, params: tuple = None):
    """Ejecutar una consulta SQL"""
    async with _pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cursor:
            await cursor.execute(query, params)
            return await cursor.fetchall()

async def execute_single_query(query: str, params: tuple = None):
    """Ejecutar una consulta SQL y obtener un solo resultado"""
    async with _pool.acquire() as conn:
        async with conn.cursor(aiomysql.DictCursor) as cursor:
            await cursor.execute(query, params)
            return await cursor.fetchone()

async def execute_insert(query: str, params: tuple = None):
    """Ejecutar una inserción SQL y obtener el ID insertado"""
    async with _pool.acquire() as conn:
        async with conn.cursor() as cursor:
            await cursor.execute(query, params)
            return cursor.lastrowid

async def execute_update(query: str, params: tuple = None):
    """Ejecutar una actualización SQL"""
    async with _pool.acquire() as conn:
        async with conn.cursor() as cursor:
            await cursor.execute(query, params)
            return cursor.rowcount

async def execute_delete(query: str, params: tuple = None):
    """Ejecutar una eliminación SQL"""
    async with _pool.acquire() as conn:
        async with conn.cursor() as cursor:
            await cursor.execute(query, params)
            return cursor.rowcount 