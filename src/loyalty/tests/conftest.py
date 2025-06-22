"""
Configuración de pytest para tests del sistema de fidelización
"""

import pytest
import asyncio
from typing import AsyncGenerator, Generator
from fastapi.testclient import TestClient
from unittest.mock import AsyncMock, MagicMock

# Configuración de pytest
pytest_plugins = ["pytest_asyncio"]

@pytest.fixture(scope="session")
def event_loop() -> Generator:
    """Crear event loop para tests asíncronos"""
    loop = asyncio.get_event_loop_policy().new_event_loop()
    yield loop
    loop.close()

@pytest.fixture
def client() -> TestClient:
    """Cliente de test para FastAPI"""
    from main import app
    return TestClient(app)

@pytest.fixture
async def mock_db() -> AsyncGenerator:
    """Mock de base de datos para tests"""
    # Mock de la conexión de base de datos
    mock_connection = AsyncMock()
    mock_connection.execute = AsyncMock()
    mock_connection.fetch_one = AsyncMock()
    mock_connection.fetch_all = AsyncMock()
    
    yield mock_connection

@pytest.fixture
def loyalty_service():
    """Instancia del servicio de fidelización para tests"""
    from services.loyalty_service import LoyaltyService
    return LoyaltyService()

@pytest.fixture
def notification_service():
    """Instancia del servicio de notificaciones para tests"""
    from services.notification_service import NotificationService
    return NotificationService()

@pytest.fixture
def marketing_service():
    """Instancia del servicio de marketing para tests"""
    from services.marketing_service import MarketingService
    return MarketingService()

@pytest.fixture
def analytics_service():
    """Instancia del servicio de análisis para tests"""
    from services.analytics_service import AnalyticsService
    return AnalyticsService()

@pytest.fixture
def sample_user_data() -> dict:
    """Datos de ejemplo de usuario para tests"""
    return {
        "usuario_ID": 1,
        "nombre": "Juan",
        "apellidos": "Pérez",
        "correo": "juan.perez@test.com",
        "current_tier": "cafe_bronze",
        "current_points": 500,
        "total_points": 500,
        "total_visits": 5,
        "total_spent": 2500.0,
        "status": "activo"
    }

@pytest.fixture
def sample_transaction_data() -> dict:
    """Datos de ejemplo de transacción para tests"""
    return {
        "user_id": 1,
        "transaction_type": "purchase",
        "points_amount": 100,
        "order_id": 123,
        "description": "Compra de café",
        "balance_before": 400,
        "balance_after": 500
    }

@pytest.fixture
def sample_reward_data() -> dict:
    """Datos de ejemplo de recompensa para tests"""
    return {
        "name": "Café Gratis",
        "description": "Un café gratis de cualquier tamaño",
        "points_cost": 200,
        "discount_percent": 100,
        "tier_required": "cafe_bronze",
        "max_uses_per_user": 1,
        "active": True
    }

@pytest.fixture
def sample_coupon_data() -> dict:
    """Datos de ejemplo de cupón para tests"""
    return {
        "user_id": 1,
        "code": "TEST123",
        "discount_type": "percentage",
        "discount_value": 15.0,
        "min_order_amount": 1000.0,
        "max_uses": 1,
        "used_count": 0,
        "active": True
    }

# Configuración de marcadores de pytest
def pytest_configure(config):
    """Configurar marcadores personalizados"""
    config.addinivalue_line(
        "markers", "unit: Tests unitarios"
    )
    config.addinivalue_line(
        "markers", "integration: Tests de integración"
    )
    config.addinivalue_line(
        "markers", "api: Tests de endpoints de API"
    )
    config.addinivalue_line(
        "markers", "database: Tests de base de datos"
    )
    config.addinivalue_line(
        "markers", "slow: Tests lentos"
    ) 