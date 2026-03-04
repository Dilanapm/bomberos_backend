# Bomberos App — Documentación API REST

**Base URL:** `http://localhost:8081/api/v1` (desarrollo)  
**Formato:** JSON  
**Auth:** Laravel Sanctum (Bearer Token)

---

## Headers requeridos en TODAS las peticiones

```
X-Client-Key: b268aa0a224d7361174815675b940a16c8c1b047f1b1982270ddf6aa3749bed8
Accept: application/json
Content-Type: application/json
```

En rutas protegidas agregar además:
```
Authorization: Bearer {token}
```

---

## Estructura de respuesta estándar

**Éxito:**
```json
{
  "status": "success",
  "message": "Descripción",
  "data": { ... }
}
```

**Error:**
```json
{
  "status": "error",
  "message": "Descripción del error",
  "code": "ERROR_CODE"
}
```

**Error de validación (422):**
```json
{
  "status": "error",
  "message": "Datos inválidos.",
  "errors": {
    "campo": ["El campo es requerido."]
  }
}
```

---

## Códigos de error globales

| Código | HTTP | Descripción |
|--------|------|-------------|
| `MISSING_CLIENT_KEY` | 401 | Falta el header `X-Client-Key` |
| `INVALID_CREDENTIALS` | 401 | Email o contraseña incorrectos |
| `ACCOUNT_DISABLED` | 403 | Cuenta desactivada por el administrador |
| `ADMIN_WEB_ONLY` | 403 | El admin solo puede ingresar por el panel web |
| `ROLE_FORBIDDEN` | 403 | El usuario no tiene rol válido para la app |
| `EMAIL_NOT_VERIFIED` | 403 | Correo no verificado (ver flujo de OTP) |
| `UNAUTHENTICATED` | 401 | Token inválido o expirado |
| `TOO_MANY_REQUESTS` | 429 | Rate limit alcanzado |

---

## 1. Autenticación

### 1.1 Login

```
POST /auth/login
```

**Body:**
```json
{
  "email": "instructor@bomberos.com",
  "password": "Password123"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Autenticación exitosa.",
  "data": {
    "token": "5|2R2UpZGZF8...",
    "token_type": "Bearer",
    "expires_at": "2026-03-24T19:54:48+00:00",
    "user": {
      "id": 1,
      "name": "Carlos Instructor",
      "username": "carlos_inst",
      "email": "instructor@bomberos.com",
      "role": "instructor",
      "avatar_url": "http://localhost:8081/storage/avatars/1/foto.jpg"
    }
  }
}
```

**Caso especial — correo no verificado (403):**
```json
{
  "status": "error",
  "message": "Debes verificar tu correo. Hemos enviado un código de 6 dígitos a instructor@bomberos.com",
  "code": "EMAIL_NOT_VERIFIED",
  "data": {
    "user_id": 1,
    "email": "instructor@bomberos.com"
  }
}
```
→ Navegar a la pantalla de verificación OTP usando `user_id` y `email`.

**Rate limit:** 5 intentos / minuto por email + IP.

---

### 1.2 Registro de aprendiz (con código de instructor)

```
POST /auth/register
```

**Body:**
```json
{
  "name": "Juan Pérez",
  "username": "juanp",
  "email": "juan@example.com",
  "password": "Password123",
  "password_confirmation": "Password123",
  "registration_code": "AB12CD34"
}
```

> El `registration_code` lo proporciona el instructor desde su pantalla de código (8 caracteres, mayúsculas).

**Respuesta exitosa (201):**
```json
{
  "status": "success",
  "message": "¡Registro exitoso! Hemos enviado un código de 6 dígitos a juan@example.com. Ingrésalo para activar tu cuenta.",
  "data": {
    "user_id": 12,
    "email": "juan@example.com"
  }
}
```
→ **No hay token todavía.** Navegar a la pantalla de verificación OTP.

**Rate limit:** 5 intentos / 10 minutos por IP.

---

### 1.3 Verificar correo con OTP

```
POST /auth/email/verify
```

