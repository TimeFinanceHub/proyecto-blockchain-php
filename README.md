# Proyecto Blockchain PHP

Este proyecto es una aplicación web robusta desarrollada en PHP que integra funcionalidades de una blockchain, gestión de usuarios, un sistema de blog, mensajería y una API RESTful. Su diseño modular permite una fácil expansión y mantenimiento.

## Características Principales

*   **Sistema de Usuarios Completo:** Registro, inicio de sesión, perfiles de usuario, y administración de usuarios (para administradores).
*   **Implementación de Blockchain:** Core de blockchain con creación y verificación de bloques, minería y gestión de la cadena.
*   **API RESTful:** Conjunto de endpoints para interactuar con todas las funcionalidades del sistema (usuarios, blockchain, blog, mensajería, tareas, etc.).
*   **Sistema de Blog:** Creación, lectura, actualización y eliminación de publicaciones.
*   **Mensajería Interna:** Comunicación entre usuarios de la plataforma.
*   **Módulos:** Estructura modular para componentes como autenticación, blockchain y lista de tareas (To-Do List).
*   **Interfaz de Usuario (Frontend):** Desarrollada con HTML, CSS y JavaScript, ofreciendo una experiencia de usuario dinámica.
*   **Base de Datos:** Gestión de datos a través de MySQL/MariaDB.

## Alcance del Proyecto

Este proyecto busca establecer una aplicación web fundamental con capacidades de blockchain integradas. Se enfoca en demostrar funcionalidades básicas como la gestión de usuarios, un sistema de blog sencillo, mensajería básica y las operaciones fundamentales de una blockchain (creación de bloques, recuperación de la cadena). La capa API permite la interacción programática con estas características.

### Dentro del Alcance (In-Scope):
*   Autenticación de usuarios (registro, inicio de sesión, gestión de perfiles).
*   Operaciones CRUD básicas para publicaciones de blog.
*   Operaciones fundamentales de blockchain (minería, visualización de la cadena).
*   API RESTful para las funcionalidades principales.
*   Arquitectura modular para las funcionalidades clave.
*   Frontend básico para la interacción del usuario.

### Fuera del Alcance (Out-of-Scope) para esta versión inicial:
*   Funciones avanzadas de blockchain (ej. contratos inteligentes, mecanismos de consenso complejos, redes P2P).
*   Auditorías de seguridad de alto nivel más allá de las buenas prácticas básicas.
*   Escalabilidad para entornos de producción de alto tráfico.
*   Notificaciones en tiempo real.
*   Gestión compleja de autorizaciones/roles (más allá de administrador/usuario básico).
*   Pasarelas de pago o funcionalidades de comercio electrónico.
*   Frameworks de pruebas automatizadas (pruebas unitarias, de integración - aunque se proporciona una guía de pruebas manuales).

## Estructura del Proyecto

```
.
├── admin_users.php
├── blog.php
├── buy.php
├── chain_union.php
├── composer.json
├── cv.html
├── dashboard.php
├── database.sql
├── favicon.png
├── GEMINI.md
├── gui_documentation.html
├── index.php
├── login.php
├── logout.php
├── messages.php
├── profile.php
├── proyecto_blockchain_php.zip
├── README.md
├── register.php
├── sales_page.html
├── testing_guide.html
├── .git/
├── api/
│   ├── admin_users.php
│   ├── config.php
│   ├── download_project.php
│   ├── get_chain.php
│   ├── login.php
│   ├── messages.php
│   ├── mine_block.php
│   ├── posts.php
│   ├── profile.php
│   ├── register.php
│   ├── send_phone_verification.php
│   ├── settings.php
│   ├── todo.php
│   ├── users.php
│   ├── verify_phone.php
│   ├── video_notes.php
│   └── videos.php
├── core/
│   ├── Block.php
│   └── Blockchain.php
├── css/
│   ├── animations.css
│   ├── background.png
│   └── style.css
├── js/
│   ├── admin_users.js
│   ├── api.js
│   ├── blog.js
│   ├── buy.js
│   ├── chain_union.js
│   ├── dashboard.js
│   ├── login.js
│   ├── messages.js
│   ├── profile.js
│   └── register.js
├── modules/
│   ├── auth_user_management/
│   │   ├── admin_users.php
│   │   ├── config.php
│   │   ├── login.php
│   │   ├── profile.php
│   │   ├── register.php
│   │   └── schema.sql
│   ├── blockchain/
│   │   ├── config.php
│   │   ├── get_chain.php
│   │   ├── mine_block.php
│   │   ├── schema.sql
│   │   └── core/
│   │       ├── Block.php
│   │       └── Blockchain.php
│   └── todo_list/
│       ├── config.php
│       ├── schema.sql
│       └── todo.php
└── templates/
```

