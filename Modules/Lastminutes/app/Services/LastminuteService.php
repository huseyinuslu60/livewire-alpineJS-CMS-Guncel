<?php

namespace Modules\Lastminutes\Services;

use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
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

            LogHelper::info('Lastminute oluşturuldu', [
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

                LogHelper::info('Lastminute güncellendi', [
                    'lastminute_id' => $lastminute->lastminute_id,
                    'title' => $lastminute->title,
                ]);

                return $lastminute;
            });
        } catch (\Exception $e) {
            LogHelper::error('LastminuteService update error', [
                'lastminute_id' => $lastminute->lastminute_id,
                'error' => $e->getMessage(),
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

                LogHelper::info('Lastminute silindi', [
                    'lastminute_id' => $lastminute->lastminute_id,
                    'title' => $lastminute->title,
                ]);
            });
        } catch (\Exception $e) {
            LogHelper::error('LastminuteService delete error', [
                'lastminute_id' => $lastminute->lastminute_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

