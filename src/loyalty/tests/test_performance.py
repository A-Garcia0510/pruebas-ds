"""
Tests de rendimiento para el sistema de fidelización
"""

import pytest
import asyncio
import time
from unittest.mock import AsyncMock, patch
from datetime import datetime, timedelta

class TestPerformance:
    """Tests de rendimiento"""
    
    @pytest.fixture
    def loyalty_service(self):
        """Instancia del servicio para tests"""
        from services.loyalty_service import LoyaltyService
        return LoyaltyService()
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_calculate_user_score_performance(self, loyalty_service):
        """Test de rendimiento del cálculo de score de usuario"""
        user_id = 1
        
        # Mock de datos de usuario
        user_data = {
            'total_visits': 50,
            'total_spent': 25000,
            'last_visit': datetime.now() - timedelta(days=2),
            'favorite_products': ["café", "pastel", "sandwich", "jugo", "galleta", "té"],
            'referral_count': 5
        }
        
        start_time = time.time()
        
        with patch.object(loyalty_service, '_get_user_data', return_value=user_data):
            score = await loyalty_service.calculate_user_score(user_id)
        
        end_time = time.time()
        execution_time = end_time - start_time
        
        # El cálculo debe completarse en menos de 100ms
        assert execution_time < 0.1
        assert score > 0
        assert score <= 100
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_bulk_points_earning_performance(self, loyalty_service):
        """Test de rendimiento de ganancia masiva de puntos"""
        # Simular 100 usuarios ganando puntos simultáneamente
        users = list(range(1, 101))
        purchase_amount = 1000
        
        start_time = time.time()
        
        tasks = []
        for user_id in users:
            task = loyalty_service.earn_points_from_purchase(
                user_id=user_id,
                purchase_amount=purchase_amount,
                order_id=user_id
            )
            tasks.append(task)
        
        results = await asyncio.gather(*tasks, return_exceptions=True)
        
        end_time = time.time()
        execution_time = end_time - start_time
        
        # Las 100 operaciones deben completarse en menos de 2 segundos
        assert execution_time < 2.0
        assert len(results) == 100
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_concurrent_reward_redemptions(self, loyalty_service):
        """Test de rendimiento de canjes concurrentes de recompensas"""
        # Simular 50 usuarios canjeando recompensas simultáneamente
        users = list(range(1, 51))
        reward_id = 1
        
        start_time = time.time()
        
        tasks = []
        for user_id in users:
            task = loyalty_service.redeem_reward(
                user_id=user_id,
                reward_id=reward_id
            )
            tasks.append(task)
        
        results = await asyncio.gather(*tasks, return_exceptions=True)
        
        end_time = time.time()
        execution_time = end_time - start_time
        
        # Las 50 operaciones deben completarse en menos de 1 segundo
        assert execution_time < 1.0
        assert len(results) == 50
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_database_query_performance(self, loyalty_service):
        """Test de rendimiento de consultas a base de datos"""
        user_id = 1
        
        # Mock de consulta rápida
        with patch.object(loyalty_service, '_execute_query', return_value={'user_id': user_id}):
            start_time = time.time()
            
            # Ejecutar 100 consultas
            for _ in range(100):
                await loyalty_service.get_user_by_id(user_id)
            
            end_time = time.time()
            execution_time = end_time - start_time
            
            # Las 100 consultas deben completarse en menos de 500ms
            assert execution_time < 0.5
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_tier_upgrade_performance(self, loyalty_service):
        """Test de rendimiento de verificación de subida de nivel"""
        # Simular 1000 usuarios verificando subida de nivel
        users = list(range(1, 1001))
        
        start_time = time.time()
        
        tasks = []
        for user_id in users:
            task = loyalty_service.check_tier_upgrade(user_id)
            tasks.append(task)
        
        results = await asyncio.gather(*tasks, return_exceptions=True)
        
        end_time = time.time()
        execution_time = end_time - start_time
        
        # Las 1000 verificaciones deben completarse en menos de 5 segundos
        assert execution_time < 5.0
        assert len(results) == 1000
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_notification_sending_performance(self, loyalty_service):
        """Test de rendimiento de envío de notificaciones"""
        # Simular envío de 100 notificaciones
        notifications = []
        for i in range(100):
            notifications.append({
                'user_id': i + 1,
                'type': 'points_earned',
                'message': f'Ganaste {100 + i} puntos!'
            })
        
        start_time = time.time()
        
        tasks = []
        for notification in notifications:
            task = loyalty_service.send_notification(**notification)
            tasks.append(task)
        
        results = await asyncio.gather(*tasks, return_exceptions=True)
        
        end_time = time.time()
        execution_time = end_time - start_time
        
        # Las 100 notificaciones deben enviarse en menos de 3 segundos
        assert execution_time < 3.0
        assert len(results) == 100
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_analytics_calculation_performance(self, loyalty_service):
        """Test de rendimiento de cálculos de analytics"""
        start_time = time.time()
        
        # Ejecutar múltiples cálculos de analytics
        tasks = [
            loyalty_service.get_loyalty_metrics(),
            loyalty_service.get_tier_distribution(),
            loyalty_service.get_redemption_analytics(),
            loyalty_service.get_user_retention_metrics()
        ]
        
        results = await asyncio.gather(*tasks, return_exceptions=True)
        
        end_time = time.time()
        execution_time = end_time - start_time
        
        # Los cálculos deben completarse en menos de 2 segundos
        assert execution_time < 2.0
        assert len(results) == 4
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_memory_usage_under_load(self, loyalty_service):
        """Test de uso de memoria bajo carga"""
        import psutil
        import os
        
        process = psutil.Process(os.getpid())
        initial_memory = process.memory_info().rss / 1024 / 1024  # MB
        
        # Simular carga alta
        tasks = []
        for i in range(1000):
            task = loyalty_service.calculate_user_score(i)
            tasks.append(task)
        
        await asyncio.gather(*tasks, return_exceptions=True)
        
        final_memory = process.memory_info().rss / 1024 / 1024  # MB
        memory_increase = final_memory - initial_memory
        
        # El aumento de memoria no debe exceder 100MB
        assert memory_increase < 100
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_response_time_under_stress(self, loyalty_service):
        """Test de tiempo de respuesta bajo estrés"""
        # Simular múltiples operaciones simultáneas
        operations = []
        
        # 50 ganancias de puntos
        for i in range(50):
            operations.append(loyalty_service.earn_points_from_purchase(
                user_id=i+1, purchase_amount=1000, order_id=i+1
            ))
        
        # 30 canjes de recompensas
        for i in range(30):
            operations.append(loyalty_service.redeem_reward(
                user_id=i+1, reward_id=1
            ))
        
        # 20 verificaciones de nivel
        for i in range(20):
            operations.append(loyalty_service.check_tier_upgrade(i+1))
        
        start_time = time.time()
        results = await asyncio.gather(*operations, return_exceptions=True)
        end_time = time.time()
        
        execution_time = end_time - start_time
        
        # Las 100 operaciones deben completarse en menos de 3 segundos
        assert execution_time < 3.0
        assert len(results) == 100
    
    @pytest.mark.performance
    @pytest.mark.slow
    async def test_cache_performance(self, loyalty_service):
        """Test de rendimiento del caché"""
        user_id = 1
        
        # Primera consulta (sin caché)
        start_time = time.time()
        with patch.object(loyalty_service, '_get_user_data', return_value={'user_id': user_id}):
            await loyalty_service.get_user_by_id(user_id)
        first_query_time = time.time() - start_time
        
        # Segunda consulta (con caché)
        start_time = time.time()
        with patch.object(loyalty_service, '_get_user_data', return_value={'user_id': user_id}):
            await loyalty_service.get_user_by_id(user_id)
        second_query_time = time.time() - start_time
        
        # La segunda consulta debe ser más rápida
        assert second_query_time < first_query_time
        assert second_query_time < 0.01  # Menos de 10ms con caché 