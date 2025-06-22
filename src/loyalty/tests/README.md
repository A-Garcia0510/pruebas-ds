# 🧪 Sistema de Testing - Café-VT Loyalty

Este directorio contiene todos los tests del sistema de fidelización Café-VT.

## 📁 Estructura de Tests

```
tests/
├── __init__.py                 # Inicialización del módulo
├── conftest.py                 # Configuración de pytest
├── test_loyalty_engine.py      # Tests unitarios del motor de scoring
├── test_transactions.py        # Tests unitarios de transacciones
├── test_api_endpoints.py       # Tests de integración de API
├── test_user_flows.py          # Tests de flujos de usuario
├── test_database_integration.py # Tests de integración con BD
├── test_performance.py         # Tests de rendimiento
└── README.md                   # Esta documentación
```

## 🎯 Tipos de Tests

### 1. Tests Unitarios (`@pytest.mark.unit`)
- **Propósito**: Probar funciones individuales y métodos
- **Archivos**: `test_loyalty_engine.py`, `test_transactions.py`
- **Cobertura**: Lógica de negocio, cálculos, validaciones

### 2. Tests de Integración (`@pytest.mark.integration`)
- **Propósito**: Probar interacción entre componentes
- **Archivos**: `test_api_endpoints.py`, `test_database_integration.py`
- **Cobertura**: Endpoints API, base de datos, servicios

### 3. Tests de API (`@pytest.mark.api`)
- **Propósito**: Probar endpoints HTTP
- **Archivos**: `test_api_endpoints.py`
- **Cobertura**: Respuestas HTTP, códigos de estado, JSON

### 4. Tests de Base de Datos (`@pytest.mark.database`)
- **Propósito**: Probar operaciones de BD
- **Archivos**: `test_database_integration.py`
- **Cobertura**: Consultas, transacciones, constraints

### 5. Tests de Usuario (`@pytest.mark.user`)
- **Propósito**: Simular flujos reales de usuario
- **Archivos**: `test_user_flows.py`
- **Cobertura**: Casos de uso completos, experiencias de usuario

### 6. Tests de Rendimiento (`@pytest.mark.performance`)
- **Propósito**: Probar rendimiento y escalabilidad
- **Archivos**: `test_performance.py`
- **Cobertura**: Tiempo de respuesta, concurrencia, memoria

## 🚀 Ejecución de Tests

### Ejecutar Todos los Tests
```bash
cd src/loyalty
python run_tests.py
```

### Ejecutar Tests Específicos

#### Tests Unitarios
```bash
python -m pytest tests/ -m unit -v
```

#### Tests de Integración
```bash
python -m pytest tests/ -m integration -v
```

#### Tests de API
```bash
python -m pytest tests/ -m api -v
```

#### Tests de Base de Datos
```bash
python -m pytest tests/ -m database -v
```

#### Tests de Usuario
```bash
python -m pytest tests/ -m user -v
```

#### Tests de Rendimiento
```bash
python -m pytest tests/ -m performance -v
```

### Ejecutar Tests Rápidos (Sin Tests Lentos)
```bash
python -m pytest tests/ -m "not slow" -v
```

### Ejecutar Tests con Cobertura
```bash
python -m pytest tests/ --cov=services --cov=utils --cov-report=html
```

### Ejecutar Tests en Paralelo
```bash
python -m pytest tests/ -n auto
```

## 📊 Reportes

### Reporte de Cobertura
- **Ubicación**: `htmlcov/index.html`
- **Métricas**: Porcentaje de código cubierto por tests

### Reporte HTML
- **Ubicación**: `reports/test_report.html`
- **Contenido**: Resultados detallados de todos los tests

### Reporte en Consola
- **Formato**: Verbose con duración de tests
- **Marcadores**: Colores para diferentes tipos de tests

## 🔧 Configuración

### pytest.ini
```ini
[tool:pytest]
testpaths = tests
python_files = test_*.py
python_classes = Test*
python_functions = test_*
addopts = 
    -v
    --tb=short
    --strict-markers
    --disable-warnings
    --color=yes
    --durations=10
    --cov=services
    --cov=utils
    --cov-report=html:htmlcov
    --cov-report=term-missing
```

### Marcadores Personalizados
- `@pytest.mark.unit` - Tests unitarios
- `@pytest.mark.integration` - Tests de integración
- `@pytest.mark.api` - Tests de API
- `@pytest.mark.database` - Tests de base de datos
- `@pytest.mark.user` - Tests de usuario
- `@pytest.mark.performance` - Tests de rendimiento
- `@pytest.mark.slow` - Tests lentos
- `@pytest.mark.smoke` - Tests de humo
- `@pytest.mark.regression` - Tests de regresión

## 🎯 Casos de Prueba Cubiertos

