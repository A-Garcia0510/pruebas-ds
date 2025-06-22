"""
Configuración del Sistema de Fidelización
Café-VT - FastAPI + PHP
"""

from pydantic_settings import BaseSettings
from typing import Dict, List
from enum import Enum
from types import SimpleNamespace
import os


class TierLevel(str, Enum):
    """Niveles de fidelización disponibles con temática de café"""
    CAFE_BRONZE = "cafe_bronze"
    CAFE_PLATA = "cafe_plata"
    CAFE_ORO = "cafe_oro"
    CAFE_DIAMANTE = "cafe_diamante"


class LoyaltyConfig(BaseSettings):
    """Configuración del sistema de fidelización"""
    
    # Configuración de niveles con temática de café
    TIER_THRESHOLDS: Dict[str, int] = {
        "cafe_bronze": 0,
        "cafe_plata": 1000,
        "cafe_oro": 5000,
        "cafe_diamante": 15000
    }
    
    # Configuración de puntos
    POINTS_PER_PESO: float = 1.0  # Puntos por peso gastado
    POINTS_EXPIRY_DAYS: int = 365  # Días hasta que expiren los puntos
    MIN_POINTS_REDEMPTION: int = 100  # Mínimo puntos para canjear
    
    # Configuración de scoring
    SCORING_WEIGHTS: Dict[str, float] = {
        "frequency": 0.25,    # Frecuencia de visitas
        "amount": 0.30,       # Monto gastado
        "recency": 0.20,      # Recencia de visitas
        "variety": 0.15,      # Variedad de productos
        "referral": 0.10      # Referidos
    }
    
    # Configuración de referidos
    REFERRAL_BONUS_POINTS: int = 500  # Puntos por referido exitoso
    REFERRAL_CODE_LENGTH: int = 8     # Longitud del código de referido
    
    # Configuración de recompensas
    DEFAULT_REWARDS: List[Dict] = [
        {
            "name": "Café Americano Gratis",
            "description": "Café americano gratis para empezar el día",
            "points_cost": 500,
            "discount_percent": 100,
            "tier_required": "cafe_bronze",
            "max_uses_per_user": 1
        },
        {
            "name": "10% Descuento en tu Próximo Café",
            "description": "10% de descuento en tu próxima compra",
            "points_cost": 200,
            "discount_percent": 10,
            "tier_required": "cafe_bronze",
            "max_uses_per_user": 3
        },
        {
            "name": "Cappuccino Especial Gratis",
            "description": "Cappuccino con arte latte gratis",
            "points_cost": 800,
            "discount_percent": 100,
            "tier_required": "cafe_plata",
            "max_uses_per_user": 2
        },
        {
            "name": "20% Descuento en Menú Completo",
            "description": "20% de descuento en tu próxima compra",
            "points_cost": 1000,
            "discount_percent": 20,
            "tier_required": "cafe_oro",
            "max_uses_per_user": 2
        },
        {
            "name": "Experiencia Café-VT Completa",
            "description": "Café + postre + snack gratis",
            "points_cost": 2000,
            "discount_percent": 100,
            "tier_required": "cafe_diamante",
            "max_uses_per_user": 1
        }
    ]
    
    # Configuración de beneficios por nivel con temática de café
    TIER_BENEFITS: Dict[str, Dict] = {
        "cafe_bronze": {
            "name": "Café Bronze",
            "display_name": "☕ Café Bronze",
            "description": "Para los amantes del café que están comenzando su viaje",
            "points_multiplier": 1.0,
            "discount_percent": 0,
            "free_delivery": False,
            "priority_support": False,
            "special_offers": False
        },
        "cafe_plata": {
            "name": "Café Plata",
            "display_name": "🥈 Café Plata",
            "description": "Conocedores del café que aprecian la calidad",
            "points_multiplier": 1.2,
            "discount_percent": 5,
            "free_delivery": False,
            "priority_support": False,
            "special_offers": True
        },
        "cafe_oro": {
            "name": "Café Oro",
            "display_name": "🥇 Café Oro",
            "description": "Expertos cafeteros con acceso a beneficios premium",
            "points_multiplier": 1.5,
            "discount_percent": 10,
            "free_delivery": True,
            "priority_support": True,
            "special_offers": True
        },
        "cafe_diamante": {
            "name": "Café Diamante",
            "display_name": "💎 Café Diamante",
            "description": "Maestros del café con todos los privilegios exclusivos",
            "points_multiplier": 2.0,
            "discount_percent": 15,
            "free_delivery": True,
            "priority_support": True,
            "special_offers": True
        }
    }
    
    # Configuración de notificaciones
    ENABLE_EMAIL_NOTIFICATIONS: bool = True
    ENABLE_PUSH_NOTIFICATIONS: bool = False
    
    # Configuración de caché
    CACHE_TTL_SECONDS: int = 3600  # 1 hora
    
    class Config:
        env_prefix = "LOYALTY_"


# Instancia global de configuración
loyalty_config = LoyaltyConfig()

settings = SimpleNamespace(
    DB_HOST=os.getenv('LOYALTY_DB_HOST', 'localhost'),
    DB_PORT=int(os.getenv('LOYALTY_DB_PORT', 3306)),
    DB_USER=os.getenv('LOYALTY_DB_USER', 'root'),
    DB_PASSWORD=os.getenv('LOYALTY_DB_PASSWORD', ''),
    DB_NAME=os.getenv('LOYALTY_DB_NAME', 'ethos_bd'),
    DB_POOL_MIN_SIZE=int(os.getenv('LOYALTY_DB_POOL_MIN_SIZE', 1)),
    DB_POOL_MAX_SIZE=int(os.getenv('LOYALTY_DB_POOL_MAX_SIZE', 5)),
    DEBUG=os.getenv('LOYALTY_DEBUG', '1') == '1',
    SECRET_KEY=os.getenv('LOYALTY_SECRET_KEY', 'supersecret'),
    EMAIL_FROM=os.getenv('LOYALTY_EMAIL_FROM', 'noreply@cafe-vt.com'),
    EMAIL_SMTP=os.getenv('LOYALTY_EMAIL_SMTP', 'smtp.cafe-vt.com'),
    EMAIL_PORT=int(os.getenv('LOYALTY_EMAIL_PORT', 587)),
    EMAIL_USER=os.getenv('LOYALTY_EMAIL_USER', 'noreply@cafe-vt.com'),
    EMAIL_PASSWORD=os.getenv('LOYALTY_EMAIL_PASSWORD', 'password'),
    HOST=os.getenv('LOYALTY_HOST', '0.0.0.0'),
    PORT=int(os.getenv('LOYALTY_PORT', 8000)),
    ALLOWED_ORIGINS=[
        "http://localhost:3000",
        "http://localhost:8080", 
        "http://localhost",
        "http://127.0.0.1:3000",
        "http://127.0.0.1:8080",
        "http://127.0.0.1",
        "http://localhost/pruebas-ds",
        "http://127.0.0.1/pruebas-ds",
        "*"  # Permitir todos los orígenes en desarrollo
    ]
) 