**Body:**
```json
{
  "user_id": 12,
  "code": "123456"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "¡Correo verificado exitosamente! Bienvenido a Bomberos App.",
  "data": {
    "token": "6|xKp2mN...",
    "token_type": "Bearer",
    "expires_at": "2026-03-24T20:00:00+00:00",
    "user": {
      "id": 12,
      "name": "Juan Pérez",
      "username": "juanp",
      "email": "juan@example.com",
      "role": "aprendiz",
      "avatar_url": null
    }
  }
}
```
→ Guardar el `token` en almacenamiento seguro (`flutter_secure_storage`). El usuario ya está dentro de la app.

**Error — código incorrecto o expirado (422):**
```json
{
  "status": "error",
  "message": "El código es incorrecto o ha expirado.",
  "code": "INVALID_OTP"
}
```

**Rate limit:** 10 intentos / 10 minutos por IP.

---

### 1.4 Reenviar código OTP

```
POST /auth/email/resend
```

**Body:**
```json
{
  "user_id": 12
}
```

**Respuesta (200):** *(siempre la misma, sin importar si el usuario existe)*
```json
{
  "status": "success",
  "message": "Si la cuenta existe y no está verificada, recibirás un nuevo código en tu correo."
}
```

**Rate limit:** 3 reenvíos / 10 minutos por IP.

---

### 1.5 Logout

```
POST /auth/logout
Authorization: Bearer {token}
```

Sin body.

**Respuesta (200):**
```json
{
  "status": "success",
  "message": "Sesión cerrada exitosamente."
}
```
→ Eliminar el token del almacenamiento local.

---

### 1.6 Datos del usuario actual

```
GET /auth/me
Authorization: Bearer {token}
```

**Respuesta (200):**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "name": "Carlos Instructor",
      "username": "carlos_inst",
      "email": "instructor@bomberos.com",
      "role": "instructor",
      "avatar_url": "http://localhost:8081/storage/avatars/1/foto.jpg"
    }
  }
}
```

---

### 1.7 Olvidé mi contraseña

```
POST /auth/forgot-password
```

**Body:**
```json
{
  "email": "instructor@bomberos.com"
}
```

**Respuesta (200):** *(siempre igual, anti-enumeración)*
```json
{
  "status": "success",
  "message": "Si el correo está registrado, recibirás las instrucciones en tu bandeja de entrada."
}
```
→ El email contiene un botón que abre la app con deep link: `bomberos://reset-password?token=xxx&email=xxx`

**Rate limit:** 3 solicitudes / 10 minutos.

---

### 1.8 Restablecer contraseña

```
POST /auth/reset-password
```

**Body:**
```json
{
  "token": "el-token-del-deep-link",
  "email": "instructor@bomberos.com",
  "password": "NuevaPassword123",
  "password_confirmation": "NuevaPassword123"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Contraseña restablecida exitosamente. Por favor inicia sesión."
}
```
→ Redirigir al login. Todos los tokens anteriores son revocados automáticamente.

**Reglas de contraseña:** mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número.

**Rate limit:** 5 intentos / 10 minutos.

---

## 2. Perfil de usuario

Todos los endpoints de esta sección requieren `Authorization: Bearer {token}`.

### 2.1 Actualizar nombre y/o username

```
PATCH /profile
Authorization: Bearer {token}
```

**Body (todos los campos son opcionales, envía solo los que cambien):**
```json
{
  "name": "Carlos A. Rodríguez",
  "username": "carlos_rod"
}
```

**Reglas:**
- `name`: solo letras, espacios, guiones y puntos
- `username`: letras, números, guion bajo y punto (único en la BD)

**Respuesta (200):**
```json
{
  "status": "success",
  "message": "Perfil actualizado.",
  "data": {
    "user": {
      "id": 1,
      "name": "Carlos A. Rodríguez",
      "username": "carlos_rod",
      "email": "instructor@bomberos.com",
      "role": "instructor",
      "avatar_url": null
    }
  }
}
```

---

### 2.2 Cambiar contraseña

```
POST /profile/password
Authorization: Bearer {token}
```

**Body:**
```json
{
  "current_password": "Password123",
  "password": "NuevaPassword456",
  "password_confirmation": "NuevaPassword456"
}
```

**Respuesta (200):**
```json
{
  "status": "success",
  "message": "Contraseña actualizada. Por seguridad, otros dispositivos han sido desconectados."
}
```
→ El token actual **sigue siendo válido**. Solo los tokens de otros dispositivos son revocados.

