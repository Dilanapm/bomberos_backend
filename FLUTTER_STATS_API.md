# Guía de Estadísticas y Reportes — Flutter AI

Instrucciones precisas para consumir los endpoints de estadísticas del backend Laravel.
Todo lo que está aquí refleja exactamente lo que devuelven los controllers.

---

## Configuración base obligatoria en CADA petición

```dart
// Base URL
const String baseUrl = 'http://<host>:8081/api/v1';

// Headers requeridos SIEMPRE
Map<String, String> headers(String token) => {
  'X-Client-Key': 'b268aa0a224d7361174815675b940a16c8c1b047f1b1982270ddf6aa3749bed8',
  'Accept':        'application/json',
  'Content-Type':  'application/json',
  'Authorization': 'Bearer $token',
};
```

---

## Campos de permiso (vienen en /auth/me y en login)

```json
{
  "can_access_ai_module":    true,   // aprendiz + instructor → acceso al módulo EPP/IA
  "can_access_stats_module": true,   // SOLO aprendiz         → acceso a sus estadísticas propias
  "can_view_student_stats":  true    // SOLO instructor       → acceso a estadísticas del grupo
}
```

**Regla de negocio estricta:**
- Antes de mostrar cualquier pantalla de stats del aprendiz → verificar `can_access_stats_module == true`
- Antes de mostrar cualquier pantalla de stats del instructor → verificar `can_view_student_stats == true`
- Si el flag es `false` → mostrar pantalla de "Módulo no disponible" y NO llamar a ningún endpoint

---

## Módulo A — Estadísticas del APRENDIZ

Rol requerido: `aprendiz`
Condición previa: `can_access_stats_module == true`

---

### A1. Dashboard principal de estadísticas

```
GET /api/v1/evaluations/stats
```

**Pantalla sugerida:** Home del módulo de reportes. Se llama al entrar al módulo.

**Respuesta exitosa:**
```json
{
  "success": true,
  "data": {
    "total_attempts": 8,
    "approved":       5,
    "failed":         3,
    "pass_rate":      62.5,
    "average_score":  74.20,
    "best_score":     82.18,

    "resultado_mas_comun":           76.5,
    "rango_tipico":                  12.3,
    "nivel_consistencia":            "moderada",
    "interpretacion_consistencia":   "Tus resultados muestran variabilidad moderada.",

    "comparacion_grupo": {
      "mi_promedio":    74.20,
      "promedio_grupo": 67.31,
      "diferencia":     6.89,
      "interpretacion": "Tu promedio está por encima de la media del grupo. ¡Sigue así!"
    },

    "last_evaluation": {
      "id":              42,
      "general_score":   83.50,
      "status":          "aprobado",
      "steps_completed": 6,
      "created_at":      "2026-02-27T12:48:45.000000Z",
      "steps":           [ ... ]
    },

    "hardest_step": {
      "step_number": 3,
      "step_name":   "Casco",
      "avg_score":   54.2,
      "attempts":    8
    },

    "progress": [
      { "id": 10, "general_score": 68.0, "status": "reprobado", "steps_completed": 4, "created_at": "..." },
      { "id": 15, "general_score": 77.5, "status": "aprobado",  "steps_completed": 6, "created_at": "..." }
    ]
  }
}
```

**Qué renderizar:**
- `total_attempts`, `approved`, `failed`, `pass_rate` → tarjetas de resumen
- `average_score` y `best_score` → indicadores destacados
- `comparacion_grupo.interpretacion` → texto informativo debajo del promedio
- `hardest_step` → tarjeta de "Punto débil a mejorar"
- `progress` → gráfico de línea (eje X = fecha, eje Y = puntaje). Ya viene en orden cronológico ascendente
- `nivel_consistencia` posibles valores: `"alta"`, `"moderada"`, `"baja"`

---

### A2. Análisis avanzado

```
GET /api/v1/evaluations/analytics
```

**Pantalla sugerida:** "Ver análisis completo" / sección de progreso detallado.

**Caso sin evaluaciones:**
```json
{
  "success": true,
  "message": "Aún no tienes evaluaciones registradas.",
  "data":    null
}
```
→ Si `data == null`, mostrar ilustración vacía con el mensaje.

