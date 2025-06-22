# Especificaciones del Sistema de Fidelización
## Café-VT - FastAPI + PHP

---

## 🎯 **NIVELES DE FIDELIZACIÓN**

### ☕ Café Bronze (0-999 puntos)
- **Nombre**: Café Bronze
- **Rango**: 0-999 puntos
- **Multiplicador de puntos**: 1.0x
- **Descuento automático**: 0%
- **Envío gratis**: No
- **Soporte prioritario**: No
- **Recompensas disponibles**: Básicas
- **Descripción**: Para los amantes del café que están comenzando su viaje

### 🥈 Café Plata (1,000-4,999 puntos)
- **Nombre**: Café Plata
- **Rango**: 1,000-4,999 puntos
- **Multiplicador de puntos**: 1.2x
- **Descuento automático**: 5%
- **Envío gratis**: No
- **Soporte prioritario**: No
- **Recompensas disponibles**: Básicas + Intermedias
- **Descripción**: Conocedores del café que aprecian la calidad

### 🥇 Café Oro (5,000-14,999 puntos)
- **Nombre**: Café Oro
- **Rango**: 5,000-14,999 puntos
- **Multiplicador de puntos**: 1.5x
- **Descuento automático**: 10%
- **Envío gratis**: Sí
- **Soporte prioritario**: Sí
- **Recompensas disponibles**: Todas excepto exclusivas
- **Descripción**: Expertos cafeteros con acceso a beneficios premium

### 💎 Café Diamante (15,000+ puntos)
- **Nombre**: Café Diamante
- **Rango**: 15,000+ puntos
- **Multiplicador de puntos**: 2.0x
- **Descuento automático**: 15%
- **Envío gratis**: Sí
- **Soporte prioritario**: Sí
- **Recompensas disponibles**: Todas (incluyendo exclusivas)
- **Descripción**: Maestros del café con todos los privilegios exclusivos

---

## 📊 **SISTEMA DE SCORING**

### Factores y Pesos
1. **Frecuencia de Visitas (25%)**
   - Número de visitas en los últimos 90 días
   - Fórmula: `(visitas_90días / 90) * 100`

2. **Monto Gastado (30%)**
   - Total gastado en los últimos 12 meses
   - Fórmula: `(total_gastado / 10000) * 100` (normalizado a 10,000)

3. **Recencia de Visitas (20%)**
   - Días desde la última visita
   - Fórmula: `max(0, (90 - días_desde_última_visita) / 90) * 100`

4. **Variedad de Productos (15%)**
   - Número de productos diferentes comprados
   - Fórmula: `(productos_únicos / 20) * 100` (normalizado a 20 productos)

5. **Sistema de Referidos (10%)**
   - Número de referidos exitosos
   - Fórmula: `min(referidos_exitosos * 10, 100)`

### Cálculo del Score Final
```
Score = (Frecuencia × 0.25) + (Monto × 0.30) + (Recencia × 0.20) + (Variedad × 0.15) + (Referidos × 0.10)
```

---

## 🎁 **SISTEMA DE RECOMPENSAS**

### Tipos de Recompensas

#### Descuentos
- **10% Descuento en tu Próximo Café**: 200 puntos (Café Bronze+)
- **20% Descuento en Menú Completo**: 1,000 puntos (Café Oro+)
- **50% Descuento en Experiencia Premium**: 2,500 puntos (Café Diamante)

#### Productos Gratis
- **Café Americano Gratis**: 500 puntos (Café Bronze+)
- **Cappuccino Especial Gratis**: 800 puntos (Café Plata+)
- **Experiencia Café-VT Completa**: 2,000 puntos (Café Diamante)

#### Beneficios Especiales
- **Envío Gratis**: 300 puntos (Café Plata+)
- **Cupón de Cumpleaños**: Automático (todos los niveles)
- **Acceso Anticipado a Nuevos Sabores**: 1,500 puntos (Café Oro+)

### Restricciones
- **Límite por usuario**: Varía por recompensa
- **Nivel requerido**: Algunas recompensas requieren nivel mínimo
- **Fechas de validez**: Todas las recompensas tienen fecha de expiración
- **Uso único**: Algunas recompensas son de un solo uso

---

## 🔗 **SISTEMA DE REFERIDOS**

### Generación de Códigos
- **Longitud**: 8 caracteres alfanuméricos
- **Formato**: `XXXX-XXXX` (con guión)
- **Unicidad**: Garantizada por algoritmo criptográfico
- **Expiración**: 30 días desde la generación

