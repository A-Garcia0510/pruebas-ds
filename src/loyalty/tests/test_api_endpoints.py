"""
Tests de integración para endpoints de la API de fidelización
"""

import pytest
from unittest.mock import AsyncMock, patch
from fastapi.testclient import TestClient
from datetime import datetime, timedelta

class TestAPIEndpoints:
    """Tests para endpoints de la API"""
    
    @pytest.fixture
    def client(self):
        """Cliente de test para FastAPI"""
        from main import app
        return TestClient(app)
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_get_loyalty_profile_success(self, client):
        """Test de obtención exitosa del perfil de fidelización"""
        user_id = 1
        
        # Mock de datos de usuario
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
        
        with patch('routes.loyalty_routes.get_user_by_id', return_value=mock_user_data):
            response = client.get(f"/api/v1/loyalty/profile/{user_id}")
            
            assert response.status_code == 200
            data = response.json()
            assert data['user_id'] == user_id
            assert data['current_tier'] == 'cafe_bronze'
            assert data['current_points'] == 500
            assert 'next_tier_progress' in data
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_get_loyalty_profile_user_not_found(self, client):
        """Test de perfil de fidelización con usuario inexistente"""
        user_id = 999
        
        with patch('routes.loyalty_routes.get_user_by_id', return_value=None):
            response = client.get(f"/api/v1/loyalty/profile/{user_id}")
            
            assert response.status_code == 404
            data = response.json()
            assert 'error' in data
            assert 'Usuario no encontrado' in data['error']
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_earn_points_success(self, client):
        """Test de ganancia exitosa de puntos"""
        earn_data = {
            'user_id': 1,
            'purchase_amount': 1500,
            'order_id': 123
        }
        
        # Mock de respuesta exitosa
        mock_response = {
            'success': True,
            'points_earned': 165,
            'new_balance': 665,
            'tier_upgrade': False
        }
        
        with patch('routes.loyalty_routes.earn_points_from_purchase', return_value=mock_response):
            response = client.post("/api/v1/loyalty/earn-points", json=earn_data)
            
            assert response.status_code == 200
            data = response.json()
            assert data['success'] == True
            assert data['points_earned'] == 165
            assert data['new_balance'] == 665
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_earn_points_invalid_data(self, client):
        """Test de ganancia de puntos con datos inválidos"""
        # Datos inválidos - monto negativo
        invalid_data = {
            'user_id': 1,
            'purchase_amount': -100,
            'order_id': 123
        }
        
        response = client.post("/api/v1/loyalty/earn-points", json=invalid_data)
        
        assert response.status_code == 400
        data = response.json()
        assert 'error' in data
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_redeem_reward_success(self, client):
        """Test de canje exitoso de recompensa"""
        redeem_data = {
            'user_id': 1,
            'reward_id': 1
        }
        
        # Mock de respuesta exitosa
        mock_response = {
            'success': True,
            'points_spent': 200,
            'new_balance': 300,
            'reward_name': 'Café Gratis'
        }
        
        with patch('routes.loyalty_routes.redeem_reward', return_value=mock_response):
            response = client.post("/api/v1/loyalty/redeem-reward", json=redeem_data)
            
            assert response.status_code == 200
            data = response.json()
            assert data['success'] == True
            assert data['points_spent'] == 200
            assert data['reward_name'] == 'Café Gratis'
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_redeem_reward_insufficient_points(self, client):
        """Test de canje con puntos insuficientes"""
        redeem_data = {
            'user_id': 1,
            'reward_id': 1
        }
        
        # Mock de error por puntos insuficientes
        mock_response = {
            'success': False,
            'error': 'Puntos insuficientes para canjear esta recompensa'
        }
        
        with patch('routes.loyalty_routes.redeem_reward', return_value=mock_response):
            response = client.post("/api/v1/loyalty/redeem-reward", json=redeem_data)
            
            assert response.status_code == 400
            data = response.json()
            assert data['success'] == False
            assert 'Puntos insuficientes' in data['error']
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_get_rewards_list(self, client):
        """Test de obtención de lista de recompensas"""
        # Mock de lista de recompensas
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
        
        with patch('routes.loyalty_routes.get_available_rewards', return_value=mock_rewards):
            response = client.get("/api/v1/loyalty/rewards")
            
            assert response.status_code == 200
            data = response.json()
            assert len(data) == 2
            assert data[0]['name'] == 'Café Gratis'
            assert data[1]['name'] == 'Descuento 15%'
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_generate_referral_code_success(self, client):
        """Test de generación exitosa de código de referido"""
        referral_data = {
            'user_id': 1
        }
        
        # Mock de respuesta exitosa
        mock_response = {
            'success': True,
            'referral_code': 'ABC12345',
            'expires_at': '2024-12-31T23:59:59'
        }
        
        with patch('routes.loyalty_routes.generate_referral_code', return_value=mock_response):
            response = client.post("/api/v1/loyalty/referral", json=referral_data)
            
            assert response.status_code == 200
            data = response.json()
            assert data['success'] == True
            assert 'referral_code' in data
            assert len(data['referral_code']) == 8
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_use_referral_code_success(self, client):
        """Test de uso exitoso de código de referido"""
        use_referral_data = {
            'user_id': 2,
            'referral_code': 'ABC12345'
        }
        
        # Mock de respuesta exitosa
        mock_response = {
            'success': True,
            'points_earned': 100,
            'referrer_points': 50,
            'message': 'Código de referido aplicado exitosamente'
        }
        
        with patch('routes.loyalty_routes.use_referral_code', return_value=mock_response):
            response = client.post("/api/v1/loyalty/use-referral", json=use_referral_data)
            
            assert response.status_code == 200
            data = response.json()
            assert data['success'] == True
            assert data['points_earned'] == 100
            assert data['referrer_points'] == 50
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_use_referral_code_invalid(self, client):
        """Test de uso de código de referido inválido"""
        use_referral_data = {
            'user_id': 2,
            'referral_code': 'INVALID'
        }
        
        # Mock de error por código inválido
        mock_response = {
            'success': False,
            'error': 'Código de referido inválido o expirado'
        }
        
        with patch('routes.loyalty_routes.use_referral_code', return_value=mock_response):
            response = client.post("/api/v1/loyalty/use-referral", json=use_referral_data)
            
            assert response.status_code == 400
            data = response.json()
            assert data['success'] == False
            assert 'Código de referido inválido' in data['error']
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_get_transaction_history(self, client):
        """Test de obtención de historial de transacciones"""
        user_id = 1
        
        # Mock de historial de transacciones
        mock_transactions = [
            {
                'id': 1,
                'transaction_type': 'purchase',
                'points_amount': 100,
                'description': 'Compra de café',
                'created_at': '2024-01-15T10:30:00'
            },
            {
                'id': 2,
                'transaction_type': 'redemption',
                'points_amount': -50,
                'description': 'Canje de recompensa',
                'created_at': '2024-01-14T15:45:00'
            }
        ]
        
        with patch('routes.loyalty_routes.get_user_transaction_history', return_value=mock_transactions):
            response = client.get(f"/api/v1/loyalty/transactions/{user_id}")
            
            assert response.status_code == 200
            data = response.json()
            assert len(data) == 2
            assert data[0]['transaction_type'] == 'purchase'
            assert data[1]['transaction_type'] == 'redemption'
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_check_tier_upgrade_success(self, client):
        """Test de verificación exitosa de subida de nivel"""
        upgrade_data = {
            'user_id': 1
        }
        
        # Mock de respuesta exitosa
        mock_response = {
            'success': True,
            'tier_upgraded': True,
            'old_tier': 'cafe_bronze',
            'new_tier': 'cafe_plata',
            'points_earned': 100,
            'message': '¡Felicitaciones! Has subido al nivel Café Plata'
        }
        
        with patch('routes.loyalty_routes.check_tier_upgrade', return_value=mock_response):
            response = client.post("/api/v1/loyalty/check-tier-upgrade", json=upgrade_data)
            
            assert response.status_code == 200
            data = response.json()
            assert data['success'] == True
            assert data['tier_upgraded'] == True
            assert data['old_tier'] == 'cafe_bronze'
            assert data['new_tier'] == 'cafe_plata'
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_check_tier_upgrade_no_upgrade(self, client):
        """Test de verificación sin subida de nivel"""
        upgrade_data = {
            'user_id': 1
        }
        
        # Mock de respuesta sin subida
        mock_response = {
            'success': True,
            'tier_upgraded': False,
            'current_tier': 'cafe_bronze',
            'points_needed': 500,
            'message': 'Necesitas 500 puntos más para subir al siguiente nivel'
        }
        
        with patch('routes.loyalty_routes.check_tier_upgrade', return_value=mock_response):
            response = client.post("/api/v1/loyalty/check-tier-upgrade", json=upgrade_data)
            
            assert response.status_code == 200
            data = response.json()
            assert data['success'] == True
            assert data['tier_upgraded'] == False
            assert 'Necesitas' in data['message']
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_health_check(self, client):
        """Test de verificación de salud de la API"""
        response = client.get("/health")
        
        assert response.status_code == 200
        data = response.json()
        assert data['status'] == 'healthy'
        assert 'timestamp' in data
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_api_documentation(self, client):
        """Test de documentación de la API"""
        response = client.get("/docs")
        
        assert response.status_code == 200
        assert 'text/html' in response.headers['content-type']
    
    @pytest.mark.integration
    @pytest.mark.api
    def test_openapi_schema(self, client):
        """Test del esquema OpenAPI"""
        response = client.get("/openapi.json")
        
        assert response.status_code == 200
        data = response.json()
        assert 'openapi' in data
        assert 'paths' in data
        assert '/api/v1/loyalty/profile/{user_id}' in data['paths'] 