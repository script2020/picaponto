<?php

namespace Database\Seeders;

use App\Models\RegistoHoras;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RegistoHorasSeeder extends Seeder
{
    // Day session patterns: [entrada_hora, saida_hora][]
    // Normal day = 9-13 + 14-18 = 8h (480min)
    private const PATTERNS = [
        'overtime' => [
            [['08:30', '13:00'], ['14:00', '18:30']], // 9h = +1h OT
            [['08:00', '13:00'], ['14:00', '19:00']], // 10h = +2h OT
            [['09:00', '13:00'], ['14:00', '18:00']], // 8h = normal
            [['08:30', '13:00'], ['14:00', '19:30']], // 10h = +2h OT
            [['09:00', '13:00'], ['14:00', '18:00']], // 8h = normal
        ],
        'mixed' => [
            [['09:00', '13:00'], ['14:00', '18:00']], // 8h = normal
            [['09:00', '13:30'], ['14:00', '18:00']], // 8.5h = +30min OT
            [['09:30', '13:00'], ['14:00', '17:00']], // 6.5h = no OT
            [['09:00', '13:00'], ['14:00', '18:30']], // 8.5h = +30min OT
            [['09:00', '13:00'], ['14:00', '18:00']], // 8h = normal
        ],
        'normal' => [
            [['09:00', '13:00'], ['14:00', '18:00']],
            [['09:00', '13:00'], ['14:00', '18:00']],
            [['09:00', '13:00'], ['14:00', '18:00']],
            [['09:00', '13:00'], ['14:00', '18:00']],
            [['09:00', '13:00'], ['14:00', '18:00']],
        ],
    ];

    public function run(): void
    {
        $testUsers = [
            ['name' => 'Ana Costa',    'email' => 'ana.costa@teste.pt',    'pattern' => 'overtime'],
            ['name' => 'Bruno Silva',  'email' => 'bruno.silva@teste.pt',  'pattern' => 'mixed'],
            ['name' => 'Carla Mendes', 'email' => 'carla.mendes@teste.pt', 'pattern' => 'normal'],
        ];

        $workingDays = $this->workingDays(
            Carbon::create(2026, 5, 4),
            Carbon::create(2026, 6, 7)
        );

        foreach ($testUsers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'               => $data['name'],
                    'password'           => Hash::make('password'),
                    'role'               => 'utilizador',
                    'email_verified_at'  => now(),
                ]
            );

            // Clear existing test records to allow re-seeding
            RegistoHoras::where('user_id', $user->id)->delete();

            $pattern = self::PATTERNS[$data['pattern']];

            foreach ($workingDays as $idx => $date) {
                $sessions = $pattern[$idx % count($pattern)];

                foreach ($sessions as [$horaEntrada, $horaSaida]) {
                    [$hE, $mE] = explode(':', $horaEntrada);
                    [$hS, $mS] = explode(':', $horaSaida);

                    RegistoHoras::create([
                        'user_id' => $user->id,
                        'entrada' => $date->copy()->setTime((int)$hE, (int)$mE),
                        'saida'   => $date->copy()->setTime((int)$hS, (int)$mS),
                    ]);
                }
            }
        }

        $this->command->info('Criados ' . count($testUsers) . ' utilizadores de teste com registos de horas.');
    }

    private function workingDays(Carbon $start, Carbon $end): array
    {
        $days = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            if ($current->isWeekday()) {
                $days[] = $current->copy();
            }
            $current->addDay();
        }
        return $days;
    }
}
