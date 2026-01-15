<?php

namespace App\Enums;

enum ProofOfPlayMode: string
{
    case SitesBySlide = 'sitesBySlide';
    case SlidesBySite = 'slidesBySite';


    public function label(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return match ($locale) {
            'es' => match ($this) {
                    self::SitesBySlide => 'Devolver todos los sitios para una diapositiva',
                    self::SlidesBySite => 'Devolver todas las diapositivas reproducidas en un sitio',
                },
            default => match ($this) {
                    self::SitesBySlide => 'Return all sites for a slide',
                    self::SlidesBySite => 'Return all slides played at a site',
                },
        };
    }

    public static function options(?string $locale = null): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [$case->value => $case->label($locale)])->all();
    }
}