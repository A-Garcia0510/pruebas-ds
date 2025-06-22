# 🚀 Guía de Despliegue - Sistema de Fidelización

## 📋 Prerrequisitos

### Software Requerido
- **Python 3.8+**
- **MySQL 8.0+**
- **Redis 6.0+** (opcional, para caché)
- **Git**

### Servidor
- **RAM mínima:** 2GB
- **CPU:** 2 cores
- **Disco:** 20GB libres
- **Sistema:** Ubuntu 20.04+ / CentOS 8+ / Windows Server 2019+

---

## 🔧 Configuración Inicial

### 1. Clonar el Repositorio
```bash
git clone <repository-url>
cd pruebas-ds/src/loyalty
```

### 2. Configurar Variables de Entorno
```bash
# Copiar archivo de ejemplo
cp env.example .env

# Editar configuración
nano .env
```

**Configuraciones importantes:**
```env
# Base de datos
DB_HOST=localhost
DB_NAME=loyalty_system
DB_USER=loyalty_user
DB_PASSWORD=your_secure_password

# API
API_HOST=0.0.0.0
API_PORT=8000
API_WORKERS=4

# Seguridad
SECRET_KEY=your_super_secret_key_here
```

### 3. Instalar Dependencias
```bash
# Instalar dependencias Python
pip install -r ../../requirements.txt

# Instalar dependencias adicionales
pip install gunicorn uvicorn[standard]
```

---

## 🗄️ Configuración de Base de Datos

### 1. Crear Base de Datos
```sql
CREATE DATABASE loyalty_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Crear Usuario
```sql
CREATE USER 'loyalty_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON loyalty_system.* TO 'loyalty_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Ejecutar Migraciones
```bash
# Ejecutar script de migración
python scripts/deploy.py --env development
```

---

## 🚀 Despliegue

### Opción 1: Despliegue Automatizado (Recomendado)
```bash
# Despliegue completo
python scripts/deploy.py --env production

# Con parámetros personalizados
python scripts/deploy.py --env production --host 0.0.0.0 --port 8000 --workers 4
```

### Opción 2: Despliegue Manual
```bash
# 1. Verificar entorno
python scripts/deploy.py --env production

# 2. Ejecutar tests
python -m pytest tests/ -v

# 3. Iniciar servidor
uvicorn main:app --host 0.0.0.0 --port 8000 --workers 4
```

### Opción 3: Con Gunicorn (Producción)
```bash
# Crear archivo de configuración
cat > gunicorn.conf.py << EOF
bind = "0.0.0.0:8000"
workers = 4
worker_class = "uvicorn.workers.UvicornWorker"
timeout = 120
keepalive = 5
max_requests = 1000
max_requests_jitter = 100
EOF

# Iniciar con Gunicorn
gunicorn main:app -c gunicorn.conf.py
```

---

## 📊 Monitoreo y Mantenimiento

### 1. Script de Monitoreo
```bash
# Monitoreo único
python scripts/monitor.py --mode once

# Monitoreo continuo
python scripts/monitor.py --mode continuous --interval 60
```

### 2. Backup Automatizado
```bash
# Crear backup
python scripts/backup.py --action backup

# Restaurar backup
python scripts/backup.py --action restore --file /path/to/backup.sql.gz
```

### 3. Optimización
```bash
# Optimización completa
python scripts/optimize.py --type full

# Optimización específica
python scripts/optimize.py --type database
```

---

## 🔒 Seguridad

### 1. Firewall
```bash
# Abrir solo puertos necesarios
sudo ufw allow 8000/tcp  # API
sudo ufw allow 22/tcp     # SSH
sudo ufw enable
```

### 2. SSL/TLS (Recomendado)
```bash
# Instalar Certbot
sudo apt install certbot

# Obtener certificado
sudo certbot certonly --standalone -d api.cafevt.com

# Configurar Nginx con SSL
```

### 3. Variables de Entorno Seguras
```bash
# Generar claves seguras
openssl rand -hex 32  # Para SECRET_KEY
openssl rand -hex 32  # Para ENCRYPTION_KEY
```

---

## 📈 Escalabilidad

### 1. Load Balancer
```nginx
# Configuración Nginx
upstream loyalty_api {
    server 127.0.0.1:8000;
    server 127.0.0.1:8001;
    server 127.0.0.1:8002;
}

server {
    listen 80;
    server_name api.cafevt.com;
    
    location / {
        proxy_pass http://loyalty_api;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### 2. Redis para Caché
```bash
# Instalar Redis
sudo apt install redis-server

# Configurar en .env
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=your_redis_password
```

### 3. Base de Datos Clustering
```sql
-- Configurar replicación MySQL
-- Implementar según documentación oficial
```

---

## 🚨 Troubleshooting

### Problemas Comunes

#### 1. Error de Conexión a Base de Datos
```bash
# Verificar conexión
mysql -u loyalty_user -p loyalty_system

# Verificar variables de entorno
echo $DB_HOST $DB_NAME $DB_USER
```

#### 2. Puerto Ocupado
```bash
# Verificar puerto
netstat -tulpn | grep 8000

# Matar proceso
sudo kill -9 <PID>
```

#### 3. Permisos de Archivos
```bash
# Corregir permisos
chmod +x scripts/*.py
chmod 600 .env
```

#### 4. Logs de Error
```bash
# Ver logs de la aplicación
tail -f logs/loyalty_api.log

# Ver logs del sistema
journalctl -u loyalty-api -f
```

---

## 📞 Soporte

### Contactos
- **Desarrollador:** [Tu Email]
- **Documentación:** [Link a docs]
- **Issues:** [Link a GitHub Issues]

### Recursos Útiles
- [Documentación FastAPI](https://fastapi.tiangolo.com/)
- [Guía MySQL](https://dev.mysql.com/doc/)
- [Documentación Redis](https://redis.io/documentation)

---

## ✅ Checklist de Despliegue

- [ ] Variables de entorno configuradas
- [ ] Base de datos creada y migrada
- [ ] Dependencias instaladas
- [ ] Tests ejecutados y pasando
- [ ] Servidor iniciado correctamente
- [ ] Monitoreo configurado
- [ ] Backup configurado
- [ ] SSL configurado (opcional)
- [ ] Firewall configurado
- [ ] Documentación actualizada

---

**Última actualización:** Diciembre 2024  
**Versión:** 1.0 