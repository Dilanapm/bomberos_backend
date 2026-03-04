<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EppEvaluation;
use App\Models\EppError;
use App\Models\EppInstructorComment;
use App\Models\EppStepEvaluation;
use App\Models\EppTimeline;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeder completo de demostración EPP.
 *
 * Crea:
 *   - 3 instructores con distintos perfiles de grupo
 *   - 10 aprendices por instructor (30 en total)
 *   - 10-25 evaluaciones por aprendiz distribuidas en 60 días
 *   - 6 pasos EPP por evaluación con scores realistas
 *   - Errores en los pasos fallidos
 *   - Timeline de confianza por evaluación
 *   - Comentarios del instructor (~35% de evaluaciones)
 *
 * Es idempotente: si un usuario ya existe lo reutiliza;
 * si ya tiene evaluaciones, se omite.
 */
class EppDemoSeeder extends Seeder
{
    // ── Definición de pasos EPP ───────────────────────────────────
    private const STEPS = [
        1 => ['name' => 'Pantalón y botas',  'base' => [0.72, 0.95]],
        2 => ['name' => 'Capucha',           'base' => [0.38, 0.70]],  // paso más difícil
        3 => ['name' => 'Chaqueta',          'base' => [0.58, 0.85]],
        4 => ['name' => 'Guantes',           'base' => [0.62, 0.90]],
        5 => ['name' => 'Casco y visor',     'base' => [0.52, 0.82]],
        6 => ['name' => 'Postura final',     'base' => [0.78, 0.98]],
    ];

    // ── Tipos de error posibles por paso ─────────────────────────
    // error_type enum: paso_omitido | orden_incorrecto | mala_ejecucion | tiempo_insuficiente | deteccion_fallida
    private const STEP_ERRORS = [
        1 => [['type' => 'orden_incorrecto',   'desc' => 'Se colocó la bota antes del pantalón',         'sev' => 'media']],
        2 => [
            ['type' => 'mala_ejecucion',       'desc' => 'La capucha no cubre completamente el cuello',  'sev' => 'alta'],
            ['type' => 'tiempo_insuficiente',   'desc' => 'Tardó más de 5 segundos en ajustar capucha',  'sev' => 'baja'],
        ],
        3 => [['type' => 'mala_ejecucion',     'desc' => 'Chaqueta sin cerrar completamente',            'sev' => 'alta']],
        4 => [['type' => 'deteccion_fallida',  'desc' => 'Guante izquierdo no detectado correctamente',  'sev' => 'media']],
        5 => [['type' => 'mala_ejecucion',     'desc' => 'Visor del casco quedó semi-abierto',           'sev' => 'alta']],
        6 => [['type' => 'paso_omitido',       'desc' => 'Verificación de postura final no realizada',   'sev' => 'baja']],
    ];

