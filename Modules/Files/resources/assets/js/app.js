// Dosyalar Modülü - Alpine.js Bileşenleri

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Dosyalar Tablo Bileşeni - Factory pattern
    function filesTableData() {
        return {
            showSuccess: true,
            showError: true,

            init() {
                // Dosyalar Tablo bileşeni başlatıldı
            },

            // Yardımcı fonksiyonlar
            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },

            getFileIcon(mimeType) {
                if (mimeType.includes('image/')) return 'fas fa-image';
                if (mimeType.includes('video/')) return 'fas fa-video';
                if (mimeType.includes('audio/')) return 'fas fa-music';
                if (mimeType.includes('pdf')) return 'fas fa-file-pdf';
                if (mimeType.includes('word')) return 'fas fa-file-word';
                if (mimeType.includes('excel')) return 'fas fa-file-excel';
                if (mimeType.includes('powerpoint')) return 'fas fa-file-powerpoint';
                return 'fas fa-file';
            },

            getFileColor(mimeType) {
            if (mimeType.includes('image/')) return 'bg-green-100 text-green-800';
            if (mimeType.includes('video/')) return 'bg-purple-100 text-purple-800';
            if (mimeType.includes('audio/')) return 'bg-pink-100 text-pink-800';
            if (mimeType.includes('pdf')) return 'bg-red-100 text-red-800';
            if (mimeType.includes('word')) return 'bg-blue-100 text-blue-800';
            if (mimeType.includes('excel')) return 'bg-green-100 text-green-800';
            if (mimeType.includes('powerpoint')) return 'bg-orange-100 text-orange-800';
            return 'bg-gray-100 text-gray-800';
        }
    };
    }

    Alpine.data('filesTable', filesTableData);

    // Global fonksiyon wrapper - x-data="filesTable" ve x-data="filesTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.filesTable) {
        window.filesTable = function () {
            return filesTableData();
        };
    }

    // Dosya Yükleme Bileşeni
    Alpine.data('fileUpload', () => ({
        files: [],
        isUploading: false,
        uploadProgress: 0,
        dragOver: false,
        showSuccess: true,
        showError: true,

        init() {
            // Dosya Yükleme bileşeni başlatıldı
            // Güvenli başlatma
            this.files = this.files || [];
            this.dragOver = this.dragOver || false;
            this.showSuccess = this.showSuccess || true;
            this.showError = this.showError || true;
        },

        handleFileSelect(event) {
            try {
                if (!event || !event.target) return;
                const files = Array.from(event.target.files || []);
                this.files = [...(this.files || []), ...files];
            } catch (error) {
                console.error('File select error:', error);
            }
        },

        handleDrop(event) {
            try {
                if (!event) return;
                event.preventDefault();
                this.dragOver = false;
                const files = Array.from(event.dataTransfer?.files || []);
                this.files = [...(this.files || []), ...files];
            } catch (error) {
                console.error('Drop error:', error);
            }
        },

        handleDragOver(event) {
            try {
                if (!event) return;
                event.preventDefault();
                this.dragOver = true;
            } catch (error) {
                console.error('Drag over error:', error);
            }
        },

        handleDragLeave(event) {
            try {
                if (!event) return;
                event.preventDefault();
                this.dragOver = false;
            } catch (error) {
                console.error('Drag leave error:', error);
            }
        },

        removeFile(index) {
            try {
                if (this.files && Array.isArray(this.files)) {
                    this.files.splice(index, 1);
                }
            } catch (error) {
                console.error('Remove file error:', error);
            }
        },

        async uploadFiles() {
            if (this.files.length === 0) return;

            this.isUploading = true;
            this.uploadProgress = 0;

            try {
                // Simulate upload progress
                const interval = setInterval(() => {
                    this.uploadProgress += 10;
                    if (this.uploadProgress >= 100) {
                        clearInterval(interval);
                        this.isUploading = false;
                        this.files = [];
                        this.uploadProgress = 0;
                        // Livewire yenilemeyi tetikle
                        this.$wire.$refresh();
                    }
                }, 200);
            } catch (error) {
                console.error('Upload error:', error);
                this.isUploading = false;
            }
        },

        getFileIcon(file) {
            if (file.type.includes('image/')) return 'fas fa-image';
            if (file.type.includes('video/')) return 'fas fa-video';
            if (file.type.includes('audio/')) return 'fas fa-music';
            if (file.type.includes('pdf')) return 'fas fa-file-pdf';
            return 'fas fa-file';
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }));

    // Dosya Önizleme Bileşeni
    Alpine.data('files.preview', () => ({
        showPreview: false,
        previewFile: null,

        openPreview(file) {
            this.previewFile = file;
            this.showPreview = true;
        },

        closePreview() {
            this.showPreview = false;
            this.previewFile = null;
        },

        isImage(file) {
            return file.type && file.type.startsWith('image/');
        },

        isVideo(file) {
            return file.type && file.type.startsWith('video/');
        },

        isAudio(file) {
            return file.type && file.type.startsWith('audio/');
        }
    }));

    // Dosya İşlemleri Bileşeni
    Alpine.data('files.actions', () => ({
        showActions: false,
        selectedFiles: [],

        toggleSelection(fileId) {
            const index = this.selectedFiles.indexOf(fileId);
            if (index > -1) {
                this.selectedFiles.splice(index, 1);
            } else {
                this.selectedFiles.push(fileId);
            }
        },

        isSelected(fileId) {
            return this.selectedFiles.includes(fileId);
        },

        selectAll(files) {
            this.selectedFiles = files.map(file => file.id);
        },

        clearSelection() {
            this.selectedFiles = [];
        },

        async deleteSelected() {
            if (this.selectedFiles.length === 0) return;

            if (confirm(`Seçilen ${this.selectedFiles.length} dosyayı silmek istediğinizden emin misiniz?`)) {
                // Livewire toplu silmeyi tetikle
                this.$wire.deleteBulk(this.selectedFiles);
                this.clearSelection();
            }
        }
    }));
}, { once: true });

