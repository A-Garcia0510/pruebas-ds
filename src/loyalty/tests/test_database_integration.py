"""
Tests de integración con la base de datos
"""

import pytest
from unittest.mock import AsyncMock, patch
from datetime import datetime, timedelta

class TestDatabaseIntegration:
    """Tests de integración con base de datos"""
    
    @pytest.fixture
    def loyalty_service(self):
        """Instancia del servicio para tests"""
        from services.loyalty_service import LoyaltyService
        return LoyaltyService()
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_database_connection(self, loyalty_service):
        """Test de conexión a la base de datos"""
        with patch('utils.database.get_db') as mock_db:
            mock_connection = AsyncMock()
            mock_db.return_value = mock_connection
            
            # Verificar que la conexión se establece correctamente
            connection = await loyalty_service._get_db_connection()
            assert connection is not None
            mock_db.assert_called_once()
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_user_data_retrieval(self, loyalty_service):
        """Test de recuperación de datos de usuario desde BD"""
        user_id = 1
        
        # Mock de datos de usuario desde BD
        mock_user_data = {
            'usuario_ID': user_id,
            'nombre': 'Juan',
            'apellidos': 'Pérez',
            'correo': 'juan.perez@test.com',
            'current_tier': 'cafe_bronze',
            'current_points': 500,
            'total_points': 500,
            'total_visits': 5,
            'total_spent': 2500.0,
            'status': 'activo'
        }
        
        with patch.object(loyalty_service, '_execute_query', return_value=mock_user_data):
            user_data = await loyalty_service.get_user_by_id(user_id)
            
            assert user_data is not None
            assert user_data['usuario_ID'] == user_id
            assert user_data['current_tier'] == 'cafe_bronze'
            assert user_data['current_points'] == 500
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_transaction_recording(self, loyalty_service):
        """Test de registro de transacciones en BD"""
        transaction_data = {
            'user_id': 1,
            'transaction_type': 'purchase',
            'points_amount': 100,
            'order_id': 123,
            'description': 'Compra de café',
            'balance_before': 400,
            'balance_after': 500
        }
        
        with patch.object(loyalty_service, '_execute_query', return_value=True):
            result = await loyalty_service._record_transaction(**transaction_data)
            assert result == True
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_points_update(self, loyalty_service):
        """Test de actualización de puntos en BD"""
        user_id = 1
        new_points = 600
        
        with patch.object(loyalty_service, '_execute_query', return_value=True):
            result = await loyalty_service._update_user_points(user_id, new_points)
            assert result == True
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_tier_update(self, loyalty_service):
        """Test de actualización de nivel en BD"""
        user_id = 1
        new_tier = 'cafe_plata'
        
        with patch.object(loyalty_service, '_execute_query', return_value=True):
            result = await loyalty_service._update_user_tier(user_id, new_tier)
            assert result == True
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_reward_creation(self, loyalty_service):
        """Test de creación de recompensa en BD"""
        reward_data = {
            'name': 'Café Gratis',
            'description': 'Un café gratis de cualquier tamaño',
            'points_cost': 200,
            'tier_required': 'cafe_bronze',
            'active': True
        }
        
        with patch.object(loyalty_service, '_execute_query', return_value={'id': 1}):
            reward_id = await loyalty_service._create_reward(reward_data)
            assert reward_id == 1
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_coupon_creation(self, loyalty_service):
        """Test de creación de cupón en BD"""
        coupon_data = {
            'user_id': 1,
            'code': 'TEST123',
            'discount_type': 'percentage',
            'discount_value': 15.0,
            'min_order_amount': 1000.0,
            'max_uses': 1,
            'active': True
        }
        
        with patch.object(loyalty_service, '_execute_query', return_value={'id': 1}):
            coupon_id = await loyalty_service._create_coupon(coupon_data)
            assert coupon_id == 1
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_referral_code_creation(self, loyalty_service):
        """Test de creación de código de referido en BD"""
        referral_data = {
            'user_id': 1,
            'code': 'REF12345',
            'expires_at': datetime.now() + timedelta(days=30),
            'active': True
        }
        
        with patch.object(loyalty_service, '_execute_query', return_value={'id': 1}):
            referral_id = await loyalty_service._create_referral_code(referral_data)
            assert referral_id == 1
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_transaction_history_retrieval(self, loyalty_service):
        """Test de recuperación de historial de transacciones desde BD"""
        user_id = 1
        
        # Mock de transacciones desde BD
        mock_transactions = [
            {
                'id': 1,
                'transaction_type': 'purchase',
                'points_amount': 100,
                'description': 'Compra de café',
                'created_at': datetime.now()
            },
            {
                'id': 2,
                'transaction_type': 'redemption',
                'points_amount': -50,
                'description': 'Canje de recompensa',
                'created_at': datetime.now()
            }
        ]
        
        with patch.object(loyalty_service, '_execute_query', return_value=mock_transactions):
            transactions = await loyalty_service.get_user_transaction_history(user_id)
            
            assert len(transactions) == 2
            assert transactions[0]['transaction_type'] == 'purchase'
            assert transactions[1]['transaction_type'] == 'redemption'
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_rewards_list_retrieval(self, loyalty_service):
        """Test de recuperación de lista de recompensas desde BD"""
        # Mock de recompensas desde BD
        mock_rewards = [
            {
                'id': 1,
                'name': 'Café Gratis',
                'description': 'Un café gratis de cualquier tamaño',
                'points_cost': 200,
                'tier_required': 'cafe_bronze',
                'active': True
            },
            {
                'id': 2,
                'name': 'Descuento 15%',
                'description': '15% de descuento en tu próxima compra',
                'points_cost': 500,
                'tier_required': 'cafe_plata',
                'active': True
            }
        ]
        
        with patch.object(loyalty_service, '_execute_query', return_value=mock_rewards):
            rewards = await loyalty_service.get_available_rewards()
            
            assert len(rewards) == 2
            assert rewards[0]['name'] == 'Café Gratis'
            assert rewards[1]['name'] == 'Descuento 15%'
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_user_statistics_retrieval(self, loyalty_service):
        """Test de recuperación de estadísticas de usuario desde BD"""
        user_id = 1
        
        # Mock de estadísticas desde BD
        mock_stats = {
            'total_visits': 15,
            'total_spent': 7500.0,
            'average_order_value': 500.0,
            'favorite_products': ['Café Americano', 'Croissant'],
            'last_visit': datetime.now() - timedelta(days=2)
        }
        
        with patch.object(loyalty_service, '_execute_query', return_value=mock_stats):
            stats = await loyalty_service._get_user_statistics(user_id)
            
            assert stats['total_visits'] == 15
            assert stats['total_spent'] == 7500.0
            assert len(stats['favorite_products']) == 2
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_tier_benefits_retrieval(self, loyalty_service):
        """Test de recuperación de beneficios por nivel desde BD"""
        tier = 'cafe_plata'
        
        # Mock de beneficios desde BD
        mock_benefits = {
            'discount_percent': 10,
            'free_coffees': 1,
            'priority_access': True,
            'birthday_bonus': 200,
            'referral_bonus': 50
        }
        
        with patch.object(loyalty_service, '_execute_query', return_value=mock_benefits):
            benefits = await loyalty_service._get_tier_benefits(tier)
            
            assert benefits['discount_percent'] == 10
            assert benefits['free_coffees'] == 1
            assert benefits['priority_access'] == True
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_expiring_points_retrieval(self, loyalty_service):
        """Test de recuperación de usuarios con puntos por expirar desde BD"""
        # Mock de usuarios con puntos por expirar desde BD
        mock_users = [
            {'user_id': 1, 'points_expiring': 100, 'expiry_date': datetime.now() + timedelta(days=5)},
            {'user_id': 2, 'points_expiring': 50, 'expiry_date': datetime.now() + timedelta(days=3)}
        ]
        
        with patch.object(loyalty_service, '_execute_query', return_value=mock_users):
            users = await loyalty_service._get_users_with_expiring_points(days=7)
            
            assert len(users) == 2
            assert users[0]['user_id'] == 1
            assert users[1]['user_id'] == 2
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_loyalty_metrics_retrieval(self, loyalty_service):
        """Test de recuperación de métricas de fidelización desde BD"""
        # Mock de métricas desde BD
        mock_metrics = {
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
        }
        
        with patch.object(loyalty_service, '_execute_query', return_value=mock_metrics):
            metrics = await loyalty_service._get_loyalty_metrics()
            
            assert metrics['total_users'] == 1000
            assert metrics['active_users'] == 750
            assert 'tier_distribution' in metrics
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_database_error_handling(self, loyalty_service):
        """Test de manejo de errores de base de datos"""
        user_id = 1
        
        # Simular error de conexión
        with patch.object(loyalty_service, '_execute_query', side_effect=Exception("Database connection error")):
            with pytest.raises(Exception):
                await loyalty_service.get_user_by_id(user_id)
        
        # Simular error de consulta
        with patch.object(loyalty_service, '_execute_query', side_effect=Exception("Query execution error")):
            with pytest.raises(Exception):
                await loyalty_service._record_transaction(
                    user_id=1,
                    transaction_type='purchase',
                    points_amount=100,
                    order_id=123,
                    description='Test',
                    balance_before=400,
                    balance_after=500
                )
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_database_transaction_rollback(self, loyalty_service):
        """Test de rollback de transacciones en caso de error"""
        user_id = 1
        
        # Simular transacción que falla en el medio
        with patch.object(loyalty_service, '_execute_query', side_effect=[
            True,  # Primera operación exitosa
            Exception("Error en segunda operación")  # Segunda operación falla
        ]):
            with pytest.raises(Exception):
                await loyalty_service._execute_transaction([
                    {'query': 'UPDATE users SET points = points + 100 WHERE id = 1'},
                    {'query': 'INSERT INTO transactions (user_id, amount) VALUES (1, 100)'}
                ])
    
    @pytest.mark.database
    @pytest.mark.integration
    async def test_database_connection_pool(self, loyalty_service):
        """Test de pool de conexiones de base de datos"""
        # Simular múltiples conexiones simultáneas
        connections = []
        
        with patch('utils.database.get_db') as mock_db:
            mock_connection = AsyncMock()
            mock_db.return_value = mock_connection
            
            # Crear múltiples conexiones
            for i in range(5):
                connection = await loyalty_service._get_db_connection()
                connections.append(connection)
            
            # Verificar que se crearon las conexiones
            assert len(connections) == 5
            assert mock_db.call_count == 5 