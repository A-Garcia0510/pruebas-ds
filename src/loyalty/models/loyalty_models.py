"""
Modelos Pydantic básicos para el sistema de fidelización
Café-VT - FastAPI + PHP
"""

from datetime import datetime
from enum import Enum
from typing import Optional
from pydantic import BaseModel


class TierLevel(str, Enum):
    """Niveles de fidelización disponibles"""
    CAFE_BRONZE = "cafe_bronze"
    CAFE_PLATA = "cafe_plata"
    CAFE_ORO = "cafe_oro"
    CAFE_DIAMANTE = "cafe_diamante"


class TransactionType(str, Enum):
    """Tipos de transacciones de fidelización"""
    EARN = "earn"
    REDEEM = "redeem"
    EXPIRE = "expire"
    REFERRAL = "referral"
    BONUS = "bonus"
    ADJUSTMENT = "adjustment"


class LoyaltyUser(BaseModel):
    """Modelo básico para usuarios de fidelización"""
    user_id: int
    total_points: Optional[int] = 0
    current_tier: Optional[TierLevel] = TierLevel.CAFE_BRONZE
    score: Optional[float] = 0.0
    join_date: Optional[datetime] = None
    last_visit: Optional[datetime] = None
    total_visits: Optional[int] = 0
    total_spent: Optional[float] = 0.0
    favorite_products: Optional[str] = None
    referral_code: Optional[str] = None
    referred_by: Optional[str] = None
    points_expiry_date: Optional[datetime] = None
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None

    class Config:
        from_attributes = True


class LoyaltyUserCreate(BaseModel):
    """Modelo para crear un usuario de fidelización"""
    user_id: int


class LoyaltyUserUpdate(BaseModel):
    """Modelo para actualizar un usuario de fidelización"""
    total_points: Optional[int] = None
    current_tier: Optional[TierLevel] = None
    score: Optional[float] = None
    last_visit: Optional[datetime] = None
    total_visits: Optional[int] = None
    total_spent: Optional[float] = None


class LoyaltyUserOut(LoyaltyUser):
    """Modelo de salida para usuarios con información adicional"""
    nombre: Optional[str] = None
    apellidos: Optional[str] = None
    correo: Optional[str] = None


class Reward(BaseModel):
    """Modelo básico para recompensas"""
    id: int
    name: str
    description: Optional[str] = None
    points_cost: int
    discount_percent: float
    tier_required: TierLevel
    active: bool

    class Config:
        from_attributes = True


class Transaction(BaseModel):
    """Modelo básico para transacciones"""
    id: int
    transaction_type: TransactionType
    points_amount: int
    description: Optional[str] = None
    created_at: datetime
    balance_before: int
    balance_after: int

    class Config:
        from_attributes = True


# Exportar solo los modelos básicos
__all__ = [
    "LoyaltyUser", "LoyaltyUserCreate", "LoyaltyUserUpdate", "LoyaltyUserOut",
    "Reward", "Transaction", "TierLevel", "TransactionType"
] 