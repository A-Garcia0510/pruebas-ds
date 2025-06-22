"""
Modelos Pydantic para recompensas y cupones del sistema de fidelización
Café-VT - FastAPI + PHP
"""

from pydantic import BaseModel, Field, validator
from typing import Optional, List
from datetime import datetime
from enum import Enum


class TierLevel(str, Enum):
    """Niveles de fidelización disponibles"""
    CAFE_BRONZE = "cafe_bronze"
    CAFE_PLATA = "cafe_plata"
    CAFE_ORO = "cafe_oro"
    CAFE_DIAMANTE = "cafe_diamante"


class DiscountType(str, Enum):
    """Tipos de descuento para cupones"""
    PERCENTAGE = "percentage"
    FIXED_AMOUNT = "fixed_amount"
    FREE_PRODUCT = "free_product"


class LoyaltyRewardBase(BaseModel):
    """Modelo base para recompensas"""
    name: str = Field(..., min_length=1, max_length=100, description="Nombre de la recompensa")
    description: str = Field(..., min_length=1, description="Descripción detallada de la recompensa")
    points_cost: int = Field(..., gt=0, description="Costo en puntos de la recompensa")
    discount_percent: float = Field(0.0, ge=0, le=100, description="Porcentaje de descuento")
    discount_amount: float = Field(0.0, ge=0, description="Monto fijo de descuento")
    tier_required: TierLevel = Field(TierLevel.CAFE_BRONZE, description="Nivel requerido para la recompensa")
    max_uses_per_user: int = Field(1, gt=0, description="Máximo usos por usuario")
    max_total_uses: Optional[int] = Field(None, gt=0, description="Máximo usos totales (None = ilimitado)")
    active: bool = Field(True, description="Si la recompensa está activa")
    expiry_date: Optional[datetime] = Field(None, description="Fecha de expiración de la recompensa")


class LoyaltyRewardCreate(LoyaltyRewardBase):
    """Modelo para crear una nueva recompensa"""
    pass


class LoyaltyRewardUpdate(BaseModel):
    """Modelo para actualizar una recompensa existente"""
    name: Optional[str] = Field(None, min_length=1, max_length=100)
    description: Optional[str] = Field(None, min_length=1)
    points_cost: Optional[int] = Field(None, gt=0)
    discount_percent: Optional[float] = Field(None, ge=0, le=100)
    discount_amount: Optional[float] = Field(None, ge=0)
    tier_required: Optional[TierLevel] = None
    max_uses_per_user: Optional[int] = Field(None, gt=0)
    max_total_uses: Optional[int] = Field(None, gt=0)
    active: Optional[bool] = None
    expiry_date: Optional[datetime] = None


class LoyaltyReward(BaseModel):
    """Modelo Pydantic para recompensas, alineado con la BD"""
    id: int
    name: str
    description: Optional[str] = None
    points_cost: int
    discount_percent: float
    tier_required: TierLevel
    max_uses_per_user: Optional[int] = 1
    active: bool
    expiry_date: Optional[datetime] = None
    created_at: Optional[datetime] = None

    class Config:
        from_attributes = True


class LoyaltyCouponBase(BaseModel):
    """Modelo base para cupones"""
    code: str = Field(..., min_length=1, max_length=50, description="Código único del cupón")
    discount_type: DiscountType = Field(..., description="Tipo de descuento")
    discount_value: float = Field(..., gt=0, description="Valor del descuento")
    min_order_amount: float = Field(0.0, ge=0, description="Monto mínimo de orden")
    max_uses: int = Field(1, gt=0, description="Máximo número de usos")
    valid_from: datetime = Field(default_factory=datetime.now, description="Fecha desde cuando es válido")
    valid_until: datetime = Field(..., description="Fecha hasta cuando es válido")
    active: bool = Field(True, description="Si el cupón está activo")


class LoyaltyCouponCreate(LoyaltyCouponBase):
    """Modelo para crear un nuevo cupón"""
    loyalty_user_ID: int = Field(..., gt=0, description="ID del usuario de fidelización")
    reward_ID: Optional[int] = Field(None, gt=0, description="ID de la recompensa que generó este cupón")


class LoyaltyCouponUpdate(BaseModel):
    """Modelo para actualizar un cupón existente"""
    discount_value: Optional[float] = Field(None, gt=0)
    min_order_amount: Optional[float] = Field(None, ge=0)
    max_uses: Optional[int] = Field(None, gt=0)
    valid_until: Optional[datetime] = None
    active: Optional[bool] = None


class LoyaltyCoupon(LoyaltyCouponBase):
    """Modelo completo de cupón con ID y timestamps"""
    coupon_ID: int = Field(..., description="ID único del cupón")
    loyalty_user_ID: int = Field(..., description="ID del usuario de fidelización")
    reward_ID: Optional[int] = Field(None, description="ID de la recompensa que generó este cupón")
    used_count: int = Field(0, ge=0, description="Número de veces usado")
    used_at: Optional[datetime] = Field(None, description="Fecha de primer uso")
    created_at: datetime = Field(..., description="Fecha de creación")
    
    class Config:
        from_attributes = True


