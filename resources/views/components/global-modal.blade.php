{{-- Global Modal Component - Tüm modüllerde kullanılacak merkezi modal --}}
<div
    x-data
    x-show="$store.globalModal.open"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 px-4"
    @click.self="$store.globalModal.close()"
    @keydown.escape.window="$store.globalModal.close()"
    role="dialog"
    aria-modal="true"
    :aria-labelledby="$store.globalModal.open ? 'global-modal-title' : null"
    tabindex="-1"
>
    <div
        x-transition
        class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4"
        @keydown.tab.prevent="$event.shiftKey || focusNext()"
        @keydown.shift.tab.prevent="focusPrevious()"
    >
        <h2
            id="global-modal-title"
            class="text-lg font-semibold text-gray-900 dark:text-white"
            x-text="$store.globalModal.title"
        ></h2>
        <p class="text-gray-600 dark:text-gray-300" x-text="$store.globalModal.message"></p>

        <div class="flex justify-end gap-3 pt-4">
            <button
                type="button"
                class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                @click="$store.globalModal.close()"
                x-text="$store.globalModal.cancelLabel"
                x-ref="cancelButton"
            >
            </button>

            <button
                type="button"
                class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 transition-colors"
                @click="$store.globalModal.confirm()"
                x-text="$store.globalModal.confirmLabel"
                x-ref="confirmButton"
            >
            </button>
        </div>
    </div>
</div>

<script>
    // Focus trap helper functions
    function focusNext() {
        const modal = document.querySelector('[role="dialog"][aria-modal="true"]');
        if (!modal) return;

        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        const currentElement = document.activeElement;

        if (currentElement === lastElement) {
            firstElement?.focus();
        } else {
            const currentIndex = Array.from(focusableElements).indexOf(currentElement);
            focusableElements[currentIndex + 1]?.focus();
        }
    }

    function focusPrevious() {
        const modal = document.querySelector('[role="dialog"][aria-modal="true"]');
        if (!modal) return;

        const focusableElements = modal.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        const currentElement = document.activeElement;

        if (currentElement === firstElement) {
            lastElement?.focus();
        } else {
            const currentIndex = Array.from(focusableElements).indexOf(currentElement);
            focusableElements[currentIndex - 1]?.focus();
        }
    }

    // Make functions globally available
    window.focusNext = focusNext;
    window.focusPrevious = focusPrevious;
</script>
