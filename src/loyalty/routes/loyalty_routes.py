"""
Rutas para el sistema de fidelización
"""

from fastapi import APIRouter, HTTPException, Depends, status, Query
from typing import List, Optional, Dict, Any
from datetime import datetime, timedelta
from pydantic import BaseModel

from models.loyalty_models import LoyaltyUser, LoyaltyUserCreate, LoyaltyUserUpdate, LoyaltyUserOut
from models.reward_models import Reward
from models.transaction_models import Transaction
from services.loyalty_service import LoyaltyService
from services.reward_service import RewardService
from utils.database import get_db, execute_query, execute_single_query

router = APIRouter()

# Inicializar servicios
loyalty_service = LoyaltyService()

# =====================================================
# RUTAS ESENCIALES PARA EL CONTROLADOR PHP
# =====================================================

@router.get("/profile/{user_id}")
async def get_user_profile(user_id: int):
    """Obtener perfil de fidelización de un usuario (para PHP)"""
    try:
        user = await loyalty_service.get_user_by_id(user_id)
        if not user:
            # Crear perfil por defecto si no existe
            return {
                "success": True,
                "data": {
                    "current_points": 0,
                    "total_points": 0,
                    "current_tier": "cafe_bronze",
                    "progress_percentage": 0,
                    "next_tier": "cafe_plata",
                    "points_to_next_tier": 1000,
                    "current_benefits": [
                        "1 punto por cada $1 gastado",
                        "Descuento del 5% en cumpleaños",
                        "Acceso a recompensas básicas"
                    ],
                    "next_benefits": [
                        "1.2 puntos por cada $1 gastado",
                        "Descuento del 10% en cumpleaños",
                        "Recompensas exclusivas",
                        "Prioridad en pedidos"
                    ],
                    "join_date": datetime.now().strftime("%Y-%m-%d"),
                    "last_visit": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                    "total_visits": 0,
                    "total_spent": 0.0,
                    "rewards_redeemed": 0
                }
            }

        # Obtener umbrales de la base de datos
        tier_config_query = "SELECT tier_name, points_required FROM loyalty_tier_config ORDER BY points_required"
        tier_configs = await execute_query(tier_config_query)
        tier_thresholds = {row['tier_name']: row['points_required'] for row in tier_configs}
        
        # Orden de niveles
        tier_order = ["cafe_bronze", "cafe_plata", "cafe_oro", "cafe_diamante"]
        current_tier = user.current_tier
        current_points = user.total_points
        idx = tier_order.index(current_tier)
        next_tier = tier_order[min(idx+1, len(tier_order)-1)]
        current_threshold = tier_thresholds[current_tier]
        next_threshold = tier_thresholds[next_tier] if next_tier != current_tier else current_threshold
        points_needed = max(0, next_threshold - current_points) if next_tier != current_tier else 0
        if next_threshold == current_threshold:
            progress = 100
        else:
            progress = min(100, max(0, ((current_points - current_threshold) / (next_threshold - current_threshold)) * 100))

        # Definir beneficios por nivel (puedes mejorarlo si tienes tabla de beneficios)
        benefits = {
            "cafe_bronze": [
                "1 punto por cada $1 gastado",
                "Descuento del 5% en cumpleaños",
                "Acceso a recompensas básicas"
            ],
            "cafe_plata": [
                "1.2 puntos por cada $1 gastado",
                "Descuento del 10% en cumpleaños",
                "Recompensas exclusivas",
                "Prioridad en pedidos"
            ],
            "cafe_oro": [
                "1.5 puntos por cada $1 gastado",
                "Descuento del 15% en cumpleaños",
                "Envío gratis",
                "Soporte prioritario",
                "Ofertas exclusivas"
            ],
            "cafe_diamante": [
                "2 puntos por cada $1 gastado",
                "Descuento del 20% en cumpleaños",
                "Envío gratis",
                "Soporte VIP",
                "Ofertas premium",
                "Acceso anticipado a nuevos productos"
            ]
        }

        # Obtener nombre de usuario y estadísticas
        user_details = await loyalty_service.get_user_details(user_id)
        total_visits = user.total_visits
        total_spent = user.total_spent
        # Contar canjes
        redemptions_query = "SELECT COUNT(*) as count FROM loyalty_redemptions WHERE user_id = %s"
        redemptions_result = await execute_single_query(redemptions_query, (user_id,))
        rewards_redeemed = redemptions_result['count'] if redemptions_result else 0

        return {
            "success": True,
            "data": {
                "user_name": user_details.get("name", "Usuario"),
                "current_points": user.total_points,
                "total_points": user.total_points,
                "current_tier": user.current_tier,
                "progress_percentage": round(progress, 1),
                "next_tier": next_tier,
                "points_to_next_tier": points_needed,
                "current_benefits": benefits.get(user.current_tier, []),
                "next_benefits": benefits.get(next_tier, []),
                "join_date": user.join_date.strftime("%Y-%m-%d") if user.join_date else datetime.now().strftime("%Y-%m-%d"),
                "last_visit": user.last_visit.strftime("%Y-%m-%d %H:%M:%S") if user.last_visit else datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
                "total_visits": total_visits,
                "total_spent": float(total_spent),
                "rewards_redeemed": rewards_redeemed
            }
        }
    except Exception as e:
        import logging
        logging.error(f"Error en get_user_profile para user_id={user_id}: {e}", exc_info=True)
        return {
            "success": False,
            "message": f"Error obteniendo perfil: {str(e)}"
        }

