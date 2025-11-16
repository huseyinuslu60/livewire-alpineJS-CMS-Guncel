// Banks Module JavaScript
// ======================

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

function initializeDateInputs() {
    // Date input picker functionality
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Add click handler to open date picker
        input.addEventListener('click', function() {
            if (this.showPicker) {
                this.showPicker();
            }
        });
        
        // Add cursor pointer style
        input.style.cursor = 'pointer';
    });
}

// Module initialization function
function initBanksModule() {
    // Date inputs initialization
    const dateInputs = document.querySelectorAll('input[type="date"]');
    if (dateInputs.length === 0) return;
    initializeDateInputs();
}

// Register module with central lifecycle manager
registerModuleInit('banks', initBanksModule);
