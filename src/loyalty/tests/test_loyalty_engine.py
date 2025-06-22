"""
Tests unitarios para el motor de scoring de fidelización
"""

import pytest
from unittest.mock import AsyncMock, patch, MagicMock
from datetime import datetime, timedelta

class TestLoyaltyEngine:
    """Tests para el motor de scoring de fidelización"""
    
    @pytest.fixture
    def loyalty_service(self):
        """Instancia del servicio para tests"""
        from services.loyalty_service import LoyaltyService
        return LoyaltyService()
    
    @pytest.mark.unit
    def test_tier_thresholds_initialization(self, loyalty_service):
        """Test de inicialización de umbrales de nivel"""
        expected_thresholds = {
            'cafe_bronze': 0,
            'cafe_plata': 1000,
            'cafe_oro': 5000,
            'cafe_diamante': 15000
        }
        
        assert loyalty_service.tier_thresholds == expected_thresholds
    
    @pytest.mark.unit
    def test_get_tier_from_score_bronze(self, loyalty_service):
        """Test de asignación de nivel Bronze"""
        # Usuario con 500 puntos debe estar en Bronze
        tier = loyalty_service._get_tier_from_score(500)
        assert tier == 'cafe_bronze'
        
        # Usuario con 999 puntos debe estar en Bronze
        tier = loyalty_service._get_tier_from_score(999)
        assert tier == 'cafe_bronze'
    
    @pytest.mark.unit
    def test_get_tier_from_score_silver(self, loyalty_service):
        """Test de asignación de nivel Silver"""
        # Usuario con 1000 puntos debe estar en Silver
        tier = loyalty_service._get_tier_from_score(1000)
        assert tier == 'cafe_plata'
        
        # Usuario con 4999 puntos debe estar en Silver
        tier = loyalty_service._get_tier_from_score(4999)
        assert tier == 'cafe_plata'
    
    @pytest.mark.unit
    def test_get_tier_from_score_gold(self, loyalty_service):
        """Test de asignación de nivel Gold"""
        # Usuario con 5000 puntos debe estar en Gold
        tier = loyalty_service._get_tier_from_score(5000)
        assert tier == 'cafe_oro'
        
        # Usuario con 14999 puntos debe estar en Gold
        tier = loyalty_service._get_tier_from_score(14999)
        assert tier == 'cafe_oro'
    
    @pytest.mark.unit
    def test_get_tier_from_score_diamond(self, loyalty_service):
        """Test de asignación de nivel Diamond"""
        # Usuario con 15000 puntos debe estar en Diamond
        tier = loyalty_service._get_tier_from_score(15000)
        assert tier == 'cafe_diamante'
        
        # Usuario con 50000 puntos debe estar en Diamond
        tier = loyalty_service._get_tier_from_score(50000)
        assert tier == 'cafe_diamante'
    
    @pytest.mark.unit
    def test_get_next_tier_progress(self, loyalty_service):
        """Test de cálculo de progreso al siguiente nivel"""
        # Usuario Bronze con 500 puntos
        progress = loyalty_service._get_next_tier_progress('cafe_bronze', 500)
        expected_progress = {
            'current_tier': 'cafe_bronze',
            'current_points': 500,
            'next_tier': 'cafe_plata',
            'points_needed': 500,
            'progress_percentage': 50.0
        }
        assert progress == expected_progress
        
        # Usuario Silver con 3000 puntos
        progress = loyalty_service._get_next_tier_progress('cafe_plata', 3000)
        expected_progress = {
            'current_tier': 'cafe_plata',
            'current_points': 3000,
            'next_tier': 'cafe_oro',
            'points_needed': 2000,
            'progress_percentage': 50.0
        }
        assert progress == expected_progress
        
        # Usuario Diamond (nivel máximo)
        progress = loyalty_service._get_next_tier_progress('cafe_diamante', 20000)
        expected_progress = {
            'current_tier': 'cafe_diamante',
            'current_points': 20000,
            'next_tier': None,
            'points_needed': 0,
            'progress_percentage': 100.0
        }
        assert progress == expected_progress
    
    @pytest.mark.unit
    def test_calculate_frequency_score(self, loyalty_service):
        """Test de cálculo de score por frecuencia"""
        # Usuario con 5 visitas en 30 días
        score = loyalty_service._calculate_frequency_score(5, 30)
        assert score > 0
        assert score <= 100
        
        # Usuario con 0 visitas
        score = loyalty_service._calculate_frequency_score(0, 30)
        assert score == 0
        
        # Usuario con muchas visitas
        score = loyalty_service._calculate_frequency_score(50, 30)
        assert score > 50
    
    @pytest.mark.unit
    def test_calculate_amount_score(self, loyalty_service):
        """Test de cálculo de score por monto"""
        # Usuario con gasto bajo
        score = loyalty_service._calculate_amount_score(1000)
        assert score > 0
        assert score <= 100
        
        # Usuario sin gastos
        score = loyalty_service._calculate_amount_score(0)
        assert score == 0
        
        # Usuario con gasto alto
        score = loyalty_service._calculate_amount_score(50000)
        assert score > 50
    
    @pytest.mark.unit
    def test_calculate_recency_score(self, loyalty_service):
        """Test de cálculo de score por recencia"""
        # Usuario que visitó hace 1 día
        recent_date = datetime.now() - timedelta(days=1)
        score = loyalty_service._calculate_recency_score(recent_date)
        assert score > 80
        
        # Usuario que visitó hace 30 días
        old_date = datetime.now() - timedelta(days=30)
        score = loyalty_service._calculate_recency_score(old_date)
        assert score < 50
        
        # Usuario que nunca ha visitado
        score = loyalty_service._calculate_recency_score(None)
        assert score == 0
    
    @pytest.mark.unit
    def test_calculate_variety_score(self, loyalty_service):
        """Test de cálculo de score por variedad"""
        # Usuario con productos variados
        products = ["café", "pastel", "sandwich", "jugo", "galleta"]
        score = loyalty_service._calculate_variety_score(products)
        assert score > 0
        assert score <= 100
        
        # Usuario con un solo producto
        products = ["café"]
        score = loyalty_service._calculate_variety_score(products)
        assert score < 20
        
        # Usuario sin productos
        score = loyalty_service._calculate_variety_score([])
        assert score == 0
    
    @pytest.mark.unit
    def test_calculate_referral_score(self, loyalty_service):
        """Test de cálculo de score por referidos"""
        # Usuario con referidos
        referrals = 3
        score = loyalty_service._calculate_referral_score(referrals)
        assert score > 0
        assert score <= 100
        
        # Usuario sin referidos
        score = loyalty_service._calculate_referral_score(0)
        assert score == 0
        
        # Usuario con muchos referidos
        score = loyalty_service._calculate_referral_score(10)
        assert score > 50
    
    @pytest.mark.asyncio
    @pytest.mark.unit
    async def test_calculate_user_score_integration(self, loyalty_service):
        """Test de integración del cálculo de score de usuario"""
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
        
        # Mockear todos los métodos que acceden a la base de datos
        with patch.object(loyalty_service, 'get_user_by_id', return_value=mock_user), \
             patch.object(loyalty_service, '_get_config_value', return_value=mock_config), \
             patch.object(loyalty_service, '_calculate_frequency_score', new_callable=AsyncMock, return_value=80.0), \
             patch.object(loyalty_service, '_calculate_amount_score', new_callable=AsyncMock, return_value=70.0), \
             patch.object(loyalty_service, '_calculate_recency_score', new_callable=AsyncMock, return_value=60.0), \
             patch.object(loyalty_service, '_calculate_variety_score', new_callable=AsyncMock, return_value=50.0), \
             patch.object(loyalty_service, '_calculate_referral_score', new_callable=AsyncMock, return_value=40.0), \
             patch.object(loyalty_service, 'update_user', return_value=mock_user):
            
            score = await loyalty_service.calculate_user_score(1)
            
            assert score > 0
            assert score <= 100
            assert isinstance(score, float)
            
            # Verificar que el score calculado es correcto según los pesos
            expected_score = (80.0 * 0.25 + 70.0 * 0.30 + 60.0 * 0.20 + 50.0 * 0.15 + 40.0 * 0.10)
            assert abs(score - expected_score) < 0.01
    
    @pytest.mark.unit
    def test_generate_referral_code(self, loyalty_service):
        """Test de generación de códigos de referido"""
        code1 = loyalty_service._generate_referral_code()
        code2 = loyalty_service._generate_referral_code()
        
        # Los códigos deben ser únicos
        assert code1 != code2
        
        # Los códigos deben tener 8 caracteres
        assert len(code1) == 8
        assert len(code2) == 8
        
        # Los códigos deben ser alfanuméricos
        assert code1.isalnum()
        assert code2.isalnum()
    
    @pytest.mark.unit
    def test_generate_coupon_code(self, loyalty_service):
        """Test de generación de códigos de cupón"""
        code1 = loyalty_service._generate_coupon_code()
        code2 = loyalty_service._generate_coupon_code()
        
        # Los códigos deben ser únicos
        assert code1 != code2
        
        # Los códigos deben tener el formato correcto
        assert len(code1) >= 6
        assert len(code2) >= 6
    
    @pytest.mark.unit
    def test_validate_points_amount(self, loyalty_service):
        """Test de validación de cantidad de puntos"""
        # Puntos válidos
        assert loyalty_service._validate_points_amount(100) == True
        assert loyalty_service._validate_points_amount(1000) == True
        
        # Puntos inválidos
        assert loyalty_service._validate_points_amount(-100) == False
        assert loyalty_service._validate_points_amount(0) == False
    
    @pytest.mark.unit
    def test_validate_tier_upgrade(self, loyalty_service):
        """Test de validación de subida de nivel"""
        # Subida válida de Bronze a Silver
        assert loyalty_service._validate_tier_upgrade('cafe_bronze', 'cafe_plata') == True
        
        # Subida válida de Silver a Gold
        assert loyalty_service._validate_tier_upgrade('cafe_plata', 'cafe_oro') == True
        
        # Subida inválida (saltar niveles)
        assert loyalty_service._validate_tier_upgrade('cafe_bronze', 'cafe_oro') == False
        
        # Subida inválida (bajar de nivel)
        assert loyalty_service._validate_tier_upgrade('cafe_oro', 'cafe_plata') == False
    
    @pytest.mark.unit
    def test_calculate_tier_benefits(self, loyalty_service):
        """Test de cálculo de beneficios por nivel"""
        benefits = loyalty_service._calculate_tier_benefits('cafe_bronze')
        assert 'discount_percent' in benefits
        assert 'free_coffees' in benefits
        assert 'priority_access' in benefits
        
        benefits = loyalty_service._calculate_tier_benefits('cafe_diamante')
        assert benefits['discount_percent'] > benefits.get('cafe_bronze', {}).get('discount_percent', 0)
    
    @pytest.mark.unit
    def test_validate_reward_redemption(self, loyalty_service):
        """Test de validación de canje de recompensas"""
        # Canje válido
        user_points = 500
        reward_cost = 200
        user_tier = 'cafe_bronze'
        reward_tier = 'cafe_bronze'
        
        assert loyalty_service._validate_reward_redemption(
            user_points, reward_cost, user_tier, reward_tier
        ) == True
        
        # Canje inválido - puntos insuficientes
        assert loyalty_service._validate_reward_redemption(
            100, reward_cost, user_tier, reward_tier
        ) == False
        
        # Canje inválido - nivel insuficiente
        assert loyalty_service._validate_reward_redemption(
            user_points, reward_cost, 'cafe_bronze', 'cafe_oro'
        ) == False 