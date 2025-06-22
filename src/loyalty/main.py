"""
Sistema de Fidelización Inteligente - Café-VT
Backend FastAPI para el sistema de fidelización
"""

from fastapi import FastAPI, HTTPException, Depends, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from contextlib import asynccontextmanager
import uvicorn
import logging
from datetime import datetime

from config import settings
from routes import loyalty_routes
from services.loyalty_service import LoyaltyService
from utils.database import init_db, close_db

# Configurar logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Gestión del ciclo de vida de la aplicación"""
    # Startup
    logger.info("🚀 Iniciando Sistema de Fidelización Café-VT...")
    await init_db()
    logger.info("✅ Base de datos inicializada")
    
    yield
    
    # Shutdown
    logger.info("🛑 Cerrando Sistema de Fidelización...")
    await close_db()
    logger.info("✅ Base de datos cerrada")

# Crear aplicación FastAPI
app = FastAPI(
    title="Sistema de Fidelización Café-VT",
    description="API para gestión de fidelización inteligente de Café-VT",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
    lifespan=lifespan
)

# Configurar CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.ALLOWED_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Incluir rutas
app.include_router(loyalty_routes.router, prefix="/api/v1/loyalty", tags=["Loyalty"])

@app.get("/")
async def root():
    """Endpoint raíz"""
    return {
        "message": "Sistema de Fidelización Café-VT",
        "version": "1.0.0",
        "status": "active",
        "features": {
            "loyalty_management": "✅"
        },
        "timestamp": datetime.now().isoformat()
    }

@app.get("/health")
async def health_check():
    """Endpoint de verificación de salud"""
    return {
        "status": "healthy",
        "service": "loyalty-system",
        "components": {
            "database": "connected"
        },
        "timestamp": datetime.now().isoformat()
    }

@app.exception_handler(HTTPException)
async def http_exception_handler(request, exc):
    """Manejador de excepciones HTTP"""
    return JSONResponse(
        status_code=exc.status_code,
        content={
            "error": exc.detail,
            "timestamp": datetime.now().isoformat(),
            "path": str(request.url)
        }
    )

@app.exception_handler(Exception)
async def general_exception_handler(request, exc):
    """Manejador de excepciones generales"""
    logger.error(f"Error no manejado: {exc}")
    return JSONResponse(
        status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
        content={
            "error": "Error interno del servidor",
            "timestamp": datetime.now().isoformat(),
            "path": str(request.url)
        }
    )

if __name__ == "__main__":
    uvicorn.run(
        "main:app",
        host=settings.HOST,
        port=settings.PORT,
        reload=settings.DEBUG,
        log_level="info"
    ) 