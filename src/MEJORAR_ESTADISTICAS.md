# 📋 PROMPT PARA LA IA - Ajustar Controladores y Endpoints

```markdown
# Mejorar Sistema de Estadísticas y Reportes

## 📊 CONTEXTO

Tengo un sistema funcional de evaluaciones EPP con estos endpoints actuales:

### **Endpoints Existentes (✅ YA FUNCIONAN):**

**APRENDIZ:**
- `POST /api/v1/evaluations` → Guardar evaluación
- `GET /api/v1/evaluations` → Historial paginado
- `GET /api/v1/evaluations/stats` → Estadísticas básicas
- `GET /api/v1/evaluations/{id}` → Detalle de evaluación

**INSTRUCTOR:**
- `POST /api/v1/instructor/evaluations` → Guardar evaluación para aprendiz
- `GET /api/v1/instructor/aprendices` → Lista de aprendices
- `GET /api/v1/instructor/aprendices/{id}/evaluations` → Historial de un aprendiz
- `GET /api/v1/instructor/evaluations/{id}` → Ver cualquier evaluación
- `POST /api/v1/instructor/evaluations/{id}/comments` → Comentar evaluación

---

## 🚀 LO QUE NECESITO QUE AGREGUES

### **PROBLEMA ACTUAL:**
Las estadísticas actuales son MUY BÁSICAS. Los jurados de mi proyecto quieren ver:
- Media, moda, desviación estándar, varianza
- **PERO explicados en términos que usuarios normales entiendan**
- Comparativas entre aprendices
- Estadísticas del instructor vs promedio institucional
- Dashboard completo para administrador

---

## 📦 NUEVOS ENDPOINTS A CREAR

### **1. Para APRENDIZ (agregar a `EvaluationController`)**

```php
// ── YA EXISTE (pero mejorar) ────────────────────────
GET /api/v1/evaluations/stats

// Mejorar con:
// - Comparación anónima con promedio del grupo
// - Nivel de consistencia (desviación estándar simplificada)
// - Identificar paso más débil con recomendaciones
// - Progreso temporal con datos para gráfica
// - "Rango típico" de sus puntajes

// ── NUEVO ENDPOINT ──────────────────────────────────
GET /api/v1/evaluations/analytics

// Retorna análisis avanzado:
{
  "personal_stats": {
    "promedio": 76.2,              // Media
    "mejor_puntaje": 85,
    "resultado_mas_comun": 78,     // Moda
    "rango_tipico": "±5.2",        // Desviación estándar en términos simples
    "nivel_consistencia": "alta",  // alta/media/baja
    "total_intentos": 8
  },
  
  "comparacion_grupo": {
    "mi_promedio": 76.2,
    "promedio_grupo": 74.5,
    "diferencia": +1.7,
    "posicion_estimada": "TOP 40%", // Sin revelar nombres
    "mejor_del_grupo": 88.3,        // Anónimo
    "para_top_10": 84.0             // Puntaje necesario
  },
  
  "fortalezas_debilidades": {
    "fortalezas": [
      {
        "paso": 6,
        "nombre": "Postura final",
        "promedio": 92,
        "estrellas": 5
      }
    ],
    "debilidades": [
      {
        "paso": 2,
        "nombre": "Capucha",
        "promedio": 58,
        "promedio_grupo": 68,
        "diferencia": -10,
        "recomendacion": "Esta es tu área más débil. Practica..."
      }
    ]
  },
  
  "progreso_temporal": [
    {"intento": 1, "puntaje": 60, "fecha": "2026-01-15"},
    {"intento": 2, "puntaje": 65, "fecha": "2026-01-17"},
    // ... últimos 10 intentos
  ],
  
  "tendencia": {
    "tipo": "positiva",        // positiva/negativa/estable
    "mejora_total": +25,       // Desde primer intento
    "velocidad_mejora": +3.1   // Puntos por intento
  }
}
```

---

### **2. Para INSTRUCTOR (crear `InstructorStatsController`)**

```php
// ── NUEVO CONTROLADOR ──────────────────────────────
namespace App\Http\Controllers\Api;
class InstructorStatsController extends Controller

