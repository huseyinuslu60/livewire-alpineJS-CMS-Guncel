// Bülten Modülü JavaScript - Alpine.js Uyumlu

import { registerModuleInit } from '@/js/livewire-alpine-lifecycle';
import { showNotification } from '@/js/ui/notifications';

// Alpine.js Components - Must be registered in alpine:init
document.addEventListener('alpine:init', () => {
    // Bülten Tablo Bileşeni - Factory pattern
    function newslettersTableData() {
        return {
            showDeleteModal: false,
            deleteNewsletterId: null,
            showSuccess: true,
            showError: true,

            init() {
                this.initializeTableInteractions();
                
                // Livewire event listener - silme onayı için
                this.$wire.on('confirm-delete-newsletter', (data) => {
                    // Livewire 3'te $wire.on ile gelen data direkt olarak dispatch edilen array'dir
                    const eventData = Array.isArray(data) && data.length > 0 ? data[0] : data;
                    const newsletterId = eventData?.newsletterId || eventData?.id;
                    
                    if (newsletterId) {
                        this.confirmDelete(newsletterId);
                    }
                });
            },

            initializeTableInteractions() {
                // Tablo satırı tıklama işleyicileri
                const root = this.$root || document;
                const newsletterTable = root.querySelector('.newsletter-table');
                if (newsletterTable) {
                    const rows = newsletterTable.querySelectorAll('tbody tr');
                    rows.forEach(row => {
                        row.addEventListener('click', function(e) {
                            if (!e.target.closest('.table-actions')) {
                                const editLink = row.querySelector('a[href*="edit"]');
                                if (editLink) {
                                    window.location.href = editLink.href;
                                }
                            }
                        });
                    });
                }
            },

            confirmDelete(newsletterId) {
                this.deleteNewsletterId = newsletterId;
                this.showDeleteModal = true;
            },

            closeDeleteModal() {
                this.showDeleteModal = false;
                this.deleteNewsletterId = null;
            },

            deleteNewsletter() {
                if (this.deleteNewsletterId) {
                    this.$wire.call('deleteNewsletter', this.deleteNewsletterId);
                    this.closeDeleteModal();
                }
            },

            toggleStatus(newsletterId, currentStatus) {
                if (confirm('Newsletter durumunu değiştirmek istediğinizden emin misiniz?')) {
                    this.$wire.call('toggleStatus', newsletterId);
                }
            }
        };
    }

    Alpine.data('newslettersTable', newslettersTableData);

    // Global fonksiyon wrapper - x-data="newslettersTable" ve x-data="newslettersTable()" için uyumluluk
    if (typeof window !== 'undefined' && !window.newslettersTable) {
        window.newslettersTable = function () {
            return newslettersTableData();
        };
    }

    // Bülten Form Bileşeni
    Alpine.data('newsletterForm', () => ({
        showPreview: false,
        showSuccess: true,
        showError: true,
        autoSaveInterval: null,

        init() {
            this.initializeFormFeatures();
            this.initializeDragAndDrop();
        },

        initializeFormFeatures() {
            // Otomatik taslak kaydetme işlevselliği
            this.autoSaveInterval = setInterval(() => {
                const nameInput = document.querySelector('input[name="name"]');
                if (nameInput && nameInput.value.trim() !== '') {
                    this.$wire.call('autoSaveDraft');
                }
            }, 30000); // Her 30 saniyede bir otomatik kaydet
        },

        initializeDragAndDrop() {
            // Sortable.js'in yüklenmesini bekle
            this.waitForSortable().then(() => {
                this.setupDragAndDrop();
            });
        },

        waitForSortable() {
            return new Promise((resolve) => {
                const checkSortable = () => {
                    if (typeof Sortable !== 'undefined') {
                        resolve();
                    } else {
                        setTimeout(checkSortable, 100);
                    }
                };
                checkSortable();
            });
        },

        setupDragAndDrop() {
            // Yazıları sürüklenebilir yap
            const draggableItems = document.querySelectorAll('[draggable="true"]');
            
            draggableItems.forEach(item => {
                item.addEventListener('dragstart', (e) => {
                    let postId = e.target.dataset.postId;
                    if (!postId) {
                        const parentWithPostId = e.target.closest('[data-post-id]');
                        if (parentWithPostId) {
                            postId = parentWithPostId.dataset.postId;
                        }
                    }
                    
                    if (postId) {
                        e.dataTransfer.setData("text", postId);
                        e.dataTransfer.effectAllowed = "move";
                    }
                });
            });

            // Make template droppable
            const dropZone = document.getElementById('newsletter-template');
            if (dropZone) {
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    e.currentTarget.style.borderColor = '#3b82f6';
                    e.currentTarget.style.backgroundColor = '#dbeafe';
                });

                dropZone.addEventListener('dragleave', (e) => {
                    e.currentTarget.style.borderColor = '#d1d5db';
                    e.currentTarget.style.backgroundColor = '#f9fafb';
                });

                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    e.currentTarget.style.borderColor = '#d1d5db';
                    e.currentTarget.style.backgroundColor = '#f9fafb';
                    
                    const postId = e.dataTransfer.getData("text");
                    if (postId) {
                        this.addPostToNewsletter(postId);
                    }
                });
            }

            // Initialize sortable for selected posts
            this.initializeSortable();
        },

        initializeSortable() {
            setTimeout(() => {
                const sortableContainer = document.getElementById('sortable-posts');
                
                if (sortableContainer && typeof Sortable !== 'undefined') {
                    // Destroy existing sortable if it exists
                    if (sortableContainer.sortable) {
                        sortableContainer.sortable.destroy();
                        delete sortableContainer.sortable;
                    }
                    
                    sortableContainer.sortable = new window.Sortable(sortableContainer, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        handle: '.sortable-item',
                        onEnd: (evt) => {
                            const newOrder = Array.from(sortableContainer.children).map(item => {
                                return parseInt(item.dataset.postId);
                            });
                            
                            this.$wire.call('reorderPosts', newOrder);
                        }
                    });
                }
            }, 200);
        },

        addPostToNewsletter(postId) {
            this.$wire.call('addPostToNewsletter', postId);
        },

        removePostFromNewsletter(postId) {
            this.$wire.call('removePostFromNewsletter', postId);
        },

        openPreview() {
            this.showPreview = true;
            // Modal'ı göster
            const modal = document.getElementById('preview-modal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        },

        closePreview() {
            this.showPreview = false;
            // Modal'ı gizle
            const modal = document.getElementById('preview-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        },

        destroy() {
            // Cleanup auto-save interval
            if (this.autoSaveInterval) {
                clearInterval(this.autoSaveInterval);
            }
        }
    }));
}, { once: true });