**Error — contraseña actual incorrecta (422):**
```json
{
  "status": "error",
  "message": "Datos inválidos.",
  "errors": {
    "current_password": ["La contraseña actual es incorrecta."]
  }
}
```

---

### 2.3 Subir avatar

```
POST /profile/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body (form-data):**
```
avatar: [archivo de imagen]
```

**Restricciones:**
- Formatos: JPG, JPEG, PNG, WEBP
- Tamaño máximo: 2 MB
- Dimensiones mínimas: 100×100 px

**Respuesta (200):**
```json
{
  "status": "success",
  "message": "Avatar actualizado.",
  "data": {
    "avatar_url": "http://localhost:8081/storage/avatars/1/foto.webp"
  }
}
```

---

### 2.4 Eliminar avatar

```
DELETE /profile/avatar
Authorization: Bearer {token}
```

Sin body.

**Respuesta (200):**
```json
{
  "status": "success",
  "message": "Avatar eliminado.",
  "data": {
    "avatar_url": null
  }
}
```

---

## 3. Instructor — Códigos de registro

Solo accesible para usuarios con rol `instructor`.

### 3.1 Generar código de registro

```
POST /instructor/registration-code
Authorization: Bearer {token}  (instructor)
```

Sin body.

**Respuesta (201):**
```json
{
  "status": "success",
  "message": "Código generado.",
  "data": {
    "code": "AB12CD34",
    "expires_at": "2026-02-22T21:30:00+00:00",
    "expires_in_sec": 1800,
    "max_uses": 50
  }
}
```

→ Mostrar el código grande en pantalla con un **countdown** de 30 minutos usando `expires_in_sec`.  
→ `max_uses: 50` — hasta 50 aprendices pueden registrarse con este código.  
→ Si el instructor ya tenía un código activo, es **revocado automáticamente** y se genera uno nuevo.

---

### 3.2 Ver código activo

```
GET /instructor/registration-code/active
Authorization: Bearer {token}  (instructor)
```

**Respuesta con código activo (200):**
```json
{
  "status": "success",
  "data": {
    "code": "AB12CD34",
    "expires_at": "2026-02-22T21:30:00+00:00",
    "expires_in_sec": 743,
    "uses": 3,
    "max_uses": 50
  }
}
```

**Sin código activo (200):**
```json
{
  "status": "success",
  "data": null
}
```

→ Usar `expires_in_sec` para el countdown. Si `data` es `null`, mostrar botón "Generar código".

---

### 3.3 Revocar código activo

```
DELETE /instructor/registration-code
Authorization: Bearer {token}  (instructor)
```

Sin body.

**Respuesta (200):**
```json
{
  "status": "success",
  "message": "Código revocado."
}
```

---

## 4. Flujos completos en Flutter

### Flujo A — Login normal

```
1. POST /auth/login
   ├── 200 → guardar token → ir a Home
   └── 403 EMAIL_NOT_VERIFIED → ir a pantalla OTP (guardar user_id + email)
         ├── POST /auth/email/verify → 200 → guardar token → ir a Home
         └── "No llegó el código" → POST /auth/email/resend → mostrar snackbar
```

### Flujo B — Registro de aprendiz

```
1. Pantalla: pedir código al instructor y mostrárselo en su app
2. POST /auth/register (con registration_code)
   └── 201 → guardar user_id + email → ir a pantalla OTP
         └── POST /auth/email/verify → 200 → guardar token → ir a Home
```

### Flujo C — Olvidé mi contraseña

```
1. POST /auth/forgot-password → mostrar "Revisa tu correo"
2. El usuario toca el botón del email → deep link abre la app
3. App recibe: bomberos://reset-password?token=xxx&email=xxx
   └── POST /auth/reset-password → 200 → ir a Login
```

### Flujo D — Instructor genera código para aprendices

```
1. GET /instructor/registration-code/active
   ├── data != null → mostrar código + countdown + uses/max_uses
   └── data == null → mostrar botón "Generar código"
         └── POST /instructor/registration-code → mostrar código + countdown

2. El instructor le dicta/muestra el código a los aprendices
3. Si quiere invalidarlo antes: DELETE /instructor/registration-code
```

---

## 5. Almacenamiento recomendado en Flutter

```dart
// Usar: flutter_secure_storage
const storage = FlutterSecureStorage();

