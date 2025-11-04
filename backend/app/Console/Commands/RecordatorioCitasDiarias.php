<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Notification;
use Carbon\Carbon;

class RecordatorioCitasDiarias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'citas:recordatorio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios automáticos de citas programadas para hoy';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🕒 Iniciando proceso de recordatorios de citas...');
        
        // Obtener citas de hoy que estén pendientes o confirmadas
        $today = Carbon::today();
        $citas = Appointment::with(['user', 'appointmentType'])
            ->whereDate('begin', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $recordatoriosEnviados = 0;

        foreach ($citas as $cita) {
            // Verificar si ya se envió recordatorio para esta cita hoy
            $recordatorioExistente = Notification::where('user_id', $cita->user_id)
                ->where('title', 'Recordatorio de Cita')
                ->whereDate('created_at', $today)
                ->whereRaw('JSON_EXTRACT(message, "$.appointment_id") = ?', [$cita->id])
                ->first();

            if (!$recordatorioExistente) {
                // Crear notificación de recordatorio
                $fechaHora = Carbon::parse($cita->begin)->format('H:i');
                $tipoCita = $cita->appointmentType->name ?? 'Cita';
                
                Notification::create([
                    'user_id' => $cita->user_id,
                    'title' => 'Recordatorio de Cita',
                    'message' => json_encode([
                        'text' => "Tiene una {$tipoCita} programada para hoy a las {$fechaHora}.",
                        'appointment_id' => $cita->id,
                        'appointment_title' => $cita->title,
                        'appointment_time' => $fechaHora
                    ]),
                    'type' => 'reminder',
                    'is_read' => false
                ]);

                $recordatoriosEnviados++;
                
                $this->line("✅ Recordatorio enviado para cita #{$cita->id} - Usuario: {$cita->user->name}");
            } else {
                $this->line("⏭️  Recordatorio ya enviado para cita #{$cita->id}");
            }
        }

        $totalCitas = $citas->count();
        
        $this->info("📊 Proceso completado:");
        $this->info("   • Citas encontradas para hoy: {$totalCitas}");
        $this->info("   • Recordatorios enviados: {$recordatoriosEnviados}");
        $this->info("   • Recordatorios omitidos (ya enviados): " . ($totalCitas - $recordatoriosEnviados));
        
        if ($recordatoriosEnviados > 0) {
            $this->info("🎉 ¡Recordatorios enviados exitosamente!");
        } elseif ($totalCitas > 0) {
            $this->warn("⚠️  Todos los recordatorios ya habían sido enviados.");
        } else {
            $this->comment("ℹ️  No hay citas programadas para hoy.");
        }

        return 0;
    }
}