## Guía de Instalación y Configuración Paso a Paso

Sigue estos pasos para poner en marcha el proyecto desde cero en tu entorno local.

### 1. Requisitos Previos

Asegúrate de tener instalado lo siguiente:

*   **Servidor Web:** Apache o Nginx.
*   **PHP:** Versión 7.4 o superior (con extensiones como `mysqli`, `json`, `curl` habilitadas).
*   **Base de Datos:** MySQL o MariaDB.
*   **Composer:** Gestor de dependencias para PHP.
*   **Git:** Para clonar el repositorio.

### 2. Clonar el Repositorio

Abre tu terminal y ejecuta el siguiente comando:

```bash
git clone [URL_DEL_REPOSITORIO]
cd proyecto_blockchain_php
```

### 3. Configuración de la Base de Datos

1.  **Crear la Base de Datos:**
    Accede a tu gestor de bases de datos (phpMyAdmin, MySQL Workbench, o la línea de comandos) y crea una nueva base de datos. Por ejemplo:

    ```sql
    CREATE DATABASE `php_blockchain_db`;
    ```

2.  **Importar Esquemas:**
    Importa el archivo principal `database.sql` y los esquemas específicos de los módulos.

    ```bash
    mysql -u tu_usuario -p php_blockchain_db < database.sql
    mysql -u tu_usuario -p php_blockchain_db < modules/auth_user_management/schema.sql
    mysql -u tu_usuario -p php_blockchain_db < modules/blockchain/schema.sql
    mysql -u tu_usuario -p php_blockchain_db < modules/todo_list/schema.sql
    ```
    *(Reemplaza `tu_usuario` con tu usuario de MySQL y, cuando se te pida, ingresa tu contraseña.)*

3.  **Configurar Conexiones a la Base de Datos:**
    Edita los archivos `config.php` en el directorio `api/` y dentro de cada módulo (`modules/*/config.php`) para que coincidan con tus credenciales de base de datos.

    **Ejemplo (`api/config.php` o `modules/auth_user_management/config.php`):**

    ```php
    <?php
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'tu_usuario');
    define('DB_PASSWORD', 'tu_contraseña');
    define('DB_NAME', 'php_blockchain_db');

    // Intentar conexión a la base de datos MySQL
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Verificar conexión
    if($link === false){
        die("ERROR: No se pudo conectar a la base de datos. " . mysqli_connect_error());
    }
    ?>
    ```
    Asegúrate de replicar esta configuración en todos los `config.php` relevantes.

### 4. Instalar Dependencias de PHP

Desde el directorio raíz del proyecto, ejecuta Composer:

```bash
composer install
```

### 5. Configuración del Servidor Web

*   **Para Apache:**
    Asegúrate de que `mod_rewrite` esté habilitado y configura un Virtual Host para apuntar la raíz del documento al directorio `proyecto_blockchain_php/`. Un ejemplo básico de configuración de `.htaccess` (si no tienes un Virtual Host más complejo) podría ser:

    ```apache
    # .htaccess en el directorio raíz del proyecto
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
    ```

*   **Para Nginx:**
    Configura un bloque `server` que apunte al directorio del proyecto y use `index.php` como archivo de entrada para solicitudes no existentes.

    ```nginx
    server {
        listen 80;
        server_name your_domain_or_ip;
        root /path/to/your/proyecto_blockchain_php;
        index index.php index.html index.htm;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock; # Asegúrate de que esta ruta sea correcta para tu versión de PHP-FPM
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.ht {
            deny all;
        }
    }
    ```
    *(Reemplaza `/path/to/your/proyecto_blockchain_php` con la ruta absoluta a tu proyecto y `php7.4-fpm.sock` con la versión correcta de PHP-FPM).*