// ── NUEVOS ENDPOINTS ────────────────────────────────

GET /api/v1/instructor/stats/my-group
// Estadísticas del grupo del instructor

Response:
{
  "resumen_grupo": {
    "total_aprendices": 28,
    "promedio_grupo": 78.5,
    "tasa_aprobacion": 82.1,
    "mejor_aprendiz": {
      "name": "Pedro Ramírez",
      "promedio": 88.3
    },
    "necesitan_apoyo": 5
  },
  
  "vs_institucion": {
    "mi_grupo_promedio": 78.5,
    "promedio_institucional": 74.8,
    "diferencia": +3.7,
    "posicion": 1,            // Entre 8 instructores
    "total_instructores": 8
  },
  
  "consistencia_grupo": {
    "desviacion_estandar": 5.2,
    "nivel": "alta",
    "interpretacion": "Tu grupo tiene resultados muy consistentes"
  }
}

// ────────────────────────────────────────────────────

GET /api/v1/instructor/stats/ranking
// Ranking de aprendices del instructor

Response:
{
  "ranking": [
    {
      "posicion": 1,
      "aprendiz_id": 45,
      "name": "Pedro Ramírez",
      "promedio": 88.3,
      "intentos": 12,
      "mejor_puntaje": 94,
      "tendencia": "positiva",
      "badge": "oro"
    },
    // ... resto del ranking
  ]
}

// ────────────────────────────────────────────────────

GET /api/v1/instructor/stats/need-help
// Aprendices que necesitan atención

Response:
{
  "aprendices": [
    {
      "id": 78,
      "name": "Pablo Ruiz",
      "problema": "Falla consistentemente Paso 2 (Capucha)",
      "promedio_paso": 42,
      "promedio_general": 67.1,
      "prioridad": "alta",
      "recomendacion": "Sesión 1-a-1 para Paso 2"
    }
  ]
}

// ────────────────────────────────────────────────────

GET /api/v1/instructor/stats/step-analysis
// Análisis de rendimiento por paso de todo el grupo

Response:
{
  "pasos": [
    {
      "numero": 1,
      "nombre": "Pantalón y botas",
      "promedio_grupo": 84.2,
      "tasa_exito": 91.7,
      "dificultad": "baja"
    },
    {
      "numero": 2,
      "nombre": "Capucha",
      "promedio_grupo": 62.7,
      "tasa_exito": 62.7,
      "dificultad": "alta",
      "problema": "Paso más difícil del grupo"
    }
  ]
}
```

---

### **3. Para ADMINISTRADOR (crear `AdminStatsController`)**

```php
// ── NUEVO CONTROLADOR ──────────────────────────────
namespace App\Http\Controllers\Api;
class AdminStatsController extends Controller

// ── NUEVOS ENDPOINTS (WEB - NO MÓVIL) ──────────────

GET /api/v1/admin/stats/global
// Vista general del sistema completo

Response:
{
  "metricas_globales": {
    "total_usuarios": 147,
    "total_evaluaciones": 2483,
    "tasa_aprobacion_general": 73.2,
    "tiempo_promedio_ejercicio": 56.3
  },
  
  "rendimiento_general": {
    "promedio_global": 74.8,
    "rango_tipico": "68-82",        // ±1 desviación estándar
    "puntaje_mas_comun": 78,        // Moda
    "nivel_consistencia": "alta"
  },
  
  "uso_sistema": {
    "evaluaciones_hoy": 45,
    "evaluaciones_semana": 312,
    "evaluaciones_mes": 1205,
    "pico_uso": "14:00-16:00"
  }
}

// ────────────────────────────────────────────────────

