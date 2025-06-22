"""
Modelos Pydantic para el sistema de fidelización
"""

# Importar modelos básicos de loyalty_models
from .loyalty_models import (
    LoyaltyUser, LoyaltyUserCreate, LoyaltyUserUpdate, LoyaltyUserOut,
    Reward, Transaction, TierLevel, TransactionType
)

# Importar modelos de recompensas (si existen)
try:
    from .reward_models import (
        LoyaltyReward, LoyaltyRewardCreate, LoyaltyRewardUpdate,
        LoyaltyCoupon, LoyaltyCouponCreate, LoyaltyCouponUpdate,
        RewardRedemptionRequest, RewardRedemptionResponse,
        RewardCatalogResponse, CouponValidationRequest, CouponValidationResponse
    )
except ImportError:
    pass

# Importar modelos de transacciones (si existen)
try:
    from .transaction_models import (
        LoyaltyTransaction, LoyaltyTransactionCreate, LoyaltyTransactionUpdate,
        PointsEarnRequest, PointsEarnResponse, PointsRedeemRequest, PointsRedeemResponse,
        UserTransactionsResponse, TransactionAnalyticsResponse
    )
except ImportError:
    pass

# Exportar todos los modelos disponibles
__all__ = [
    # Modelos básicos
    "LoyaltyUser", "LoyaltyUserCreate", "LoyaltyUserUpdate", "LoyaltyUserOut",
    "Reward", "Transaction", "TierLevel", "TransactionType",
] 