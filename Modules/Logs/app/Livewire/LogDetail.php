<?php

namespace Modules\Logs\Livewire;

use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\HandlesExceptionsWithToast;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Logs\Models\UserLog;

class LogDetail extends Component
{
    use HandlesExceptionsWithToast, InteractsWithToast;

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
            $this->toastSuccess('Log kaydı başarıyla silindi.');

            return redirect()->route('logs.index');
        } catch (\Throwable $e) {
            $this->handleException($e, 'Log kaydı silinirken bir hata oluştu. Lütfen tekrar deneyin.', [
                'log_id' => $this->log->id ?? null,
            ]);
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
