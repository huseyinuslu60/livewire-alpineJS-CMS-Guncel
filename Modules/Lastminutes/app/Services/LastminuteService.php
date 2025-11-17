<?php

namespace Modules\Lastminutes\Services;

use App\Helpers\LogHelper;
use App\Services\SlugGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Lastminutes\Domain\Events\LastminuteCreated;
use Modules\Lastminutes\Domain\Events\LastminuteDeleted;
use Modules\Lastminutes\Domain\Events\LastminuteUpdated;
use Modules\Lastminutes\Domain\Repositories\LastminuteRepositoryInterface;
use Modules\Lastminutes\Domain\Services\LastminuteValidator;
use Modules\Lastminutes\Models\Lastminute;

class LastminuteService
{
    protected SlugGenerator $slugGenerator;
    protected LastminuteValidator $lastminuteValidator;
    protected LastminuteRepositoryInterface $lastminuteRepository;

    public function __construct(
        ?SlugGenerator $slugGenerator = null,
        ?LastminuteValidator $lastminuteValidator = null,
        ?LastminuteRepositoryInterface $lastminuteRepository = null
    ) {
        $this->slugGenerator = $slugGenerator ?? app(SlugGenerator::class);
        $this->lastminuteValidator = $lastminuteValidator ?? app(LastminuteValidator::class);
        $this->lastminuteRepository = $lastminuteRepository ?? app(LastminuteRepositoryInterface::class);
    }

    /**
     * Create a new lastminute
     */
    public function create(array $data): Lastminute
    {
        try {
            // Validate lastminute data
            $this->lastminuteValidator->validate($data);

            return DB::transaction(function () use ($data) {
                // Generate slug if not provided
                if (empty($data['slug']) && !empty($data['title'])) {
                    $slug = $this->slugGenerator->generate($data['title'], Lastminute::class, 'slug', 'lastminute_id');
                    $data['slug'] = $slug->toString();
                }

                $lastminute = $this->lastminuteRepository->create($data);

                // Fire domain event
                Event::dispatch(new LastminuteCreated($lastminute));

                LogHelper::info('Lastminute oluşturuldu', [
                    'lastminute_id' => $lastminute->lastminute_id,
                    'title' => $lastminute->title,
                ]);

                return $lastminute;
            });
        } catch (\Exception $e) {
            LogHelper::error('LastminuteService create error', [
                'title' => $data['title'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing lastminute
     */
    public function update(Lastminute $lastminute, array $data): Lastminute
    {
        try {
            // Validate lastminute data
            $this->lastminuteValidator->validate($data);

            return DB::transaction(function () use ($lastminute, $data) {
                // Generate slug if title changed and slug is empty
                if (isset($data['title']) && $data['title'] !== $lastminute->title && empty($data['slug'])) {
                    $slug = $this->slugGenerator->generate($data['title'], Lastminute::class, 'slug', 'lastminute_id', $lastminute->lastminute_id);
                    $data['slug'] = $slug->toString();
                }

                $lastminute = $this->lastminuteRepository->update($lastminute, $data);

                // Fire domain event
                $changedAttributes = array_keys($data);
                Event::dispatch(new LastminuteUpdated($lastminute, $changedAttributes));

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
                $this->lastminuteRepository->delete($lastminute);

                // Fire domain event
                Event::dispatch(new LastminuteDeleted($lastminute));

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