// Guardar tras login/verify exitoso
await storage.write(key: 'token', value: data['token']);
await storage.write(key: 'user_role', value: data['user']['role']);
await storage.write(key: 'user_id', value: data['user']['id'].toString());

// Leer en cada request
final token = await storage.read(key: 'token');

// Limpiar en logout
await storage.deleteAll();
```

---

## 6. Manejo de errores recomendado en Flutter

```dart
// Interceptor global con Dio
dio.interceptors.add(InterceptorsWrapper(
  onError: (error, handler) {
    final response = error.response;
    if (response == null) return handler.next(error);

    final code = response.data['code'];

    switch (code) {
      case 'UNAUTHENTICATED':
        // Token expirado → limpiar storage → ir a Login
        break;
      case 'EMAIL_NOT_VERIFIED':
        // Ir a pantalla de OTP con user_id y email del response
        final userId = response.data['data']['user_id'];
        final email  = response.data['data']['email'];
        break;
      case 'ACCOUNT_DISABLED':
        // Mostrar diálogo y limpiar sesión
        break;
      case 'TOO_MANY_REQUESTS':
        // Mostrar "Demasiados intentos, espera un momento"
        break;
    }
  },
));
```

---

## 7. Tabla resumen de rutas

| Método | Ruta | Auth | Rol | Descripción |
|--------|------|------|-----|-------------|
| POST | `/auth/login` | No | — | Iniciar sesión |
| POST | `/auth/register` | No | — | Registrar aprendiz con código |
| POST | `/auth/email/verify` | No | — | Verificar OTP de 6 dígitos |
| POST | `/auth/email/resend` | No | — | Reenviar OTP |
| POST | `/auth/forgot-password` | No | — | Solicitar email de reset |
| POST | `/auth/reset-password` | No | — | Cambiar contraseña con token |
| POST | `/auth/logout` | ✅ | ambos | Cerrar sesión |
| GET | `/auth/me` | ✅ | ambos | Datos del usuario |
| PATCH | `/profile` | ✅ | ambos | Editar nombre / username |
| POST | `/profile/password` | ✅ | ambos | Cambiar contraseña |
| POST | `/profile/avatar` | ✅ | ambos | Subir foto de perfil |
| DELETE | `/profile/avatar` | ✅ | ambos | Eliminar foto de perfil |
| POST | `/instructor/registration-code` | ✅ | instructor | Generar código |
| GET | `/instructor/registration-code/active` | ✅ | instructor | Ver código activo |
| DELETE | `/instructor/registration-code` | ✅ | instructor | Revocar código |

---

## 8. Configuración deep link (Flutter)

Para recibir el link de reset de contraseña (`bomberos://reset-password?token=xxx&email=xxx`):

**Android** — `AndroidManifest.xml`:
```xml
<intent-filter>
  <action android:name="android.intent.action.VIEW"/>
  <category android:name="android.intent.category.DEFAULT"/>
  <category android:name="android.intent.category.BROWSABLE"/>
  <data android:scheme="bomberos"/>
</intent-filter>
```

**iOS** — `Info.plist`:
```xml
<key>CFBundleURLTypes</key>
<array>
  <dict>
    <key>CFBundleURLSchemes</key>
    <array>
      <string>bomberos</string>
    </array>
  </dict>
</array>
```

**Leer los parámetros en Flutter (con `uni_links` o `go_router`):**
```dart
// El link llega como: bomberos://reset-password?token=abc&email=user@mail.com
final uri = Uri.parse(link);
final token = uri.queryParameters['token'];
final email = uri.queryParameters['email'];
// → Navegar a la pantalla de nueva contraseña con estos valores
```


netsh interface portproxy add v4tov4 listenport=8081 listenaddress=0.0.0.0 connectport=8081 connectaddress=172.21.74.208

New-NetFirewallRule -DisplayName "WSL2 Laravel API" -Direction Inbound -Protocol TCP -LocalPort 8081 -Action Allow

# Guardar como: actualizar-wsl-proxy.ps1 (ejecutar como Admin cada reboot)
$wslIp = (wsl hostname -I).Trim().Split(" ")[0]
netsh interface portproxy delete v4tov4 listenport=8081 listenaddress=0.0.0.0
netsh interface portproxy add v4tov4 listenport=8081 listenaddress=0.0.0.0 connectport=8081 connectaddress=$wslIp
Write-Host "Puerto 8081 redirigido a WSL2: $wslIp"