    // ── Perfiles de instructores ──────────────────────────────────
    private const INSTRUCTORS = [
        [
            'name'       => 'Carlos Mendoza',
            'email'      => 'instructor1@bomberos.local',
            'username'   => 'carlos_mendoza',
            'multiplier' => 1.05,   // grupo de alto rendimiento
            'aprendices' => [
                ['name' => 'Pedro Ramírez',   'email' => 'aprendiz1@bomberos.local',  'mult' => 1.10],
                ['name' => 'Ana Gómez',       'email' => 'aprendiz2@bomberos.local',  'mult' => 1.02],
                ['name' => 'Luis Torres',     'email' => 'aprendiz3@bomberos.local',  'mult' => 0.98],
                ['name' => 'María Vargas',    'email' => 'aprendiz4@bomberos.local',  'mult' => 1.05],
                ['name' => 'Pablo Ruiz',      'email' => 'aprendiz5@bomberos.local',  'mult' => 0.95],
                ['name' => 'Sofía León',      'email' => 'aprendiz6@bomberos.local',  'mult' => 1.08],
                ['name' => 'Jorge Mora',      'email' => 'aprendiz7@bomberos.local',  'mult' => 1.00],
                ['name' => 'Valeria Cruz',    'email' => 'aprendiz8@bomberos.local',  'mult' => 0.97],
                ['name' => 'Diego Herrera',   'email' => 'aprendiz9@bomberos.local',  'mult' => 1.03],
                ['name' => 'Camila Ortiz',    'email' => 'aprendiz10@bomberos.local', 'mult' => 1.06],
            ],
        ],
        [
            'name'       => 'Laura Ríos',
            'email'      => 'instructor2@bomberos.local',
            'username'   => 'laura_rios',
            'multiplier' => 0.90,   // grupo promedio
            'aprendices' => [
                ['name' => 'Andrés Pérez',    'email' => 'aprendiz11@bomberos.local', 'mult' => 0.92],
                ['name' => 'Isabella Rojas',  'email' => 'aprendiz12@bomberos.local', 'mult' => 0.88],
                ['name' => 'Ricardo Silva',   'email' => 'aprendiz13@bomberos.local', 'mult' => 0.95],
                ['name' => 'Natalia Flores',  'email' => 'aprendiz14@bomberos.local', 'mult' => 0.85],
                ['name' => 'Sebastián Castro','email' => 'aprendiz15@bomberos.local', 'mult' => 0.90],
                ['name' => 'Daniela Reyes',   'email' => 'aprendiz16@bomberos.local', 'mult' => 0.87],
                ['name' => 'Martín Álvarez',  'email' => 'aprendiz17@bomberos.local', 'mult' => 0.93],
                ['name' => 'Gabriela Ramos',  'email' => 'aprendiz18@bomberos.local', 'mult' => 0.89],
                ['name' => 'Felipe Gutiérrez','email' => 'aprendiz19@bomberos.local', 'mult' => 0.91],
                ['name' => 'Carolina Medina', 'email' => 'aprendiz20@bomberos.local', 'mult' => 0.86],
            ],
        ],
        [
            'name'       => 'Marco Díaz',
            'email'      => 'instructor3@bomberos.local',
            'username'   => 'marco_diaz',
            'multiplier' => 0.72,   // grupo con dificultades
            'aprendices' => [
                ['name' => 'Kevin Jiménez',   'email' => 'aprendiz21@bomberos.local', 'mult' => 0.78],
                ['name' => 'Paola Navarro',   'email' => 'aprendiz22@bomberos.local', 'mult' => 0.70],
                ['name' => 'Cristian Vega',   'email' => 'aprendiz23@bomberos.local', 'mult' => 0.65],
                ['name' => 'Juliana Molina',  'email' => 'aprendiz24@bomberos.local', 'mult' => 0.72],
                ['name' => 'Alejandro Muñoz', 'email' => 'aprendiz25@bomberos.local', 'mult' => 0.68],
                ['name' => 'Tatiana Romero',  'email' => 'aprendiz26@bomberos.local', 'mult' => 0.74],
                ['name' => 'Bryan Suárez',    'email' => 'aprendiz27@bomberos.local', 'mult' => 0.62],
                ['name' => 'Luisa Guerrero',  'email' => 'aprendiz28@bomberos.local', 'mult' => 0.76],
                ['name' => 'Omar Delgado',    'email' => 'aprendiz29@bomberos.local', 'mult' => 0.69],
                ['name' => 'Samantha Ríos',   'email' => 'aprendiz30@bomberos.local', 'mult' => 0.73],
            ],
        ],
    ];

    // ─────────────────────────────────────────────────────────────

    public function run(): void
    {
        foreach (self::INSTRUCTORS as $instrData) {
            $instructor = $this->createUser(
                $instrData['name'],
                $instrData['email'],
                $instrData['username'],
                'instructor'
            );

            foreach ($instrData['aprendices'] as $aprData) {
                $aprendiz = $this->createUser(
                    $aprData['name'],
                    $aprData['email'],
                    Str::slug($aprData['name'], '_'),
                    'aprendiz'
                );

                if (EppEvaluation::where('user_id', $aprendiz->id)->exists()) {
                    $this->command->line("  ↳ {$aprData['name']}: ya tiene evaluaciones, omitiendo.");
                    continue;
                }

                $numEvals = rand(10, 25);
                $this->seedEvaluations($aprendiz, $instructor, $aprData['mult'], $numEvals);
                $this->command->line("  ↳ {$aprData['name']}: {$numEvals} evaluaciones creadas.");
            }

            $this->command->info("✅ Instructor {$instrData['name']} y sus aprendices completados.");
        }

        $this->printSummary();
    }

