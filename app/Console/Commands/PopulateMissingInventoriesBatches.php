<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Models\InventoriesBatch;
use Illuminate\Support\Facades\DB;

class PopulateMissingInventoriesBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batches:populate-inventories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate missing inventories_batches records for existing batches from finalized purchases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Buscando batches sin registro en inventories_batches...');

        DB::beginTransaction();

        try {
            // Obtener todos los batches que tienen inventory_id (incluyendo los con cantidad 0)
            $batches = Batch::whereNotNull('inventory_id')
                ->get();

            $this->info("✅ Encontrados {$batches->count()} batches con inventario asignado");

            $created = 0;
            $skipped = 0;

            foreach ($batches as $batch) {
                // Verificar si ya existe el registro en inventories_batches
                $exists = InventoriesBatch::where('id_inventory', $batch->inventory_id)
                    ->where('id_batch', $batch->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Crear el registro faltante
                InventoriesBatch::create([
                    'id_inventory' => $batch->inventory_id,
                    'id_batch' => $batch->id,
                    'quantity' => $batch->available_quantity,
                    'operation_date' => $batch->incoming_date ?? now(),
                ]);

                $created++;

                $this->line("  ✓ Creado inventories_batches para batch ID {$batch->id} (inventory: {$batch->inventory_id}, cantidad: {$batch->available_quantity})");
            }

            DB::commit();

            $this->newLine();
            $this->info("✅ Proceso completado:");
            $this->info("   • Registros creados: {$created}");
            $this->info("   • Registros existentes (omitidos): {$skipped}");
            $this->info("   • Total procesados: {$batches->count()}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error al procesar batches: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
