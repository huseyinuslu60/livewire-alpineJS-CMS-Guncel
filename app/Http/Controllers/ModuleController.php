<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function __construct()
    {
        // Route'larda zaten admin middleware'i var, burada ek kontrol yapmaya gerek yok
    }

    /**
     * Modül yönetimi ana sayfası
     */
    public function index()
    {
        // Permission middleware zaten kontrol ediyor, ekstra kontrol gerekmez
        $modules = Module::orderBy('sort_order')->get();

        /** @var view-string $view */
        $view = 'modules.index';

        return view($view, compact('modules'));
    }

    /**
     * Modül durumunu değiştir (aktif/pasif)
     */
    public function toggleStatus(Request $request, $id)
    {
        // Permission middleware zaten kontrol ediyor
        $module = Module::findOrFail($id);
        $module->toggleStatus();

        return response()->json([
            'success' => true,
            'message' => $module->display_name.' modülü '.($module->is_active ? 'aktif' : 'pasif').' edildi.',
            'is_active' => $module->is_active,
        ]);
    }

    /**
     * Modül detaylarını güncelle
     */
    public function update(Request $request, $id)
    {
        // Permission middleware zaten kontrol ediyor
        $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0',
        ]);

        $module = Module::findOrFail($id);
        $module->update($request->only(['display_name', 'description', 'icon', 'sort_order']));

        return response()->json([
            'success' => true,
            'message' => 'Modül başarıyla güncellendi.',
        ]);
    }

    /**
     * Modül durumunu kontrol et
     */
    public function checkStatus($moduleName)
    {
        $isActive = Module::isActive($moduleName);

        return response()->json([
            'module' => $moduleName,
            'is_active' => $isActive,
        ]);
    }
}
