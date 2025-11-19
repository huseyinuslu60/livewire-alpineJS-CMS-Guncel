// Temel import'lar
import './bootstrap';

// jQuery - Trumbowyg'den ÖNCE import edilmeli ve global olarak expose edilmelidir
// Global exposure'ı garanti eden özel dosyadan import et
// Bu dosya window.jQuery ve window.$ değerlerini senkron olarak ayarlar
import './jquery-init';

// jQuery'nin global olarak expose edildiğini doğrula
if (typeof window.jQuery === 'undefined' || typeof window.$ === 'undefined') {
    throw new Error('jQuery global olarak expose edilemedi - jquery-init.js çalışmadı');
}

// Trumbowyg - jQuery global olarak erişilebilir olduktan SONRA NPM'den import et
// Trumbowyg jQuery.fn'i genişletir, bu yüzden jQuery önce mevcut olmalıdır
// Artık jQuery global olduğu için, Trumbowyg güvenle genişletebilir
import 'trumbowyg';
import 'trumbowyg/dist/langs/tr.min.js';
// Trumbowyg CSS
import 'trumbowyg/dist/ui/trumbowyg.css';
// Trumbowyg Archive Plugin
import './trumbowyg-archive-plugin';
// Trumbowyg SVG ikonları - URL olarak import et ve path'i ayarla
// Vite SVG'yi bundle edip public URL'ini döndürecek
import svgPath from 'trumbowyg/dist/ui/icons.svg?url';

// Trumbowyg için SVG path'ini ayarla - jQuery ve Trumbowyg yüklendikten sonra
// Trumbowyg UMD modülü olduğu için jQuery.fn'e extend etmesi biraz zaman alabilir
const setTrumbowygSvgPath = () => {
    if (window.jQuery && window.jQuery.trumbowyg) {
        window.jQuery.trumbowyg.svgPath = svgPath;
        return true;
    }
    return false;
};

// Hemen dene
if (!setTrumbowygSvgPath()) {
    // Trumbowyg henüz jQuery'ye extend etmemişse, kısa bir süre bekle
    // Bu, UMD modülünün yüklenmesi için gerekli
    let attempts = 0;
    const maxAttempts = 20;
    const interval = setInterval(() => {
        attempts++;
        if (setTrumbowygSvgPath() || attempts >= maxAttempts) {
            clearInterval(interval);
        }
    }, 50);
}
// Trix kaldırıldı - artık sadece Trumbowyg kullanılıyor

// Alpine.js ve plugin'ler
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import csp from '@alpinejs/csp';
import Sortable from 'sortablejs';

// Global Alpine.js Bileşenleri - Alpine.start()'tan ÖNCE tanımlanmalıdır
// adminApp state objesi döndüren bir fonksiyon olarak tanımlanır
// Blade template'inde şu şekilde kullanılır: x-data="adminApp()" x-init="init()"
window.adminApp = () => ({
    sidebarOpen: false,
    darkMode: false,
    osDarkMode: false,
    osDarkModeListener: null,

    init() {
        this.detectOSDarkMode();

        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode !== null) {
            this.darkMode = savedDarkMode === 'true';
        } else {
            this.darkMode = this.osDarkMode;
        }

        this.applyDarkMode();
        this.setupOSDarkModeListener();

        // Desktop auto sidebar behavior
        if (window.innerWidth >= 1024) {
            const savedSidebarState = localStorage.getItem('sidebarOpen');
            if (savedSidebarState !== null) {
                this.sidebarOpen = savedSidebarState === 'true';
            }
        }
    },

    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        localStorage.setItem('sidebarOpen', this.sidebarOpen ? 'true' : 'false');
    },

    detectOSDarkMode() {
        if (!window.matchMedia) return;
        this.osDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    },

    setupOSDarkModeListener() {
        if (!window.matchMedia) return;
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        this.osDarkModeListener = (event) => {
            if (!localStorage.getItem('darkMode')) {
                this.osDarkMode = event.matches;
                this.darkMode = this.osDarkMode;
                this.applyDarkMode();
            }
        };
        mediaQuery.addEventListener('change', this.osDarkModeListener);
    },

    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('darkMode', String(this.darkMode));
        this.applyDarkMode();
    },

    applyDarkMode() {
        const root = document.documentElement;
        if (this.darkMode) {
            root.classList.add('dark');
            root.dataset.theme = 'dark';
        } else {
            root.classList.remove('dark');
            root.dataset.theme = 'light';
        }
    },
});

// Modül Yönetimi Bileşeni - window pattern kullanarak tutarlılık sağla
// Blade template'inde şu şekilde kullanılır: x-data="moduleManagement()"
window.moduleManagement = () => ({
    showSuccess: true,
    showError: true,
    selectedModules: [],

    init() {
        // Modül Yönetimi Alpine bileşeni başlatıldı
    },

    toggleModule(moduleId) {
        if (this.selectedModules.includes(moduleId)) {
            this.selectedModules = this.selectedModules.filter(id => id !== moduleId);
        } else {
            this.selectedModules.push(moduleId);
        }
    },

    selectAll() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="module_ids[]"]');
        this.selectedModules = Array.from(checkboxes).map(cb => cb.value);
    },

    deselectAll() {
        this.selectedModules = [];
    }
});