    // ── Crear/recuperar usuario ───────────────────────────────────
    private function createUser(string $name, string $email, string $username, string $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'username'          => $username,
                'password'          => Hash::make('Test12345!!'),
                'email_verified_at' => now(),
            ]
        );
        $user->syncRoles([$role]);
        return $user;
    }

    // ── Generar evaluaciones para un aprendiz ─────────────────────
    private function seedEvaluations(User $aprendiz, User $instructor, float $multiplier, int $count): void
    {
        for ($attempt = 1; $attempt <= $count; $attempt++) {
            // Mejora progresiva leve con cada intento
            $bonus      = ($attempt - 1) * 0.007;
            $effMult    = min($multiplier + $bonus, 1.05);

            // Fecha: distribuida en los últimos 60 días, más densa en los últimos 30
            $daysAgo    = $this->randomDaysAgo($attempt, $count);
            $createdAt  = now()->subDays($daysAgo)->setHour(rand(7, 18))->setMinute(rand(0, 59));

            // Calcular scores de los 6 pasos
            $stepScores     = [];
            $stepsCompleted = 0;

            foreach (self::STEPS as $num => $step) {
                [$min, $max] = $step['base'];
                $raw   = $min + ($max - $min) * (mt_rand(0, 100) / 100);
                $score = round(min($raw * $effMult, 1.0), 4);
                $stepScores[$num] = $score;
                if ($score >= 0.60) $stepsCompleted++;
            }

            $generalScore = round(array_sum($stepScores) / 6 * 100, 2);
            $correctOrder = rand(1, 10) > 2;  // 80% probable
            $duration     = rand(40, 130);

            $evalStatus = match (true) {
                $stepsCompleted >= 5 && $generalScore >= 70 && $correctOrder => 'aprobado',
                $stepsCompleted < 6  => 'incompleto',
                default              => 'reprobado',
            };

            $evaluation = EppEvaluation::create([
                'session_id'            => (string) Str::uuid(),
                'user_id'               => $aprendiz->id,
                'general_score'         => $generalScore,
                'total_steps'           => 6,
                'steps_completed'       => $stepsCompleted,
                'correct_order'         => $correctOrder,
                'duration_seconds'      => $duration,
                'total_frames'          => rand(700, 2200),
                'frames_with_detection' => rand(600, 1900),
                'detection_rate'        => round(rand(72, 99) / 100, 2),
                'status'                => $evalStatus,
                'recommendations'       => $evalStatus !== 'aprobado'
                    ? 'Reforzar Paso 2 (Capucha) y verificar secuencia correcta de colocación.'
                    : null,
                'created_at'            => $createdAt,
                'updated_at'            => $createdAt,
            ]);

            $this->insertSteps($evaluation, $stepScores, $createdAt);
            $this->insertErrors($evaluation, $stepScores, $createdAt);
            $this->insertTimeline($evaluation, $duration, $createdAt);

            // Comentario del instructor en ~35% de evaluaciones
            if (rand(1, 100) <= 35) {
                $this->insertComment($evaluation, $instructor, $evalStatus, $createdAt);
            }
        }
    }

    // ── Pasos EPP ─────────────────────────────────────────────────
    private function insertSteps(EppEvaluation $eval, array $stepScores, \Carbon\Carbon $createdAt): void
    {
        $rows = [];
        foreach (self::STEPS as $num => $step) {
            $score   = $stepScores[$num];
            $rows[]  = [
                'evaluation_id' => $eval->id,
                'step_number'   => $num,
                'step_name'     => $step['name'],
                'score'         => $score,
                'status'        => $score >= 0.60 ? 'correcto' : 'incorrecto',
                'detected'      => $score > 0.25,
                'feedback'      => $score < 0.60
                    ? "Mejorar técnica: {$step['name']}"
                    : 'Bien ejecutado',
                'time_start'    => ($num - 1) * 10.0,
                'time_end'      => $num * 10.0,
                'duration'      => 10.0,
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
        }
        EppStepEvaluation::insert($rows);
    }

    // ── Errores en pasos fallidos ─────────────────────────────────
    private function insertErrors(EppEvaluation $eval, array $stepScores, \Carbon\Carbon $createdAt): void
    {
        $rows = [];
        foreach ($stepScores as $num => $score) {
            if ($score < 0.60 && isset(self::STEP_ERRORS[$num])) {
                // Tomar uno de los errores definidos para ese paso
                $err    = self::STEP_ERRORS[$num][array_rand(self::STEP_ERRORS[$num])];
                $rows[] = [
                    'evaluation_id' => $eval->id,
                    'step_number'   => $num,
                    'error_type'    => $err['type'],
                    'description'   => $err['desc'],
                    'severity'      => $err['sev'],
                    'created_at'    => $createdAt,
                    'updated_at'    => $createdAt,
                ];
            }
        }
        if (!empty($rows)) {
            EppError::insert($rows);
        }
    }

    // ── Timeline (un punto cada ~2 segundos) ──────────────────────
    private function insertTimeline(EppEvaluation $eval, int $duration, \Carbon\Carbon $createdAt): void
    {
        $rows     = [];
        $interval = 2.0;
        $time     = 0.0;
        $stepKeys = array_keys(self::STEPS);

        while ($time <= $duration) {
            // Determinar qué paso corresponde a este momento
            $stepIdx      = min((int) ($time / 10), 5);
            $stepDetected = $stepKeys[$stepIdx];

            $rows[] = [
                'evaluation_id' => $eval->id,
                'time'          => round($time, 2),
                'step_detected' => $stepDetected,
                'confidence'    => round(0.65 + (mt_rand(0, 35) / 100), 4),
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
            $time += $interval;
        }
        EppTimeline::insert($rows);
    }

    // ── Comentario del instructor ─────────────────────────────────
    private function insertComment(EppEvaluation $eval, User $instructor, string $status, \Carbon\Carbon $createdAt): void
    {
        $aprobado = $status === 'aprobado';

        $comentarios = $aprobado ? [
            '¡Excelente trabajo! Mantén este nivel de desempeño.',
            'Muy buena ejecución. Sigue practicando para ser más fluido.',
            'Aprobado. La secuencia estuvo perfecta esta vez.',
        ] : [
            'El Paso 2 (Capucha) sigue fallando. Practica frente al espejo.',
            'Necesitas mejorar el tiempo de reacción en el Paso 5.',
            'Revisar el orden de colocación. El protocolo debe ser exacto.',
            'Buen intento. Con más práctica en casa lograrás aprobarlo.',
        ];

        EppInstructorComment::create([
            'evaluation_id' => $eval->id,
            'instructor_id' => $instructor->id,
            'type'          => $aprobado ? 'felicitacion' : 'correcion',
            'comment'       => $comentarios[array_rand($comentarios)],
            'created_at'    => $createdAt->copy()->addMinutes(rand(15, 120)),
            'updated_at'    => $createdAt->copy()->addMinutes(rand(15, 120)),
        ]);
    }

    // ── Distribución de días (más densa en los últimos 30) ────────
    private function randomDaysAgo(int $attempt, int $total): int
    {
        // Las primeras evaluaciones son más antiguas (hasta 60 días)
        // Las últimas son más recientes (dentro de 10 días)
        $fraction = ($total - $attempt) / $total;        // 1.0 → 0.0
        $maxDays  = (int) round($fraction * 60);
        return rand(0, max(0, $maxDays));
    }

    // ── Resumen final ─────────────────────────────────────────────
    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('════════════════════════════════════════');
        $this->command->info('  EppDemoSeeder completado');
        $this->command->info('════════════════════════════════════════');
        $this->command->line('  Contraseña de todos los usuarios: Test12345!!');
        $this->command->newLine();
        $this->command->line('  Instructores:');
        $this->command->line('    instructor1@bomberos.local  (Carlos Mendoza  — alto rendimiento)');
        $this->command->line('    instructor2@bomberos.local  (Laura Ríos       — rendimiento medio)');
        $this->command->line('    instructor3@bomberos.local  (Marco Díaz       — necesita apoyo)');
        $this->command->newLine();
        $this->command->line('  Aprendices:');
        $this->command->line('    aprendiz1@bomberos.local … aprendiz10@bomberos.local  (grupo Carlos)');
        $this->command->line('    aprendiz11@bomberos.local … aprendiz20@bomberos.local (grupo Laura)');
        $this->command->line('    aprendiz21@bomberos.local … aprendiz30@bomberos.local (grupo Marco)');
        $this->command->info('════════════════════════════════════════');
    }
}
