# Sistema de Logística - Quesos y Productos Leslie

Sistema integral de gestión logística desarrollado en PHP puro con arquitectura MVC, MySQL 5.7 y Bootstrap 5.

## 📋 Características Principales

### 🏭 Módulo de Gestión de Producción e Inventario
- Registro de producción en 3 modalidades (granel, pieza, paquete)
- Gestión de inventario por lotes con trazabilidad completa
- Asignación inteligente de lotes a pedidos
- Alertas proactivas por proximidad de caducidad

### 📦 Módulo de Gestión de Pedidos (Preventas)
- Captura multicanal de pedidos (web, WhatsApp)
- Flexibilidad para ajustar cantidades en entrega
- Validación con códigos QR únicos
- Seguimiento de estatus en tiempo real

### 🚛 Módulo de Optimización Logística y Rutas
- Gestión de recursos (vendedores-choferes)
- Planificación de rutas optimizadas
- Monitoreo en tiempo real
- Protocolos de validación (QR, WhatsApp)

### 💰 Módulo de Ventas en Punto de Entrega
- Ventas directas durante la entrega
- Verificación mediante QR y WhatsApp
- Gestión de múltiples métodos de pago

### 🔄 Módulo de Control de Retornos y Calidad
- Registro de devoluciones con trazabilidad
- Evaluación de calidad para reingreso a inventario
- Control de mermas

### 😊 Módulo de Experiencia del Cliente
- Encuestas multicanal de satisfacción
- Análisis de feedback y calificaciones
- Reportes segmentados

### 📊 Módulo de Analítica y Reportes
- Cierre operativo diario automatizado
- Reportes especializados con filtros avanzados
- Dashboards interactivos con gráficas

### 👥 Módulo de Gestión de Clientes
- Base de datos centralizada
- Histórico integral de pedidos y entregas
- Comunicación integrada

### 💼 Módulo de Administración Financiera
- Control detallado de ingresos por canal
- Gestión de cobranza y conciliación
- Exportación en múltiples formatos

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 7+ (puro, sin framework)
- **Base de Datos:** MySQL 5.7
- **Frontend:** HTML5, CSS3, JavaScript ES6
- **Framework CSS:** Bootstrap 5
- **Gráficas:** Chart.js
- **Calendario:** FullCalendar.js
- **Arquitectura:** MVC (Model-View-Controller)
- **Autenticación:** Sesiones PHP + password_hash()

## 📥 Instalación

### Requisitos del Sistema

- **Servidor Web:** Apache 2.4+ con mod_rewrite habilitado
- **PHP:** 7.4+ con las siguientes extensiones:
  - PDO
  - PDO_MySQL
  - Session
  - JSON
  - mbstring
- **Base de Datos:** MySQL 5.7+ o MariaDB 10.3+

### Pasos de Instalación

1. **Clonar o descargar el repositorio:**
   ```bash
   git clone https://github.com/danjohn007/LogisicaLeslie.git
   cd LogisicaLeslie
   ```

2. **Configurar el servidor web:**
   - Asegúrese de que mod_rewrite esté habilitado en Apache
   - Configure el DocumentRoot para apuntar al directorio del proyecto
   - El archivo `.htaccess` ya está incluido para URL amigables

3. **Crear la base de datos:**
   ```sql
   CREATE DATABASE logistica_leslie CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Importar el esquema de la base de datos:**
   ```bash
   mysql -u tu_usuario -p logistica_leslie < database/schema.sql
   ```

5. **Importar datos de ejemplo (opcional):**
   ```bash
   mysql -u tu_usuario -p logistica_leslie < database/sample_data.sql
   ```

6. **Configurar la conexión a la base de datos:**
   Edite el archivo `config/config.php` y modifique las constantes de conexión:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'logistica_leslie');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

7. **Configurar permisos:**
   ```bash
   chmod 755 public/uploads/
   chmod 755 public/qr_codes/
   chmod 755 logs/
   ```

8. **Acceder al sistema:**
   - Abra su navegador y vaya a: `http://su-servidor/LogisicaLeslie/`
   - Para probar la conexión: `http://su-servidor/LogisicaLeslie/test-connection`