// Giriş Uygulaması Bileşeni - adminApp ile aynı pattern
// Blade template'inde şu şekilde kullanılır: x-data="loginApp()"
window.loginApp = () => ({
    showPassword: false,
    darkMode: false,
    isLoading: false,
    osDarkMode: false,
    osDarkModeListener: null,

    init() {
        // OS dark mode tercihini kontrol et
        this.detectOSDarkMode();

        // Dark mode'u localStorage'dan yükle, yoksa OS tercihini kullan
        const savedDarkMode = localStorage.getItem('darkMode');
        if (savedDarkMode !== null) {
            this.darkMode = savedDarkMode === 'true';
        } else {
            // Kullanıcı tercihi yoksa OS tercihini kullan
            this.darkMode = this.osDarkMode;
        }

        // Sayfa yüklendiğinde dark mode'u uygula
        this.applyDarkMode();

        // OS dark mode değişikliklerini dinle (sadece kullanıcı tercihi yoksa)
        this.setupOSDarkModeListener();
    },

    detectOSDarkMode() {
        if (window.matchMedia) {
            this.osDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
    },

    setupOSDarkModeListener() {
        if (window.matchMedia) {
            this.osDarkModeListener = window.matchMedia('(prefers-color-scheme: dark)');
            this.osDarkModeListener.addEventListener('change', (e) => {
                // Sadece kullanıcı tercihi yoksa OS değişimini uygula
                if (localStorage.getItem('darkMode') === null) {
                    this.darkMode = e.matches;
                    this.applyDarkMode();
                }
            });
        }
    },

    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        // Boolean'ı string olarak kaydet
        localStorage.setItem('darkMode', String(this.darkMode));
        this.applyDarkMode();
    },

    applyDarkMode() {
        // String'e karşı sigorta - mutlaka boolean'a çevir
        const isDark = !!this.darkMode;
        document.documentElement.classList.toggle('dark', isDark);
    }
});

// Lifecycle manager'lar - merkezi event yönetimi
// adminApp tanımlandıktan SONRA ama Alpine.start()'tan ÖNCE import et
import { mountEditorsLifecycle } from './editors-lifecycle';
import { mountLivewireAlpineLifecycle } from './livewire-alpine-lifecycle';
import './admin-layout';

// Image Editor - Alpine.js başlatılmadan önce kaydet
import { registerImageEditor } from './image-editor/index';

// AlpineJS Singleton Pattern - Çift başlatmayı önle
// Alpine başlatılmadan önce global Alpine component'lerin tanımlı olduğundan emin ol
if (import.meta.env.DEV) {
    if (typeof window.adminApp === 'undefined') {
        console.warn('adminApp Alpine.start()\'tan önce tanımlı değil');
    }
    if (typeof window.loginApp === 'undefined') {
        console.warn('loginApp Alpine.start()\'tan önce tanımlı değil');
    }
    if (typeof window.moduleManagement === 'undefined') {
        console.warn('moduleManagement Alpine.start()\'tan önce tanımlı değil');
    }
}

// Image Editor'ı kaydet - Alpine.js başlatılmadan önce
document.addEventListener('alpine:init', () => {
    registerImageEditor();
}, { once: true });

// Alpine.js'i başlat - global component'ler zaten tanımlı olmalı
// Her şeyin hazır olduğundan emin olmak için DOMContentLoaded kullan
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.plugin(collapse);

    // CSP plugin'i sadece production'da aktif et
    if (import.meta.env.PROD) {
        Alpine.plugin(csp);
    }

    // Alpine.js'i başlat - DOM hazır olduğunda
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            Alpine.start();
        }, { once: true });
    } else {
        // DOM zaten hazırsa hemen başlat
        Alpine.start();
    }
}

// Global kütüphaneler - legacy kod için window'a ekle
window.Sortable = Sortable;

// Bağımlılıkların yüklendiğini doğrula (sadece dev modunda)
if (import.meta.env.DEV) {
    const errors = [];
    if (typeof window.$ === 'undefined' || typeof window.jQuery === 'undefined') {
        errors.push('jQuery');
    }
    if (window.jQuery && typeof window.jQuery.fn.trumbowyg === 'undefined') {
        errors.push('Trumbowyg');
    }
    if (errors.length > 0) {
        console.error(`Eksik bağımlılıklar: ${errors.join(', ')}`);
    }
}

// Editor Lifecycle Manager - Merkezi lifecycle manager tarafından yönetilir
mountEditorsLifecycle();

// Livewire + Alpine Lifecycle Manager - Merkezi orkestratör
mountLivewireAlpineLifecycle();

// Files Modal Handler
import { initFilesModalHandler } from './files-modal-handler';
initFilesModalHandler();
