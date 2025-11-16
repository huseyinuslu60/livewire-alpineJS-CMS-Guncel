<?php

namespace Modules\Settings\Http\Livewire;

use App\Helpers\LogHelper;
use App\Helpers\MenuHelper;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class MenuManagement extends Component
{
    /** @var array<int, array{id: int, name: string, title: string, icon: string, type: string, route: string, permission: string, active_pattern: string, parent_id: int|null, sort_order: int, is_active: bool, children: array}> */
    public array $menuItems = [];

    /** @var array<int, string> */
    public array $roles = [];

    // Form fields
    public ?\App\Models\MenuItem $editingItem = null;

    public string $name = '';

    public string $title = '';

    public ?string $icon = null;

    public string $type = 'menu';

    public ?string $route = null;

    public ?string $permission = null;

    /** @var array<int> */
    public array $selectedRoles = [];

    public ?string $activePattern = null;

    public ?int $parentId = null;

    public int $sortOrder = 0;

    public bool $isActive = true;

    // UI states
    public bool $showModal = false;

    public bool $isEditing = false;

    public bool $isLoading = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'title' => 'required|string|max:255',
        'icon' => 'nullable|string|max:255',
        'type' => 'required|in:menu,submenu,divider',
        'route' => 'nullable|string|max:255',
        'permission' => 'nullable|string|max:255',
        'selectedRoles' => 'nullable|array',
        'activePattern' => 'nullable|string|max:255',
        'parentId' => 'nullable|integer',
        'sortOrder' => 'required|integer|min:0',
        'isActive' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Ad alanı zorunludur.',
        'name.string' => 'Ad alanı metin olmalıdır.',
        'name.max' => 'Ad alanı en fazla 255 karakter olabilir.',
        'title.required' => 'Başlık alanı zorunludur.',
        'title.string' => 'Başlık alanı metin olmalıdır.',
        'title.max' => 'Başlık alanı en fazla 255 karakter olabilir.',
        'icon.string' => 'İkon alanı metin olmalıdır.',
        'icon.max' => 'İkon alanı en fazla 255 karakter olabilir.',
        'type.required' => 'Tür alanı zorunludur.',
        'type.in' => 'Seçilen tür geçersiz. Lütfen menü, alt menü veya ayırıcı seçin.',
        'route.string' => 'Rota alanı metin olmalıdır.',
        'route.max' => 'Rota alanı en fazla 255 karakter olabilir.',
        'permission.string' => 'İzin alanı metin olmalıdır.',
        'permission.max' => 'İzin alanı en fazla 255 karakter olabilir.',
        'selectedRoles.array' => 'Roller bir dizi olmalıdır.',
        'activePattern.string' => 'Aktif desen alanı metin olmalıdır.',
        'activePattern.max' => 'Aktif desen alanı en fazla 255 karakter olabilir.',
        'parentId.integer' => 'Üst menü ID\'si bir sayı olmalıdır.',
        'sortOrder.required' => 'Sıralama alanı zorunludur.',
        'sortOrder.integer' => 'Sıralama alanı bir sayı olmalıdır.',
        'sortOrder.min' => 'Sıralama alanı en az 0 olmalıdır.',
        'isActive.boolean' => 'Aktif durumu true veya false olmalıdır.',
    ];

    protected function attributes()
    {
        return [
            'name' => 'ad',
            'title' => 'başlık',
            'icon' => 'ikon',
            'type' => 'tür',
            'route' => 'rota',
            'permission' => 'izin',
            'selectedRoles' => 'roller',
            'activePattern' => 'aktif desen',
            'parentId' => 'üst menü',
            'sortOrder' => 'sıralama',
            'isActive' => 'aktif durumu',
        ];
    }

    public function mount()
    {
        LogHelper::info('MenuManagement component mounted');
        try {
            Gate::authorize('manage menu');
            $this->loadData();
            LogHelper::info('MenuManagement component loaded successfully');
        } catch (\Exception $e) {
            \Log::error('MenuManagement component error: '.$e->getMessage());
            throw $e;
        }
    }

    public function loadData()
    {
        LogHelper::info('Loading menu data...');
        $this->menuItems = MenuItem::with('parent', 'children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'title' => $item->title,
                    'icon' => $item->icon,
                    'type' => $item->type,
                    'route' => $item->route,
                    'permission' => $item->permission,
                    'active_pattern' => $item->active_pattern,
                    'parent_id' => $item->parent_id,
                    'sort_order' => $item->sort_order,
                    'is_active' => $item->is_active,
                    'children' => $item->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'title' => $child->title,
                            'icon' => $child->icon,
                            'type' => $child->type,
                            'route' => $child->route,
                            'permission' => $child->permission,
                            'active_pattern' => $child->active_pattern,
                            'parent_id' => $child->parent_id,
                            'sort_order' => $child->sort_order,
                            'is_active' => $child->is_active,
                            'children' => $child->children->map(function ($subChild) {
                                return [
                                    'id' => $subChild->id,
                                    'name' => $subChild->name,
                                    'title' => $subChild->title,
                                    'icon' => $subChild->icon,
                                    'type' => $subChild->type,
                                    'route' => $subChild->route,
                                    'permission' => $subChild->permission,
                                    'active_pattern' => $subChild->active_pattern,
                                    'parent_id' => $subChild->parent_id,
                                    'sort_order' => $subChild->sort_order,
                                    'is_active' => $subChild->is_active,
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();

        LogHelper::info('Menu items loaded: '.count($this->menuItems));
        $this->roles = Role::all()->pluck('name', 'id')->toArray();
    }

    public function showCreateModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function showEditModal($itemId)
    {
        LogHelper::info('showEditModal called with ID: '.$itemId);

        $item = MenuItem::find($itemId);
        if (! $item) {
            session()->flash('error', 'Menü öğesi bulunamadı.');

            return;
        }

        $this->editingItem = $item;
        $this->name = $item->name;
        $this->title = $item->title;
        $this->icon = $item->icon ?? null;
        $this->type = $item->type;
        $this->route = $item->route ?? null;
        $this->permission = $item->permission ?? null;
        $this->selectedRoles = is_array($item->roles) ? $item->roles : (is_string($item->roles) ? json_decode($item->roles, true) ?? [] : []);
        $this->activePattern = $item->active_pattern ?? null;
        $this->parentId = $item->parent_id;
        $this->sortOrder = $item->sort_order;
        $this->isActive = $item->is_active;

        $this->isEditing = true;
        $this->showModal = true;

        LogHelper::info('showEditModal completed, showModal: true');
        LogHelper::info('Modal state - showModal: true, isEditing: true');
    }

    public function saveMenuItem()
    {
        // Validation - hata varsa otomatik olarak exception fırlatır ve form kapanmaz
        $this->validate();

        $this->isLoading = true;

        try {
            $data = [
                'name' => $this->name,
                'title' => $this->title,
                'icon' => $this->icon ?? null,
                'type' => $this->type,
                'route' => $this->route ?? null,
                'permission' => $this->permission ?? null,
                'roles' => $this->selectedRoles, // Model zaten array olarak cast ediyor, json_encode gerekmez
                'active_pattern' => $this->activePattern ?? null,
                'parent_id' => $this->parentId,
                'sort_order' => $this->sortOrder,
                'is_active' => $this->isActive,
            ];

            if ($this->isEditing && $this->editingItem) {
                $this->editingItem->update($data);
                session()->flash('success', 'Menü öğesi başarıyla güncellendi.');
            } else {
                MenuItem::create($data);
                session()->flash('success', 'Menü öğesi başarıyla oluşturuldu.');
            }

            // Başarılı kayıt sonrası formu kapat ve verileri yenile
            $this->isLoading = false;
            $this->resetValidation(); // Validation hatalarını temizle
            $this->closeModal();
            $this->loadData();

            // Sidebar menü cache'ini temizle
            MenuHelper::clearAllCache();

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation hatası - form kapanmaz, hatalar gösterilir
            $this->isLoading = false;
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Bir hata oluştu: '.$e->getMessage());
            $this->isLoading = false;
        }
    }

    public function deleteMenuItem($itemId)
    {
        try {
            $item = MenuItem::find($itemId);
            if ($item) {
                $item->delete();
                session()->flash('success', 'Menü öğesi başarıyla silindi.');
                $this->loadData();

                // Sidebar menü cache'ini temizle
                MenuHelper::clearAllCache();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Silme işlemi sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function toggleActive($itemId)
    {
        try {
            $item = MenuItem::find($itemId);
            if ($item) {
                $item->update(['is_active' => ! $item->is_active]);
                $this->loadData();

                // Sidebar menü cache'ini temizle
                MenuHelper::clearAllCache();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Durum değiştirme sırasında bir hata oluştu: '.$e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->editingItem = null;
        $this->resetForm();
        $this->resetValidation(); // Validation hatalarını temizle
    }

    public function updateSortOrder($newOrder)
    {
        try {
            LogHelper::info('updateSortOrder called with:', $newOrder);

            if (empty($newOrder) || ! is_array($newOrder)) {
                \Log::warning('Empty or invalid newOrder array');

                return;
            }

            // Duplicate ID'leri önlemek için son güncellemeyi kullan
            $processedIds = [];
            foreach (array_reverse($newOrder) as $item) {
                if (! isset($item['id']) || ! isset($item['sort_order'])) {
                    \Log::warning('Missing id or sort_order in item:', $item);

                    continue;
                }

                // Duplicate ID'yi atla (son güncelleme zaten yapıldı)
                if (in_array($item['id'], $processedIds)) {
                    continue;
                }
                $processedIds[] = $item['id'];

                $updateData = ['sort_order' => $item['sort_order']];

                // parent_id varsa güncelle (null veya 0 ise null yap)
                if (isset($item['parent_id'])) {
                    $updateData['parent_id'] = ($item['parent_id'] === 0 || $item['parent_id'] === null) ? null : (int) $item['parent_id'];
                }

                MenuItem::where('id', $item['id'])
                    ->update($updateData);
            }

            $this->loadData();

            // Sidebar menü cache'ini temizle
            MenuHelper::clearAllCache();

            session()->flash('success', 'Menü sıralaması başarıyla güncellendi.');
        } catch (\Exception $e) {
            \Log::error('Sort order update error: '.$e->getMessage(), [
                'newOrder' => $newOrder,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Sıralama güncellenirken bir hata oluştu: '.$e->getMessage());
        }
    }

    public function addToGroup($type)
    {
        $this->resetForm();
        $this->type = $type;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function showCreateSubmenuModal($parentId)
    {
        $this->resetForm();
        $this->parentId = $parentId;
        $this->type = 'submenu';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function showCreateSubSubmenuModal($parentId)
    {
        $this->resetForm();
        $this->parentId = $parentId;
        $this->type = 'submenu';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->name = '';
        $this->title = '';
        $this->icon = null;
        $this->type = 'menu';
        $this->route = null;
        $this->permission = null;
        $this->selectedRoles = [];
        $this->activePattern = null;
        $this->parentId = null;
        $this->sortOrder = 0;
        $this->isActive = true;
    }

    public function render()
    {
        LogHelper::info('MenuManagement render method called - showModal: '.($this->showModal ? 'true' : 'false'));

        /** @var view-string $view */
        $view = 'settings::livewire.menu-management';

        return view($view)
            ->extends('layouts.admin')
            ->section('content');
    }
}
