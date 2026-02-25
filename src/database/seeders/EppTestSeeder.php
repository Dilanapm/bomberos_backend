<?php

namespace Database\Seeders;

use App\Models\EppEvaluation;
use App\Models\EppStepEvaluation;
use App\Models\EppInstructorComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeder para pruebas de los endpoints de estadísticas EPP.
 *
 * Crea:
 *  - 1 instructor  → instructor@bomberos.local / Test12345!!
 *  - 5 aprendices  → aprendiz1@bomberos.local … aprendiz5@bomberos.local / Test12345!!
 *  - 8-15 evaluaciones por aprendiz con sus 6 pasos cada una
 *  - Algunos comentarios del instructor en evaluaciones
 */
class EppTestSeeder extends Seeder
{
    // Nombres de los 6 pasos EPP
    private array $stepNames = [
        1 => 'Pantalón y botas',
        2 => 'Capucha',
        3 => 'Chaqueta',
        4 => 'Guantes',
        5 => 'Casco y visor',
        6 => 'Postura final',
    ];

    // Scores base por paso (probabilidad alta/baja por paso para simular dificultades)
    private array $stepBaseScores = [
        1 => [0.75, 0.95],  // Paso 1: fácil
        2 => [0.40, 0.70],  // Paso 2: difícil (capucha)
        3 => [0.60, 0.85],  // Paso 3: medio
        4 => [0.65, 0.90],  // Paso 4: medio-fácil
        5 => [0.55, 0.80],  // Paso 5: medio
        6 => [0.80, 0.98],  // Paso 6: fácil
    ];

