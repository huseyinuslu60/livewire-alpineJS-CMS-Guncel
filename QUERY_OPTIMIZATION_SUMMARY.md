# Query Optimization Summary (PR-007)

## Genel Bakış

Liste ekranlarındaki Eloquent sorgularında gereksiz kolon ve relation yüklemeleri azaltılarak performans optimize edildi.

## Optimize Edilen Sorgular

### 1. Posts Listesi (`PostQueryService`)

**Önce:**
```php
Post::query()
    ->with(['author', 'primaryFile', 'categories', 'tags', 'creator', 'updater'])
    // Tüm kolonlar yükleniyordu
```

**Sonra:**
```php
Post::query()
    ->select([
        'post_id', 'title', 'post_type', 'view_count', 'status', 
        'is_mainpage', 'created_at', 'updated_at', 'created_by', 'updated_by', 'content'
    ])
    ->with([
        'creator:id,name,created_at',
        'updater:id,name,updated_at',
        'categories:category_id,name',
        'primaryFile:file_id,file_path,alt_text,is_image',
    ])
```

**Değişiklikler:**
- ✅ `select()` ile sadece gerekli 11 kolon seçiliyor (önceden tüm kolonlar)
- ✅ `author` relation'ı kaldırıldı (view'de kullanılmıyor)
- ✅ `tags` relation'ı kaldırıldı (view'de kullanılmıyor)
- ✅ Relation'larda projection uygulandı (sadece gerekli alanlar)
- ⚠️ `content` kolonu eklendi (gallery için `getPrimaryFileForGallery()` metodunda kullanılıyor)

**Tahmini Performans İyileştirmesi:**
- Kolon sayısı: ~20+ → 11 (%45 azalma)
- Relation sayısı: 6 → 4 (%33 azalma)
- Relation projection ile veri transferi: ~%60-70 azalma

---

### 2. Logs Listesi (`LogService`)

**Önce:**
```php
UserLog::query()
    ->with(['user'])
    // Tüm kolonlar yükleniyordu
```

**Sonra:**
```php
UserLog::query()
    ->select([
        'log_id', 'user_id', 'action', 'description', 
        'model_type', 'model_id', 'ip_address', 'created_at'
    ])
    ->with(['user:id,name,email'])
```

**Değişiklikler:**
- ✅ `select()` ile sadece gerekli 8 kolon seçiliyor
- ✅ User relation'ında projection uygulandı (sadece name ve email)

**Tahmini Performans İyileştirmesi:**
- Kolon sayısı: ~15+ → 8 (%47 azalma)
- User relation veri transferi: ~%80 azalma (sadece id, name, email)

---

### 3. AgencyNews Listesi (`AgencyNewsService`)

**Önce:**
```php
AgencyNews::query()
    // Tüm kolonlar yükleniyordu, relation yok
```

**Sonra:**
```php
AgencyNews::query()
    ->select([
        'record_id', 'title', 'summary', 'agency_id', 
        'category', 'has_image', 'created_at'
    ])
```

**Değişiklikler:**
- ✅ `select()` ile sadece gerekli 7 kolon seçiliyor
- ✅ Relation yok (zaten yoktu)

**Tahmini Performans İyileştirmesi:**
- Kolon sayısı: ~15+ → 7 (%53 azalma)

---

### 4. Lastminutes Listesi (`LastminuteService`)

**Önce:**
```php
Lastminute::query()
    // Tüm kolonlar yükleniyordu, relation yok
```

**Sonra:**
```php
Lastminute::query()
    ->select([
        'lastminute_id', 'title', 'redirect', 'end_at', 
        'status', 'weight', 'created_at'
    ])
```

**Değişiklikler:**
- ✅ `select()` ile sadece gerekli 7 kolon seçiliyor
- ✅ Relation yok (zaten yoktu)

**Tahmini Performans İyileştirmesi:**
- Kolon sayısı: ~12+ → 7 (%42 azalma)

---

## Özet İstatistikler

| Sorgu | Önceki Kolon Sayısı | Yeni Kolon Sayısı | Azalma | Önceki Relation | Yeni Relation | Relation Azalma |
|-------|---------------------|-------------------|--------|-----------------|---------------|-----------------|
| Posts | ~20+ | 11 | %45 | 6 | 4 | %33 |
| Logs | ~15+ | 8 | %47 | 1 (tam) | 1 (projection) | ~%80 veri |
| AgencyNews | ~15+ | 7 | %53 | 0 | 0 | - |
| Lastminutes | ~12+ | 7 | %42 | 0 | 0 | - |

## Genel Performans İyileştirmeleri

1. **Veri Transferi Azalması:**
   - Ortalama %45-50 daha az kolon yükleniyor
   - Relation'larda projection ile %60-80 daha az veri transferi

2. **Bellek Kullanımı:**
   - Daha az bellek kullanımı (özellikle büyük listelerde)
   - Pagination ile birlikte daha verimli

3. **Sorgu Hızı:**
   - Daha az veri transferi = daha hızlı sorgu
   - Özellikle yavaş network bağlantılarında belirgin fark

## Notlar

1. **Posts Content Alanı:**
   - `content` alanı gallery post'ları için `getPrimaryFileForGallery()` metodunda kullanılıyor
   - Bu alan büyük olabilir, ancak mevcut view yapısı nedeniyle gerekli
   - Gelecekte bu metod optimize edilebilir (gallery için ayrı relation)

2. **Backward Compatibility:**
   - Tüm filtre kombinasyonları korundu
   - View'lerde kullanılan tüm alanlar select içinde
   - Business logic değişmedi

3. **Test Edilmesi Gerekenler:**
   - Tüm filtre kombinasyonları (status, type, editor, category)
   - Pagination çalışması
   - View'lerde hata olmaması
   - Sorgu sonuçlarının sayısı değişmemesi

## Sonraki Adımlar (Opsiyonel)

1. Gallery post'ları için `getPrimaryFileForGallery()` metodunu optimize et
2. Dashboard sorgularını kontrol et ve optimize et
3. Index migration'ları ekle (ayrı PR)
4. Query log ile gerçek performans ölçümleri yap

