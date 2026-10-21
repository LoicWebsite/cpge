<?php

/**
 * Construit des retours explicites, sans dépendre de l'historique du navigateur.
 * Le JavaScript complète leur contexte ; PHP contrôle la destination et fournit
 * aussi un lien utilisable sans JavaScript ou lors d'une entrée directe.
 */

// Liste commune aux constructeurs et au validateur : chaque page a ses propres critères.
function pagesNavigationRetour(): array {
    return [
        'resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php' => ['reference', 'filiere', 'concours', 'ecole', 'tri', 'direction'],
        'resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php' => ['ecole', 'recherche', 'anneeEcole', 'origine', 'specialite', 'strict', 'rechercher', 'filiere', 'domaine'],
        'statistique-integration-ecole-d-ingenieur-par-specialite-cpge-post-prepa.php' => ['specialite', 'strict', 'rechercher', 'filiere', 'domaine', 'tri', 'direction'],
        'statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php' => ['reference', 'filiere', 'concours', 'ecole', 'rang'],
        'statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php' => ['ecole', 'recherche', 'anneeEcole'],
        'classement-ecole-d-ingenieur.php' => [],
        'statistique-attractivite-ecoles.php' => ['tri', 'direction'],
        'salaire-ingenieur-cpge-post-prepa.php' => ['promotion', 'horizon', 'etablissement', 'col', 'filtre', 'tri', 'direction'],
    ];
}

/** Construit une URL interne en ne gardant que les paramètres utiles à la page. */
function urlPageNavigation(string $page, array $parametres = []): string {
    $pages = pagesNavigationRetour();
    if (!isset($pages[$page])) throw new InvalidArgumentException('Page de navigation inconnue.');
    $permis = array_flip(array_merge($pages[$page], ['retour', 'positionRetour', 'panneaux']));
    $parametres = array_intersect_key($parametres, $permis);
    return $page . ($parametres ? '?' . http_build_query($parametres, '', '&', PHP_QUERY_RFC3986) : '');
}

/** N'accepte que les pages de liste/critères du site, y compris un retour imbriqué. */
function validerUrlNavigation($url, int $profondeur = 0): ?string {
    // Les retours imbriqués sont bornés pour éviter une URL ou une récursion excessive.
    if (!is_string($url) || strlen($url) > 12000 || $profondeur > 4) return null;
    $parties = parse_url($url);
    $pages = pagesNavigationRetour();
    $page = $parties['path'] ?? '';
    if ($parties === false || !isset($pages[$page])
        || array_diff(array_keys($parties), ['path', 'query', 'fragment'])) return null;
    parse_str($parties['query'] ?? '', $parametres);
    $permis = array_merge($pages[$page], ['retour', 'positionRetour', 'panneaux']);
    if (array_diff(array_keys($parametres), $permis)
        || count(array_filter($parametres, 'is_string')) !== count($parametres)) return null;
    if (isset($parametres['retour'])) {
        // Exemple : recherche -> résultats par école -> fiche, puis deux retours successifs.
        $imbrique = validerUrlNavigation($parametres['retour'], $profondeur + 1);
        if ($imbrique === null) return null;
        $parametres['retour'] = $imbrique;
    }
    $fragment = $parties['fragment'] ?? '';
    if ($fragment !== '' && !preg_match('/^[a-zA-Z0-9_-]+$/D', $fragment)) return null;
    return urlPageNavigation($page, $parametres) . ($fragment !== '' ? '#' . $fragment : '');
}

/** Résout le lien et ses libellés : retour explicite, référent local, puis critères disponibles. */
function destinationRetourNavigation(array $parametres, array $serveur = [], string $origineDefaut = ''): array {
    $origines = [
        'filiere' => 'resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php',
        'ecole' => 'resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php',
        'specialite' => 'statistique-integration-ecole-d-ingenieur-par-specialite-cpge-post-prepa.php',
        'classement' => 'classement-ecole-d-ingenieur.php',
        'attractivite' => 'statistique-attractivite-ecoles.php',
        'salaire' => 'salaire-ingenieur-cpge-post-prepa.php',
    ];
    $libelles = [
        'filiere' => "Retour à la liste d'écoles",
        'ecole' => "Retour à la liste d'écoles",
        'specialite' => 'Retour à la recherche par spécialité',
        'classement' => 'Retour au classement',
        'attractivite' => "Retour à l'attractivité",
        'salaire' => 'Retour aux salaires',
    ];
    $origine = is_string($parametres['origine'] ?? null) ? $parametres['origine'] : $origineDefaut;
    if (!isset($origines[$origine])) $origine = $origineDefaut;
    $page = $origines[$origine] ?? 'statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php';
    $url = null;
    if (isset($parametres['retour'])) {
        $url = validerUrlNavigation($parametres['retour']);
        if ($url === null) error_log('Navigation : destination de retour invalide ; utilisation des critères disponibles.');
    } elseif (!empty($serveur['HTTP_REFERER'])) {
        // Compatibilité avec les anciens liens sans retour : même hôte, port et répertoire,
        // puis validation de la page et des paramètres comme pour un retour explicite.
        $reference = parse_url($serveur['HTTP_REFERER']);
        $origineLocale = parse_url('http://' . ($serveur['HTTP_HOST'] ?? ''));
        if ($reference !== false && $origineLocale !== false
            && in_array($reference['scheme'] ?? '', ['http', 'https'], true)
            && ($reference['host'] ?? '') === ($origineLocale['host'] ?? '')
            && ($reference['port'] ?? null) === ($origineLocale['port'] ?? null)
            && dirname($reference['path'] ?? '') === dirname($serveur['SCRIPT_NAME'] ?? '')) {
            $candidate = basename($reference['path'] ?? '')
                . (isset($reference['query']) ? '?' . $reference['query'] : '')
                . (isset($reference['fragment']) ? '#' . $reference['fragment'] : '');
            $url = validerUrlNavigation($candidate);
        }
    }
    if ($url === null) {
        // Une entrée directe ou un retour refusé doit conserver une destination exploitable.
        $criteres = $parametres;
        unset($criteres['retour']);
        if ($origine === 'filiere') unset($criteres['ecole']);
        if ($origine === 'specialite') $criteres['rechercher'] = '1';
        $url = urlPageNavigation($page, $criteres);
    }
    // Le libellé suit la destination validée, qui peut différer de l'origine annoncée.
    $pageRetour = parse_url($url, PHP_URL_PATH);
    $origineRetour = array_search($pageRetour, $origines, true);
    return [
        'url' => $url,
        'libelle' => $origineRetour !== false ? $libelles[$origineRetour] : 'Retour aux critères',
        'rubrique' => $origineRetour !== false ? [
            'filiere' => 'Statistiques', 'ecole' => 'Statistiques', 'specialite' => 'Spécialités',
            'classement' => 'Classement', 'attractivite' => 'Attractivité', 'salaire' => 'Salaires',
        ][$origineRetour] : 'Critères',
    ];
}