### Bonificaciones
- **Referidor**: 500 puntos por referido exitoso
- **Referido**: 200 puntos de bienvenida
- **Condición**: El referido debe realizar su primera compra

### Tracking
- **Registro**: Todas las referencias se registran
- **Estado**: Pendiente, Exitoso, Expirado
- **Métricas**: Conversión, tiempo hasta primera compra

---

## ⏰ **SISTEMA DE PUNTOS**

### Ganancia de Puntos
- **Base**: 1 punto por peso gastado
- **Multiplicador**: Aplicado según nivel actual
- **Bonificaciones**: Eventos especiales, referidos, cumpleaños

### Expiración
- **Período**: 365 días desde la ganancia
- **Notificación**: 30 días antes de expirar
- **Recuperación**: No es posible extender puntos expirados

### Canje
- **Mínimo**: 100 puntos para canjear
- **Proceso**: Selección → Confirmación → Aplicación
- **Reversión**: No es posible revertir canjes

---

## 📧 **SISTEMA DE NOTIFICACIONES**

### Tipos de Notificaciones

#### Automáticas
- **Bienvenida**: Al registrarse en el programa
- **Subida de nivel**: Cuando alcanza nuevo nivel
- **Puntos por expirar**: 30 días antes de expiración
- **Cumpleaños**: Cupón automático de cumpleaños

#### Transaccionales
- **Puntos ganados**: Después de cada compra
- **Recompensa canjeada**: Confirmación de canje
- **Referido exitoso**: Cuando un referido hace su primera compra

#### Marketing
- **Ofertas personalizadas**: Basadas en comportamiento
- **Recordatorios**: Para usuarios inactivos
- **Promociones**: Eventos especiales

### Canales
- **Email**: Notificaciones principales
- **Push**: Notificaciones en tiempo real (futuro)
- **SMS**: Notificaciones críticas (futuro)

---

## 🔒 **SEGURIDAD Y PRIVACIDAD**

### Autenticación
- **Requerida**: Para todas las operaciones
- **Tokens**: JWT con expiración
- **Refresh**: Tokens de renovación automática

### Autorización
- **Niveles**: Verificación de nivel para recompensas
- **Límites**: Control de límites de uso
- **Auditoría**: Log completo de todas las acciones

### Protección de Datos
- **Cifrado**: Códigos y cupones cifrados
- **Anonimización**: Datos sensibles protegidos
- **GDPR**: Cumplimiento con regulaciones de privacidad

---

## 📈 **MÉTRICAS Y ANÁLISIS**

### KPIs Principales
1. **Retención**: % de usuarios que regresan
2. **Engagement**: Frecuencia de uso del programa
3. **Conversión**: Tasa de canje de recompensas
4. **ROI**: Retorno de inversión del programa

### Métricas de Usuario
- **Distribución por nivel**: % de usuarios en cada nivel
- **Progresión**: Tiempo promedio para subir de nivel
- **Churn**: Tasa de abandono por nivel

### Métricas de Negocio
- **Ticket promedio**: Por nivel de fidelización
- **Frecuencia de compra**: Por nivel
- **Valor de por vida**: Por usuario

---

## 🚀 **INTEGRACIÓN TÉCNICA**

### APIs
- **RESTful**: Endpoints estándar REST
- **Documentación**: Swagger/OpenAPI
- **Versionado**: API v1 con compatibilidad hacia atrás

### Base de Datos
- **MySQL**: Base de datos principal
- **Redis**: Caché para performance
- **Backup**: Automático diario

### Frontend
- **PHP**: Integración con sistema existente
- **JavaScript**: Interacciones dinámicas
- **Responsive**: Compatible con móviles

---

## 📋 **CRITERIOS DE ACEPTACIÓN**

### Funcionales
- [ ] Usuario puede registrarse en el programa
- [ ] Sistema calcula puntos automáticamente
- [ ] Usuario puede canjear recompensas
- [ ] Sistema asigna niveles correctamente
- [ ] Usuario puede generar códigos de referido

### Técnicos
- [ ] API responde en menos de 200ms
- [ ] Base de datos optimizada
- [ ] Manejo de errores robusto
- [ ] Logs completos de auditoría
- [ ] Tests con cobertura >80%

### Negocio
- [ ] Incremento en visitas recurrentes
- [ ] Incremento en ticket promedio
- [ ] Mejora en retención de clientes
- [ ] ROI positivo del programa

---

**Versión**: 1.0.0  
**Fecha**: Diciembre 2024  
**Estado**: En Desarrollo 