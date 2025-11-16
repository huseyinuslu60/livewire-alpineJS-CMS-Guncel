@extends('layouts.admin')

@section('title', 'Modül Yönetimi')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="#!">Modül Yönetimi</a></li>
@endsection

@can('view settings')
@section('content')
<div class="min-h-screen bg-gray-50" x-data="moduleManagement()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl flex items-center justify-center mr-6">
                        <i class="fas fa-cogs text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Modül Yönetimi</h1>
                        <p class="text-gray-600 text-lg mt-2">Sistemdeki modülleri aktif/pasif yapabilir ve yönetebilirsiniz</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-700">Sistem Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Aktif Modüller -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-transparent hover:border-green-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-4xl font-bold mb-2">{{ $modules->where('is_active', true)->count() }}</h3>
                        <p class="text-green-100 text-sm font-medium">Aktif Modül</p>
                    </div>
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pasif Modüller -->
            <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-transparent hover:border-orange-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-4xl font-bold mb-2">{{ $modules->where('is_active', false)->count() }}</h3>
                        <p class="text-orange-100 text-sm font-medium">Pasif Modül</p>
                    </div>
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                        <i class="fas fa-pause-circle text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Toplam Modüller -->
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-transparent hover:border-purple-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-4xl font-bold mb-2">{{ $modules->count() }}</h3>
                        <p class="text-purple-100 text-sm font-medium">Toplam Modül</p>
                    </div>
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                        <i class="fas fa-cube text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Aktiflik Oranı -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-transparent hover:border-indigo-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-4xl font-bold mb-2">{{ round(($modules->where('is_active', true)->count() / $modules->count()) * 100, 1) }}%</h3>
                        <p class="text-indigo-100 text-sm font-medium">Aktiflik Oranı</p>
                    </div>
                    <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Module Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($modules as $module)
            <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 border border-gray-200 hover:border-purple-300 overflow-hidden"
                 x-data="{
                     isActive: {{ $module->is_active ? 'true' : 'false' }},
                     isLoading: false
                 }">
                <!-- Module Header -->
                <div class="bg-gradient-to-r {{ $module->is_active ? 'from-green-500 to-emerald-600' : 'from-orange-500 to-red-500' }} p-6 text-white min-h-[160px] flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-4">
                                @if($module->icon && str_contains($module->icon, 'feather'))
                                    <i class="fas fa-{{ str_replace('feather icon-', '', $module->icon) }} text-l"></i>
                                @else
                                    <i class="{{ $module->icon ?: 'fas fa-cube' }} text-xl"></i>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-l font-bold">{{ $module->display_name }}</h3>
                                <p class="text-sm opacity-90">{{ $module->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-medium">
                                v{{ $module->version }}
                            </span>
                            <div class="w-3 h-3 rounded-full {{ $module->is_active ? 'bg-green-400' : 'bg-red-400' }}"></div>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $module->is_active ? 'bg-green-500/20 text-green-100 border border-green-400/30' : 'bg-red-500/20 text-red-100 border border-red-400/30' }} backdrop-blur-sm">
                            <i class="fas fa-{{ $module->is_active ? 'check' : 'times' }} mr-2"></i>
                            {{ $module->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                </div>

                <!-- Module Content -->
                <div class="p-6">
                    <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                        {{ $module->description ?? 'Açıklama bulunmuyor' }}
                    </p>

                    @if($module->permissions)
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">İzinler:</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($module->permissions, 0, 3) as $permission)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $permission }}
                            </span>
                            @endforeach
                            @if(count($module->permissions) > 3)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-200 text-gray-600">
                                +{{ count($module->permissions) - 3 }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Module Actions -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between space-x-3">
                        <button type="button"
                                @click="toggleModuleStatus({{ $module->id }}, '{{ $module->display_name }}')"
                                :disabled="isLoading"
                                :class="isLoading ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 bg-gradient-to-r {{ $module->is_active ? 'from-red-500 to-red-600 hover:from-red-600 hover:to-red-700' : 'from-green-500 to-green-600 hover:from-green-600 hover:to-green-700' }} text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 hover:shadow-lg cursor-pointer">
                            <i class="fas fa-{{ $module->is_active ? 'pause' : 'play' }} mr-2"></i>
                            <span x-text="isLoading ? 'İşleniyor...' : '{{ $module->is_active ? 'Pasif Yap' : 'Aktif Yap' }}'"></span>
                        </button>
                        <button type="button"
                                @click="editModule({{ $module->id }}, '{{ addslashes($module->display_name) }}', '{{ addslashes($module->description ?? '') }}', '{{ addslashes($module->icon) }}', {{ $module->sort_order }})"
                                class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 hover:shadow-lg cursor-pointer">
                            <i class="fas fa-edit mr-2"></i>
                            Düzenle
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Module Edit Modal -->
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         x-show="showEditModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="bg-black/60 backdrop-blur-sm"
         @click="closeModal()"
         x-init="
             $watch('showEditModal', value => {
                 if (value) {
                     document.body.style.overflow = 'hidden';
                 } else {
                     document.body.style.overflow = 'auto';
                 }
             });
         "
         x-cloak>

        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[95vh] overflow-hidden transform transition-all duration-300 relative z-[10000]"
             @click.stop>
            <!-- Modal Header -->
            <div class="relative bg-gradient-to-r from-purple-600 via-indigo-600 to-blue-600 p-8 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mr-6">
                            <i class="fas fa-edit text-3xl"></i>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold mb-2">Modül Düzenle</h2>
                            <p class="text-blue-100 text-lg">Modül bilgilerini güncelleyin</p>
                        </div>
                    </div>
                    <button @click="closeModal()"
                            class="w-12 h-12 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 cursor-pointer">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <form @submit.prevent="updateModule()" class="p-8">
                <input type="hidden" x-model="editModuleId" name="module_id">

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Görünen Ad</label>
                        <input type="text"
                               x-model="editDisplayName"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                               placeholder="Modül görünen adı"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Açıklama</label>
                        <textarea x-model="editDescription"
                                  rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                  placeholder="Modül açıklaması"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">İkon</label>
                        <input type="text"
                               x-model="editIcon"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                               placeholder="fas fa-cog"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sıralama</label>
                        <input type="number"
                               x-model="editSortOrder"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                               placeholder="0"
                               required>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-4 pt-8 border-t border-gray-200 mt-8">
                    <button type="button"
                            @click="closeModal()"
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-all duration-200 cursor-pointer">
                        İptal
                    </button>
                    <button type="submit"
                            :disabled="isUpdating"
                            :class="isUpdating ? 'opacity-50 cursor-not-allowed' : ''"
                            class="px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl cursor-pointer">
                        <i class="fas fa-save mr-2"></i>
                        <span x-text="isUpdating ? 'Kaydediliyor...' : 'Kaydet'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@else
@section('content')
<div class="main-body">
    <div class="page-wrapper">
                <div class="page-header">
                    <div class="page-header-title">
                        <h4>
                            <i class="fas fa-shield-alt text-danger mr-2"></i>
                            Erişim Reddedildi
                        </h4>
                        <p class="text-muted mb-0">Bu sayfaya erişim yetkiniz bulunmamaktadır.</p>
                    </div>
                </div>
                <div class="page-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-shield-alt text-danger text-6xl"></i>
                                    <h4 class="mt-3 text-danger">Erişim Yetkisi Yok</h4>
                                    <p class="text-muted">Modül yönetimi sayfasına erişmek için admin yetkisine sahip olmanız gerekmektedir.</p>
                                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                        <i class="fas fa-arrow-left mr-1"></i>
                                        Dashboard'a Dön
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@endcan

@push('styles')
<style>
[x-cloak] {
    display: none !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('moduleManagement', () => ({
        // Edit modal data
        showEditModal: false,
        editModuleId: null,
        editDisplayName: '',
        editDescription: '',
        editIcon: '',
        editSortOrder: 0,
        isUpdating: false,

        // Initialize component
        init() {
            // Ensure modal is hidden on init
            this.showEditModal = false;
            this.editModuleId = null;
            this.editDisplayName = '';
            this.editDescription = '';
            this.editIcon = '';
            this.editSortOrder = 0;
            this.isUpdating = false;
        },

        // Toggle module status
        async toggleModuleStatus(moduleId, moduleName) {
            if (confirm(`${moduleName} modülünün durumunu değiştirmek istediğinizden emin misiniz?`)) {
                try {
                    const response = await fetch(`/modules/${moduleId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showNotification('success', data.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        this.showNotification('error', data.message || 'Bir hata oluştu!');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showNotification('error', 'Bir hata oluştu!');
                }
            }
        },

        // Edit module
        editModule(id, name, description, icon, sortOrder) {
            try {
                // Reset form data first
                this.editModuleId = null;
                this.editDisplayName = '';
                this.editDescription = '';
                this.editIcon = '';
                this.editSortOrder = 0;

                // Set new values
                this.editModuleId = id;
                this.editDisplayName = name || '';
                this.editDescription = description || '';
                this.editIcon = icon || '';
                this.editSortOrder = sortOrder || 0;

                // Show modal
                this.showEditModal = true;
            } catch (error) {
                console.error('Edit module error:', error);
                this.showNotification('error', 'Modal açılırken bir hata oluştu!');
            }
        },

        // Close modal
        closeModal() {
            this.showEditModal = false;
            this.editModuleId = null;
            this.editDisplayName = '';
            this.editDescription = '';
            this.editIcon = '';
            this.editSortOrder = 0;
            this.isUpdating = false;
        },

        // Update module
        async updateModule() {
            this.isUpdating = true;

            try {
                const response = await fetch(`/modules/${this.editModuleId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        display_name: this.editDisplayName,
                        description: this.editDescription,
                        icon: this.editIcon,
                        sort_order: this.editSortOrder
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification('success', data.message);
                    this.closeModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    this.showNotification('error', data.message || 'Bir hata oluştu!');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('error', 'Bir hata oluştu!');
            } finally {
                this.isUpdating = false;
            }
        },

        // Show notification
        showNotification(type, message) {
            const alertClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'check-circle' : 'x-circle';

            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-[10000] ${alertClass} text-white px-6 py-4 rounded-xl shadow-lg flex items-center space-x-3 min-w-[300px]`;
            notification.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }
    }));
});
</script>
@endpush