### Usuarios por Defecto (con datos de ejemplo)

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin | password | Administrador |
| gerente | password | Gerente |
| vendedor1 | password | Vendedor |
| chofer1 | password | Chofer |
| almacen1 | password | Almacén |

**⚠️ IMPORTANTE:** Cambie todas las contraseñas por defecto antes de usar en producción.

## 🔧 Configuración

### URL Base Automática
El sistema detecta automáticamente la URL base, pero puede configurarla manualmente en `config/config.php` si es necesario.

### Configuraciones Principales
Todas las configuraciones se encuentran en `config/config.php`:

- `APP_ENVIRONMENT`: 'development' o 'production'
- `SESSION_LIFETIME`: Tiempo de vida de la sesión en segundos
- `QR_CODE_SIZE`: Tamaño de los códigos QR generados
- Configuraciones de WhatsApp API
- Zona horaria del sistema

## 🚀 Uso del Sistema

### Acceso Inicial
1. Vaya a la URL de su instalación
2. Haga clic en "Test de Conexión" para verificar la configuración
3. Use "Iniciar Sesión" con las credenciales por defecto
4. Acceda al dashboard principal

### Módulos Principales
- **Dashboard:** Vista general del sistema
- **Producción:** Gestión de lotes y producción
- **Inventario:** Control de stock y movimientos
- **Pedidos:** Gestión de pedidos y preventas
- **Rutas:** Planificación y seguimiento de entregas
- **Ventas:** Registro de ventas directas
- **Reportes:** Análisis y reportes del negocio

## 📊 Estructura del Proyecto

```
LogisicaLeslie/
├── app/
│   ├── controllers/          # Controladores MVC
│   ├── models/              # Modelos de datos
│   ├── views/               # Vistas y templates
│   └── core/                # Clases base del framework
├── config/                  # Archivos de configuración
├── database/                # Scripts SQL
├── public/                  # Archivos públicos (CSS, JS, imágenes)
├── logs/                    # Archivos de log
├── tests/                   # Pruebas del sistema
├── .htaccess               # Configuración Apache
└── index.php               # Punto de entrada principal
```

## 🛡️ Seguridad

- Contraseñas encriptadas con `password_hash()`
- Protección CSRF en formularios
- Validación de entrada de datos
- Control de acceso basado en roles
- Sesiones seguras con tiempo de vida limitado

## 🔍 Solución de Problemas

### Problemas Comunes

1. **Error de conexión a la base de datos:**
   - Verifique las credenciales en `config/config.php`
   - Asegúrese de que MySQL esté ejecutándose
   - Use `test-connection` para diagnosticar

2. **URLs no funcionan (404):**
   - Verifique que mod_rewrite esté habilitado
   - Confirme que el archivo `.htaccess` esté presente
   - Revise la configuración de Apache

3. **Errores de permisos:**
   - Asegúrese de que Apache tenga permisos de escritura en directorios necesarios
   - Configure correctamente los permisos de archivos

## 🗺️ Roadmap

### Fase 1 - Implementación Básica ✅
- [x] Arquitectura MVC base
- [x] Sistema de autenticación
- [x] Gestión básica de productos e inventario
- [x] Interface principal con Bootstrap 5

### Fase 2 - Módulos Core (En desarrollo)
- [ ] Sistema completo de pedidos
- [ ] Gestión de rutas y logística
- [ ] Módulo de ventas directas
- [ ] Control de retornos

### Fase 3 - Funcionalidades Avanzadas
- [ ] Integración con WhatsApp API
- [ ] Generación de códigos QR
- [ ] Reportes avanzados con gráficas
- [ ] Aplicación móvil para choferes

### Fase 4 - Optimización
- [ ] Cache y optimización de consultas
- [ ] API REST para integraciones
- [ ] Módulo de facturación electrónica
- [ ] Backup automático

## 📞 Soporte

Para soporte técnico o reportar errores, por favor:
1. Revise la documentación
2. Use la función de test de conexión
3. Verifique los logs del sistema
4. Contacte al equipo de desarrollo

## 📄 Licencia

Este proyecto está desarrollado para uso específico de Quesos y Productos Leslie.

---

**Sistema de Logística Leslie** - Versión 1.0.0  
Desarrollado con ❤️ para optimizar la logística de productos lácteos.