**Caso con evaluaciones:**
```json
{
  "success": true,
  "data": {
    "personal_stats": {
      "promedio":            74.20,
      "mejor_puntaje":       82.18,
      "resultado_mas_comun": 76.5,
      "rango_tipico":        12.3,
      "nivel_consistencia":  "moderada",
      "interpretacion":      "Tus resultados muestran variabilidad moderada.",
      "total_intentos":      8
    },

    "comparacion_grupo": {
      "mi_promedio":       74.20,
      "promedio_grupo":    67.31,
      "diferencia":        6.89,
      "posicion_estimada": "top 35%",
      "mejor_del_grupo":   82.18,
      "para_top_10":       80.5
    },

    "fortalezas_debilidades": {
      "fortalezas": [
        {
          "paso":     1,
          "nombre":   "Pantalón y botas",
          "promedio": 91.2,
          "estrellas": 5
        }
      ],
      "debilidades": [
        {
          "paso":           3,
          "nombre":         "Casco",
          "promedio":       54.2,
          "promedio_grupo": 61.0,
          "diferencia":     -6.8,
          "recomendacion":  "El Paso 3 (Casco) es tu área más débil. Repasa el procedimiento..."
        }
      ]
    },

    "progreso_temporal": [
      { "intento": 1, "puntaje": 68.0, "fecha": "2026-02-01" },
      { "intento": 2, "puntaje": 72.5, "fecha": "2026-02-05" }
    ],

    "tendencia": {
      "tipo":            "positiva",
      "mejora_total":    14.2,
      "velocidad_mejora": 1.8,
      "interpretacion":  "¡Vas mejorando! Has avanzado 14.2 puntos desde tu primer intento."
    }
  }
}
```

**Qué renderizar:**
- `personal_stats.promedio` + `mejor_puntaje` → encabezado de la pantalla
- `comparacion_grupo.posicion_estimada` → badge tipo "Top 35% del grupo"
- `comparacion_grupo.para_top_10` → mensaje motivacional "Te faltan X puntos para el top 10%"
- `fortalezas` → lista de pasos con `estrellas` (1-5). `estrellas` es entero, renderizar como ★
- `debilidades` → lista con la `recomendacion` como texto de apoyo
- `progreso_temporal` → gráfico de línea (eje X = `intento`, eje Y = `puntaje`)
- `tendencia.tipo` posibles valores: `"positiva"`, `"negativa"`, `"estable"`
- `tendencia.interpretacion` → texto motivacional inferior

---

### A3. Historial paginado de evaluaciones

```
GET /api/v1/evaluations?page=1
```

**Pantalla sugerida:** "Mis intentos" / lista con scroll.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "per_page":     10,
    "total":        8,
    "last_page":    1,
    "data": [
      {
        "id":              42,
        "session_id":      "sess_abc123",
        "general_score":   83.50,
        "total_steps":     6,
        "steps_completed": 5,
        "correct_order":   true,
        "status":          "aprobado",
        "created_at":      "2026-02-27T12:48:45.000000Z",
        "steps":           [ ... ],
        "errors":          [ ... ]
      }
    ]
  }
}
```

**Paginación:**
```dart
// Detectar si hay más páginas
bool hasMore = response['data']['current_page'] < response['data']['last_page'];

// Siguiente página
GET /api/v1/evaluations?page=2
```

**`status`** posibles valores: `"aprobado"` | `"reprobado"` | `"incompleto"`

---

### A4. Detalle de una evaluación

```
GET /api/v1/evaluations/{id}
```

**Pantalla sugerida:** Detalle al tocar un elemento de la lista.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "id":              42,
    "general_score":   83.50,
    "status":          "aprobado",
    "duration_seconds": 58.30,
    "detection_rate":  93.08,
    "correct_order":   true,
    "recommendations": "Mejorar colocación del casco.",

    "steps": [
      {
        "step_number": 1,
        "step_name":   "Pantalón y botas",
        "score":       0.9500,
        "status":      "correcto",
        "detected":    true,
        "feedback":    "Paso realizado correctamente.",
        "time_start":  0.0,
        "time_end":    8.5,
        "duration":    8.5
      }
    ],

    "errors": [
      {
        "step_number": 3,
        "error_type":  "mala_ejecucion",
        "description": "El casco no quedó bien ajustado.",
        "severity":    "media"
      }
    ],

    "timeline": [ ... ],

    "comments": [
      {
        "id":      1,
        "comment": "Buen intento, trabaja el paso 3.",
        "instructor": {
          "id":   2,
          "name": "Instructor Juan"
        },
        "created_at": "2026-02-28T09:00:00.000000Z"
      }
    ]
  }
}
```

**Importante:** `steps[].score` viene en escala 0.0–1.0. Para mostrarlo como porcentaje: `score * 100`.