### Motor de Scoring
- ✅ Cálculo de score por frecuencia
- ✅ Cálculo de score por monto
- ✅ Cálculo de score por recencia
- ✅ Cálculo de score por variedad
- ✅ Cálculo de score por referidos
- ✅ Asignación de niveles
- ✅ Progreso al siguiente nivel

### Transacciones
- ✅ Registro de transacciones válidas
- ✅ Validación de datos de entrada
- ✅ Cálculo de puntos por compra
- ✅ Aplicación de multiplicadores por nivel
- ✅ Historial de transacciones
- ✅ Auditoría de transacciones

### API Endpoints
- ✅ GET /api/v1/loyalty/profile/{user_id}
- ✅ POST /api/v1/loyalty/earn-points
- ✅ POST /api/v1/loyalty/redeem-reward
- ✅ GET /api/v1/loyalty/rewards
- ✅ POST /api/v1/loyalty/referral
- ✅ POST /api/v1/loyalty/use-referral
- ✅ GET /api/v1/loyalty/transactions/{user_id}
- ✅ POST /api/v1/loyalty/check-tier-upgrade

### Flujos de Usuario
- ✅ Registro completo de usuario
- ✅ Compra y ganancia de puntos
- ✅ Subida de nivel
- ✅ Canje de recompensas
- ✅ Sistema de referidos
- ✅ Expiración de puntos
- ✅ Campañas de marketing
- ✅ Analytics e insights

### Base de Datos
- ✅ Conexión y configuración
- ✅ Operaciones CRUD
- ✅ Transacciones y rollback
- ✅ Manejo de errores
- ✅ Pool de conexiones

### Rendimiento
- ✅ Tiempo de respuesta < 200ms
- ✅ Carga con 1000+ usuarios
- ✅ Concurrencia de operaciones
- ✅ Uso de memoria < 100MB
- ✅ Caché y optimizaciones

## 🐛 Debugging

### Ejecutar Tests con Debug
```bash
python -m pytest tests/ -s -v --pdb
```

### Ejecutar Test Específico
```bash
python -m pytest tests/test_loyalty_engine.py::TestLoyaltyEngine::test_calculate_user_score -v
```

### Ver Cobertura Detallada
```bash
python -m pytest tests/ --cov=services --cov-report=term-missing
```

## 📈 Métricas de Calidad

### Cobertura Objetivo
- **Cobertura Total**: > 90%
- **Cobertura de Servicios**: > 95%
- **Cobertura de Utils**: > 90%

### Rendimiento Objetivo
- **Tests Unitarios**: < 1 segundo
- **Tests de Integración**: < 5 segundos
- **Tests de Rendimiento**: < 10 segundos
- **Tiempo Total**: < 30 segundos

### Confiabilidad
- **Tests Pasando**: 100%
- **Tests Estables**: > 99%
- **Falsos Positivos**: < 1%

## 🔄 CI/CD Integration

### GitHub Actions
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Set up Python
        uses: actions/setup-python@v2
        with:
          python-version: 3.9
      - name: Install dependencies
        run: |
          pip install -r requirements.txt
          pip install pytest pytest-cov pytest-html
      - name: Run tests
        run: |
          cd src/loyalty
          python run_tests.py
```

## 📝 Mejores Prácticas

### Escribir Tests
1. **Nombres Descriptivos**: `test_calculate_user_score_with_high_frequency`
2. **Arrange-Act-Assert**: Estructura clara de tests
3. **Mocks Apropiados**: Mockear dependencias externas
4. **Datos de Prueba**: Usar fixtures y datos realistas
5. **Assertions Específicos**: Verificar comportamiento exacto

### Mantener Tests
1. **Actualizar con Cambios**: Mantener tests sincronizados
2. **Refactorizar Tests**: Eliminar duplicación
3. **Optimizar Rendimiento**: Tests rápidos y eficientes
4. **Documentar Casos**: Comentarios para casos complejos

## 🆘 Troubleshooting

### Problemas Comunes

#### Tests Faltan
```bash
# Verificar instalación de pytest
pip install pytest pytest-asyncio

# Verificar marcadores
python -m pytest --markers
```

#### Tests Lentos
```bash
# Ejecutar solo tests rápidos
python -m pytest tests/ -m "not slow"

# Ver duración de tests
python -m pytest tests/ --durations=10
```

#### Errores de Base de Datos
```bash
# Verificar configuración de BD
python -c "from utils.database import get_db; print('DB OK')"

# Ejecutar tests sin BD
python -m pytest tests/ -m "not database"
```

## 📞 Soporte

Para problemas con tests:
1. Revisar logs de ejecución
2. Verificar configuración de pytest
3. Consultar documentación de pytest
4. Revisar issues del proyecto

---

**Última actualización**: Diciembre 2024
**Versión**: 1.0
**Responsable**: Equipo de Testing 