<?php

namespace Modules\Banks\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Banks\Models\Stock;

class StockCreate extends Component
{
    public string $name = '';

    public string $unvan = '';

    public string $kurulus_tarihi = '';

    public string $ilk_islem_tarihi = '';

    public string $merkez_adres = '';

    public string $web = '';

    public string $telefon = '';

    public string $faks = '';

    public string $personel_sayisi = '';

    public string $genel_mudur = '';

    public string $yonetim_kurulu = '';

    public string $faaliyet_alani = '';

    public string $endeksler = '';

    public string $details = '';

    public string $last_status = 'active';

    protected $rules = [
        'name' => 'required|string|max:255',
        'unvan' => 'required|string|max:255',
        'kurulus_tarihi' => 'nullable|date',
        'ilk_islem_tarihi' => 'nullable|date',
        'merkez_adres' => 'nullable|string',
        'web' => 'nullable|url',
        'telefon' => 'nullable|string|max:20',
        'faks' => 'nullable|string|max:20',
        'personel_sayisi' => 'nullable|integer|min:0',
        'genel_mudur' => 'nullable|string|max:255',
        'yonetim_kurulu' => 'nullable|string',
        'faaliyet_alani' => 'nullable|string',
        'endeksler' => 'nullable|string',
        'details' => 'nullable|string',
        'last_status' => 'required|in:active,inactive',
    ];

    protected $messages = [
        'name.required' => 'Hisse senedi adı gereklidir.',
        'unvan.required' => 'Ünvan gereklidir.',
        'kurulus_tarihi.date' => 'Kuruluş tarihi geçerli bir tarih olmalıdır.',
        'ilk_islem_tarihi.date' => 'İlk işlem tarihi geçerli bir tarih olmalıdır.',
        'web.url' => 'Web adresi geçerli bir URL olmalıdır.',
        'personel_sayisi.integer' => 'Personel sayısı sayı olmalıdır.',
        'personel_sayisi.min' => 'Personel sayısı 0\'dan küçük olamaz.',
    ];

    public function save()
    {
        if (! Auth::user()->can('create stocks')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        $this->validate();

        try {
            Stock::create([
                'name' => $this->name,
                'unvan' => $this->unvan,
                'kurulus_tarihi' => $this->kurulus_tarihi ?: null,
                'ilk_islem_tarihi' => $this->ilk_islem_tarihi ?: null,
                'merkez_adres' => $this->merkez_adres,
                'web' => $this->web,
                'telefon' => $this->telefon,
                'faks' => $this->faks,
                'personel_sayisi' => $this->personel_sayisi ?: null,
                'genel_mudur' => $this->genel_mudur,
                'yonetim_kurulu' => $this->yonetim_kurulu,
                'faaliyet_alani' => $this->faaliyet_alani,
                'endeksler' => $this->endeksler,
                'details' => $this->details,
                'last_status' => $this->last_status,
                // Audit fields (created_by, updated_by) are handled by AuditFields trait
            ]);

            session()->flash('success', 'Hisse senedi başarıyla oluşturuldu.');

            return redirect()->route('banks.stocks.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Hisse senedi oluşturulurken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        if (! Auth::user()->can('create stocks')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        /** @var view-string $view */
        $view = 'banks::livewire.stock-create';

        return view($view)
            ->extends('layouts.admin')
            ->section('content');
    }
}