GET /api/v1/admin/stats/instructors
// Comparativa entre instructores

Response:
{
  "instructores": [
    {
      "id": 5,
      "name": "Juan Pérez",
      "aprendices": 28,
      "promedio_grupo": 78.5,
      "tasa_aprobacion": 82.1,
      "consistencia": "alta",
      "ranking": 1
    },
    {
      "id": 12,
      "name": "Ana Torres",
      "aprendices": 22,
      "promedio_grupo": 71.8,
      "tasa_aprobacion": 68.2,
      "consistencia": "baja",
      "ranking": 4,
      "alerta": "Grupo con alta variabilidad"
    }
  ],
  
  "diferencia_max_min": 6.7,
  "instructor_mas_consistente": "Juan Pérez"
}

// ────────────────────────────────────────────────────

GET /api/v1/admin/stats/step-analysis
// Análisis de pasos a nivel institucional

Response:
{
  "pasos": [
    {
      "numero": 2,
      "nombre": "Capucha",
      "promedio_institucional": 62.7,
      "tasa_fallo": 37.3,
      "variacion": "alta",
      "es_mas_dificil": true,
      "recomendacion": "Revisar material de entrenamiento"
    }
  ]
}

// ────────────────────────────────────────────────────

GET /api/v1/admin/stats/trends
// Tendencias temporales del sistema

Response:
{
  "evolucion_mensual": [
    {"mes": "Enero 2026", "promedio": 71.2, "aprobacion": 68},
    {"mes": "Febrero 2026", "promedio": 73.5, "aprobacion": 71},
    {"mes": "Marzo 2026", "promedio": 74.8, "aprobacion": 73}
  ],
  
  "tendencia": "positiva",
  "mejora_mensual": +1.6,
  "proyeccion_proximo_mes": 76.4
}
```

---

## 🧮 FUNCIONES AUXILIARES NECESARIAS

Crea un Helper `StatsHelper.php` con estas funciones:

```php
namespace App\Helpers;

class StatsHelper
{
    /**
     * Calcula la media (promedio)
     */
    public static function mean(array $values): float
    {
        return count($values) > 0 ? array_sum($values) / count($values) : 0;
    }
    
    /**
     * Calcula la moda (valor más común)
     */
    public static function mode(array $values): float|int|null
    {
        if (empty($values)) return null;
        
        $frequencies = array_count_values($values);
        arsort($frequencies);
        return array_key_first($frequencies);
    }
    
    /**
     * Calcula la desviación estándar
     */
    public static function standardDeviation(array $values): float
    {
        $count = count($values);
        if ($count < 2) return 0;
        
        $mean = self::mean($values);
        $variance = array_sum(array_map(
            fn($x) => pow($x - $mean, 2),
            $values
        )) / $count;
        
        return sqrt($variance);
    }
    
    /**
     * Calcula el rango típico (media ± 1 desviación estándar)
     * Retorna string legible: "68-82"
     */
    public static function typicalRange(array $values): string
    {
        $mean = self::mean($values);
        $std = self::standardDeviation($values);
        
        $lower = round($mean - $std, 1);
        $upper = round($mean + $std, 1);
        
        return "{$lower}-{$upper}";
    }
    
    /**
     * Determina nivel de consistencia
     * Retorna: "alta" | "media" | "baja"
     */
    public static function consistencyLevel(array $values): string
    {
        $std = self::standardDeviation($values);
        $mean = self::mean($values);
        
        if ($mean == 0) return "baja";
        
        $cv = ($std / $mean) * 100; // Coeficiente de variación
        
        if ($cv < 10) return "alta";
        if ($cv < 20) return "media";
        return "baja";
    }
    
    /**
     * Calcula posición percentil (para "TOP X%")
     * Ejemplo: percentileRank([60,70,80,90], 75) → 50 (TOP 50%)
     */
    public static function percentileRank(array $values, float $score): int
    {
        sort($values);
        $count = count($values);
        
        $below = count(array_filter($values, fn($v) => $v < $score));
        
        return $count > 0 ? (int) round(($below / $count) * 100) : 0;
    }
    