// Module initialization function
function initFilesModule() {
    // File drag drop initialization
    const dropZone = document.querySelector('[data-drop-zone]');
    if (!dropZone) return;
    initializeFileDragDrop();
}

// Register module with central lifecycle manager
registerModuleInit('files', initFilesModule);

// Yardımcı fonksiyonlar
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getFileIcon(mimeType) {
    if (mimeType.includes('image/')) return 'fas fa-image';
    if (mimeType.includes('video/')) return 'fas fa-video';
    if (mimeType.includes('audio/')) return 'fas fa-music';
    if (mimeType.includes('pdf')) return 'fas fa-file-pdf';
    if (mimeType.includes('word')) return 'fas fa-file-word';
    if (mimeType.includes('excel')) return 'fas fa-file-excel';
    if (mimeType.includes('powerpoint')) return 'fas fa-file-powerpoint';
    if (mimeType.includes('zip')) return 'fas fa-file-archive';
    return 'fas fa-file';
}

function getFileColor(mimeType) {
    if (mimeType.includes('image/')) return 'text-green-600';
    if (mimeType.includes('video/')) return 'text-purple-600';
    if (mimeType.includes('audio/')) return 'text-pink-600';
    if (mimeType.includes('pdf')) return 'text-red-600';
    if (mimeType.includes('word')) return 'text-blue-600';
    if (mimeType.includes('excel')) return 'text-green-600';
    if (mimeType.includes('powerpoint')) return 'text-orange-600';
    return 'text-gray-600';
}

// Dosya sürükle-bırak işlevselliği
function initializeFileDragDrop() {
    const dropZones = document.querySelectorAll('[data-drop-zone]');

    dropZones.forEach(zone => {
        // Null kontrolü ekle
        if (!zone) return;

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (zone && zone.classList) {
                zone.classList.add('border-blue-400', 'bg-blue-50');
            }
        });

        zone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            if (zone && zone.classList) {
                zone.classList.remove('border-blue-400', 'bg-blue-50');
            }
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            if (zone && zone.classList) {
                zone.classList.remove('border-blue-400', 'bg-blue-50');
            }

            const files = Array.from(e.dataTransfer.files);
            // Bırakılan dosyaları işle
        });
    });
}

// Livewire olay dinleyicileri
document.addEventListener('livewire:updated', () => {
    initializeFileDragDrop();
});
