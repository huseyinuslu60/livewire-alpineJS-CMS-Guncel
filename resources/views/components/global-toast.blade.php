{{-- Global Toast Component - Tüm modüllerde kullanılacak merkezi toast sistemi --}}
<div class="fixed top-4 right-4 z-[9999] space-y-3" x-data>
    <template x-for="toast in $store.globalToast.toasts" :key="toast.id">
        <div
            x-transition.opacity.duration.300ms
            @mouseenter="$store.globalToast.pause(toast.id)"
            @mouseleave="$store.globalToast.resume(toast.id)"
            class="px-4 py-3 rounded-lg shadow-lg bg-white dark:bg-gray-800 border-l-4 min-w-[300px] max-w-md cursor-pointer"
            :class="{
                'border-green-500': toast.type === 'success',
                'border-red-500': toast.type === 'error',
                'border-yellow-500': toast.type === 'warning',
                'border-blue-500': toast.type === 'info',
            }"
        >
            <div class="flex items-center gap-2">
                <i
                    class="fas text-sm flex-shrink-0"
                    :class="{
                        'fa-check-circle text-green-500': toast.type === 'success',
                        'fa-exclamation-circle text-red-500': toast.type === 'error',
                        'fa-exclamation-triangle text-yellow-500': toast.type === 'warning',
                        'fa-info-circle text-blue-500': toast.type === 'info',
                    }"
                ></i>
                <p x-text="toast.message" class="text-sm text-gray-900 dark:text-gray-100 flex-1"></p>
                <button
                    @click="$store.globalToast.remove(toast.id)"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors ml-2 flex-shrink-0"
                    type="button"
                    aria-label="Kapat"
                >
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </div>
    </template>
</div>
