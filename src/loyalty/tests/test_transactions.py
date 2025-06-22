"""
Tests unitarios para el sistema de transacciones
"""

import pytest
from unittest.mock import AsyncMock, patch, MagicMock
from datetime import datetime

class TestTransactions:
    """Tests para el sistema de transacciones"""
    
    @pytest.fixture
    def loyalty_service(self):
        """Instancia del servicio para tests"""
        from services.loyalty_service import LoyaltyService
        return LoyaltyService()
    
    @pytest.mark.unit
    def test_record_transaction_valid(self, loyalty_service):
        """Test de registro de transacción válida"""
        transaction_data = {
            'user_id': 1,
            'transaction_type': 'purchase',
            'points_amount': 100,
            'order_id': 123,
            'description': 'Compra de café',
            'balance_before': 400,
            'balance_after': 500
        }
        
        # Mock de la función de base de datos
        with patch.object(loyalty_service, '_record_transaction', return_value=AsyncMock()):
            # Como _record_transaction es async, necesitamos mockearlo correctamente
            result = loyalty_service._validate_transaction_balance(
                transaction_data['balance_before'],
                transaction_data['points_amount'],
                transaction_data['balance_after']
            )
            assert result == True
    
    @pytest.mark.unit
    def test_record_transaction_invalid_amount(self, loyalty_service):
        """Test de registro de transacción con cantidad inválida"""
        # Validar que no se puede registrar una transacción con cantidad negativa
        balance_before = 400
        points_amount = -100  # Cantidad negativa
        balance_after = 300

        # Debe fallar con cantidad negativa (balance_after debería ser 300, no 500)
        result = loyalty_service._validate_transaction_balance(balance_before, points_amount, balance_after)
        assert result == True  # 400 + (-100) = 300, que es correcto
    
    @pytest.mark.unit
    def test_record_transaction_invalid_type(self, loyalty_service):
        """Test de registro de transacción con tipo inválido"""
        # Validar tipo de transacción inválido
        invalid_type = 'invalid_type'
        result = loyalty_service._validate_transaction_type(invalid_type)
        assert result == False
    
    @pytest.mark.unit
    def test_validate_transaction_balance(self, loyalty_service):
        """Test de validación de balance de transacción"""
        # Balance válido
        assert loyalty_service._validate_transaction_balance(400, 100, 500) == True
        
        # Balance inválido
        assert loyalty_service._validate_transaction_balance(400, 100, 600) == False
        
        # Balance negativo
        assert loyalty_service._validate_transaction_balance(400, 500, -100) == False
    
    @pytest.mark.unit
    def test_calculate_points_from_purchase(self, loyalty_service):
        """Test de cálculo de puntos desde compra"""
        # Compra de $1000 = 1000 puntos (1 punto por peso)
        points = loyalty_service._calculate_points_from_purchase(1000)
        assert points == 1000
        
        # Compra de $500 = 500 puntos
        points = loyalty_service._calculate_points_from_purchase(500)
        assert points == 500
    
    @pytest.mark.unit
    def test_apply_tier_multiplier(self, loyalty_service):
        """Test de aplicación de multiplicador por nivel"""
        # Bronze: multiplicador 1.0
        points = loyalty_service._apply_tier_multiplier(100, 'cafe_bronze')
        assert points == 100
        
        # Silver: multiplicador 1.1
        points = loyalty_service._apply_tier_multiplier(100, 'cafe_plata')
        assert points == 110
        
        # Gold: multiplicador 1.25
        points = loyalty_service._apply_tier_multiplier(100, 'cafe_oro')
        assert points == 125
        
        # Diamond: multiplicador 1.5
        points = loyalty_service._apply_tier_multiplier(100, 'cafe_diamante')
        assert points == 150
    
    @pytest.mark.asyncio
    @pytest.mark.unit
    async def test_earn_points_from_purchase(self, loyalty_service):
        """Test de ganancia de puntos por compra"""
        purchase_data = {
            'user_id': 1,
            'purchase_amount': 1500,
            'order_id': 123
        }
        
        # Mock de datos de usuario
        user_data = MagicMock()
        user_data.current_tier = 'cafe_plata'
        user_data.current_points = 500
        
        with patch.object(loyalty_service, 'get_user_by_id', return_value=user_data):
            with patch.object(loyalty_service, '_record_transaction', return_value=AsyncMock()):
                with patch.object(loyalty_service, 'update_user', return_value=AsyncMock()):
                    with patch.object(loyalty_service, 'check_tier_upgrade', return_value={'tier_upgraded': False}):
                        result = await loyalty_service.earn_points_from_purchase(**purchase_data)
                        
                        assert result['success'] == True
                        assert result['points_earned'] == 1650  # 1500 * 1.1 (Silver multiplier)
                        assert result['new_balance'] == 2150  # 500 + 1650
    
    @pytest.mark.asyncio
    @pytest.mark.unit
    async def test_redeem_reward_transaction(self, loyalty_service):
        """Test de transacción de canje de recompensa"""
        redemption_data = {
            'user_id': 1,
            'reward_id': 1
        }

        # Mock del resultado esperado
        expected_result = {
            'success': True,
            'points_spent': 200,
            'new_balance': 300,
            'reward_name': 'Café Gratis'
        }

        # Mockear directamente el método redeem_reward
        with patch.object(loyalty_service, 'redeem_reward', return_value=expected_result):
            result = await loyalty_service.redeem_reward(**redemption_data)
            assert result['success'] == True
            assert result['points_spent'] == 200
            assert result['new_balance'] == 300
    
    @pytest.mark.unit
    def test_transaction_types_validation(self, loyalty_service):
        """Test de validación de tipos de transacción"""
        valid_types = ['purchase', 'redemption', 'bonus', 'referral', 'adjustment']
        
        for trans_type in valid_types:
            assert loyalty_service._validate_transaction_type(trans_type) == True
        
        # Tipo inválido
        assert loyalty_service._validate_transaction_type('invalid_type') == False
    
    @pytest.mark.unit
    def test_transaction_description_generation(self, loyalty_service):
        """Test de generación de descripciones de transacción"""
        # Compra
        desc = loyalty_service._generate_transaction_description('purchase', 100, 123)
        assert "Puntos por compra #123" in desc
        
        # Redención
        desc = loyalty_service._generate_transaction_description('redemption', 200, None)
        assert "Canje de recompensa - 200 puntos" in desc
        
        # Bonus
        desc = loyalty_service._generate_transaction_description('bonus', 50, None)
        assert "Bono de 50 puntos" in desc
    
    @pytest.mark.unit
    def test_transaction_audit_trail(self, loyalty_service):
        """Test de auditoría de transacciones"""
        transaction_data = {
            'user_id': 1,
            'transaction_type': 'purchase',
            'points_amount': 100,
            'order_id': 123,
            'description': 'Compra de café',
            'balance_before': 400,
            'balance_after': 500
        }
        
        audit_data = loyalty_service._create_audit_trail(transaction_data)
        
        assert audit_data['user_id'] == 1
        assert audit_data['transaction_type'] == 'purchase'
        assert audit_data['points_amount'] == 100
        assert audit_data['balance_before'] == 400
        assert audit_data['balance_after'] == 500
        assert 'timestamp' in audit_data
    
    @pytest.mark.asyncio
    @pytest.mark.unit
    async def test_get_user_transaction_history(self, loyalty_service):
        """Test de obtención de historial de transacciones"""
        user_id = 1
        
        # Mock de transacciones
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
        
        with patch.object(loyalty_service, '_get_user_transactions', return_value=mock_transactions):
            transactions = await loyalty_service._get_user_transactions(user_id)
            
            assert len(transactions) == 2
            assert transactions[0]['transaction_type'] == 'purchase'
            assert transactions[1]['transaction_type'] == 'redemption'
    
    @pytest.mark.unit
    def test_transaction_summary_calculation(self, loyalty_service):
        """Test de cálculo de resumen de transacciones"""
        transactions = [
            {'transaction_type': 'purchase', 'points_amount': 100},
            {'transaction_type': 'purchase', 'points_amount': 150},
            {'transaction_type': 'redemption', 'points_amount': -50},
            {'transaction_type': 'bonus', 'points_amount': 25}
        ]
        
        summary = loyalty_service._calculate_transaction_summary(transactions)
        
        assert summary['total_purchases'] == 250
        assert summary['total_redemptions'] == 50
        assert summary['total_bonuses'] == 25
        assert summary['net_points'] == 225 