**Error — evaluación ajena (404):**
```json
{ "message": "No query results for model [EppEvaluation]." }
```
→ El aprendiz solo puede ver sus propias evaluaciones. Si recibe 404, volver al listado.

---

## Módulo B — Estadísticas del INSTRUCTOR

Rol requerido: `instructor`
Condición previa: `can_view_student_stats == true`

---

### B1. Resumen del grupo

```
GET /api/v1/instructor/stats/my-group
```

**Pantalla sugerida:** Dashboard principal del instructor.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "resumen_grupo": {
      "total_aprendices": 12,
      "promedio_grupo":   71.40,
      "tasa_aprobacion":  68.5,
      "mejor_aprendiz": {
        "name":    "Ana Gómez",
        "promedio": 88.20
      },
      "necesitan_apoyo": 3
    },

    "vs_institucion": {
      "mi_grupo_promedio":      71.40,
      "promedio_institucional": 71.40,
      "diferencia":             0.0,
      "interpretacion":         "Estadísticas del sistema completo (grupo único)."
    },

    "consistencia_grupo": {
      "desviacion_estandar": 9.3,
      "nivel":               "moderada",
      "interpretacion":      "Los resultados del grupo muestran variabilidad moderada."
    }
  }
}
```

**Qué renderizar:**
- `resumen_grupo` → tarjetas: total aprendices, promedio, tasa de aprobación
- `mejor_aprendiz` → destacar con medalla o badge especial
- `necesitan_apoyo` → badge de alerta rojo con contador
- `consistencia_grupo.nivel` → chip de color (alta=verde, moderada=amarillo, baja=rojo)

---

### B2. Ranking de aprendices

```
GET /api/v1/instructor/stats/ranking
```

**Pantalla sugerida:** Tabla/lista de clasificación.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "ranking": [
      {
        "posicion":      1,
        "aprendiz_id":   5,
        "name":          "Ana Gómez",
        "promedio":      88.20,
        "intentos":      10,
        "mejor_puntaje": 94.50,
        "tendencia":     "positiva",
        "badge":         "oro"
      },
      {
        "posicion":  2,
        "name":      "Carlos López",
        "promedio":  76.10,
        "tendencia": "estable",
        "badge":     "plata"
      }
    ]
  }
}
```

**`tendencia`** posibles valores: `"positiva"` | `"negativa"` | `"estable"`
**`badge`** posibles valores: `"oro"` | `"plata"` | `"bronce"` | `null`
→ Usar íconos de medalla según `badge`. Íconos de flecha según `tendencia`.

---

### B3. Aprendices que necesitan ayuda

```
GET /api/v1/instructor/stats/need-help
```

**Pantalla sugerida:** Sección de alertas / "Atención requerida".

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total": 2,
    "aprendices": [
      {
        "id":               7,
        "name":             "Pedro Ramírez",
        "problema":         "Falla consistentemente Paso 3 (Casco)",
        "promedio_paso":    41.0,
        "promedio_general": 55.30,
        "prioridad":        "alta",
        "recomendacion":    "Sesión 1-a-1 para Paso 3 (Casco)"
      }
    ]
  }
}
```

**`prioridad`** posibles valores: `"alta"` (promedio < 50) | `"media"` (50–64)
→ `"alta"` = tarjeta roja. `"media"` = tarjeta naranja.
→ Si `data.total == 0`, mostrar mensaje "¡Todo el grupo está progresando bien!"

---

### B4. Análisis por paso del EPP

```
GET /api/v1/instructor/stats/step-analysis
```

**Pantalla sugerida:** Gráfico de barras por paso / análisis de dificultad.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "pasos": [
      {
        "numero":         1,
        "nombre":         "Pantalón y botas",
        "promedio_grupo": 85.3,
        "tasa_exito":     85.3,
        "dificultad":     "facil",
        "problema":       null
      },
      {
        "numero":         3,
        "nombre":         "Casco",
        "promedio_grupo": 54.1,
        "tasa_exito":     54.1,
        "dificultad":     "dificil",
        "problema":       "Paso más difícil del grupo"
      }
    ]
  }
}
```

**`dificultad`** posibles valores: `"facil"` | `"moderado"` | `"dificil"`
→ `promedio_grupo` ya está en escala 0–100, no multiplicar.
→ Usar gráfico de barras horizontales ordenado por `numero`.
→ Si `problema != null`, mostrar indicador de advertencia en esa barra.