@router.get("/referrals/{user_id}")
async def get_user_referrals(user_id: int):
    """Obtener datos de referidos de un usuario (para PHP)"""
    try:
        # Por ahora retornamos datos simulados
        return {
            "success": True,
            "data": {
                "my_code": f"REF{user_id:06d}",
                "referrals": [],
                "total_referrals": 0,
                "active_referrals": 0,
                "total_points_earned": 0,
                "conversion_rate": 0
            }
        }
    except Exception as e:
        return {
            "success": False,
            "message": f"Error obteniendo referidos: {str(e)}"
        }

@router.get("/rewards")
async def get_available_rewards():
    """Obtener recompensas disponibles (para PHP)"""
    try:
        print("🔍 DEBUG: Petición a /v1/loyalty/rewards recibida.")
        rewards = await loyalty_service.get_all_rewards()
        print(f"🔍 DEBUG: Recompensas obtenidas del servicio: {len(rewards)} items.")
        
        # Para ver qué datos se están obteniendo exactamente
        if rewards:
            print(f"🔍 DEBUG: Primera recompensa: {rewards[0].__dict__}")

        return {
            "success": True,
            "data": rewards
        }
    except Exception as e:
        # Usamos logger para un error más formal
        import logging
        logger = logging.getLogger(__name__)
        logger.error(f"❌ ERROR en /rewards: {e}", exc_info=True)
        return {
            "success": False,
            "message": f"Error obteniendo recompensas: {str(e)}"
        }

@router.get("/transactions/{user_id}")
async def get_user_transactions_api(user_id: int, page: int = 1):
    """Obtener transacciones de un usuario (para PHP)"""
    try:
        skip = (page - 1) * 10
        transactions = await loyalty_service.get_user_transactions(user_id, skip=skip, limit=10)
        
        return {
            "success": True,
            "data": transactions
        }
    except Exception as e:
        return {
            "success": False,
            "message": f"Error obteniendo transacciones: {str(e)}"
        }

class RedeemRewardRequest(BaseModel):
    user_id: int
    reward_id: int

@router.post("/redeem-reward")
async def redeem_reward_api(request: RedeemRewardRequest):
    """Canjear una recompensa (para PHP)"""
    try:
        result = await loyalty_service.redeem_reward(request.user_id, request.reward_id)
        return {
            "success": True,
            "data": result
        }
    except ValueError as e:
        return {"success": False, "message": str(e)}
    except Exception as e:
        import logging
        logger = logging.getLogger(__name__)
        logger.error(f"❌ ERROR en /redeem-reward: {e}", exc_info=True)
        return {
            "success": False,
            "message": f"Error inesperado canjeando recompensa: {str(e)}"
        }

class EarnPointsRequest(BaseModel):
    user_id: int
    points_amount: int
    transaction_type: str = "earn"
    description: str = ""

@router.post("/earn-points")
async def earn_points_api(request: EarnPointsRequest):
    """Otorgar puntos a un usuario (para PHP)"""
    try:
        print(f"🔍 DEBUG: Petición a /earn-points recibida - Usuario: {request.user_id}, Puntos: {request.points_amount}")
        
        # Verificar que el usuario existe
        user = await loyalty_service.get_user_by_id(request.user_id)
        if not user:
            # Crear usuario si no existe
            user = await loyalty_service.create_user(LoyaltyUserCreate(user_id=request.user_id))
            print(f"🔍 DEBUG: Usuario creado - ID: {request.user_id}")
        
        # Otorgar puntos
        result = await loyalty_service.earn_points(
            request.user_id, 
            request.points_amount, 
            None,  # order_id opcional
            request.description
        )
        
        print(f"🔍 DEBUG: Puntos otorgados exitosamente - Usuario: {request.user_id}, Puntos: {request.points_amount}")
        
        return {
            "success": True,
            "data": {
                "user_id": request.user_id,
                "points_earned": request.points_amount,
                "new_balance": result.get("new_balance", 0),
                "message": f"Se otorgaron {request.points_amount} puntos exitosamente"
            }
        }
    except Exception as e:
        import logging
        logger = logging.getLogger(__name__)
        logger.error(f"❌ ERROR en /earn-points: {e}", exc_info=True)
        return {
            "success": False,
            "message": f"Error otorgando puntos: {str(e)}"
        } 