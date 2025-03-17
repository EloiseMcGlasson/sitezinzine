<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('highlight', [$this, 'highlightText'], ['is_safe' => ['html']])
        ];
    }

    public function highlightText(string $text, ?string $searchTerm): string
    {
        if (!$searchTerm) {
            return $text;
        }

        // Échapper les caractères spéciaux pour éviter les erreurs d'expression régulière
        $escapedSearch = preg_quote($searchTerm, '/');

        // Remplacer toutes les occurrences du mot recherché par une version en surbrillance
        return preg_replace('/(' . $escapedSearch . ')/i', '<span class="highlight">$1</span>', $text);
    }
}