// Module initialization function
function initNewslettersModule() {
    // Newsletter builder initialization
    const builderContainer = document.querySelector('[data-newsletter-builder]');
    if (!builderContainer) return;
    waitForSortable().then(() => {
        initializeNewsletterBuilder();
    });
}

// Register module with central lifecycle manager
registerModuleInit('newsletters', initNewslettersModule);

// Livewire Olay Dinleyicileri (Livewire 3 compatible)
document.addEventListener('livewire:init', () => {
    Livewire.on('newsletter-created', () => {
        showSuccessMessage('Bülten başarıyla oluşturuldu!');
    });

    Livewire.on('newsletter-updated', () => {
        showSuccessMessage('Bülten başarıyla güncellendi!');
    });

    Livewire.on('newsletter-deleted', () => {
        showSuccessMessage('Bülten başarıyla silindi!');
    });

    Livewire.on('post-added', () => {
        // Livewire güncellemelerinden sonra sürükle-bırak'ı yeniden başlat
        setTimeout(() => {
            const newsletterFormElement = document.querySelector('[x-data="newsletterForm()"]');
            if (newsletterFormElement) {
                const newsletterForm = Alpine.$data(newsletterFormElement);
                if (newsletterForm && newsletterForm.setupDragAndDrop) {
                    newsletterForm.setupDragAndDrop();
                }
            }
            waitForSortable().then(() => {
                initializeNewsletterBuilder();
            });
        }, 300);
    });

    // Bülten silme onayı olayı
    Livewire.on('confirm-delete-newsletter', (event) => {
        const newsletterId = event.detail.id;
        const newsletterName = event.detail.name;
        
        if (confirm(`"${newsletterName}" bültenini silmek istediğinizden emin misiniz?`)) {
            Livewire.dispatch('deleteNewsletter', { id: newsletterId });
        }
    });
});

// Başarı mesajları için yardımcı fonksiyon
// Now uses shared Tailwind toast notification instead of container-based message
function showSuccessMessage(message) {
    showNotification(message, 'success');
}


function removePostFromNewsletter(postId) {
    Livewire.dispatch('removePostFromNewsletter', { postId: postId });
}

// Sortable.js'in mevcut olmasını bekle (Livewire olayları için gerekli)
function waitForSortable() {
    return new Promise((resolve) => {
        const checkSortable = () => {
            if (typeof Sortable !== 'undefined') {
                resolve();
            } else {
                setTimeout(checkSortable, 100);
            }
        };
        checkSortable();
    });
}

