"""
Servicio de recompensas para el sistema de fidelización
"""

from typing import Dict, Any, List, Optional
from datetime import datetime, timedelta
import logging

logger = logging.getLogger(__name__)

class RewardService:
    """Servicio de gestión de recompensas"""
    
    def __init__(self):
        """Inicializar servicio de recompensas"""
        self.rewards = {
            1: {
                'id': 1,
                'name': 'Café Gratis',
                'description': 'Un café gratis de cualquier tamaño',
                'points_cost': 200,
                'tier_required': 'cafe_bronze',
                'active': True,
                'max_uses_per_user': 5,
                'expiry_days': 30
            },
            2: {
                'id': 2,
                'name': 'Descuento 15%',
                'description': '15% de descuento en tu próxima compra',
                'points_cost': 500,
                'tier_required': 'cafe_plata',
                'active': True,
                'max_uses_per_user': 3,
                'expiry_days': 60
            },
            3: {
                'id': 3,
                'name': 'Descuento 25%',
                'description': '25% de descuento en tu próxima compra',
                'points_cost': 800,
                'tier_required': 'cafe_oro',
                'active': True,
                'max_uses_per_user': 2,
                'expiry_days': 90
            },
            4: {
                'id': 4,
                'name': 'Combo VIP',
                'description': 'Café + Pastel + Bebida especial',
                'points_cost': 1200,
                'tier_required': 'cafe_diamante',
                'active': True,
                'max_uses_per_user': 1,
                'expiry_days': 120
            }
        }
    
    async def get_reward_by_id(self, reward_id: int) -> Optional[Dict[str, Any]]:
        """Obtener recompensa por ID"""
        return self.rewards.get(reward_id)
    
    async def get_available_rewards(self, user_tier: str = None) -> List[Dict[str, Any]]:
        """Obtener recompensas disponibles"""
        available_rewards = []
        
        for reward in self.rewards.values():
            if not reward['active']:
                continue
                
            if user_tier:
                # Verificar si el usuario puede acceder a esta recompensa
                if not self._can_user_access_reward(user_tier, reward['tier_required']):
                    continue
            
            available_rewards.append(reward)
        
        return available_rewards
    
    def _can_user_access_reward(self, user_tier: str, required_tier: str) -> bool:
        """Verificar si un usuario puede acceder a una recompensa"""
        tier_hierarchy = {
            'cafe_bronze': 1,
            'cafe_plata': 2,
            'cafe_oro': 3,
            'cafe_diamante': 4
        }
        
        user_level = tier_hierarchy.get(user_tier, 0)
        required_level = tier_hierarchy.get(required_tier, 0)
        
        return user_level >= required_level
    
    async def validate_reward_redemption(self, user_id: int, reward_id: int, 
                                       user_points: int, user_tier: str) -> Dict[str, Any]:
        """Validar canje de recompensa"""
        reward = await self.get_reward_by_id(reward_id)
        
        if not reward:
            return {
                'valid': False,
                'error': 'Recompensa no encontrada'
            }
        
        if not reward['active']:
            return {
                'valid': False,
                'error': 'Recompensa no disponible'
            }
        
        if user_points < reward['points_cost']:
            return {
                'valid': False,
                'error': 'Puntos insuficientes'
            }
        
        if not self._can_user_access_reward(user_tier, reward['tier_required']):
            return {
                'valid': False,
                'error': 'Nivel requerido no alcanzado'
            }
        
        # Verificar límite de usos por usuario
        user_redemptions = await self._get_user_redemptions(user_id, reward_id)
        if user_redemptions >= reward['max_uses_per_user']:
            return {
                'valid': False,
                'error': 'Límite de usos alcanzado'
            }
        
        return {
            'valid': True,
            'reward': reward,
            'points_cost': reward['points_cost'],
            'new_balance': user_points - reward['points_cost']
        }
    
    async def _get_user_redemptions(self, user_id: int, reward_id: int) -> int:
        """Obtener número de canjes de un usuario para una recompensa específica"""
        # Mock de datos
        return 0
    
    async def process_reward_redemption(self, user_id: int, reward_id: int, 
                                      user_points: int) -> Dict[str, Any]:
        """Procesar canje de recompensa"""
        reward = await self.get_reward_by_id(reward_id)
        
        if not reward:
            return {
                'success': False,
                'error': 'Recompensa no encontrada'
            }
        
        new_balance = user_points - reward['points_cost']
        
        # Mock de registro de canje
        redemption_record = {
            'user_id': user_id,
            'reward_id': reward_id,
            'points_spent': reward['points_cost'],
            'redemption_date': datetime.now(),
            'expiry_date': datetime.now() + timedelta(days=reward['expiry_days'])
        }
        
        return {
            'success': True,
            'reward_name': reward['name'],
            'points_spent': reward['points_cost'],
            'new_balance': new_balance,
            'expiry_date': redemption_record['expiry_date'],
            'redemption_code': f"RED{user_id}{reward_id}{datetime.now().strftime('%Y%m%d')}"
        }
    
    async def get_user_reward_history(self, user_id: int) -> List[Dict[str, Any]]:
        """Obtener historial de recompensas de un usuario"""
        # Mock de historial
        return [
            {
                'id': 1,
                'reward_name': 'Café Gratis',
                'points_spent': 200,
                'redemption_date': datetime.now() - timedelta(days=5),
                'expiry_date': datetime.now() + timedelta(days=25),
                'status': 'active'
            },
            {
                'id': 2,
                'reward_name': 'Descuento 15%',
                'points_spent': 500,
                'redemption_date': datetime.now() - timedelta(days=30),
                'expiry_date': datetime.now() - timedelta(days=5),
                'status': 'expired'
            }
        ]
    
    async def get_reward_statistics(self) -> Dict[str, Any]:
        """Obtener estadísticas de recompensas"""
        total_redemptions = 0
        total_points_spent = 0
        most_popular_reward = None
        max_redemptions = 0
        
        for reward in self.rewards.values():
            # Mock de estadísticas
            redemptions = 150 if reward['id'] == 1 else 80 if reward['id'] == 2 else 45
            points_spent = redemptions * reward['points_cost']
            
            total_redemptions += redemptions
            total_points_spent += points_spent
            
            if redemptions > max_redemptions:
                max_redemptions = redemptions
                most_popular_reward = reward['name']
        
        return {
            'total_redemptions': total_redemptions,
            'total_points_spent': total_points_spent,
            'most_popular_reward': most_popular_reward,
            'average_points_per_redemption': total_points_spent / total_redemptions if total_redemptions > 0 else 0,
            'redemption_rate': 0.72
        }
    
    async def create_custom_reward(self, name: str, description: str, points_cost: int,
                                 tier_required: str, max_uses: int = 1, 
                                 expiry_days: int = 30) -> Dict[str, Any]:
        """Crear recompensa personalizada"""
        reward_id = max(self.rewards.keys()) + 1
        
        new_reward = {
            'id': reward_id,
            'name': name,
            'description': description,
            'points_cost': points_cost,
            'tier_required': tier_required,
            'active': True,
            'max_uses_per_user': max_uses,
            'expiry_days': expiry_days,
            'created_at': datetime.now()
        }
        
        self.rewards[reward_id] = new_reward
        
        return {
            'success': True,
            'reward_id': reward_id,
            'reward': new_reward
        }
    
    async def deactivate_reward(self, reward_id: int) -> Dict[str, Any]:
        """Desactivar recompensa"""
        reward = await self.get_reward_by_id(reward_id)
        
        if not reward:
            return {
                'success': False,
                'error': 'Recompensa no encontrada'
            }
        
        reward['active'] = False
        
        return {
            'success': True,
            'message': f"Recompensa '{reward['name']}' desactivada"
        }
    
    async def update_reward(self, reward_id: int, updates: Dict[str, Any]) -> Dict[str, Any]:
        """Actualizar recompensa"""
        reward = await self.get_reward_by_id(reward_id)
        
        if not reward:
            return {
                'success': False,
                'error': 'Recompensa no encontrada'
            }
        
        # Actualizar campos permitidos
        allowed_fields = ['name', 'description', 'points_cost', 'max_uses_per_user', 'expiry_days']
        
        for field in allowed_fields:
            if field in updates:
                reward[field] = updates[field]
        
        return {
            'success': True,
            'reward': reward
        } 