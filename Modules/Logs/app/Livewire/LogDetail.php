<?php

namespace Modules\Logs\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Logs\Models\UserLog;

class LogDetail extends Component
{
    public ?\Modules\Logs\Models\UserLog $log = null;

    public function mount($id)
    {
        if (! Auth::user()->can('view logs')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        $this->log = UserLog::with('user')->findOrFail($id);
    }

    public function deleteLog()
    {
        if (! Auth::user()->can('delete logs')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $this->log->delete();
            session()->flash('success', 'Log kaydı başarıyla silindi.');

            return redirect()->route('logs.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Log kaydı silinirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Modül aktiflik kontrolü
        if (! \App\Helpers\SystemHelper::isModuleActive('logs')) {
            abort(404, 'Logs modülü aktif değil.');
        }

        /** @var view-string $view */
        $view = 'logs::livewire.log-detail';

        return view($view)
            ->extends('layouts.admin')
            ->section('content');
    }
}
