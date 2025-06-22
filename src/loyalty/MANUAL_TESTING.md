# 📖 Manual de Testing - Sistema de Fidelización

## 🚀 Cómo ejecutar los tests

1. **Desde la terminal, ve a la carpeta de fidelización:**
   ```bash
   cd src/loyalty
   ```

2. **Ejecuta todos los tests:**
   ```bash
   py -m pytest tests/ -v
   ```

3. **Ejecuta solo los tests unitarios del motor de scoring:**
   ```bash
   py -m pytest tests/test_loyalty_engine.py -v
   ```

4. **Ejecuta solo los tests de transacciones:**
   ```bash
   py -m pytest tests/test_transactions.py -v
   ```

5. **Ver reporte de cobertura de código:**
   ```bash
   py -m pytest --cov=services --cov=utils --cov-report=html
   # Abre el archivo htmlcov/index.html en tu navegador
   ```

---

## 🛠️ Cómo crear un nuevo test

1. **Crea un archivo nuevo en `src/loyalty/tests/`**  
   Ejemplo: `test_mi_funcionalidad.py`

2. **Estructura básica de un test:**
   ```python
   import pytest

   def test_mi_funcion():
       resultado = 2 + 2
       assert resultado == 4
   ```

3. **Para tests async:**
   ```python
   import pytest

   @pytest.mark.asyncio
   async def test_async_funcion():
       resultado = await mi_funcion_async()
       assert resultado == "ok"
   ```

4. **Para tests con mocks:**
   ```python
   from unittest.mock import patch

   def test_funcion_mock():
       with patch('modulo.funcion', return_value=10):
           assert mi_funcion() == 10
   ```

---

## 🏷️ Marcadores útiles

- `@pytest.mark.unit` — Test unitario
- `@pytest.mark.integration` — Test de integración
- `@pytest.mark.api` — Test de endpoint API
- `@pytest.mark.performance` — Test de rendimiento
- `@pytest.mark.user` — Test de flujo de usuario

---

## 💡 Tips

- Usa `pytest.mark.asyncio` para tests async.
- Usa `unittest.mock` para simular base de datos o servicios externos.
- Si un test falla por base de datos, mockea las funciones de acceso.
- Para ver solo los tests que fallan:  
  ```bash
  py -m pytest --lf
  ```

---

¡Feliz testing! 🚦 