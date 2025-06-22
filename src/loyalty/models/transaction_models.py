"""
Modelos Pydantic para transacciones del sistema de fidelización
Café-VT - FastAPI + PHP
"""

from pydantic import BaseModel, Field, validator
from typing import Optional, List
from datetime import datetime
from enum import Enum


class TransactionType(str, Enum):
    """Tipos de transacciones de fidelización"""
    EARN = "earn"
    REDEEM = "redeem"
    EXPIRE = "expire"
    BONUS = "bonus"
    REFERRAL = "referral"
    ADJUSTMENT = "adjustment"


class ReferralStatus(str, Enum):
    """Estados de referidos"""
    PENDING = "pending"
    COMPLETED = "completed"
    EXPIRED = "expired"


class ChangeReason(str, Enum):
    """Razones de cambio de nivel"""
    POINTS_THRESHOLD = "points_threshold"
    MANUAL_ADJUSTMENT = "manual_adjustment"
    SYSTEM_CORRECTION = "system_correction"


class LoyaltyTransactionBase(BaseModel):
    """Modelo base para transacciones de fidelización"""
    transaction_type: TransactionType = Field(..., description="Tipo de transacción")
    points_amount: int = Field(..., description="Cantidad de puntos (positivo para ganancia, negativo para pérdida)")
    order_ID: Optional[int] = Field(None, gt=0, description="ID de la compra relacionada")
    reward_ID: Optional[int] = Field(None, gt=0, description="ID de la recompensa canjeada")
    description: str = Field(..., min_length=1, description="Descripción de la transacción")


class LoyaltyTransactionCreate(LoyaltyTransactionBase):
    """Modelo para crear una nueva transacción"""
    loyalty_user_ID: int = Field(..., gt=0, description="ID del usuario de fidelización")
    balance_before: int = Field(..., ge=0, description="Balance antes de la transacción")
    balance_after: int = Field(..., ge=0, description="Balance después de la transacción")


class LoyaltyTransactionUpdate(BaseModel):
    """Modelo para actualizar una transacción existente"""
    description: Optional[str] = Field(None, min_length=1)
    order_ID: Optional[int] = Field(None, gt=0)
    reward_ID: Optional[int] = Field(None, gt=0)


class LoyaltyTransaction(LoyaltyTransactionBase):
    """Modelo completo de transacción con ID y timestamps"""
    transaction_ID: int = Field(..., description="ID único de la transacción")
    loyalty_user_ID: int = Field(..., description="ID del usuario de fidelización")
    balance_before: int = Field(..., description="Balance antes de la transacción")
    balance_after: int = Field(..., description="Balance después de la transacción")
    created_at: datetime = Field(..., description="Fecha de creación")
    
    class Config:
        from_attributes = True


class LoyaltyTransactionDetail(LoyaltyTransaction):
    """Modelo de transacción con información del usuario"""
    user_name: str = Field(..., description="Nombre del usuario")
    user_lastname: str = Field(..., description="Apellido del usuario")
    user_email: str = Field(..., description="Email del usuario")


class PointsEarnRequest(BaseModel):
    """Modelo para solicitar ganancia de puntos"""
    user_id: int = Field(..., gt=0, description="ID del usuario")
    order_id: int = Field(..., gt=0, description="ID de la compra")
    order_amount: float = Field(..., gt=0, description="Monto de la compra")
    description: Optional[str] = Field(None, description="Descripción opcional")


class PointsEarnResponse(BaseModel):
    """Modelo de respuesta para ganancia de puntos"""
    success: bool = Field(..., description="Si la operación fue exitosa")
    message: str = Field(..., description="Mensaje descriptivo")
    points_earned: int = Field(..., description="Puntos ganados")
    points_before: int = Field(..., description="Puntos antes de la operación")
    points_after: int = Field(..., description="Puntos después de la operación")
    tier_multiplier: float = Field(..., description="Multiplicador del nivel aplicado")
    transaction_id: int = Field(..., description="ID de la transacción creada")


class PointsRedeemRequest(BaseModel):
    """Modelo para solicitar canje de puntos"""
    user_id: int = Field(..., gt=0, description="ID del usuario")
    reward_id: int = Field(..., gt=0, description="ID de la recompensa")
    description: Optional[str] = Field(None, description="Descripción opcional")


