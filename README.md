# TagFlow API REST

Backend API REST para **TagFlow**, una red social orientada a la interacción entre usuarios mediante publicaciones, comentarios, reacciones, mensajería privada, notificaciones y moderación.

Desarrollada con **Symfony 4.4**, autenticación mediante **JWT**, base de datos **MySQL/MariaDB** y documentación interactiva de la API.

---

## Tecnologías utilizadas

- PHP 7.4+
- Symfony 4.4
- Doctrine ORM
- MySQL / MariaDB
- JWT Authentication (LexikJWTAuthenticationBundle)
- NelmioApiDocBundle (Swagger/OpenAPI)
- Composer
- Docker

---

## Funcionalidades principales

### Autenticación y seguridad
- Registro de usuarios
- Inicio de sesión con JWT
- Refresh token
- Cierre de sesión
- Cierre de sesión en todos los dispositivos
- Recuperación de contraseña
- Restablecimiento de contraseña

### Gestión de usuarios
- Perfil público
- Edición del perfil propio
- Gestión de intereses
- Seguimiento entre usuarios
- Bloqueo y desbloqueo de usuarios
- Listado de seguidores y seguidos

### Publicaciones
- Crear publicaciones
- Editar publicaciones
- Eliminar publicaciones
- Asociar tags
- Gestión multimedia
- Guardar publicaciones
- Reacciones a publicaciones
- Comentarios

### Comentarios
- Crear comentarios
- Editar comentarios
- Eliminar comentarios
- Respuestas a comentarios
- Reacciones a comentarios

### Feed y exploración
- Feed personalizado
- Feed mixto
- Exploración de publicaciones públicas
- Filtrado por tags
- Búsqueda por contenido

### Mensajería privada
- Crear conversaciones
- Enviar mensajes
- Editar mensajes
- Eliminar mensajes
- Marcar mensajes como leídos

### Notificaciones
- Listado de notificaciones
- Marcar notificación como leída
- Marcar todas como leídas

### Reportes y moderación
- Reportar usuarios
- Reportar publicaciones
- Reportar comentarios
- Gestión de reportes
- Acciones administrativas sobre usuarios

---

# Instalación

## Clonar repositorio

```bash
git clone https://github.com/tu-usuario/tagflow-api.git
cd tagflow-api
```

---

## Instalar dependencias

```bash
composer install
```

---

## Configurar variables de entorno

Editar:

```bash
.env
```

Ejemplo:

```env
DATABASE_URL=mysql://usuario:password@127.0.0.1:3306/tagflow
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=tu_passphrase
```

---

## Crear base de datos

```bash
php bin/console doctrine:database:create
```

---

## Crear esquema

```bash
php bin/console doctrine:schema:update --force
```

---

## Cargar datos de prueba (fixtures)

```bash
php bin/console doctrine:fixtures:load
```

---

## Ejecutar servidor

```bash
symfony server:start
```

o con Docker.

---

# Documentación API

La documentación interactiva de la API está disponible en:

```text
/tagflow/doc
```

Ejemplo local:

```text
http://localhost:8082/tagflow/doc
```

Incluye:

- endpoints disponibles
- métodos HTTP
- autenticación requerida
- parámetros
- body JSON
- respuestas esperadas

---

# Autenticación

La API utiliza **JWT Bearer Token**.

Ejemplo de cabecera:

```http
Authorization: Bearer TU_TOKEN
```

---

# Ejemplo de login

### Endpoint

```http
POST /tagflow/auth/login
```

### Body

```json
{
  "email": "demo@test.com",
  "password": "123456"
}
```

### Respuesta

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "refresh_token": "abc123..."
}
```

---

# Estructura de endpoints

## Auth
```text
/tagflow/auth/register
/tagflow/auth/login
/tagflow/auth/refresh
/tagflow/auth/me
/tagflow/auth/logout
/tagflow/auth/logout-all
/tagflow/auth/forgot-password
/tagflow/auth/reset-password
```

## Users
```text
/tagflow/users/me
/tagflow/users/me/interests
/tagflow/users/{id_user}
/tagflow/users/{id_user}/followers
/tagflow/users/{id_user}/following
/tagflow/users/{id_user}/follow
/tagflow/users/{id_user}/block
```

## Tags
```text
/tagflow/tags
/tagflow/tags/{slug}
```

## Posts
```text
/tagflow/posts
/tagflow/posts/feed
/tagflow/posts/explore
/tagflow/posts/{id_post}
/tagflow/posts/{id_post}/tags
/tagflow/posts/{id_post}/media
/tagflow/posts/{id_post}/save
/tagflow/posts/{id_post}/reactions
/tagflow/posts/{id_post}/comments
```

## Comments
```text
/tagflow/comments/{id_comment}
/tagflow/comments/{id_comment}/replies
/tagflow/comments/{id_comment}/reactions
```

## Conversations
```text
/tagflow/conversations
/tagflow/conversations/{id_conversation}/messages
/tagflow/conversations/{id_conversation}/messages/{id_message}
/tagflow/conversations/{id_conversation}/messages/{id_message}/read
```

## Notifications
```text
/tagflow/notifications
/tagflow/notifications/{id_notification}/read
/tagflow/notifications/read-all
```

## Reports
```text
/tagflow/reports/posts/{id_post}
/tagflow/reports/comments/{id_comment}
/tagflow/reports/users/{id_user}
```

## Moderation
```text
/tagflow/moderation
/tagflow/moderation/reports/{type}/{id_report}
/tagflow/admin/users/{id_user}/status
```

---

# Testing

Se recomienda utilizar:

- Postman
- Swagger UI
- Insomnia

---

# Estado del proyecto

Proyecto académico / TFG orientado a la simulación de una red social moderna con arquitectura API REST.

---

# Autor

Desarrollado como proyecto **TagFlow**.
