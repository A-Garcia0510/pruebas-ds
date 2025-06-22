"""
Servicio principal para el sistema de fidelización
"""

import logging
from typing import List, Optional, Dict, Any
from datetime import datetime, timedelta
import json
import secrets
import string
import asyncio

from models.loyalty_models import LoyaltyUser, LoyaltyUserCreate, LoyaltyUserUpdate
from models.transaction_models import Transaction, TransactionCreate
from models.reward_models import Reward, LoyaltyReward
from utils.database import execute_query, execute_single_query, execute_insert, execute_update
from config import settings
from .loyalty_engine import LoyaltyEngine
from sqlalchemy import select
from utils.database import get_db
from sqlalchemy.ext.asyncio import AsyncSession

logger = logging.getLogger(__name__)

class LoyaltyService:
    """Servicio para gestión de usuarios de fidelización"""
    
    def __init__(self):
        """Inicializar servicio"""
        self.engine = LoyaltyEngine()
    
    def _map_db_result_to_loyalty_user(self, db_result: dict) -> LoyaltyUser:
        """Mapear resultado de base de datos a objeto LoyaltyUser"""
        # Mapear nombres de columnas de la BD real al modelo Pydantic
        mapped_data = {
            'user_id': db_result.get('user_id'),  # La BD usa 'user_id' como PK
            'total_points': db_result.get('total_points', 0),
            'current_tier': db_result.get('current_tier', 'cafe_bronze'),
            'score': db_result.get('score', 0.0),
            'join_date': db_result.get('join_date'),
            'last_visit': db_result.get('last_visit'),
            'total_visits': db_result.get('total_visits', 0),
            'total_spent': db_result.get('total_spent', 0.0),
            'favorite_products': db_result.get('favorite_products'),
            'referral_code': db_result.get('referral_code'),
            'referred_by': db_result.get('referred_by'),
            'points_expiry_date': db_result.get('points_expiry_date'),
            'created_at': db_result.get('created_at'),
            'updated_at': db_result.get('updated_at')
        }
        return LoyaltyUser(**mapped_data)
    
    async def get_users(self, skip: int = 0, limit: int = 100, tier: Optional[str] = None, status: Optional[str] = None) -> List[LoyaltyUser]:
        """Obtener lista de usuarios de fidelización"""
        try:
            query = """
                SELECT * FROM loyalty_users 
                WHERE 1=1
            """
            params = []
            
            if tier:
                query += " AND current_tier = %s"
                params.append(tier)
            
            if status:
                query += " AND status = %s"
                params.append(status)
            
            query += " ORDER BY created_at DESC LIMIT %s OFFSET %s"
            params.extend([limit, skip])
            
            results = await execute_query(query, tuple(params))
            return [self._map_db_result_to_loyalty_user(result) for result in results]
            
        except Exception as e:
            logger.error(f"Error al obtener usuarios: {e}")
            raise
    
    async def get_user_by_id(self, user_id: int) -> Optional[LoyaltyUser]:
        """Obtener un usuario por ID"""
        query = "SELECT * FROM loyalty_users WHERE user_id = %s"
        user_data = await execute_single_query(query, (user_id,))
        if user_data:
            return self._map_db_result_to_loyalty_user(user_data)
        return None
    
    async def get_user_details(self, user_id: int) -> dict:
        """Obtener detalles básicos de un usuario desde la tabla principal `Usuario`."""
        try:
            query = "SELECT nombre, apellidos, correo FROM Usuario WHERE usuario_ID = %s"
            user_data = await execute_single_query(query, (user_id,))
            if user_data:
                return {
                    "name": f"{user_data['nombre']} {user_data['apellidos']}".strip(),
                    "email": user_data["correo"]
                }
            return {}
        except Exception as e:
            logger.error(f"Error al obtener detalles del usuario {user_id}: {e}")
            return {}
    
    async def get_user_by_usuario_id(self, usuario_id: int) -> Optional[LoyaltyUser]:
        """Obtener un usuario por usuario_ID"""
        try:
            query = "SELECT * FROM loyalty_users WHERE user_id = %s"
            result = await execute_single_query(query, (usuario_id,))
            
            if result:
                return self._map_db_result_to_loyalty_user(result)
            return None
            
        except Exception as e:
            logger.error(f"Error al obtener usuario por usuario_ID {usuario_id}: {e}")
            raise
    
    async def create_user(self, user_data: LoyaltyUserCreate) -> LoyaltyUser:
        """Crear un nuevo usuario de fidelización"""
        try:
            # Verificar si el usuario ya existe
            existing_user = await self.get_user_by_usuario_id(user_data.user_id)
            if existing_user:
                raise ValueError("El usuario ya está registrado en el programa de fidelización")
            
            # Generar código de referido único
            referral_code = self._generate_referral_code()
            
            # Obtener puntos de bienvenida
            welcome_points = await self._get_config_value('welcome_points', 200)
            
            # Calcular fecha de expiración de puntos
            expiry_days = await self._get_config_value('points_expiry_days', 365)
            points_expiry_date = datetime.now() + timedelta(days=expiry_days)
            
            query = """
                INSERT INTO loyalty_users (
                    user_id, total_points, current_tier, score,
                    join_date, total_visits, total_spent, favorite_products,
                    referral_code, referred_by, points_expiry_date
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            
            params = (
                user_data.user_id,
                welcome_points,
                'cafe_bronze',
                0.0,
                datetime.now(),
                0,
                0.0,
                None,  # favorite_products no está en el modelo básico
                referral_code,
                None,  # referred_by no está en el modelo básico
                points_expiry_date
            )
            
            user_id = await execute_insert(query, params)
            
            # Registrar transacción de puntos de bienvenida
            await self._record_transaction(
                user_data.user_id, 'bonus', welcome_points, None, None,
                f"Puntos de bienvenida al programa de fidelización", 0, welcome_points
            )
            
            return await self.get_user_by_id(user_data.user_id)
            
        except Exception as e:
            logger.error(f"Error al crear usuario: {e}")
            raise
    
    async def update_user(self, user_id: int, user_data: LoyaltyUserUpdate) -> Optional[LoyaltyUser]:
        """Actualizar un usuario de fidelización"""
        try:
            # Verificar si el usuario existe
            existing_user = await self.get_user_by_id(user_id)
            if not existing_user:
                return None
            
            # Construir query de actualización
            update_fields = []
            params = []
            
            if user_data.total_points is not None:
                update_fields.append("total_points = %s")
                params.append(user_data.total_points)
            
            if user_data.current_tier is not None:
                update_fields.append("current_tier = %s")
                params.append(user_data.current_tier)
            
            if user_data.score is not None:
                update_fields.append("score = %s")
                params.append(user_data.score)
            
            if user_data.status is not None:
                update_fields.append("status = %s")
                params.append(user_data.status)
            
            if user_data.favorite_products is not None:
                update_fields.append("favorite_products = %s")
                params.append(json.dumps(user_data.favorite_products))
            
            if not update_fields:
                return existing_user
            
            update_fields.append("updated_at = %s")
            params.append(datetime.now())
            params.append(user_id)
            
            query = f"UPDATE loyalty_users SET {', '.join(update_fields)} WHERE user_id = %s"
            await execute_update(query, tuple(params))
            
            return await self.get_user_by_id(user_id)
            
        except Exception as e:
            logger.error(f"Error al actualizar usuario {user_id}: {e}")
            raise
    
    async def delete_user(self, user_id: int) -> bool:
        """Eliminar un usuario de fidelización"""
        try:
            query = "DELETE FROM loyalty_users WHERE user_id = %s"
            affected_rows = await execute_update(query, (user_id,))
            return affected_rows > 0
            
        except Exception as e:
            logger.error(f"Error al eliminar usuario {user_id}: {e}")
            raise
    
    async def redeem_reward(self, user_id: int, reward_id: int) -> dict:
        """
        Canjea una recompensa para un usuario.
        Verifica si el usuario y la recompensa existen, y si el usuario tiene puntos suficientes.
        """
        # Obtener usuario y recompensa en paralelo
        user_query = "SELECT * FROM loyalty_users WHERE user_id = %s"
        reward_query = "SELECT * FROM loyalty_rewards WHERE id = %s AND active = 1"
        
        user_data, reward_data = await asyncio.gather(
            execute_single_query(user_query, (user_id,)),
            execute_single_query(reward_query, (reward_id,))
        )

        if not user_data:
            raise ValueError("El usuario de fidelización no existe.")
        if not reward_data:
            raise ValueError("La recompensa no existe o no está activa.")

        user = LoyaltyUser(**user_data)
        reward = LoyaltyReward(**reward_data)

        # Validar canje usando el motor de fidelización
        if not self.engine._validate_reward_redemption(user.total_points, reward.points_cost, user.current_tier, reward.tier_required):
            raise ValueError("No cumples con los requisitos para canjear esta recompensa (puntos o nivel insuficiente).")

        # Restar puntos
        new_total_points = user.total_points - reward.points_cost
        
        # Actualizar puntos del usuario
        update_query = "UPDATE loyalty_users SET total_points = %s, updated_at = %s WHERE user_id = %s"
        await execute_update(update_query, (new_total_points, datetime.now(), user_id))
        
        # Registrar la transacción de canje
        await self._record_transaction(
            user_id,
            'redeem',
            -reward.points_cost,
            None, # order_id no aplica aquí
            reward.id,
            f"Canje de recompensa: {reward.name}",
            user.total_points,
            new_total_points
        )

        # Registrar el canje en la tabla `loyalty_redemptions`
        redemption_query = """
            INSERT INTO loyalty_redemptions (user_id, reward_id, points_spent, redeemed_at)
            VALUES (%s, %s, %s, %s)
        """
        await execute_insert(redemption_query, (user_id, reward_id, reward.points_cost, datetime.now()))

        return {
            "success": True, 
            "message": "Recompensa canjeada con éxito.",
            "new_total_points": new_total_points
        }
    
    async def calculate_user_score(self, user_id: int) -> float:
        """
        Calcula el score de fidelización de un usuario basado en varios factores.
        Esta función ahora delega la lógica al LoyaltyEngine.
        """
        try:
            # La lógica de cálculo de score ahora reside en el LoyaltyEngine
            user_data = await self._get_user_data(user_id) # Se obtienen los datos reales
            
            frequency_score = self.engine._calculate_frequency_score(
                user_data.get('total_visits', 0), 
                (datetime.now() - user_data.get('join_date', datetime.now())).days
            )
            
            amount_score = self.engine._calculate_amount_score(
                user_data.get('total_spent', 0)
            )
            
            recency_score = self.engine._calculate_recency_score(
                user_data.get('last_visit')
            )
            
            variety_score = self.engine._calculate_variety_score(
                json.loads(user_data.get('favorite_products', '[]'))
            )
            
            referral_score = self.engine._calculate_referral_score(
                user_data.get('referral_count', 0)
            )
            
            # Ponderación de scores definida en el engine
            total_score = (
                frequency_score * 0.3 +
                amount_score * 0.3 +
                recency_score * 0.2 +
                variety_score * 0.1 +
                referral_score * 0.1
            )
            
            return round(total_score, 2)
        except Exception as e:
            logger.error(f"Error al calcular score para el usuario {user_id}: {e}")
            return 0.0
    
    async def adjust_points(self, user_id: int, points: int, reason: str) -> Dict[str, Any]:
        """Ajustar puntos de un usuario (para administradores)"""
        try:
            user = await self.get_user_by_id(user_id)
            if not user:
                raise ValueError("Usuario no encontrado")
            
            new_points = user.total_points + points
            if new_points < 0:
                raise ValueError("Los puntos no pueden ser negativos")
            
            await self.update_user(user_id, LoyaltyUserUpdate(total_points=new_points))
            
            # Registrar transacción
            await self._record_transaction(
                user_id, 'adjustment', points, None, None,
                f"Ajuste manual: {reason}", user.total_points, new_points
            )
            
            return {
                "success": True,
                "message": f"Puntos ajustados: {points:+d}",
                "previous_points": user.total_points,
                "new_points": new_points,
                "reason": reason
            }
            
        except Exception as e:
            logger.error(f"Error al ajustar puntos: {e}")
            raise
    
    async def get_system_stats(self) -> Dict[str, Any]:
        """Obtener estadísticas generales del sistema"""
        try:
            # Total de usuarios
            total_users_query = "SELECT COUNT(*) as total FROM loyalty_users WHERE status = 'activo'"
            total_users_result = await execute_single_query(total_users_query)
            total_users = total_users_result['total']
            
            # Usuarios por nivel
            tier_stats_query = """
                SELECT current_tier, COUNT(*) as count 
                FROM loyalty_users 
                WHERE status = 'activo' 
                GROUP BY current_tier
            """
            tier_stats = await execute_query(tier_stats_query)
            
            # Puntos totales en el sistema
            total_points_query = "SELECT SUM(total_points) as total FROM loyalty_users WHERE status = 'activo'"
            total_points_result = await execute_single_query(total_points_query)
            total_points = total_points_result['total'] or 0
            
            # Transacciones del mes
            month_transactions_query = """
                SELECT COUNT(*) as count 
                FROM loyalty_transactions 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())
            """
            month_transactions_result = await execute_single_query(month_transactions_query)
            month_transactions = month_transactions_result['count']
            
            return {
                "total_users": total_users,
                "tier_distribution": {stat['current_tier']: stat['count'] for stat in tier_stats},
                "total_points": total_points,
                "month_transactions": month_transactions,
                "timestamp": datetime.now().isoformat()
            }
            
        except Exception as e:
            logger.error(f"Error al obtener estadísticas del sistema: {e}")
            raise
    
    async def get_tier_stats(self) -> List[Dict[str, Any]]:
        """Obtener estadísticas por nivel"""
        try:
            query = """
                SELECT 
                    current_tier,
                    COUNT(*) as total_users,
                    AVG(total_points) as avg_points,
                    AVG(total_spent) as avg_spent,
                    AVG(total_visits) as avg_visits,
                    AVG(score) as avg_score
                FROM loyalty_users
                WHERE status = 'activo'
                GROUP BY current_tier
                ORDER BY 
                    CASE current_tier
                        WHEN 'cafe_bronze' THEN 1
                        WHEN 'cafe_plata' THEN 2
                        WHEN 'cafe_oro' THEN 3
                        WHEN 'cafe_diamante' THEN 4
                    END
            """
            
            results = await execute_query(query)
            return results
            
        except Exception as e:
            logger.error(f"Error al obtener estadísticas por nivel: {e}")
            raise
    
    def _generate_referral_code(self) -> str:
        """Generar código de referido único"""
        return ''.join(secrets.choice(string.ascii_uppercase + string.digits) for _ in range(8))
    
    def _generate_coupon_code(self) -> str:
        """Generar código de cupón único"""
        return ''.join(secrets.choice(string.ascii_uppercase + string.digits) for _ in range(10))
    
    async def _get_config_value(self, key: str, default_value: Any) -> Any:
        """Obtener valor de configuración"""
        try:
            query = "SELECT config_value FROM loyalty_config WHERE config_key = %s"
            result = await execute_single_query(query, (key,))
            return result['config_value'] if result else default_value
        except Exception as e:
            logger.error(f"Error al obtener valor de configuración para {key}: {e}")
            return default_value
    
    async def _record_transaction(self, user_id: int, transaction_type: str, points_amount: int, 
                                order_id: Optional[int], reward_id: Optional[int], 
                                description: str, balance_before: int, balance_after: int):
        """Registrar una transacción"""
        query = """
            INSERT INTO loyalty_transactions (
                user_id, transaction_type, points_amount, order_id,
                description, balance_before, balance_after, created_at
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """
        
        params = (
            user_id, transaction_type, points_amount, order_id,
            description, balance_before, balance_after, datetime.now()
        )
        
        await execute_insert(query, params)
    
    async def _process_referral(self, referral_code: str, new_user_id: int):
        """Procesar un código de referido y otorgar puntos."""
        try:
            # Buscar usuario que hizo la referencia
            referrer_query = "SELECT user_id FROM loyalty_users WHERE referral_code = %s"
            referrer_result = await execute_single_query(referrer_query, (referral_code,))
            
            if referrer_result:
                referrer_id = referrer_result['user_id']
                bonus_points = await self._get_config_value('referral_bonus_points', 500)
                
                # Dar puntos bonus al referidor
                referrer = await self.get_user_by_id(referrer_id)
                new_points = referrer.total_points + bonus_points
                await self.update_user(referrer_id, LoyaltyUserUpdate(total_points=new_points))
                
                # Registrar transacción
                await self._record_transaction(
                    referrer_id, 'referral', bonus_points, None, None,
                    f"Puntos por referido exitoso", referrer.total_points, new_points
                )
                
                # Registrar en tabla de referidos
                referral_query = """
                    INSERT INTO loyalty_referrals (
                        referrer_user_ID, referred_user_ID, referral_code, status, bonus_points_given
                    ) VALUES (%s, %s, %s, %s, %s)
                """
                await execute_insert(referral_query, (referrer_id, new_user_id, referral_code, 'completed', True))
                
        except Exception as e:
            logger.error(f"Error al procesar referido para el nuevo usuario {new_user_id}: {e}")
            # No relanzar la excepción para no afectar el registro del usuario principal
    
    async def _get_user_data(self, user_id: int) -> dict:
        """Obtener datos consolidados de un usuario para el cálculo de score."""
        try:
            # Query para obtener datos de loyalty_users
            loyalty_query = "SELECT * FROM loyalty_users WHERE user_id = %s"
            loyalty_data = await execute_single_query(loyalty_query, (user_id,))
            if not loyalty_data:
                return {}

            # Query para contar referidos (ejemplo)
            referral_query = "SELECT COUNT(*) as referral_count FROM loyalty_users WHERE referred_by = %s"
            referral_data = await execute_single_query(referral_query, (loyalty_data['referral_code'],))
            
            loyalty_data['referral_count'] = referral_data.get('referral_count', 0)
            return loyalty_data

        except Exception as e:
            logger.error(f"Error al obtener datos del usuario {user_id} para score: {e}")
            return {}

    async def earn_points_from_purchase(self, user_id: int, purchase_amount: float, order_id: int) -> Dict[str, Any]:
        """
        Otorga puntos a un usuario por una compra y verifica si sube de nivel.
        """
        try:
            user = await self.get_user_by_id(user_id)
            if not user:
                return {"status": "error", "message": "Usuario no encontrado"}

            # Calcular puntos usando el motor
            base_points = self.engine._calculate_points_from_purchase(purchase_amount)
            final_points = self.engine._apply_tier_multiplier(base_points, user.current_tier)

            if final_points <= 0:
                return {"status": "no_change", "message": "No se generaron puntos para esta compra."}

            balance_before = user.total_points
            balance_after = balance_before + final_points

            # Actualizar puntos y estadísticas del usuario
            update_query = """
                UPDATE loyalty_users 
                SET total_points = %s, total_visits = total_visits + 1, 
                    total_spent = total_spent + %s, last_visit = %s, updated_at = %s
                WHERE user_id = %s
            """
            await execute_update(update_query, (
                balance_after, purchase_amount, datetime.now(), datetime.now(), user_id
            ))

            # Registrar la transacción
            await self._record_transaction(
                user_id, 'earn', final_points, order_id, None,
                f"Puntos ganados por compra #{order_id}", balance_before, balance_after
            )

            # Verificar si el usuario sube de nivel
            tier_upgrade_status = await self.check_tier_upgrade(user_id)

            return {
                "status": "success",
                "points_earned": final_points,
                "new_total_points": balance_after,
                "tier_status": tier_upgrade_status
            }
        except Exception as e:
            logger.error(f"Error al otorgar puntos por compra al usuario {user_id}: {e}")
            return {"status": "error", "message": str(e)}

    async def get_reward_by_id(self, reward_id: int) -> Optional[Reward]:
        """Obtener una recompensa por ID"""
        try:
            query = "SELECT * FROM loyalty_rewards WHERE reward_id = %s"
            result = await execute_single_query(query, (reward_id,))
            
            if result:
                return Reward(**result)
            return None
            
        except Exception as e:
            logger.error(f"Error al obtener recompensa {reward_id}: {e}")
            return None

    def _validate_transaction_type(self, transaction_type: str) -> bool:
        """Validar tipo de transacción"""
        valid_types = ['purchase', 'redemption', 'bonus', 'referral', 'adjustment']
        return transaction_type in valid_types

    def _generate_transaction_description(self, transaction_type: str, points_amount: int, order_id: int) -> str:
        """Generar descripción de transacción"""
        descriptions = {
            'purchase': f"Puntos por compra #{order_id}",
            'redemption': f"Canje de recompensa - {points_amount} puntos",
            'bonus': f"Bono de {points_amount} puntos",
            'referral': f"Puntos por referido - {points_amount} puntos",
            'adjustment': f"Ajuste de {points_amount} puntos"
        }
        return descriptions.get(transaction_type, f"Transacción {transaction_type}")

    def _create_audit_trail(self, transaction_data: dict) -> dict:
        """Crear auditoría de transacción"""
        return {
            'timestamp': datetime.now(),
            'user_id': transaction_data['user_id'],
            'transaction_type': transaction_data['transaction_type'],
            'points_amount': transaction_data['points_amount'],
            'balance_before': transaction_data['balance_before'],
            'balance_after': transaction_data['balance_after'],
            'description': transaction_data['description']
        }

    async def _get_user_transactions(self, user_id: int, limit: int = 50) -> List[dict]:
        """Obtener transacciones del usuario"""
        try:
            query = """
                SELECT * FROM loyalty_transactions 
                WHERE user_id = %s 
                ORDER BY created_at DESC 
                LIMIT %s
            """
            results = await execute_query(query, (user_id, limit))
            return results
            
        except Exception as e:
            logger.error(f"Error al obtener transacciones del usuario {user_id}: {e}")
            return []

    async def get_user_transactions(self, user_id: int, skip: int = 0, limit: int = 50) -> List[dict]:
        """Obtener transacciones del usuario con paginación"""
        try:
            # Primero verificar que el usuario existe
            user = await self.get_user_by_usuario_id(user_id)
            if not user:
                return []
            
            # Obtener transacciones con paginación
            query = """
                SELECT 
                    id,
                    user_id,
                    transaction_type,
                    points_amount,
                    order_id,
                    description,
                    balance_before,
                    balance_after,
                    created_at
                FROM loyalty_transactions 
                WHERE user_id = %s 
                ORDER BY created_at DESC 
                LIMIT %s OFFSET %s
            """
            results = await execute_query(query, (user.user_id, limit, skip))
            return results
            
        except Exception as e:
            logger.error(f"Error al obtener transacciones del usuario {user_id}: {e}")
            return []

    def _calculate_transaction_summary(self, transactions: List[dict]) -> dict:
        """Calcular resumen de transacciones"""
        summary = {
            'total_purchases': 0,
            'total_redemptions': 0,
            'total_bonuses': 0,
            'total_referrals': 0,
            'total_adjustments': 0,
            'net_points': 0
        }
        
        for transaction in transactions:
            trans_type = transaction['transaction_type']
            points = transaction['points_amount']
            
            if trans_type == 'purchase':
                summary['total_purchases'] += points
            elif trans_type == 'redemption':
                summary['total_redemptions'] += abs(points)
            elif trans_type == 'bonus':
                summary['total_bonuses'] += points
            elif trans_type == 'referral':
                summary['total_referrals'] += points
            elif trans_type == 'adjustment':
                summary['total_adjustments'] += points
            
            summary['net_points'] += points
        
        return summary

    async def check_tier_upgrade(self, user_id: int) -> Dict[str, Any]:
        """
        Verifica si un usuario ha subido de nivel y actualiza su estado.
        Devuelve información sobre el cambio de nivel si ocurre.
        """
        try:
            user = await self.get_user_by_id(user_id)
            if not user:
                return {"status": "error", "message": "Usuario no encontrado"}

            current_points = user.total_points
            current_tier = user.current_tier

            # Usa el motor de fidelización para determinar el nuevo nivel
            new_tier = self.engine._get_tier_from_score(current_points)

            if new_tier != current_tier:
                # Valida que el cambio sea un ascenso
                current_tier_value = self.engine.tier_thresholds.get(current_tier, 0)
                new_tier_value = self.engine.tier_thresholds.get(new_tier, 0)

                if new_tier_value > current_tier_value:
                    # Actualiza el nivel del usuario en la base de datos
                    update_query = "UPDATE loyalty_users SET current_tier = %s, updated_at = %s WHERE user_id = %s"
                    await execute_update(update_query, (new_tier, datetime.now(), user_id))

                    return {
                        "status": "success",
                        "old_tier": current_tier,
                        "new_tier": new_tier,
                        "message": f"¡Felicidades! Has ascendido a {new_tier.replace('_', ' ').title()}."
                    }

            return {"status": "no_change", "current_tier": current_tier}

        except Exception as e:
            logger.error(f"Error al verificar la actualización de nivel para el usuario {user_id}: {e}")
            return {"status": "error", "message": str(e)}

    async def get_tier_benefits(self, tier: str) -> dict:
        """Obtener beneficios por nivel"""
        return self.engine._calculate_tier_benefits(tier)

    async def earn_points(self, usuario_id: int, points: int, order_id: Optional[int], description: str) -> Dict[str, Any]:
        """Otorga puntos a un usuario y registra la transacción."""
        try:
            print(f"🔍 DEBUG: earn_points iniciado - usuario_id: {usuario_id}, points: {points}")
            
            # Buscar usuario de fidelización por el usuario_ID de la app principal
            user = await self.get_user_by_usuario_id(usuario_id)
            print(f"🔍 DEBUG: Usuario encontrado en fidelización: {user is not None}")
            
            if not user:
                # Si el usuario no existe en el sistema de lealtad, lo creamos
                logger.info(f"Usuario de fidelización no encontrado para usuario_ID {usuario_id}. Creando nuevo perfil.")
                print(f"🔍 DEBUG: Creando nuevo usuario de fidelización para usuario_ID {usuario_id}")
                new_user_data = LoyaltyUserCreate(user_id=usuario_id)
                user = await self.create_user(new_user_data)
                print(f"🔍 DEBUG: Usuario creado: {user is not None}")

            balance_before = user.total_points
            print(f"🔍 DEBUG: Balance antes: {balance_before}")
            
            # Actualizar puntos del usuario en la tabla loyalty_users
            update_query = """
                UPDATE loyalty_users 
                SET total_points = total_points + %s, last_visit = %s 
                WHERE user_id = %s
            """
            print(f"🔍 DEBUG: Ejecutando UPDATE con {points} puntos para user_id {user.user_id}")
            await execute_update(update_query, (points, datetime.now(), user.user_id))
            print(f"🔍 DEBUG: UPDATE ejecutado exitosamente")
            
            # Registrar la transacción
            print(f"🔍 DEBUG: Registrando transacción")
            await self._record_transaction(
                user.user_id, 'earn', points, order_id, None,
                description, balance_before, balance_before + points
            )
            print(f"🔍 DEBUG: Transacción registrada")
            
            # Verificar si sube de nivel
            print(f"🔍 DEBUG: Verificando subida de nivel")
            await self.check_tier_upgrade(user.user_id)
            
            new_balance = balance_before + points
            print(f"🔍 DEBUG: Nuevo balance: {new_balance}")
            
            return {
                "message": "Puntos otorgados exitosamente.",
                "points_earned": points,
                "new_balance": new_balance
            }
        except Exception as e:
            print(f"❌ ERROR en earn_points: {str(e)}")
            logger.error(f"Error en earn_points para usuario_ID {usuario_id}: {e}")
            raise

    async def get_all_rewards(self) -> List[LoyaltyReward]:
        """Obtener todas las recompensas disponibles"""
        try:
            query = "SELECT * FROM loyalty_rewards WHERE active = TRUE ORDER BY points_cost"
            results = await execute_query(query)
            
            rewards = []
            for result in results:
                reward = LoyaltyReward(
                    id=result['id'],
                    name=result['name'],
                    description=result['description'],
                    points_cost=result['points_cost'],
                    discount_percent=result['discount_percent'],
                    tier_required=result['tier_required'],
                    max_uses_per_user=result['max_uses_per_user'],
                    active=result['active'],
                    expiry_date=result['expiry_date'],
                    created_at=result['created_at']
                )
                rewards.append(reward)
            
            return rewards
            
        except Exception as e:
            logger.error(f"Error al obtener recompensas: {e}")
            return []

    # =====================================================
    # MÉTODOS PARA EL SISTEMA DE CUPONES
    # =====================================================

    async def create_coupon(self, coupon_data: dict) -> Optional[int]:
        """Crear un nuevo cupón de descuento"""
        try:
            # Generar código único
            code = self._generate_coupon_code()
            
            query = """
                INSERT INTO loyalty_coupons (
                    user_id, code, discount_type, discount_value, min_order_amount,
                    max_uses, valid_from, valid_until, active, created_at
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            
            params = (
                coupon_data['user_id'],
                code,
                coupon_data['discount_type'],
                coupon_data['discount_value'],
                coupon_data['min_order_amount'],
                coupon_data['max_uses'],
                datetime.now(),
                coupon_data.get('valid_until'),
                True,
                datetime.now()
            )
            
            coupon_id = await execute_insert(query, params)
            return coupon_id
            
        except Exception as e:
            logger.error(f"Error creando cupón: {e}")
            return None

    async def get_coupon_by_id(self, coupon_id: int) -> Optional[dict]:
        """Obtener cupón por ID"""
        try:
            query = "SELECT * FROM loyalty_coupons WHERE id = %s"
            result = await execute_single_query(query, (coupon_id,))
            return result
        except Exception as e:
            logger.error(f"Error obteniendo cupón {coupon_id}: {e}")
            return None

    async def get_coupon_by_code(self, code: str) -> Optional[dict]:
        """Obtener cupón por código"""
        try:
            query = "SELECT * FROM loyalty_coupons WHERE code = %s"
            result = await execute_single_query(query, (code,))
            return result
        except Exception as e:
            logger.error(f"Error obteniendo cupón con código {code}: {e}")
            return None

    async def get_user_active_coupons(self, user_id: int) -> List[dict]:
        """Obtener cupones activos de un usuario"""
        try:
            query = """
                SELECT * FROM loyalty_coupons 
                WHERE user_id = %s AND active = TRUE 
                AND (valid_until IS NULL OR valid_until > %s)
                AND used_count < max_uses
                ORDER BY created_at DESC
            """
            results = await execute_query(query, (user_id, datetime.now()))
            return results
        except Exception as e:
            logger.error(f"Error obteniendo cupones activos del usuario {user_id}: {e}")
            return []

    async def get_all_coupons(self, skip: int = 0, limit: int = 100, 
                            user_id: Optional[int] = None, active_only: bool = True,
                            expired_only: bool = False) -> List[dict]:
        """Obtener todos los cupones con filtros"""
        try:
            conditions = []
            params = []
            
            if user_id:
                conditions.append("user_id = %s")
                params.append(user_id)
            
            if active_only:
                conditions.append("active = TRUE")
            
            if expired_only:
                conditions.append("valid_until < %s")
                params.append(datetime.now())
            
            where_clause = " AND ".join(conditions) if conditions else "1=1"
            
            query = f"""
                SELECT * FROM loyalty_coupons 
                WHERE {where_clause}
                ORDER BY created_at DESC
                LIMIT %s OFFSET %s
            """
            params.extend([limit, skip])
            
            results = await execute_query(query, tuple(params))
            return results
        except Exception as e:
            logger.error(f"Error obteniendo cupones: {e}")
            return []

    async def update_coupon(self, coupon_id: int, update_data: dict) -> bool:
        """Actualizar cupón existente"""
        try:
            set_clauses = []
            params = []
            
            for key, value in update_data.items():
                if key in ['discount_type', 'discount_value', 'min_order_amount', 
                          'max_uses', 'valid_until', 'active']:
                    set_clauses.append(f"{key} = %s")
                    params.append(value)
            
            if not set_clauses:
                return False
            
            set_clauses.append("updated_at = %s")
            params.append(datetime.now())
            params.append(coupon_id)
            
            query = f"""
                UPDATE loyalty_coupons 
                SET {', '.join(set_clauses)}
                WHERE id = %s
            """
            
            affected_rows = await execute_update(query, tuple(params))
            return affected_rows > 0
            
        except Exception as e:
            logger.error(f"Error actualizando cupón {coupon_id}: {e}")
            return False

    async def delete_coupon(self, coupon_id: int) -> bool:
        """Eliminar cupón"""
        try:
            query = "DELETE FROM loyalty_coupons WHERE id = %s"
            affected_rows = await execute_delete(query, (coupon_id,))
            return affected_rows > 0
        except Exception as e:
            logger.error(f"Error eliminando cupón {coupon_id}: {e}")
            return False

    async def activate_coupon(self, coupon_id: int) -> bool:
        """Activar cupón"""
        try:
            query = "UPDATE loyalty_coupons SET active = TRUE WHERE id = %s"
            affected_rows = await execute_update(query, (coupon_id,))
            return affected_rows > 0
        except Exception as e:
            logger.error(f"Error activando cupón {coupon_id}: {e}")
            return False

    async def deactivate_coupon(self, coupon_id: int) -> bool:
        """Desactivar cupón"""
        try:
            query = "UPDATE loyalty_coupons SET active = FALSE WHERE id = %s"
            affected_rows = await execute_update(query, (coupon_id,))
            return affected_rows > 0
        except Exception as e:
            logger.error(f"Error desactivando cupón {coupon_id}: {e}")
            return False

    async def use_coupon(self, coupon_code: str) -> bool:
        """Marcar cupón como usado"""
        try:
            query = """
                UPDATE loyalty_coupons 
                SET used_count = used_count + 1 
                WHERE code = %s AND used_count < max_uses
            """
            affected_rows = await execute_update(query, (coupon_code,))
            return affected_rows > 0
        except Exception as e:
            logger.error(f"Error usando cupón {coupon_code}: {e}")
            return False

    async def get_coupon_statistics(self) -> dict:
        """Obtener estadísticas generales de cupones"""
        try:
            # Total de cupones
            total_query = "SELECT COUNT(*) as total FROM loyalty_coupons"
            total_result = await execute_single_query(total_query)
            
            # Cupones activos
            active_query = "SELECT COUNT(*) as active FROM loyalty_coupons WHERE active = TRUE"
            active_result = await execute_single_query(active_query)
            
            # Cupones usados
            used_query = "SELECT COUNT(*) as used FROM loyalty_coupons WHERE used_count > 0"
            used_result = await execute_single_query(used_query)
            
            # Cupones expirados
            expired_query = "SELECT COUNT(*) as expired FROM loyalty_coupons WHERE valid_until < %s"
            expired_result = await execute_single_query(expired_query, (datetime.now(),))
            
            # Valor total de descuentos
            value_query = """
                SELECT SUM(discount_value) as total_value 
                FROM loyalty_coupons 
                WHERE used_count > 0
            """
            value_result = await execute_single_query(value_query)
            
            return {
                "total_coupons": total_result.get('total', 0),
                "active_coupons": active_result.get('active', 0),
                "used_coupons": used_result.get('used', 0),
                "expired_coupons": expired_result.get('expired', 0),
                "total_discount_value": value_result.get('total_value', 0),
                "redemption_rate": round((used_result.get('used', 0) / total_result.get('total', 1)) * 100, 2)
            }
        except Exception as e:
            logger.error(f"Error obteniendo estadísticas de cupones: {e}")
            return {}

    async def get_user_coupon_statistics(self, user_id: int) -> dict:
        """Obtener estadísticas de cupones de un usuario específico"""
        try:
            # Total de cupones del usuario
            total_query = "SELECT COUNT(*) as total FROM loyalty_coupons WHERE user_id = %s"
            total_result = await execute_single_query(total_query, (user_id,))
            
            # Cupones activos
            active_query = """
                SELECT COUNT(*) as active 
                FROM loyalty_coupons 
                WHERE user_id = %s AND active = TRUE 
                AND (valid_until IS NULL OR valid_until > %s)
            """
            active_result = await execute_single_query(active_query, (user_id, datetime.now()))
            
            # Cupones usados
            used_query = "SELECT COUNT(*) as used FROM loyalty_coupons WHERE user_id = %s AND used_count > 0"
            used_result = await execute_single_query(used_query, (user_id,))
            
            # Valor total de descuentos usados
            value_query = """
                SELECT SUM(discount_value) as total_value 
                FROM loyalty_coupons 
                WHERE user_id = %s AND used_count > 0
            """
            value_result = await execute_single_query(value_query, (user_id,))
            
            return {
                "user_id": user_id,
                "total_coupons": total_result.get('total', 0),
                "active_coupons": active_result.get('active', 0),
                "used_coupons": used_result.get('used', 0),
                "total_discount_value": value_result.get('total_value', 0),
                "redemption_rate": round((used_result.get('used', 0) / total_result.get('total', 1)) * 100, 2)
            }
        except Exception as e:
            logger.error(f"Error obteniendo estadísticas de cupones del usuario {user_id}: {e}")
            return {}

    async def get_coupon_effectiveness_analysis(self) -> dict:
        """Obtener análisis de efectividad de cupones"""
        try:
            # Cupones por tipo de descuento
            type_query = """
                SELECT discount_type, COUNT(*) as count, 
                       AVG(discount_value) as avg_value,
                       SUM(CASE WHEN used_count > 0 THEN 1 ELSE 0 END) as used_count
                FROM loyalty_coupons 
                GROUP BY discount_type
            """
            type_results = await execute_query(type_query)
            
            # Cupones por rango de descuento
            range_query = """
                SELECT 
                    CASE 
                        WHEN discount_value <= 10 THEN '0-10%'
                        WHEN discount_value <= 20 THEN '11-20%'
                        WHEN discount_value <= 30 THEN '21-30%'
                        ELSE '30%+'
                    END as discount_range,
                    COUNT(*) as count,
                    SUM(CASE WHEN used_count > 0 THEN 1 ELSE 0 END) as used_count
                FROM loyalty_coupons 
                WHERE discount_type = 'percentage'
                GROUP BY discount_range
            """
            range_results = await execute_query(range_query)
            
            # Tasa de uso por mes
            monthly_query = """
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as created,
                    SUM(CASE WHEN used_count > 0 THEN 1 ELSE 0 END) as used
                FROM loyalty_coupons 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month
                ORDER BY month
            """
            monthly_results = await execute_query(monthly_query)
            
            return {
                "by_type": type_results,
                "by_range": range_results,
                "monthly_trend": monthly_results
            }
        except Exception as e:
            logger.error(f"Error obteniendo análisis de efectividad de cupones: {e}")
            return {} 