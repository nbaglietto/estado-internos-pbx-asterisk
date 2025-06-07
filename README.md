Sistema de Extensiones Telefónicas para telefonia ip Asterisk
Sistema web para monitorear el estado de las extensiones telefónicas conectadas a Asterisk/Isabel PBX.
Características

📞 Monitoreo en tiempo real de extensiones
📊 Agrupación por áreas/departamentos
🎨 Interfaz moderna y responsive
🔄 Actualización automática del estado
📋 Generación de reportes PDF

Requisitos

PHP 7.4 o superior
Servidor web (Apache/Nginx)
Acceso a API de Isabel PBX >> Luego la subire
Composer (para dependencias)

Instalación

Clonar el repositorio:

bashgit clone https://github.com/nbaglietto/estado-internos-pbx-asterisk.git

cd estado-internos-pbx-asterisk

Instalar dependencias:

bashcomposer install

Configurar variables de entorno:

bashcp .env.example .env
nano .env

Completar las variables en .env:

APP_NAME=Sistema Internos
API_URL=https://tu-servidor/api/estado_extensiones.php
API_TIMEOUT=3

Configurar servidor web para que apunte a la carpeta public/

Estructura del Proyecto
├── app/
│   ├── config/         # Configuraciones
│   ├── lib/           # Clases principales
│   └── database/      # Archivos de base de datos
├── public/            # Archivos públicos
│   ├── css/          # Estilos
│   ├── js/           # JavaScript
│   └── img/          # Imágenes
├── logs/             # Archivos de log
└── vendor/           # Dependencias (no incluido en Git)
Uso

Acceder a http://tu-servidor/public/
El sistema mostrará automáticamente las extensiones agrupadas por área
Los estados se actualizan automáticamente cada 30 segundos

Estados de Extensiones

🟢 Disponible: Extensión libre y operativa
🔴 Ocupado: Extensión en uso
🔔 Llamando: Extensión recibiendo llamada
❌ No registrada: Extensión fuera de línea

Desarrollo
Agregar nuevas funcionalidades

Crear rama para la funcionalidad:

bashgit checkout -b feature/nueva-funcionalidad

Desarrollar y hacer commits:

bashgit add .
git commit -m "Descripción de los cambios"

Subir rama y crear Pull Request:

bashgit push origin feature/nueva-funcionalidad
Mantenimiento

Los logs se almacenan en la carpeta logs/
Para limpiar archivos temporales: git clean -fd
Actualizar dependencias: composer update

Contribuir

Fork del repositorio
Crear rama para tu funcionalidad
Hacer commit de tus cambios
Push a tu rama
Crear Pull Request

Licencia
Este proyecto es de uso interno. Todos los derechos reservados.
Soporte
Para soporte técnico, contactar a:
Nahuel Baglietto
Email: nbaglietto@metrotecno.com.ar
4702-9920 Extensión: 611 (Soporte TI)
