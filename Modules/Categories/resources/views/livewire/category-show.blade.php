<div>
    <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kategori Detayları: {{ $category->name }}</h3>
                    <div class="card-header-right">
                        <a href="{{ route('categories.edit', $category->category_id) }}" class="btn btn-warning">
                            <i class="feather icon-edit"></i> Düzenle
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                            <i class="feather icon-arrow-left"></i> Geri Dön
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Kategori Adı</label>
                                        <p class="form-control-plaintext">{{ $category->name }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Slug</label>
                                        <p class="form-control-plaintext">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Kategori Tipi</label>
                                        <p class="form-control-plaintext">
                                            @if($category->type == 'news')
                                                <span class="badge badge-primary">📰 Haber</span>
                                            @elseif($category->type == 'gallery')
                                                <span class="badge badge-info">🖼️ Galeri</span>
                                            @elseif($category->type == 'video')
                                                <span class="badge badge-warning">🎥 Video</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($category->type) }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Durum</label>
                                        <p class="form-control-plaintext">
                                            @if($category->status == 'active')
                                                <span class="badge badge-success">Aktif</span>
                                            @elseif($category->status == 'inactive')
                                                <span class="badge badge-danger">Pasif</span>
                                            @else
                                                <span class="badge badge-warning">Taslak</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Üst Kategori</label>
                                        <p class="form-control-plaintext">
                                            @if($category->parent)
                                                {{ $category->parent->name }}
                                            @else
                                                <span class="text-muted">Ana Kategori</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Sıralama</label>
                                        <p class="form-control-plaintext">{{ $category->weight }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold text-dark">Menüde Göster</label>
                                <p class="form-control-plaintext">
                                    @if($category->show_in_menu)
                                        <span class="badge badge-success">Evet</span>
                                    @else
                                        <span class="badge badge-secondary">Hayır</span>
                                    @endif
                                </p>
                            </div>

                            @if($category->meta_title || $category->meta_description || $category->meta_keywords)
                                <div class="section-divider" data-title="SEO Bilgileri"></div>
                                
                                @if($category->meta_title)
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Meta Başlık</label>
                                        <p class="form-control-plaintext">{{ $category->meta_title }}</p>
                                    </div>
                                @endif

                                @if($category->meta_description)
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Meta Açıklama</label>
                                        <p class="form-control-plaintext">{{ $category->meta_description }}</p>
                                    </div>
                                @endif

                                @if($category->meta_keywords)
                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Meta Anahtar Kelimeler</label>
                                        <p class="form-control-plaintext">{{ $category->meta_keywords }}</p>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Alt Kategoriler</h5>
                                </div>
                                <div class="card-body">
                                    @if($category->children->count() > 0)
                                        <div class="list-group">
                                            @foreach($category->children as $child)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $child->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            @if($child->status == 'active')
                                                                <span class="badge badge-success badge-sm">Aktif</span>
                                                            @elseif($child->status == 'inactive')
                                                                <span class="badge badge-danger badge-sm">Pasif</span>
                                                            @else
                                                                <span class="badge badge-warning badge-sm">Taslak</span>
                                                            @endif
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('categories.show', $child->category_id) }}" 
                                                           class="btn btn-sm btn-info" title="Görüntüle">
                                                            <i class="feather icon-eye"></i>
                                                        </a>
                                                        <a href="{{ route('categories.edit', $child->category_id) }}" 
                                                           class="btn btn-sm btn-warning" title="Düzenle">
                                                            <i class="feather icon-edit"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <i class="feather icon-folder" style="font-size: 32px; color: #ccc;"></i>
                                            <p class="text-muted mt-2">Alt kategori bulunmuyor</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Categories modülü asset dosyalarını dahil et --}}
    @vite(['Modules/Categories/resources/assets/sass/app.scss', 'Modules/Categories/resources/assets/js/app.js'])
</div>