class RewardRedemptionRequest(BaseModel):
    """Modelo para solicitar el canje de una recompensa"""
    reward_ID: int = Field(..., gt=0, description="ID de la recompensa a canjear")
    user_id: int = Field(..., gt=0, description="ID del usuario que canjea")


class RewardRedemptionResponse(BaseModel):
    """Modelo de respuesta para el canje de recompensas"""
    success: bool = Field(..., description="Si el canje fue exitoso")
    message: str = Field(..., description="Mensaje descriptivo")
    coupon_code: Optional[str] = Field(None, description="Código del cupón generado")
    points_deducted: Optional[int] = Field(None, description="Puntos deducidos")
    remaining_points: Optional[int] = Field(None, description="Puntos restantes")
    discount_value: Optional[float] = Field(None, description="Valor del descuento")
    valid_until: Optional[datetime] = Field(None, description="Fecha de validez del cupón")


class RewardCatalogResponse(BaseModel):
    """Modelo de respuesta para el catálogo de recompensas"""
    rewards: List[LoyaltyReward] = Field(..., description="Lista de recompensas disponibles")
    user_tier: TierLevel = Field(..., description="Nivel actual del usuario")
    user_points: int = Field(..., description="Puntos disponibles del usuario")
    total_rewards: int = Field(..., description="Total de recompensas disponibles")


class CouponValidationRequest(BaseModel):
    """Modelo para validar un cupón"""
    coupon_code: str = Field(..., min_length=1, description="Código del cupón a validar")
    order_amount: float = Field(..., gt=0, description="Monto de la orden")


class CouponValidationResponse(BaseModel):
    """Modelo de respuesta para la validación de cupones"""
    valid: bool = Field(..., description="Si el cupón es válido")
    message: str = Field(..., description="Mensaje descriptivo")
    discount_value: Optional[float] = Field(None, description="Valor del descuento aplicable")
    discount_type: Optional[DiscountType] = Field(None, description="Tipo de descuento")
    min_order_amount: Optional[float] = Field(None, description="Monto mínimo requerido")
    valid_until: Optional[datetime] = Field(None, description="Fecha de validez")


class UserCouponsResponse(BaseModel):
    """Modelo de respuesta para los cupones del usuario"""
    active_coupons: List[LoyaltyCoupon] = Field(..., description="Cupones activos del usuario")
    expired_coupons: List[LoyaltyCoupon] = Field(..., description="Cupones expirados del usuario")
    total_active: int = Field(..., description="Total de cupones activos")
    total_expired: int = Field(..., description="Total de cupones expirados")


class RewardUsageStats(BaseModel):
    """Modelo para estadísticas de uso de recompensas"""
    reward_ID: int = Field(..., description="ID de la recompensa")
    reward_name: str = Field(..., description="Nombre de la recompensa")
    total_redemptions: int = Field(..., description="Total de canjes")
    total_points_spent: int = Field(..., description="Total de puntos gastados")
    average_redemptions_per_user: float = Field(..., description="Promedio de canjes por usuario")
    popularity_rank: int = Field(..., description="Ranking de popularidad")


class RewardAnalyticsResponse(BaseModel):
    """Modelo de respuesta para análisis de recompensas"""
    most_popular_rewards: List[RewardUsageStats] = Field(..., description="Recompensas más populares")
    least_popular_rewards: List[RewardUsageStats] = Field(..., description="Recompensas menos populares")
    total_rewards_created: int = Field(..., description="Total de recompensas creadas")
    total_redemptions: int = Field(..., description="Total de canjes realizados")
    average_points_per_redemption: float = Field(..., description="Promedio de puntos por canje")
    conversion_rate: float = Field(..., description="Tasa de conversión de recompensas")


# Validadores personalizados
@validator('valid_until')
def validate_valid_until(cls, v, values):
    """Validar que la fecha de validez sea posterior a la fecha actual"""
    if v and v <= datetime.now():
        raise ValueError("La fecha de validez debe ser posterior a la fecha actual")
    return v


@validator('discount_value')
def validate_discount_value(cls, v, values):
    """Validar el valor del descuento según el tipo"""
    discount_type = values.get('discount_type')
    if discount_type == DiscountType.PERCENTAGE and (v < 0 or v > 100):
        raise ValueError("El porcentaje de descuento debe estar entre 0 y 100")
    elif discount_type == DiscountType.FIXED_AMOUNT and v <= 0:
        raise ValueError("El monto de descuento debe ser mayor a 0")
    return v


@validator('code')
def validate_coupon_code(cls, v):
    """Validar formato del código de cupón"""
    if not v.replace('-', '').replace('_', '').isalnum():
        raise ValueError("El código del cupón debe contener solo letras, números, guiones y guiones bajos")
    return v.upper()


# Modelo Pydantic Reward para compatibilidad con tests y servicios
class Reward(LoyaltyRewardBase):
    id: int = Field(..., description="ID único de la recompensa")
    active: bool = Field(True)
    expiry_date: Optional[datetime] = None


# Exportar para importación directa
__all__ = [
    "TierLevel", "DiscountType", "LoyaltyRewardBase", "LoyaltyRewardUpdate", "Reward"
] 