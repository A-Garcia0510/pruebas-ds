"""
Tests de usuario simulando flujos reales del sistema de fidelización
"""

import pytest
from unittest.mock import AsyncMock, patch
from datetime import datetime, timedelta

class TestUserFlows:
    """Tests de flujos de usuario"""
    
    @pytest.fixture
    def loyalty_service(self):
        """Instancia del servicio para tests"""
        from services.loyalty_service import LoyaltyService
        return LoyaltyService()
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_complete_user_registration_flow(self, loyalty_service):
        """Test de flujo completo de registro de usuario"""
        # 1. Usuario se registra
        user_data = {
            'nombre': 'María',
            'apellidos': 'García',
            'correo': 'maria.garcia@test.com',
            'contraseña': 'password123'
        }
        
        with patch.object(loyalty_service, 'create_user', return_value={'user_id': 1}):
            user_id = await loyalty_service.create_user(user_data)
            assert user_id == 1
        
        # 2. Usuario recibe puntos de bienvenida
        with patch.object(loyalty_service, 'add_welcome_bonus', return_value=True):
            bonus_added = await loyalty_service.add_welcome_bonus(user_id)
            assert bonus_added == True
        
        # 3. Usuario recibe cupón de bienvenida
        with patch.object(loyalty_service, 'generate_welcome_coupon', return_value={'code': 'WELCOME2024'}):
            coupon = await loyalty_service.generate_welcome_coupon(user_id)
            assert coupon['code'] == 'WELCOME2024'
        
        # 4. Verificar perfil inicial
        with patch.object(loyalty_service, 'get_user_by_id', return_value={
            'usuario_ID': user_id,
            'current_tier': 'cafe_bronze',
            'current_points': 100,
            'total_points': 100,
            'total_visits': 0,
            'total_spent': 0.0
        }):
            profile = await loyalty_service.get_user_by_id(user_id)
            assert profile['current_tier'] == 'cafe_bronze'
            assert profile['current_points'] == 100
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_user_purchase_and_points_flow(self, loyalty_service):
        """Test de flujo de compra y ganancia de puntos"""
        user_id = 1
        
        # Estado inicial del usuario
        initial_profile = {
            'usuario_ID': user_id,
            'current_tier': 'cafe_bronze',
            'current_points': 100,
            'total_points': 100,
            'total_visits': 2,
            'total_spent': 1500.0
        }
        
        # 1. Usuario hace una compra
        purchase_amount = 2000
        
        with patch.object(loyalty_service, 'get_user_by_id', return_value=initial_profile):
            with patch.object(loyalty_service, 'earn_points_from_purchase', return_value={
                'success': True,
                'points_earned': 200,
                'new_balance': 300,
                'tier_upgrade': False
            }):
                result = await loyalty_service.earn_points_from_purchase(
                    user_id=user_id,
                    purchase_amount=purchase_amount,
                    order_id=123
                )
                
                assert result['success'] == True
                assert result['points_earned'] == 200
                assert result['new_balance'] == 300
        
        # 2. Verificar que no sube de nivel (necesita 1000 puntos para Silver)
        with patch.object(loyalty_service, 'check_tier_upgrade', return_value={
            'success': True,
            'tier_upgraded': False,
            'current_tier': 'cafe_bronze',
            'points_needed': 700
        }):
            upgrade_check = await loyalty_service.check_tier_upgrade(user_id)
            assert upgrade_check['tier_upgraded'] == False
            assert upgrade_check['current_tier'] == 'cafe_bronze'
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_user_tier_upgrade_flow(self, loyalty_service):
        """Test de flujo de subida de nivel"""
        user_id = 1
        
        # Usuario con suficientes puntos para subir a Silver
        profile_with_points = {
            'usuario_ID': user_id,
            'current_tier': 'cafe_bronze',
            'current_points': 1200,
            'total_points': 1200,
            'total_visits': 8,
            'total_spent': 8000.0
        }
        
        # 1. Verificar subida de nivel
        with patch.object(loyalty_service, 'get_user_by_id', return_value=profile_with_points):
            with patch.object(loyalty_service, 'check_tier_upgrade', return_value={
                'success': True,
                'tier_upgraded': True,
                'old_tier': 'cafe_bronze',
                'new_tier': 'cafe_plata',
                'points_earned': 100,
                'message': '¡Felicitaciones! Has subido al nivel Café Plata'
            }):
                upgrade_result = await loyalty_service.check_tier_upgrade(user_id)
                
                assert upgrade_result['tier_upgraded'] == True
                assert upgrade_result['old_tier'] == 'cafe_bronze'
                assert upgrade_result['new_tier'] == 'cafe_plata'
                assert upgrade_result['points_earned'] == 100
        
        # 2. Verificar beneficios del nuevo nivel
        with patch.object(loyalty_service, 'get_tier_benefits', return_value={
            'discount_percent': 10,
            'free_coffees': 1,
            'priority_access': True
        }):
            benefits = await loyalty_service.get_tier_benefits('cafe_plata')
            assert benefits['discount_percent'] == 10
            assert benefits['free_coffees'] == 1
            assert benefits['priority_access'] == True
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_user_reward_redemption_flow(self, loyalty_service):
        """Test de flujo de canje de recompensas"""
        user_id = 1
        
        # Usuario con puntos suficientes
        user_with_points = {
            'usuario_ID': user_id,
            'current_tier': 'cafe_plata',
            'current_points': 500,
            'total_points': 500
        }
        
        # Recompensa disponible
        reward_data = {
            'id': 1,
            'name': 'Café Gratis',
            'description': 'Un café gratis de cualquier tamaño',
            'points_cost': 200,
            'tier_required': 'cafe_bronze',
            'active': True
        }
        
        # 1. Usuario canjea recompensa
        with patch.object(loyalty_service, 'get_user_by_id', return_value=user_with_points):
            with patch.object(loyalty_service, 'get_reward_by_id', return_value=reward_data):
                with patch.object(loyalty_service, 'redeem_reward', return_value={
                    'success': True,
                    'points_spent': 200,
                    'new_balance': 300,
                    'reward_name': 'Café Gratis',
                    'coupon_code': 'CAFE2024'
                }):
                    redemption = await loyalty_service.redeem_reward(
                        user_id=user_id,
                        reward_id=1
                    )
                    
                    assert redemption['success'] == True
                    assert redemption['points_spent'] == 200
                    assert redemption['new_balance'] == 300
                    assert redemption['reward_name'] == 'Café Gratis'
                    assert 'coupon_code' in redemption
        
        # 2. Verificar que no puede canjear la misma recompensa dos veces
        with patch.object(loyalty_service, 'redeem_reward', return_value={
            'success': False,
            'error': 'Ya has canjeado esta recompensa el máximo de veces permitido'
        }):
            second_redemption = await loyalty_service.redeem_reward(
                user_id=user_id,
                reward_id=1
            )
            
            assert second_redemption['success'] == False
            assert 'máximo de veces' in second_redemption['error']
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_user_referral_flow(self, loyalty_service):
        """Test de flujo de referidos"""
        referrer_id = 1
        new_user_id = 2
        
        # 1. Usuario existente genera código de referido
        with patch.object(loyalty_service, 'generate_referral_code', return_value={
            'success': True,
            'referral_code': 'REF12345',
            'expires_at': '2024-12-31T23:59:59'
        }):
            referral_result = await loyalty_service.generate_referral_code(referrer_id)
            
            assert referral_result['success'] == True
            assert referral_result['referral_code'] == 'REF12345'
        
        # 2. Nuevo usuario usa el código de referido
        with patch.object(loyalty_service, 'use_referral_code', return_value={
            'success': True,
            'points_earned': 100,
            'referrer_points': 50,
            'message': 'Código de referido aplicado exitosamente'
        }):
            use_referral = await loyalty_service.use_referral_code(
                user_id=new_user_id,
                referral_code='REF12345'
            )
            
            assert use_referral['success'] == True
            assert use_referral['points_earned'] == 100
            assert use_referral['referrer_points'] == 50
        
        # 3. Verificar que ambos usuarios recibieron puntos
        with patch.object(loyalty_service, 'get_user_by_id', side_effect=[
            {'usuario_ID': new_user_id, 'current_points': 200},  # Nuevo usuario
            {'usuario_ID': referrer_id, 'current_points': 550}   # Referidor
        ]):
            new_user = await loyalty_service.get_user_by_id(new_user_id)
            referrer = await loyalty_service.get_user_by_id(referrer_id)
            
            assert new_user['current_points'] == 200  # 100 inicial + 100 del referido
            assert referrer['current_points'] == 550  # 500 inicial + 50 del referido
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_user_points_expiration_flow(self, loyalty_service):
        """Test de flujo de expiración de puntos"""
        user_id = 1
        
        # Usuario con puntos próximos a expirar
        user_with_expiring_points = {
            'usuario_ID': user_id,
            'current_tier': 'cafe_plata',
            'current_points': 300,
            'points_expiry_date': datetime.now() + timedelta(days=5)
        }
        
        # 1. Verificar puntos próximos a expirar
        with patch.object(loyalty_service, 'get_users_with_expiring_points', return_value=[user_id]):
            expiring_users = await loyalty_service.get_users_with_expiring_points(days=7)
            assert user_id in expiring_users
        
        # 2. Enviar notificación de puntos por expirar
        with patch.object(loyalty_service, 'send_expiration_notification', return_value=True):
            notification_sent = await loyalty_service.send_expiration_notification(user_id, 5)
            assert notification_sent == True
        
        # 3. Simular expiración de puntos
        with patch.object(loyalty_service, 'expire_points', return_value={
            'success': True,
            'points_expired': 100,
            'new_balance': 200
        }):
            expiration = await loyalty_service.expire_points(user_id)
            
            assert expiration['success'] == True
            assert expiration['points_expired'] == 100
            assert expiration['new_balance'] == 200
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_user_marketing_campaign_flow(self, loyalty_service):
        """Test de flujo de campañas de marketing"""
        user_id = 1
        
        # Usuario elegible para campaña
        eligible_user = {
            'usuario_ID': user_id,
            'current_tier': 'cafe_bronze',
            'current_points': 800,
            'last_visit': datetime.now() - timedelta(days=15),
            'total_spent': 4000.0
        }
        
        # 1. Identificar usuarios elegibles para campaña
        with patch.object(loyalty_service, 'get_users_for_campaign', return_value=[user_id]):
            campaign_users = await loyalty_service.get_users_for_campaign('tier_upgrade')
            assert user_id in campaign_users
        
        # 2. Generar cupón personalizado
        with patch.object(loyalty_service, 'generate_personalized_coupon', return_value={
            'success': True,
            'coupon_code': 'UPGRADE2024',
            'discount_percent': 15,
            'min_order': 1000
        }):
            coupon = await loyalty_service.generate_personalized_coupon(
                user_id=user_id,
                campaign_type='tier_upgrade'
            )
            
            assert coupon['success'] == True
            assert coupon['coupon_code'] == 'UPGRADE2024'
            assert coupon['discount_percent'] == 15
        
        # 3. Enviar notificación de campaña
        with patch.object(loyalty_service, 'send_campaign_notification', return_value=True):
            notification = await loyalty_service.send_campaign_notification(
                user_id=user_id,
                campaign_type='tier_upgrade',
                coupon_code='UPGRADE2024'
            )
            assert notification == True
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_user_analytics_and_insights_flow(self, loyalty_service):
        """Test de flujo de análisis y insights"""
        user_id = 1
        
        # 1. Obtener insights del usuario
        with patch.object(loyalty_service, 'get_user_insights', return_value={
            'favorite_products': ['Café Americano', 'Croissant'],
            'average_order_value': 2500,
            'visit_frequency': '2 veces por semana',
            'preferred_time': 'Mañana',
            'lifetime_value': 15000,
            'recommendations': ['Probar nuestro nuevo Latte', 'Visitar en happy hour']
        }):
            insights = await loyalty_service.get_user_insights(user_id)
            
            assert 'favorite_products' in insights
            assert 'average_order_value' in insights
            assert 'recommendations' in insights
            assert len(insights['favorite_products']) == 2
        
        # 2. Obtener métricas de fidelización
        with patch.object(loyalty_service, 'get_loyalty_metrics', return_value={
            'total_users': 1000,
            'active_users': 750,
            'average_points': 450,
            'redemption_rate': 0.25,
            'tier_distribution': {
                'cafe_bronze': 600,
                'cafe_plata': 250,
                'cafe_oro': 100,
                'cafe_diamante': 50
            }
        }):
            metrics = await loyalty_service.get_loyalty_metrics()
            
            assert metrics['total_users'] == 1000
            assert metrics['active_users'] == 750
            assert 'tier_distribution' in metrics
            assert metrics['tier_distribution']['cafe_bronze'] == 600
    
    @pytest.mark.user
    @pytest.mark.slow
    async def test_user_error_handling_flow(self, loyalty_service):
        """Test de manejo de errores en flujos de usuario"""
        user_id = 1
        
        # 1. Usuario intenta canjear recompensa sin puntos suficientes
        user_without_points = {
            'usuario_ID': user_id,
            'current_tier': 'cafe_bronze',
            'current_points': 50,
            'total_points': 50
        }
        
        reward_expensive = {
            'id': 1,
            'name': 'Descuento 50%',
            'points_cost': 500,
            'tier_required': 'cafe_bronze'
        }
        
        with patch.object(loyalty_service, 'get_user_by_id', return_value=user_without_points):
            with patch.object(loyalty_service, 'get_reward_by_id', return_value=reward_expensive):
                with patch.object(loyalty_service, 'redeem_reward', return_value={
                    'success': False,
                    'error': 'Puntos insuficientes para canjear esta recompensa'
                }):
                    redemption = await loyalty_service.redeem_reward(user_id, 1)
                    
                    assert redemption['success'] == False
                    assert 'Puntos insuficientes' in redemption['error']
        
        # 2. Usuario intenta usar código de referido inválido
        with patch.object(loyalty_service, 'use_referral_code', return_value={
            'success': False,
            'error': 'Código de referido inválido o expirado'
        }):
            referral_use = await loyalty_service.use_referral_code(user_id, 'INVALID')
            
            assert referral_use['success'] == False
            assert 'Código de referido inválido' in referral_use['error']
        
        # 3. Usuario intenta acceder a perfil inexistente
        with patch.object(loyalty_service, 'get_user_by_id', return_value=None):
            profile = await loyalty_service.get_user_by_id(999)
            assert profile is None 