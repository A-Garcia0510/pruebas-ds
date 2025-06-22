"""
Motor de fidelización para el sistema Café-VT
"""

import random
import string
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Any

class LoyaltyEngine:
    """Motor de scoring y gestión de fidelización"""
    
    def __init__(self):
        """Inicializar motor de fidelización"""
        self.tier_thresholds = {
            'cafe_bronze': 0,
            'cafe_plata': 5000,
            'cafe_oro': 25000,
            'cafe_diamante': 75000
        }
        
        self.tier_multipliers = {
            'cafe_bronze': 1.0,
            'cafe_plata': 1.2,
            'cafe_oro': 1.5,
            'cafe_diamante': 2.0
        }
        
        self.tier_benefits = {
            'cafe_bronze': {
                'discount_percent': 5,
                'free_coffees': 0,
                'priority_access': False
            },
            'cafe_plata': {
                'discount_percent': 10,
                'free_coffees': 1,
                'priority_access': True
            },
            'cafe_oro': {
                'discount_percent': 15,
                'free_coffees': 2,
                'priority_access': True
            },
            'cafe_diamante': {
                'discount_percent': 20,
                'free_coffees': 3,
                'priority_access': True
            }
        }
    
    def _get_tier_from_score(self, points: int) -> str:
        """Obtener nivel basado en puntos"""
        if points >= self.tier_thresholds['cafe_diamante']:
            return 'cafe_diamante'
        elif points >= self.tier_thresholds['cafe_oro']:
            return 'cafe_oro'
        elif points >= self.tier_thresholds['cafe_plata']:
            return 'cafe_plata'
        else:
            return 'cafe_bronze'
    
    def _get_next_tier_progress(self, current_tier: str, current_points: int) -> Dict[str, Any]:
        """Calcular progreso al siguiente nivel"""
        tiers = list(self.tier_thresholds.keys())
        current_index = tiers.index(current_tier)
        
        if current_index == len(tiers) - 1:  # Nivel máximo
            return {
                'current_tier': current_tier,
                'current_points': current_points,
                'next_tier': None,
                'points_needed': 0,
                'progress_percentage': 100.0
            }
        
        next_tier = tiers[current_index + 1]
        next_threshold = self.tier_thresholds[next_tier]
        current_threshold = self.tier_thresholds[current_tier]
        points_needed = next_threshold - current_points
        progress = ((current_points - current_threshold) / (next_threshold - current_threshold)) * 100
        
        return {
            'current_tier': current_tier,
            'current_points': current_points,
            'next_tier': next_tier,
            'points_needed': points_needed,
            'progress_percentage': min(progress, 100.0)
        }
    
    def _calculate_frequency_score(self, visits: int, days: int) -> float:
        """Calcular score por frecuencia de visitas"""
        if visits == 0:
            return 0
        
        visits_per_day = visits / max(days, 1)
        score = min(visits_per_day * 100, 100)
        return round(score, 2)
    
    def _calculate_amount_score(self, total_spent: float) -> float:
        """Calcular score por monto gastado"""
        if total_spent <= 0:
            return 0
        
        # 1 punto por cada $100 gastados, máximo 100 puntos
        score = min(total_spent / 100, 100)
        return round(score, 2)
    
    def _calculate_recency_score(self, last_visit: Optional[datetime]) -> float:
        """Calcular score por recencia de visita"""
        if not last_visit:
            return 0
        
        days_since_visit = (datetime.now() - last_visit).days
        
        if days_since_visit <= 1:
            return 100
        elif days_since_visit <= 7:
            return 80
        elif days_since_visit <= 30:
            return 45
        elif days_since_visit <= 90:
            return 20
        else:
            return 0
    
    def _calculate_variety_score(self, products: List[str]) -> float:
        """Calcular score por variedad de productos"""
        if not products:
            return 0
        
        unique_products = len(set(products))
        score = min(unique_products * 10, 100)
        return round(score, 2)
    
    def _calculate_referral_score(self, referrals: int) -> float:
        """Calcular score por referidos"""
        score = min(referrals * 20, 100)
        return round(score, 2)
    
    async def calculate_user_score(self, user_id: int) -> float:
        """Calcular score total del usuario"""
        # Mock de datos de usuario
        user_data = await self._get_user_data(user_id)
        
        frequency_score = self._calculate_frequency_score(
            user_data.get('total_visits', 0), 
            30
        )
        
        amount_score = self._calculate_amount_score(
            user_data.get('total_spent', 0)
        )
        
        recency_score = self._calculate_recency_score(
            user_data.get('last_visit')
        )
        
        variety_score = self._calculate_variety_score(
            user_data.get('favorite_products', [])
        )
        
        referral_score = self._calculate_referral_score(
            user_data.get('referral_count', 0)
        )
        
        # Ponderación de scores
        total_score = (
            frequency_score * 0.3 +
            amount_score * 0.3 +
            recency_score * 0.2 +
            variety_score * 0.1 +
            referral_score * 0.1
        )
        
        return round(total_score, 2)
    
    async def _get_user_data(self, user_id: int) -> Dict[str, Any]:
        """Obtener datos del usuario (mock)"""
        return {
            'total_visits': 10,
            'total_spent': 5000,
            'last_visit': datetime.now() - timedelta(days=5),
            'favorite_products': ["café", "pastel", "sandwich"],
            'referral_count': 2
        }
    
    def _generate_referral_code(self) -> str:
        """Generar código de referido único"""
        return ''.join(random.choices(string.ascii_uppercase + string.digits, k=8))
    
    def _generate_coupon_code(self) -> str:
        """Generar código de cupón único"""
        return ''.join(random.choices(string.ascii_uppercase + string.digits, k=10))
    
    def _validate_points_amount(self, amount: int) -> bool:
        """Validar cantidad de puntos"""
        return amount > 0
    
    def _validate_transaction_type(self, trans_type: str) -> bool:
        """Validar tipo de transacción"""
        valid_types = ['purchase', 'redemption', 'bonus', 'referral', 'adjustment']
        return trans_type in valid_types
    
    def _validate_transaction_balance(self, before: int, amount: int, after: int) -> bool:
        """Validar balance de transacción"""
        return before + amount == after
    
    def _calculate_points_from_purchase(self, amount: float) -> int:
        """Calcular puntos desde monto de compra"""
        # 1 punto por cada 100 unidades monetarias
        return int(amount / 100)
    
    def _apply_tier_multiplier(self, points: int, tier: str) -> int:
        """Aplicar multiplicador por nivel"""
        multiplier = self.tier_multipliers.get(tier, 1.0)
        return int(points * multiplier)
    
    def _validate_tier_upgrade(self, old_tier: str, new_tier: str) -> bool:
        """Validar subida de nivel"""
        tiers = list(self.tier_thresholds.keys())
        old_index = tiers.index(old_tier)
        new_index = tiers.index(new_tier)
        return new_index == old_index + 1
    
    def _calculate_tier_benefits(self, tier: str) -> Dict[str, Any]:
        """Calcular beneficios por nivel"""
        return self.tier_benefits.get(tier, {})
    
    def _validate_reward_redemption(self, user_points: int, reward_cost: int, user_tier: str, reward_tier: str) -> bool:
        """Validar canje de recompensa"""
        if user_points < reward_cost:
            return False
        
        tiers = list(self.tier_thresholds.keys())
        user_index = tiers.index(user_tier)
        reward_index = tiers.index(reward_tier)
        
        return user_index >= reward_index
    
    def _generate_transaction_description(self, trans_type: str, points: int, order_id: int = None, reward_name: str = None, reason: str = None) -> str:
        """Generar descripción de transacción"""
        if trans_type == 'purchase':
            return f"Compra #{order_id} - {points} puntos"
        elif trans_type == 'redemption':
            return f"Canje de {reward_name} - {abs(points)} puntos"
        elif trans_type == 'bonus':
            return f"Bonus {reason} - {points} puntos"
        else:
            return f"Transacción {trans_type} - {points} puntos"
    
    def _create_audit_trail(self, transaction_data: Dict[str, Any]) -> Dict[str, Any]:
        """Crear auditoría de transacción"""
        return {
            'timestamp': datetime.now(),
            'user_id': transaction_data.get('user_id'),
            'transaction_type': transaction_data.get('transaction_type'),
            'points_amount': transaction_data.get('points_amount'),
            'balance_before': transaction_data.get('balance_before'),
            'balance_after': transaction_data.get('balance_after')
        }
    
    def _calculate_transaction_summary(self, transactions: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Calcular resumen de transacciones"""
        total_earned = sum(t['points_amount'] for t in transactions if t['points_amount'] > 0)
        total_redeemed = abs(sum(t['points_amount'] for t in transactions if t['points_amount'] < 0))
        net_points = total_earned - total_redeemed
        
        return {
            'total_earned': total_earned,
            'total_redeemed': total_redeemed,
            'net_points': net_points,
            'total_transactions': len(transactions)
        } 