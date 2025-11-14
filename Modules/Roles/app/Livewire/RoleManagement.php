<?php

namespace Modules\Roles\Livewire;

use App\Traits\ValidationMessages;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagement extends Component
{
    use ValidationMessages;

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

            $role = Role::create([
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
            ]);

            // Yetkileri ata
            if (! empty($this->selectedPermissions)) {
                // Modül yönetimi yetkilerini kontrol et - sadece super_admin değiştirebilir
                $modulePermissions = ['view modules', 'edit modules', 'activate modules'];
                $isSuperAdmin = Auth::user()->hasRole('super_admin');

                // Eğer super_admin değilse, modül yetkilerini selectedPermissions'dan çıkar
                if (! $isSuperAdmin) {
                    $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
                }

                $permissions = Permission::whereIn('name', $this->selectedPermissions)->get();
                $role->syncPermissions($permissions);

                // Log kaydı - yetki atama
                \Modules\Logs\Models\UserLog::log(
                    'update',
                    'Role yetkileri atandı: '.$role->name.' - '.$permissions->pluck('name')->implode(', '),
                    'Spatie\Permission\Models\Role',
                    $role->id
                );
            }

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

        $role = Role::with('permissions')->findOrFail($roleId);

        // Süper Admin rolünü düzenlemeyi engelle (zaten tüm yetkilere sahip)
        if ($role->name === 'super_admin') {
            session()->flash('error', 'Süper Admin rolü düzenlenemez! Bu rol zaten tüm yetkilere sahiptir.');

            return;
        }

        $this->editingRole = $role;
        $this->name = $role->name;
        $this->display_name = $role->name; // Spatie Permission'da display_name yok, name kullanıyoruz
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
            if ($this->editingRole->name === 'super_admin') {
                session()->flash('error', 'Süper Admin rolü güncellenemez! Bu rol zaten tüm yetkilere sahiptir.');

                return;
            }

            $this->isLoading = true;
            $this->rules['name'] = 'required|string|max:255|unique:roles,name,'.$this->editingRole->id;
            $this->validate();

            $this->editingRole->update([
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
            ]);

            // Yetkileri güncelle
            if (! empty($this->selectedPermissions)) {
                // Modül yönetimi yetkilerini kontrol et - sadece super_admin değiştirebilir
                $modulePermissions = ['view modules', 'edit modules', 'activate modules'];
                $isSuperAdmin = Auth::user()->hasRole('super_admin');

                // Eğer super_admin değilse, modül yetkilerini selectedPermissions'dan çıkar
                if (! $isSuperAdmin) {
                    $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
                }

                $permissions = Permission::whereIn('name', $this->selectedPermissions)->get();
                $this->editingRole->syncPermissions($permissions);

                // Log kaydı - yetki güncelleme
                \Modules\Logs\Models\UserLog::log(
                    'update',
                    'Role yetkileri güncellendi: '.$this->editingRole->name.' - '.$permissions->pluck('name')->implode(', '),
                    'Spatie\Permission\Models\Role',
                    $this->editingRole->id
                );
            } else {
                $this->editingRole->syncPermissions([]);

                // Log kaydı - yetki temizleme
                \Modules\Logs\Models\UserLog::log(
                    'update',
                    'Role yetkileri temizlendi: '.$this->editingRole->name,
                    'Spatie\Permission\Models\Role',
                    $this->editingRole->id
                );
            }

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

        $role = Role::findOrFail($roleId);

        // Süper Admin rolünü silmeyi engelle
        if ($role->name === 'super_admin') {
            session()->flash('error', 'Süper Admin rolü silinemez!');

            return;
        }

        $role->delete();
        session()->flash('message', 'Rol silindi');

        // Sayfayı yenile ki menü güncellensin
        $this->dispatch('refresh-page');
    }

    public function openPermissionModal(int $roleId): void
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        // Süper Admin rolünün yetkilerini düzenlemeyi engelle (zaten tüm yetkilere sahip)
        if ($role->name === 'super_admin') {
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
            if ($this->editingRole->name === 'super_admin') {
                session()->flash('error', 'Süper Admin rolünün yetkileri güncellenemez! Bu rol zaten tüm yetkilere sahiptir.');

                return;
            }

            $this->isLoading = true;
            $this->validate(['selectedPermissions' => ['array']]);

            // Modül yönetimi yetkilerini kontrol et - sadece super_admin değiştirebilir
            $modulePermissions = ['view modules', 'edit modules', 'activate modules'];
            $isSuperAdmin = Auth::user()->hasRole('super_admin');

            // Eğer super_admin değilse, modül yetkilerini selectedPermissions'dan çıkar
            if (! $isSuperAdmin) {
                $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
            }

            $this->editingRole->syncPermissions($this->selectedPermissions);
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
        $permissions = Permission::all()->groupBy(function ($permission) {
            $name = $permission->name;

            // Özel kontroller - önce özel durumları kontrol et
            if (str_contains($name, 'newsletter')) {
                return 'newsletters';
            }

            if (str_contains($name, 'modules')) {
                return 'modules';
            }

            // Modül isimlerini kontrol et - permission name'inde geçen modül adını bul
            $moduleKeywords = [
                'articles' => 'articles',
                'users' => 'users',
                'categories' => 'categories',
                'posts' => 'posts',
                'roles' => 'roles',
                'authors' => 'authors',
                'comments' => 'comments',
                'logs' => 'logs',
                'featured' => 'featured',
                'lastminutes' => 'lastminutes',
                'agency_news' => 'agency_news',
                'files' => 'files',
                'settings' => 'settings',
                'menu' => 'settings',
                'stocks' => 'banks',
                'investor_questions' => 'banks',
            ];

            // Permission name'inde hangi modül adı geçiyor kontrol et
            foreach ($moduleKeywords as $keyword => $module) {
                if (str_contains($name, $keyword)) {
                    return $module;
                }
            }

            return 'other';
        });

        /** @var view-string $view */
        $view = 'roles::livewire.role-management';

        return view($view, compact('roles', 'permissions'))
            ->extends('layouts.admin')->section('content');
    }
}
