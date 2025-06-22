# Sistema de Fidelización Inteligente - Café-VT

## 📋 Descripción General

El Sistema de Fidelización Inteligente es un módulo completo que se integra con la aplicación existente de Café-VT para proporcionar un programa de fidelización avanzado con scoring inteligente, múltiples niveles de beneficios y un sistema de recompensas personalizado.

## 🏗️ Arquitectura del Sistema

### Estructura de Directorios

```
src/loyalty/
├── __init__.py                 # Módulo principal
├── config.py                   # Configuración del sistema
├── models/                     # Modelos Pydantic
│   ├── __init__.py
│   ├── loyalty_models.py       # Modelos de fidelización
│   ├── reward_models.py        # Modelos de recompensas
│   └── transaction_models.py   # Modelos de transacciones
├── services/                   # Lógica de negocio
│   ├── __init__.py
│   ├── loyalty_engine.py       # Motor de scoring
│   ├── reward_service.py       # Servicio de recompensas
│   └── transaction_service.py  # Servicio de transacciones
├── routes/                     # Endpoints de la API
│   ├── __init__.py
│   └── loyalty_routes.py       # Rutas de fidelización
├── utils/                      # Utilidades
│   ├── __init__.py
│   ├── code_generator.py       # Generador de códigos
│   └── tier_calculator.py      # Calculadora de niveles
└── README.md                   # Esta documentación
```

## 🎯 Componentes Principales

### 1. Motor de Scoring (LoyaltyEngine)
- **Responsabilidad**: Calcular el score de fidelización de cada usuario
- **Algoritmo**: Combina múltiples factores con pesos configurables
- **Factores**: Frecuencia, monto, recencia, variedad, referidos

### 2. Sistema de Niveles
- **Bronze**: 0-999 puntos
- **Silver**: 1,000-4,999 puntos  
- **Gold**: 5,000-14,999 puntos
- **Diamond**: 15,000+ puntos

### 3. Sistema de Puntos
- **Ganancia**: 1 punto por peso gastado (configurable)
- **Expiración**: 365 días (configurable)
- **Canje**: Mínimo 100 puntos

### 4. Sistema de Recompensas
- **Tipos**: Descuentos, productos gratis, beneficios especiales
- **Restricciones**: Por nivel, límite de usos, fechas de validez
- **Personalización**: Basada en comportamiento del usuario

### 5. Sistema de Referidos
- **Códigos únicos**: 8 caracteres alfanuméricos
- **Bonificación**: 500 puntos por referido exitoso
- **Tracking**: Seguimiento completo de referidos

## 🔄 Flujo de Datos

### Ganancia de Puntos
1. Usuario realiza compra
2. Sistema calcula puntos basado en monto
3. Se aplica multiplicador del nivel actual
4. Puntos se añaden al balance del usuario
5. Se actualiza el score de fidelización
6. Se verifica posible subida de nivel

### Canje de Recompensas
1. Usuario selecciona recompensa
2. Sistema verifica puntos disponibles
3. Se valida nivel requerido
4. Se verifica límites de uso
5. Se descuentan puntos
6. Se genera cupón o se aplica descuento

### Cálculo de Scoring
1. Se obtienen datos históricos del usuario
2. Se calcula cada factor individual
3. Se aplican pesos configurados
4. Se combinan en score final
5. Se determina nivel correspondiente

## 🗄️ Base de Datos

### Tablas Principales
- `loyalty_users`: Perfiles de fidelización
- `loyalty_transactions`: Historial de transacciones
- `loyalty_rewards`: Recompensas disponibles
- `loyalty_coupons`: Cupones generados

### Relaciones
- Integración con tabla `Usuario` existente
- Conexión con sistema de pedidos actual
- Sincronización con base de datos PHP

## 🔌 Integración

### Con Sistema Existente
- **Autenticación**: Usa sistema de usuarios actual
- **Pedidos**: Se conecta con tabla `Compra` existente
- **Productos**: Utiliza tabla `Producto` para tracking
- **Frontend**: Se integra con interfaz PHP existente

### APIs Externas
- **Notificaciones**: Sistema de emails automáticos
- **Análisis**: Métricas y reportes avanzados
- **Marketing**: Campañas personalizadas

## 🛡️ Seguridad

### Medidas Implementadas
- **Validación**: Todos los inputs se validan con Pydantic
- **Autenticación**: Requiere autenticación para todas las operaciones
- **Autorización**: Verificación de permisos por nivel
- **Auditoría**: Log completo de todas las transacciones
- **Cifrado**: Códigos de referido y cupones cifrados

## 📊 Métricas y Monitoreo

### KPIs Principales
- **Retención**: Porcentaje de usuarios que regresan
- **Engagement**: Frecuencia de uso del programa
- **Conversión**: Tasa de canje de recompensas
- **ROI**: Retorno de inversión del programa

### Alertas
- Puntos por expirar
- Usuarios inactivos
- Anomalías en scoring
- Errores en transacciones

## 🚀 Despliegue

### Requisitos
- Python 3.8+
- MySQL 8.0+
- Redis (opcional, para caché)
- FastAPI 0.109+

### Variables de Entorno
```bash
LOYALTY_POINTS_PER_PESO=1.0
LOYALTY_POINTS_EXPIRY_DAYS=365
LOYALTY_CACHE_TTL_SECONDS=3600
```

## 📝 Notas de Desarrollo

### Convenciones
- **Nombres**: snake_case para Python, camelCase para JSON
- **Documentación**: Docstrings en todos los métodos públicos
- **Testing**: Cobertura mínima del 80%
- **Logging**: Log estructurado para debugging

### Mejores Prácticas
- **Separación de responsabilidades**: Cada servicio tiene una función específica
- **Inyección de dependencias**: Uso de FastAPI Depends
- **Manejo de errores**: Excepciones personalizadas y códigos de error
- **Performance**: Caché para cálculos costosos
- **Escalabilidad**: Diseño modular para futuras expansiones

---

**Versión**: 1.0.0  
**Fecha**: Diciembre 2024  
**Equipo**: Café-VT Development 