### 6. Acceder a la Aplicación

Una vez configurado el servidor web, abre tu navegador y navega a la URL donde está alojado el proyecto (ej. `http://localhost/proyecto_blockchain_php/` o `http://your_domain_or_ip/`).

## Uso de la Aplicación

*   **Navegación:** Explora las diferentes secciones como el dashboard, blog, registro/login, etc.
*   **Interacción con Blockchain:**
    *   Puedes minar nuevos bloques a través de la interfaz web o llamando al endpoint `api/mine_block.php`.
    *   Visualiza la cadena completa accediendo a `api/get_chain.php`.
*   **API:** Utiliza herramientas como Postman o cURL para interactuar con los endpoints RESTful disponibles en el directorio `api/`.

## Guía de Pruebas

Para una guía exhaustiva sobre cómo testear todas las funcionalidades del sistema, incluyendo autenticación, blog, blockchain, mensajería y API, consulta el documento:

*   [**Guía de Pruebas Completa**](testing_guide.html)

## Endpoints de la API (Ejemplos)

A continuación, se listan algunos de los endpoints clave disponibles. Para detalles completos sobre parámetros y respuestas, se recomienda consultar el código fuente o usar herramientas de desarrollo.

*   `GET /api/get_chain.php`: Obtiene la cadena de bloques completa.
*   `POST /api/mine_block.php`: Mina un nuevo bloque en la cadena.
*   `POST /api/register.php`: Registra un nuevo usuario.
*   `POST /api/login.php`: Inicia sesión de un usuario.
*   `GET /api/profile.php`: Obtiene el perfil del usuario autenticado.
*   `POST /api/profile.php`: Actualiza el perfil del usuario.
*   `GET /api/posts.php`: Obtiene publicaciones del blog.
*   `POST /api/posts.php`: Crea una nueva publicación.
*   `GET /api/messages.php`: Obtiene mensajes del usuario.
*   `POST /api/messages.php`: Envía un mensaje.
*   `GET /api/todo.php`: Obtiene tareas pendientes.
*   `POST /api/todo.php`: Crea una nueva tarea pendiente.

## Licencia y Derechos de Autor

Este proyecto, incluyendo todo su código fuente, documentación y activos, es propiedad intelectual exclusiva de [**Tu Nombre/Entidad**].

Al utilizar, modificar o distribuir este proyecto, usted acepta los siguientes términos:

1.  **Créditos y Atribución:** Cualquier uso o derivación de este proyecto debe incluir una atribución clara y visible a [**Tu Nombre/Entidad**] como el autor original y propietario intelectual. Esto debe incluir, pero no limitarse a, la conservación de esta sección de licencia en el `README.md` y en cualquier documentación relevante.
2.  **Propiedad Intelectual:** La propiedad intelectual sobre el diseño, la arquitectura y el código original de este proyecto permanece en todo momento con [**Tu Nombre/Entidad**]. Las modificaciones o mejoras realizadas por terceros no confieren derechos de propiedad sobre el proyecto original.
3.  **Monetización y Participación en Ganancias:** Si este proyecto (o cualquier derivación, adaptación o software que incorpore sustancialmente este proyecto) es utilizado con fines comerciales o monetizado de alguna forma, se requerirá un porcentaje del [**especificar porcentaje, ej. 10%**] de las ganancias brutas obtenidas de dicha monetización. Este porcentaje debe ser negociado y acordado formalmente con [**Tu Nombre/Entidad**] antes de la monetización.
4.  **Publicación de Modificaciones:** Cualquier modificación, mejora o adaptación realizada sobre este proyecto debe ser publicada y puesta a disposición del autor original ([**Tu Nombre/Entidad**]) de forma transparente y accesible. Esto incluye la publicación del código fuente de las modificaciones, idealmente mediante un pull request o un repositorio público enlazado. El autor original se reserva el derecho de integrar o rechazar dichas modificaciones en el proyecto principal.

Para cualquier consulta, propuesta de colaboración o solicitud de licencia comercial personalizada, por favor, contacta a [**tu.email@example.com**].

---

**¡Disfruta del proyecto!**