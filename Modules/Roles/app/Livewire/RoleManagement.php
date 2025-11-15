<?php

namespace Modules\Roles\Livewire;

use App\Livewire\Concerns\InteractsWithModal;
use App\Livewire\Concerns\InteractsWithToast;
use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Roles\Services\RoleService;
use Spatie\Permission\Models\Role;

class RoleManagement extends Component
{
    use ValidationMessages, InteractsWithModal, InteractsWithToast;

    protected RoleService $roleService;

    public string $name = '';

    public string $display_name = ''; // UI için, backend'de name kullanılıyor

    public string $description = '';

    public ?string $successMessage = null;

    public ?\Spatie\Permission\Models\Role $editingRole = null;

    /** @var array<string> */
    public array $selectedPermissions = [];

    public bool $showPermissionModal = false;

    public bool $showRoleForm = false;

    public bool $isLoading = false;

    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
        'display_name' => 'required|string|max:255', // UI validation
        'description' => 'nullable|string',
    ];

    protected function messages()
    {
        return $this->getContextualValidationMessages()['role'] ?? $this->getValidationMessages();
    }

    public function boot(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function createRole()
    {
        if (! Auth::user()->can('create roles')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        $this->showRoleForm = true;
        $this->reset(['name', 'display_name', 'description', 'editingRole', 'selectedPermissions']);
    }

    public function closeRoleForm()
    {
        $this->showRoleForm = false;
        $this->reset(['name', 'display_name', 'description', 'editingRole', 'selectedPermissions']);
    }

    public function store()
    {
        try {
            $this->isLoading = true;
            $this->validate();

            $data = [
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
                'permissions' => $this->selectedPermissions,
            ];

            $this->roleService->createRole($data, Auth::user());

            $this->toastSuccess($this->createContextualSuccessMessage('created', 'name', 'role'));
            $this->reset(['name', 'display_name', 'description', 'isLoading', 'showRoleForm', 'selectedPermissions']);
            $this->dispatch('role-created');

            // Sayfayı yenile ki menü güncellensin
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            $this->isLoading = false;
            $this->toastError('Rol oluşturulurken hata oluştu: '.$e->getMessage());
        }
    }

    public function editRole($roleId)
    {
        if (! Auth::user()->can('edit roles')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        $role = $this->roleService->getRoleWithPermissions($roleId);

        // Süper Admin rolünü düzenlemeyi engelle (zaten tüm yetkilere sahip)
        if ($this->roleService->isSystemRole($role)) {
            $this->toastError('Süper Admin rolü düzenlenemez! Bu rol zaten tüm yetkilere sahiptir.');
            return;
        }

        $this->editingRole = $role;
        $this->name = $role->name;
        $this->display_name = $role->display_name ?? $role->name;
        $this->description = $role->description ?? '';
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showRoleForm = true;
    }

    public function saveRole()
    {
        if ($this->editingRole) {
            $this->update();
        } else {
            $this->store();
        }
    }

    public function update()
    {
        try {
            if (! $this->editingRole) {
                $this->toastError('Düzenlenecek rol bulunamadı.');
                return;
            }

            // Süper Admin rolünü güncellemeyi engelle (zaten tüm yetkilere sahip)
            if ($this->roleService->isSystemRole($this->editingRole)) {
                $this->toastError('Süper Admin rolü güncellenemez! Bu rol zaten tüm yetkilere sahiptir.');
                return;
            }

            $this->isLoading = true;
            $this->rules['name'] = 'required|string|max:255|unique:roles,name,'.$this->editingRole->id;
            $this->validate();

            $data = [
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
                'permissions' => $this->selectedPermissions,
            ];

            $this->roleService->updateRole($this->editingRole, $data, Auth::user());

            $this->toastSuccess($this->createContextualSuccessMessage('updated', 'name', 'role'));
            $this->reset(['name', 'display_name', 'description', 'editingRole', 'isLoading', 'showRoleForm', 'selectedPermissions']);
            $this->dispatch('role-updated');

            // Sayfayı yenile ki menü güncellensin
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            $this->isLoading = false;
            $this->toastError('Rol güncellenirken hata oluştu: '.$e->getMessage());
        }
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'display_name', 'description', 'editingRole']);
    }

    public function confirmDeleteRole(int $id): void
    {
        $this->confirmModal(
            'Rol Sil',
            'Bu rolü silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.',
            'deleteRole',
            ['id' => $id],
            [
                'confirmLabel' => 'Sil',
                'cancelLabel' => 'İptal',
            ]
        );
    }

    #[On('deleteRole')]
    public function deleteRole(int $roleId): void
    {
        if (! Auth::user()->can('delete roles')) {
            $this->toastError('Bu işlem için yetkiniz bulunmuyor.');
            return;
        }

        try {
            $role = Role::findOrFail($roleId);

            // Süper Admin rolünü silmeyi engelle
            if ($this->roleService->isSystemRole($role)) {
                $this->toastError('Süper Admin rolü silinemez!');
                return;
            }

            $this->roleService->deleteRole($role, Auth::user());
            $this->toastSuccess('Rol silindi');

            // Sayfayı yenile ki menü güncellensin
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            $this->toastError('Rol silinirken hata oluştu: '.$e->getMessage());
        }
    }

    public function openPermissionModal(int $roleId): void
    {
        $role = $this->roleService->getRoleWithPermissions($roleId);

        // Süper Admin rolünün yetkilerini düzenlemeyi engelle (zaten tüm yetkilere sahip)
        if ($this->roleService->isSystemRole($role)) {
            $this->toastError('Süper Admin rolünün yetkileri düzenlenemez! Bu rol zaten tüm yetkilere sahiptir.');
            return;
        }

        $this->editingRole = $role;
        $this->selectedPermissions = $this->editingRole->permissions->pluck('name')->all();
        $this->showPermissionModal = true;
    }

    public function closePermissionModal(): void
    {
        $this->reset('showPermissionModal', 'editingRole', 'selectedPermissions', 'isLoading');
    }

    public function updatePermissions(): void
    {
        try {
            if (! $this->editingRole) {
                $this->toastError('Düzenlenecek rol bulunamadı.');
                return;
            }

            // Süper Admin rolünün yetkilerini güncellemeyi engelle (zaten tüm yetkilere sahip)
            if ($this->roleService->isSystemRole($this->editingRole)) {
                $this->toastError('Süper Admin rolünün yetkileri güncellenemez! Bu rol zaten tüm yetkilere sahiptir.');
                return;
            }

            $this->isLoading = true;
            $this->validate(['selectedPermissions' => ['array']]);

            $this->roleService->syncRolePermissions($this->editingRole, $this->selectedPermissions, Auth::user());
            $this->toastSuccess('Yetkiler güncellendi');
            $this->closePermissionModal();

            // Sayfayı yenile ki menü güncellensin
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            $this->isLoading = false;
            $this->toastError('Yetkiler güncellenirken hata oluştu: '.$e->getMessage());
        }
    }

    public function render()
    {
        // Her render'da fresh data çek (küçük referans listesi - limit ile)
        $roles = Role::withCount(['users', 'permissions'])->limit(50)->get();
        $permissions = $this->roleService->getGroupedPermissions();

        /** @var view-string $view */
        $view = 'roles::livewire.role-management';

        return view($view, compact('roles', 'permissions'))
            ->extends('layouts.admin')->section('content');
    }
}