    /**
     * Detecta tendencia (positiva/negativa/estable)
     */
    public static function detectTrend(array $values): string
    {
        if (count($values) < 3) return "estable";
        
        // Regresión lineal simple
        $n = count($values);
        $x = range(1, $n);
        
        $sumX = array_sum($x);
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $values[$i];
            $sumX2 += $x[$i] * $x[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        
        if ($slope > 1) return "positiva";
        if ($slope < -1) return "negativa";
        return "estable";
    }
}
```

---

## 📋 TAREAS ESPECÍFICAS

### **TAREA 1: Mejorar `EvaluationController@stats`**

Modifica el método `stats()` existente para incluir:
- Moda (resultado más común)
- Desviación estándar como "rango típico"
- Nivel de consistencia
- Comparación anónima con promedio del grupo

### **TAREA 2: Crear `InstructorStatsController`**

Nuevo controlador con 4 métodos:
- `myGroup()` → Estadísticas del grupo del instructor
- `ranking()` → Ranking de aprendices
- `needHelp()` → Quién necesita apoyo
- `stepAnalysis()` → Análisis por pasos del grupo

### **TAREA 3: Crear `AdminStatsController`**

Nuevo controlador con 4 métodos:
- `global()` → Vista general del sistema
- `instructors()` → Comparativa entre instructores
- `stepAnalysis()` → Pasos problemáticos institucionales
- `trends()` → Tendencias temporales

### **TAREA 4: Actualizar Routes**

Agregar en `api.php`:

```php
// APRENDIZ
Route::get('/evaluations/analytics', [EvaluationController::class, 'analytics']);

// INSTRUCTOR
Route::middleware(['auth:sanctum', 'role:instructor'])->group(function () {
    Route::prefix('instructor/stats')->group(function () {
        Route::get('/my-group',      [InstructorStatsController::class, 'myGroup']);
        Route::get('/ranking',       [InstructorStatsController::class, 'ranking']);
        Route::get('/need-help',     [InstructorStatsController::class, 'needHelp']);
        Route::get('/step-analysis', [InstructorStatsController::class, 'stepAnalysis']);
    });
});

// ADMIN (web, no móvil)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::prefix('admin/stats')->group(function () {
        Route::get('/global',        [AdminStatsController::class, 'global']);
        Route::get('/instructors',   [AdminStatsController::class, 'instructors']);
        Route::get('/step-analysis', [AdminStatsController::class, 'stepAnalysis']);
        Route::get('/trends',        [AdminStatsController::class, 'trends']);
    });
});
```

---

## ⚠️ IMPORTANTE

**NO elimines ni modifiques los endpoints existentes.** Solo:
1. ✅ Mejora el método `stats()` existente
2. ✅ Agrega nuevos endpoints
3. ✅ Crea los nuevos controladores

**Los términos estadísticos deben traducirse:**
- Media → "Promedio"
- Moda → "Resultado más común"
- Desviación estándar → "Rango típico"
- Varianza → "Nivel de consistencia"

**Siempre incluir "interpretación" en las respuestas JSON** para que el frontend pueda mostrar explicaciones al usuario.

---

## ✅ ENTREGABLES

1. `app/Helpers/StatsHelper.php` (nuevo)
2. `app/Http/Controllers/Api/InstructorStatsController.php` (nuevo)
3. `app/Http/Controllers/Api/AdminStatsController.php` (nuevo)
4. `app/Http/Controllers/Api/EvaluationController.php` (modificado - solo método stats())
5. `routes/api.php` (modificado - agregar nuevas rutas)

---

¿Entiendes lo que necesito? Empieza creando el `StatsHelper.php` primero, luego los controladores.
```

---