class PointsRedeemResponse(BaseModel):
    """Modelo de respuesta para canje de puntos"""
    success: bool = Field(..., description="Si la operación fue exitosa")
    message: str = Field(..., description="Mensaje descriptivo")
    points_redeemed: int = Field(..., description="Puntos canjeados")
    points_before: int = Field(..., description="Puntos antes de la operación")
    points_after: int = Field(..., description="Puntos después de la operación")
    coupon_generated: bool = Field(..., description="Si se generó un cupón")
    coupon_code: Optional[str] = Field(None, description="Código del cupón generado")
    transaction_id: int = Field(..., description="ID de la transacción creada")


class PointsAdjustmentRequest(BaseModel):
    """Modelo para solicitar ajuste manual de puntos"""
    user_id: int = Field(..., gt=0, description="ID del usuario")
    points_amount: int = Field(..., description="Cantidad de puntos a ajustar (positivo o negativo)")
    reason: str = Field(..., min_length=1, description="Razón del ajuste")
    admin_id: int = Field(..., gt=0, description="ID del administrador que realiza el ajuste")


class PointsAdjustmentResponse(BaseModel):
    """Modelo de respuesta para ajuste de puntos"""
    success: bool = Field(..., description="Si la operación fue exitosa")
    message: str = Field(..., description="Mensaje descriptivo")
    points_adjusted: int = Field(..., description="Puntos ajustados")
    points_before: int = Field(..., description="Puntos antes del ajuste")
    points_after: int = Field(..., description="Puntos después del ajuste")
    transaction_id: int = Field(..., description="ID de la transacción creada")


class UserTransactionsResponse(BaseModel):
    """Modelo de respuesta para transacciones del usuario"""
    transactions: List[LoyaltyTransaction] = Field(..., description="Lista de transacciones")
    total_transactions: int = Field(..., description="Total de transacciones")
    total_points_earned: int = Field(..., description="Total de puntos ganados")
    total_points_redeemed: int = Field(..., description="Total de puntos canjeados")
    current_balance: int = Field(..., description="Balance actual de puntos")


class TransactionStats(BaseModel):
    """Modelo para estadísticas de transacciones"""
    transaction_type: TransactionType = Field(..., description="Tipo de transacción")
    total_count: int = Field(..., description="Total de transacciones de este tipo")
    total_points: int = Field(..., description="Total de puntos de este tipo")
    average_points: float = Field(..., description="Promedio de puntos por transacción")
    percentage_of_total: float = Field(..., description="Porcentaje del total de transacciones")


class TransactionAnalyticsResponse(BaseModel):
    """Modelo de respuesta para análisis de transacciones"""
    stats_by_type: List[TransactionStats] = Field(..., description="Estadísticas por tipo de transacción")
    total_transactions: int = Field(..., description="Total de transacciones")
    total_points_earned: int = Field(..., description="Total de puntos ganados")
    total_points_redeemed: int = Field(..., description="Total de puntos canjeados")
    net_points_change: int = Field(..., description="Cambio neto de puntos")
    average_transactions_per_user: float = Field(..., description="Promedio de transacciones por usuario")
    most_active_users: List[dict] = Field(..., description="Usuarios más activos")


class LoyaltyReferralBase(BaseModel):
    """Modelo base para referidos"""
    referral_code: str = Field(..., min_length=1, max_length=20, description="Código de referido usado")
    status: ReferralStatus = Field(ReferralStatus.PENDING, description="Estado del referido")


class LoyaltyReferralCreate(LoyaltyReferralBase):
    """Modelo para crear un nuevo referido"""
    referrer_user_ID: int = Field(..., gt=0, description="ID del usuario que hace la referencia")
    referred_user_ID: int = Field(..., gt=0, description="ID del usuario referido")


class LoyaltyReferralUpdate(BaseModel):
    """Modelo para actualizar un referido existente"""
    status: Optional[ReferralStatus] = None
    bonus_points_given: Optional[bool] = None
    first_purchase_date: Optional[datetime] = None
    completed_at: Optional[datetime] = None


class LoyaltyReferral(LoyaltyReferralBase):
    """Modelo completo de referido con ID y timestamps"""
    referral_ID: int = Field(..., description="ID único del referido")
    referrer_user_ID: int = Field(..., description="ID del usuario que hace la referencia")
    referred_user_ID: int = Field(..., description="ID del usuario referido")
    bonus_points_given: bool = Field(False, description="Si ya se dieron los puntos bonus")
    first_purchase_date: Optional[datetime] = Field(None, description="Fecha de primera compra del referido")
    created_at: datetime = Field(..., description="Fecha de creación")
    completed_at: Optional[datetime] = Field(None, description="Fecha cuando se completó la referencia")
    
    class Config:
        from_attributes = True


