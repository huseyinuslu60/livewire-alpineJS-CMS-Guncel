<?php

namespace App\Services\ValueObjects;

use InvalidArgumentException;

/**
 * Slug Value Object
 *
 * Tüm modüllerde kullanılabilir ortak Slug ValueObject.
 * Business rule: Slug boş olamaz, sadece alfanumerik karakterler ve tire içerebilir.
 */
final class Slug
{
    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    /**
     * String'den Slug oluştur
     *
     * @param  string  $value  Slug değeri
     *
     * @throws InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Slug'ı string olarak döndür
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Slug'ı string olarak döndür (magic method)
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * İki slug'ı karşılaştır
     *
     * @param  Slug  $other  Karşılaştırılacak slug
     */
    public function equals(Slug $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Slug'ın boş olup olmadığını kontrol et
     */
    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    /**
     * Slug validation
     *
     * @param  string  $value  Slug değeri
     *
     * @throws InvalidArgumentException
     */
    private function validate(string $value): void
    {
        if (empty(trim($value))) {
            throw new InvalidArgumentException('Slug boş olamaz');
        }

        // Slug sadece alfanumerik karakterler, tire ve alt çizgi içerebilir
        if (! preg_match('/^[a-z0-9_-]+$/', $value)) {
            throw new InvalidArgumentException('Slug sadece küçük harf, rakam, tire ve alt çizgi içerebilir');
        }

        // Maksimum uzunluk kontrolü
        if (strlen($value) > 255) {
            throw new InvalidArgumentException('Slug maksimum 255 karakter olabilir');
        }
    }
}
