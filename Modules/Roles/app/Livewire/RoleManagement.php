<?php

namespace Modules\Roles\Livewire;

use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Modules\Roles\Services\RoleService;
use Spatie\Permission\Models\Role;

class RoleManagement extends Component
{
    use ValidationMessages;

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

            session()->flash('success', $this->createContextualSuccessMessage('created', 'name', 'role'));
            $this->reset(['name', 'display_name', 'description', 'isLoading', 'showRoleForm', 'selectedPermissions']);
            $this->dispatch('role-created');

            // Sayfayı yenile ki menü güncellensin
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Rol oluşturulurken hata oluştu: '.$e->getMessage());
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
            session()->flash('error', 'Süper Admin rolü düzenlenemez! Bu rol zaten tüm yetkilere sahiptir.');

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
                session()->flash('error', 'Düzenlenecek rol bulunamadı.');

                return;
            }

            // Süper Admin rolünü güncellemeyi engelle (zaten tüm yetkilere sahip)
            if ($this->roleService->isSystemRole($this->editingRole)) {
                session()->flash('error', 'Süper Admin rolü güncellenemez! Bu rol zaten tüm yetkilere sahiptir.');

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

            session()->flash('success', $this->createContextualSuccessMessage('updated', 'name', 'role'));
            $this->reset(['name', 'display_name', 'description', 'editingRole', 'isLoading', 'showRoleForm', 'selectedPermissions']);
            $this->dispatch('role-updated');

            // Sayfayı yenile ki menü güncellensin
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Rol güncellenirken hata oluştu: '.$e->getMessage());
        }
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'display_name', 'description', 'editingRole']);
    }

    public function confirmDeleteRole(int $id): void
    {
        $this->dispatch('confirm-delete-role', title: 'Silinsin mi?', message: 'Bu rol silinecek, onaylıyor musunuz?', roleId: $id);
    }

    #[On('deleteRole')]
    public function deleteRole(int $roleId): void
    {
        if (! Auth::user()->can('delete roles')) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        try {
            $role = Role::findOrFail($roleId);

            // Süper Admin rolünü silmeyi engelle
            if ($this->roleService->isSystemRole($role)) {
                session()->flash('error', 'Süper Admin rolü silinemez!');

                return;
            }

            $this->roleService->deleteRole($role, Auth::user());
            session()->flash('message', 'Rol silindi');

            // Sayfayı yenile ki menü güncellensin
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            session()->flash('error', 'Rol silinirken hata oluştu: '.$e->getMessage());
        }
    }

    public function openPermissionModal(int $roleId): void
    {
        $role = $this->roleService->getRoleWithPermissions($roleId);

        // Süper Admin rolünün yetkilerini düzenlemeyi engelle (zaten tüm yetkilere sahip)
        if ($this->roleService->isSystemRole($role)) {
            session()->flash('error', 'Süper Admin rolünün yetkileri düzenlenemez! Bu rol zaten tüm yetkilere sahiptir.');

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
                session()->flash('error', 'Düzenlenecek rol bulunamadı.');

                return;
            }

            // Süper Admin rolünün yetkilerini güncellemeyi engelle (zaten tüm yetkilere sahip)
            if ($this->roleService->isSystemRole($this->editingRole)) {
                session()->flash('error', 'Süper Admin rolünün yetkileri güncellenemez! Bu rol zaten tüm yetkilere sahiptir.');

                return;
            }

            $this->isLoading = true;
            $this->validate(['selectedPermissions' => ['array']]);

            $this->roleService->syncRolePermissions($this->editingRole, $this->selectedPermissions, Auth::user());
            session()->flash('message', 'Yetkiler güncellendi');
            $this->closePermissionModal();

            // Sayfayı yenile ki menü güncellensin
            $this->dispatch('refresh-page');
        } catch (\Exception $e) {
            $this->isLoading = false;
            session()->flash('error', 'Yetkiler güncellenirken hata oluştu: '.$e->getMessage());
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