class ReferralStats(BaseModel):
    """Modelo para estadísticas de referidos"""
    total_referrals: int = Field(..., description="Total de referidos")
    completed_referrals: int = Field(..., description="Referidos completados")
    pending_referrals: int = Field(..., description="Referidos pendientes")
    expired_referrals: int = Field(..., description="Referidos expirados")
    conversion_rate: float = Field(..., description="Tasa de conversión")
    total_bonus_points_given: int = Field(..., description="Total de puntos bonus otorgados")
    average_time_to_completion: Optional[float] = Field(None, description="Tiempo promedio hasta completar referido")


class ReferralAnalyticsResponse(BaseModel):
    """Modelo de respuesta para análisis de referidos"""
    stats: ReferralStats = Field(..., description="Estadísticas generales de referidos")
    top_referrers: List[dict] = Field(..., description="Usuarios con más referidos exitosos")
    recent_referrals: List[LoyaltyReferral] = Field(..., description="Referidos recientes")
    conversion_trends: List[dict] = Field(..., description="Tendencias de conversión")


class LoyaltyTierHistoryBase(BaseModel):
    """Modelo base para historial de cambios de nivel"""
    old_tier: str = Field(..., description="Nivel anterior")
    new_tier: str = Field(..., description="Nuevo nivel")
    points_at_change: int = Field(..., ge=0, description="Puntos al momento del cambio")
    score_at_change: float = Field(..., ge=0, description="Score al momento del cambio")
    change_reason: ChangeReason = Field(ChangeReason.POINTS_THRESHOLD, description="Razón del cambio")


class LoyaltyTierHistoryCreate(LoyaltyTierHistoryBase):
    """Modelo para crear un nuevo registro de cambio de nivel"""
    loyalty_user_ID: int = Field(..., gt=0, description="ID del usuario de fidelización")


class LoyaltyTierHistory(LoyaltyTierHistoryBase):
    """Modelo completo de historial de cambios de nivel"""
    history_ID: int = Field(..., description="ID único del registro")
    loyalty_user_ID: int = Field(..., description="ID del usuario de fidelización")
    created_at: datetime = Field(..., description="Fecha del cambio")
    
    class Config:
        from_attributes = True


# Validadores personalizados
@validator('points_amount')
def validate_points_amount(cls, v):
    """Validar que la cantidad de puntos no sea cero"""
    if v == 0:
        raise ValueError("La cantidad de puntos no puede ser cero")
    return v


@validator('balance_after')
def validate_balance_after(cls, v, values):
    """Validar que el balance después sea consistente"""
    balance_before = values.get('balance_before', 0)
    points_amount = values.get('points_amount', 0)
    expected_balance = balance_before + points_amount
    
    if v != expected_balance:
        raise ValueError(f"El balance después debe ser {expected_balance}, no {v}")
    return v


@validator('referred_user_ID')
def validate_different_users(cls, v, values):
    """Validar que el referido y referidor sean usuarios diferentes"""
    referrer_user_ID = values.get('referrer_user_ID')
    if referrer_user_ID and v == referrer_user_ID:
        raise ValueError("El usuario referido no puede ser el mismo que hace la referencia")
    return v


@validator('new_tier')
def validate_tier_change(cls, v, values):
    """Validar que el nuevo nivel sea diferente al anterior"""
    old_tier = values.get('old_tier')
    if old_tier and v == old_tier:
        raise ValueError("El nuevo nivel debe ser diferente al anterior")
    return v


# Modelos Pydantic para transacciones
from pydantic import BaseModel, Field
from typing import Optional
from datetime import datetime

class TransactionCreate(BaseModel):
    loyalty_user_ID: int = Field(..., description="ID del usuario de fidelización")
    transaction_type: str = Field(..., description="Tipo de transacción")
    points_amount: int = Field(..., description="Cantidad de puntos")
    order_ID: Optional[int] = None
    reward_ID: Optional[int] = None
    description: str = Field(...)
    balance_before: int = Field(...)
    balance_after: int = Field(...)

class Transaction(TransactionCreate):
    transaction_ID: int = Field(...)
    created_at: datetime = Field(...)

# Exportar para importación directa
__all__ = [
    "TransactionType", "ReferralStatus", "ChangeReason",
    "TransactionCreate", "Transaction"
] 