---

### B5. Lista de todos los aprendices (para seleccionar antes del entrenamiento)

```
GET /api/v1/instructor/aprendices/all
```

**Pantalla sugerida:** Selector de aprendiz antes de iniciar grabación EPP.

**Respuesta:**
```json
{
  "success": true,
  "total":   5,
  "data": [
    {
      "id":                 12,
      "name":               "Juan Pérez",
      "username":           "juanp",
      "avatar_url":         null,
      "evaluations_count":  3,
      "avg_score":          74.20,
      "best_score":         82.18,
      "last_evaluation":    "2026-02-27T12:48:45.000000Z"
    }
  ]
}
```

→ Si `evaluations_count == 0`, es un aprendiz nuevo sin evaluaciones aún.
→ `avg_score` y `best_score` pueden ser `null` si no tiene evaluaciones.

---

### B6. Lista de aprendices con evaluaciones

```
GET /api/v1/instructor/aprendices
```

**Pantalla sugerida:** Sección "Mis aprendices" para revisar historial.

Misma estructura que B5, pero **solo devuelve aprendices que tienen al menos una evaluación.**

---

### B7. Historial de un aprendiz específico

```
GET /api/v1/instructor/aprendices/{aprendiz_id}/evaluations?page=1
```

**Pantalla sugerida:** Detalle del aprendiz → lista de sus intentos.

**Respuesta:**
```json
{
  "success":  true,
  "aprendiz": {
    "id":       12,
    "name":     "Juan Pérez",
    "username": "juanp"
  },
  "data": {
    "current_page": 1,
    "per_page":     10,
    "total":        8,
    "last_page":    1,
    "data": [ ... ]
  }
}
```

→ `aprendiz` viene fuera del objeto `data`. Usarlo para el encabezado de la pantalla.

---

## Flujos de navegación recomendados

### Aprendiz — Módulo de estadísticas

```
Verificar can_access_stats_module en usuario local (del último /auth/me)
│
├── false → Pantalla "Módulo no disponible"
│
└── true  →
      │
      ├── Entrar al módulo → GET /evaluations/stats           (datos del dashboard)
      │
      ├── Tocar "Análisis completo" → GET /evaluations/analytics
      │     └── Si data == null → "Sin evaluaciones aún"
      │
      ├── Tocar "Mis intentos" → GET /evaluations?page=1
      │     └── Scroll al fondo → GET /evaluations?page=N+1 si current_page < last_page
      │
      └── Tocar un intento → GET /evaluations/{id}
```

### Instructor — Módulo de estadísticas

```
Verificar can_view_student_stats en usuario local (del último /auth/me)
│
├── false → Pantalla "Módulo no disponible"
│
└── true  →
      │
      ├── Entrar al módulo → GET /instructor/stats/my-group   (dashboard del grupo)
      │
      ├── Pestaña "Ranking" → GET /instructor/stats/ranking
      │
      ├── Pestaña "Atención" → GET /instructor/stats/need-help
      │
      ├── Pestaña "Por paso" → GET /instructor/stats/step-analysis
      │
      ├── Sección "Mis aprendices" → GET /instructor/aprendices
      │     └── Tocar aprendiz → GET /instructor/aprendices/{id}/evaluations
      │
      └── Selector antes del entrenamiento → GET /instructor/aprendices/all
```

---

## Actualización de permisos en tiempo real

Los flags `can_access_stats_module` y `can_view_student_stats` los puede cambiar el admin en cualquier momento. Para reflejarlos sin que el usuario cierre sesión:

```dart
// Llamar /auth/me cada vez que la app vuelve al primer plano
@override
void didChangeAppLifecycleState(AppLifecycleState state) {
  if (state == AppLifecycleState.resumed) {
    // GET /auth/me → actualizar los flags en el estado local
    authRepository.refreshMe();
  }
}
```

Si el módulo estaba abierto y el flag cambió a `false`, navegar automáticamente a la pantalla de "Módulo no disponible".

---

## Manejo de errores específicos

| HTTP | Qué hacer en Flutter |
|------|----------------------|
| `401` | Token expirado → limpiar storage → ir al Login |
| `403` | Sin permiso de rol → mostrar "No tienes acceso a esta sección" |
| `404` | Recurso no encontrado (evaluación ajena) → volver al listado |
| `429` | Rate limit → mostrar "Demasiadas solicitudes, espera un momento" |
| `500` | Error del servidor → mostrar "Error temporal, intenta más tarde" |
