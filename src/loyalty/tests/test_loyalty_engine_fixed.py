"""
Tests unitarios corregidos para el motor de scoring de fidelización
"""

import pytest
from unittest.mock import AsyncMock, patch, MagicMock
from datetime import datetime, timedelta

class TestLoyaltyEngineFixed:
    """Tests corregidos para el motor de scoring de fidelización"""
    
    @pytest.fixture
    def loyalty_service(self):
        """Instancia del servicio para tests"""
        from services.loyalty_service import LoyaltyService
        return LoyaltyService()
    
    @pytest.mark.asyncio
    @pytest.mark.unit
    async def test_calculate_user_score_integration_fixed(self, loyalty_service):
        """Test de integración del cálculo de score de usuario - VERSIÓN CORREGIDA"""
        # Mock de usuario de la base de datos
        mock_user = MagicMock()
        mock_user.loyalty_user_ID = 1
        mock_user.usuario_ID = 1
        mock_user.total_points = 1000
        mock_user.current_points = 1000
        mock_user.current_tier = 'cafe_bronze'
        mock_user.score = 75.0

        # Mock de configuración
        mock_config = '{"frequency": 0.25, "amount": 0.30, "recency": 0.20, "variety": 0.15, "referral": 0.10}'

        async def return_80(*args, **kwargs): return 80.0
        async def return_70(*args, **kwargs): return 70.0
        async def return_60(*args, **kwargs): return 60.0
        async def return_50(*args, **kwargs): return 50.0
        async def return_40(*args, **kwargs): return 40.0

        with patch.object(loyalty_service, 'get_user_by_id', return_value=mock_user), \
             patch.object(loyalty_service, '_get_config_value', return_value=mock_config), \
             patch.object(loyalty_service, '_calculate_frequency_score', side_effect=return_80), \
             patch.object(loyalty_service, '_calculate_amount_score', side_effect=return_70), \
             patch.object(loyalty_service, '_calculate_recency_score', side_effect=return_60), \
             patch.object(loyalty_service, '_calculate_variety_score', side_effect=return_50), \
             patch.object(loyalty_service, '_calculate_referral_score', side_effect=return_40), \
             patch.object(loyalty_service, 'update_user', return_value=mock_user):

            score = await loyalty_service.calculate_user_score(1)
            assert score == 64.5  # Score esperado basado en los mocks 