Endpoints de Reportes y Estadísticas — Aprendiz

Headers requeridos en todos:
Authorization: Bearer {token_aprendiz}
X-Client-Key: {API_CLIENT_KEY}
Condición previa: Solo mostrar la sección si can_access_ai_module = true en /auth/me.
1. GET /api/v1/evaluations
Historial paginado de evaluaciones. Pantalla de historial/lista de intentos.
{
  "success": true,
  "data": {
    "current_page": 1,
    "per_page": 10,
    "total": 8,
    "data": [
      {
        "id": 42,
        "session_id": "sess_abc123",
        "general_score": 83.50,
        "total_steps": 6,
        "steps_completed": 5,
        "correct_order": true,
        "status": "aprobado",
        "created_at": "2026-02-27T12:48:45.000000Z",
        "steps": [ ... ],
        "errors": [ ... ]
      }
    ]
  }
}
status: "aprobado", "reprobado", "incompleto"
Tiene paginación — implementar scroll infinito o botón "cargar más"
2. GET /api/v1/evaluations/{id}
Detalle completo de una evaluación. Pantalla de detalle al tocar un intento.
{
  "success": true,
  "data": {
    "id": 42,
    "general_score": 83.50,
    "status": "aprobado",
    "duration_seconds": 58.30,
    "detection_rate": 93.08,
    "correct_order": true,
    "recommendations": "Mejorar colocación del casco.",
    "steps": [
      {
        "step_number": 1,
        "step_name": "Pantalón y botas",
        "score": 0.9500,
        "status": "correcto",
        "detected": true,
        "feedback": "Paso realizado correctamente.",
        "time_start": 0.0,
        "time_end": 8.5,
        "duration": 8.5
      }
    ],
    "errors": [
      {
        "step_number": 3,
        "error_type": "mala_ejecucion",
        "description": "El casco no quedó bien ajustado.",
        "severity": "media"
      }
    ],
    "timeline": [ ... ],
    "comments": [
      {
        "id": 1,
        "comment": "Buen intento, trabaja el paso 3.",
        "instructor": {
          "id": 2,
          "name": "Instructor Juan"
        },
        "created_at": "2026-02-28T09:00:00.000000Z"
      }
    ]
  }
}
3. GET /api/v1/evaluations/stats
Estadísticas personales resumidas. Pantalla principal de mis estadísticas / dashboard del aprendiz.
{
  "success": true,
  "data": {
    "total_attempts": 8,
    "approved": 5,
    "failed": 3,
    "pass_rate": 62.5,
    "average_score": 74.20,
    "best_score": 82.18,

    "resultado_mas_comun": 76.5,
    "rango_tipico": 12.3,
    "nivel_consistencia": "moderada",
    "interpretacion_consistencia": "Tus resultados muestran variabilidad moderada.",

    "comparacion_grupo": {
      "mi_promedio": 74.20,
      "promedio_grupo": 67.31,
      "diferencia": 6.89,
      "interpretacion": "Tu promedio está por encima de la media del grupo. ¡Sigue así!"
    },

    "last_evaluation": { ... },
    "hardest_step": {
      "step_number": 3,
      "step_name": "Casco",
      "avg_score": 54.2,
      "attempts": 8
    },
    "progress": [
      { "id": 10, "general_score": 68.0, "status": "reprobado", "steps_completed": 4, "created_at": "..." },
      { "id": 15, "general_score": 77.5, "status": "aprobado",  "steps_completed": 6, "created_at": "..." }
    ]
  }
}
nivel_consistencia: "alta", "moderada", "baja"
progress: últimas 10 evaluaciones en orden cronológico, usar para gráfico de línea
hardest_step.avg_score: ya viene en porcentaje (0-100), no multiplicar por 100
4. GET /api/v1/evaluations/analytics
Análisis profundo. Pantalla de análisis avanzado / "Ver mi progreso detallado".

Si el aprendiz no tiene evaluaciones:
{
  "success": true,
  "message": "Aún no tienes evaluaciones registradas.",
  "data": null
}
Si tiene evaluaciones:
