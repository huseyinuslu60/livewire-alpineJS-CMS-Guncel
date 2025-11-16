<?php

namespace Modules\Lastminutes\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Lastminutes\Models\Lastminute;

class LastminuteService
{
    /**
     * Create a new lastminute
     */
    public function create(array $data): Lastminute
    {
        return DB::transaction(function () use ($data) {
            $lastminute = Lastminute::create($data);

            Log::info('Lastminute created', [
                'lastminute_id' => $lastminute->lastminute_id,
                'title' => $lastminute->title,
            ]);

            return $lastminute;
        });
    }

    /**
     * Update an existing lastminute
     */
    public function update(Lastminute $lastminute, array $data): Lastminute
    {
        try {
            return DB::transaction(function () use ($lastminute, $data) {
                $lastminute->update($data);

                Log::info('Lastminute updated', [
                    'lastminute_id' => $lastminute->lastminute_id,
                    'title' => $lastminute->title,
                ]);

                return $lastminute;
            });
        } catch (\Exception $e) {
            Log::error('LastminuteService update error:', [
                'lastminute_id' => $lastminute->lastminute_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a lastminute
     */
    public function delete(Lastminute $lastminute): void
    {
        try {
            DB::transaction(function () use ($lastminute) {
                $lastminute->delete();

                Log::info('Lastminute deleted', [
                    'lastminute_id' => $lastminute->lastminute_id,
                    'title' => $lastminute->title,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('LastminuteService delete error:', [
                'lastminute_id' => $lastminute->lastminute_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