    public function run(): void
    {
        // ── 1. Instructor ────────────────────────────────────────────
        $instructor = User::firstOrCreate(
            ['email' => 'instructor@bomberos.local'],
            [
                'name'              => 'Carlos Mendoza',
                'username'          => 'instructor1',
                'password'          => Hash::make('Test12345!!'),
                'email_verified_at' => now(),
            ]
        );
        $instructor->syncRoles(['instructor']);

        // ── 2. Aprendices ────────────────────────────────────────────
        $aprendicesData = [
            ['name' => 'Pedro Ramírez',  'email' => 'aprendiz1@bomberos.local', 'multiplier' => 1.10], // buen alumno
            ['name' => 'Ana Gómez',      'email' => 'aprendiz2@bomberos.local', 'multiplier' => 1.00], // promedio
            ['name' => 'Luis Torres',    'email' => 'aprendiz3@bomberos.local', 'multiplier' => 0.90], // por debajo
            ['name' => 'María Vargas',   'email' => 'aprendiz4@bomberos.local', 'multiplier' => 0.75], // necesita ayuda
            ['name' => 'Pablo Ruiz',     'email' => 'aprendiz5@bomberos.local', 'multiplier' => 0.65], // necesita ayuda urgente
        ];

        $aprendices = [];
        foreach ($aprendicesData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'username'          => Str::slug($data['name'], '_'),
                    'password'          => Hash::make('Test12345!!'),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles(['aprendiz']);
            $aprendices[] = ['user' => $user, 'multiplier' => $data['multiplier']];
        }

        // ── 3. Evaluaciones por aprendiz ────────────────────────────
        foreach ($aprendices as $entry) {
            $user       = $entry['user'];
            $multiplier = $entry['multiplier'];

            // Saltar si ya tiene evaluaciones (re-seed seguro)
            if (EppEvaluation::where('user_id', $user->id)->exists()) {
                continue;
            }

            $numEvals = rand(8, 15);

            for ($attempt = 1; $attempt <= $numEvals; $attempt++) {
                // Simular mejora progresiva: el multiplier sube ligeramente con cada intento
                $progressBonus = ($attempt - 1) * 0.008;
                $effectiveMultiplier = min($multiplier + $progressBonus, 1.05);

                // Calcular scores de los 6 pasos
                $stepScores    = [];
                $totalScore    = 0;
                $stepsCompleted = 0;

                foreach ($this->stepNames as $stepNum => $stepName) {
                    [$min, $max] = $this->stepBaseScores[$stepNum];
                    $rawScore = $min + ($max - $min) * mt_rand(0, 100) / 100;
                    $score    = round(min($rawScore * $effectiveMultiplier, 1.0), 4);

                    $status   = $score >= 0.60 ? 'correcto' : 'incorrecto';
                    if ($status === 'correcto') {
                        $stepsCompleted++;
                    }

                    $stepScores[$stepNum] = $score;
                    $totalScore += $score * 100;
                }

                $generalScore = round($totalScore / 6, 2);
                $correctOrder = rand(0, 10) > 2; // 70% de probabilidad de orden correcto
                $status       = ($stepsCompleted >= 5 && $generalScore >= 70 && $correctOrder)
                    ? 'aprobado'
                    : ($stepsCompleted < 6 ? 'incompleto' : 'reprobado');

                $daysAgo      = $numEvals - $attempt; // más antiguo = más intentos atrás
                $createdAt    = now()->subDays($daysAgo)->subHours(rand(0, 8));

                $evaluation = EppEvaluation::create([
                    'session_id'            => Str::uuid(),
                    'user_id'               => $user->id,
                    'general_score'         => $generalScore,
                    'total_steps'           => 6,
                    'steps_completed'       => $stepsCompleted,
                    'correct_order'         => $correctOrder,
                    'duration_seconds'      => rand(45, 120),
                    'total_frames'          => rand(800, 2000),
                    'frames_with_detection' => rand(600, 1800),
                    'detection_rate'        => round(rand(70, 98) / 100, 2),
                    'status'                => $status,
                    'recommendations'       => $status !== 'aprobado'
                        ? 'Practicar más el Paso 2 (Capucha) y verificar el orden correcto.'
                        : null,
                    'created_at'            => $createdAt,
                    'updated_at'            => $createdAt,
                ]);

                // Insertar los 6 pasos
                $steps = [];
                foreach ($this->stepNames as $stepNum => $stepName) {
                    $score  = $stepScores[$stepNum];
                    $steps[] = [
                        'evaluation_id' => $evaluation->id,
                        'step_number'   => $stepNum,
                        'step_name'     => $stepName,
                        'score'         => $score,
                        'status'        => $score >= 0.60 ? 'correcto' : 'incorrecto',
                        'detected'      => $score > 0.30,
                        'feedback'      => $score < 0.60
                            ? "Mejorar técnica en {$stepName}"
                            : "Bien ejecutado",
                        'time_start'    => ($stepNum - 1) * 10.0,
                        'time_end'      => $stepNum * 10.0,
                        'duration'      => 10.0,
                        'created_at'    => $createdAt,
                        'updated_at'    => $createdAt,
                    ];
                }
                EppStepEvaluation::insert($steps);

                // El instructor comenta ~30% de las evaluaciones
                if (rand(1, 10) <= 3) {
                    $commentType = $status === 'aprobado' ? 'felicitacion' : 'correcion';
                    EppInstructorComment::create([
                        'evaluation_id' => $evaluation->id,
                        'instructor_id' => $instructor->id,
                        'type'          => $commentType,
                        'comment'       => $status === 'aprobado'
                            ? '¡Buen trabajo! Sigue practicando para mantener el nivel.'
                            : 'Necesitas reforzar el Paso 2. Te recomiendo practicar frente al espejo.',
                        'created_at'    => $createdAt->copy()->addMinutes(30),
                        'updated_at'    => $createdAt->copy()->addMinutes(30),
                    ]);
                }
            }
        }

        $this->command->info('✅ EppTestSeeder completado:');
        $this->command->info('   Instructor: instructor@bomberos.local / Test12345!!');
        $this->command->info('   Aprendices: aprendiz1@bomberos.local … aprendiz5@bomberos.local / Test12345!!');
        $this->command->info('   Admin:      admin@bomberos.local / Admin12345!!');
    }
}