// Bülten Oluşturucu Fonksiyonları (Livewire olayları için gerekli)
function initializeNewsletterBuilder() {
    // Çift kayıtları önlemek için mevcut olay dinleyicilerini kaldır
    removeExistingListeners();
    
    // Yazıları sürüklenebilir yap
    const draggableItems = document.querySelectorAll('[draggable="true"]');
    
    draggableItems.forEach(item => {
        // Varsa mevcut dinleyiciyi kaldır
        if (item._dragStartListener) {
            item.removeEventListener('dragstart', item._dragStartListener);
        }
        
        // Yeni dinleyici oluştur
        item._dragStartListener = function(e) {
            let postId = e.target.dataset.postId;
            if (!postId) {
                const parentWithPostId = e.target.closest('[data-post-id]');
                if (parentWithPostId) {
                    postId = parentWithPostId.dataset.postId;
                }
            }
            
            if (postId) {
                e.dataTransfer.setData("text", postId);
                e.dataTransfer.effectAllowed = "move";
            }
        };
        
        item.addEventListener('dragstart', item._dragStartListener);
    });

    // Şablonu bırakılabilir yap
    const dropZone = document.getElementById('newsletter-template');
    if (dropZone) {
        // Mevcut dinleyicileri kaldır
        if (dropZone._dragoverListener) {
            dropZone.removeEventListener('dragover', dropZone._dragoverListener);
        }
        if (dropZone._dragleaveListener) {
            dropZone.removeEventListener('dragleave', dropZone._dragleaveListener);
        }
        if (dropZone._dropListener) {
            dropZone.removeEventListener('drop', dropZone._dropListener);
        }
        
        // Yeni dinleyiciler oluştur
        dropZone._dragoverListener = (e) => {
            e.preventDefault();
            e.currentTarget.style.borderColor = '#3b82f6';
            e.currentTarget.style.backgroundColor = '#dbeafe';
        };
        dropZone._dragleaveListener = (e) => {
            e.currentTarget.style.borderColor = '#d1d5db';
            e.currentTarget.style.backgroundColor = '#f9fafb';
        };
        dropZone._dropListener = (e) => {
            e.preventDefault();
            e.currentTarget.style.borderColor = '#d1d5db';
            e.currentTarget.style.backgroundColor = '#f9fafb';
            
            const data = e.dataTransfer.getData("text");
            if (data) {
                Livewire.dispatch('addPostToNewsletter', { postId: data });
            }
        };
        
        dropZone.addEventListener('dragover', dropZone._dragoverListener);
        dropZone.addEventListener('dragleave', dropZone._dragleaveListener);
        dropZone.addEventListener('drop', dropZone._dropListener);
    }
    
    // Seçili yazılar için sıralanabilir başlat
    initializeSortable();
}

// Mevcut olay dinleyicilerini kaldır
function removeExistingListeners() {
    const draggableItems = document.querySelectorAll('[draggable="true"]');
    draggableItems.forEach(item => {
        if (item._dragStartListener) {
            item.removeEventListener('dragstart', item._dragStartListener);
            delete item._dragStartListener;
        }
    });
    
    const dropZone = document.getElementById('newsletter-template');
    if (dropZone) {
        if (dropZone._dragoverListener) {
            dropZone.removeEventListener('dragover', dropZone._dragoverListener);
            delete dropZone._dragoverListener;
        }
        if (dropZone._dragleaveListener) {
            dropZone.removeEventListener('dragleave', dropZone._dragleaveListener);
            delete dropZone._dragleaveListener;
        }
        if (dropZone._dropListener) {
            dropZone.removeEventListener('drop', dropZone._dropListener);
            delete dropZone._dropListener;
        }
    }
}

// Sıralanabilir işlevselliği başlat
function initializeSortable() {
    setTimeout(() => {
        const sortableContainer = document.getElementById('sortable-posts');
        
        if (sortableContainer && typeof Sortable !== 'undefined') {
            // Varsa mevcut sıralanabilir örneği yok et
            if (sortableContainer.sortable) {
                sortableContainer.sortable.destroy();
                delete sortableContainer.sortable;
            }
            
            try {
                sortableContainer.sortable = new Sortable(sortableContainer, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    handle: '.sortable-item',
                    onEnd: function(evt) {
                        const newOrder = Array.from(sortableContainer.children).map(item => {
                            return parseInt(item.dataset.postId);
                        });
                        
                        try {
                            Livewire.dispatch('reorderPosts', { orderedIds: newOrder });
                        } catch (error) {
                            console.error('Livewire dispatch failed:', error);
                        }
                    }
                });
            } catch (error) {
                console.error('Error initializing Sortable:', error);
            }
        }
    }, 200);
}

// Livewire güncellemelerinden sonra yeniden başlat (Livewire 3 compatible)
document.addEventListener('livewire:updated', function() {
    setTimeout(() => {
        waitForSortable().then(() => {
            initializeNewsletterBuilder();
        });
    }, 300);
});

// Global preview functions (for onclick handlers)
window.openPreview = function() {
    const modal = document.getElementById('preview-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'block';
    }
};

window.closePreview = function() {
    const modal = document.getElementById('preview-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
};