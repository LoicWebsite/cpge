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
        // Fiche école : destination de retour de la page de méthode de l'estimation (onglet filière en ancre).
        'detail-resultat-admission-par-ecole.php' => ['ecole', 'origine', 'diplome', 'filiere', 'concours', 'reference', 'an', 'specialite', 'strict'],
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
function validerUrlNavigation($url, int $profondeur = 0, ?string &$motif = null): ?string {
    // Le motif ne contient que des codes fixes et des noms nettoyés : il est destiné au journal.
    $motif = null;
    // Les retours imbriqués sont bornés pour éviter une URL ou une récursion excessive.
    if (!is_string($url)) { $motif = 'valeur non textuelle'; return null; }
    if (strlen($url) > 12000) { $motif = 'URL trop longue'; return null; }
    if ($profondeur > 4) { $motif = 'imbrication excessive'; return null; }
    $parties = parse_url($url);
    $pages = pagesNavigationRetour();
    if ($parties === false) { $motif = 'URL illisible'; return null; }
    $page = $parties['path'] ?? '';
    if (!isset($pages[$page])) { $motif = 'page inconnue : ' . extraitJournalNavigation($page, 120); return null; }
    if (array_diff(array_keys($parties), ['path', 'query', 'fragment'])) { $motif = 'schema ou hote present'; return null; }
    parse_str($parties['query'] ?? '', $parametres);
    $permis = array_merge($pages[$page], ['retour', 'positionRetour', 'panneaux']);
    $inconnus = array_diff(array_map('strval', array_keys($parametres)), $permis);
    if ($inconnus) {
        $motif = 'parametre non autorise : ' . extraitJournalNavigation(implode(',', $inconnus), 120);
        return null;
    }
    if (count(array_filter($parametres, 'is_string')) !== count($parametres)) { $motif = 'parametre non textuel (tableau)'; return null; }
    if (isset($parametres['retour'])) {
        // Exemple : recherche -> résultats par école -> fiche, puis deux retours successifs.
        $imbrique = validerUrlNavigation($parametres['retour'], $profondeur + 1, $motifImbrique);
        if ($imbrique === null) { $motif = 'retour imbrique (' . $motifImbrique . ')'; return null; }
        $parametres['retour'] = $imbrique;
    }
    $fragment = $parties['fragment'] ?? '';
    if ($fragment !== '' && !preg_match('/^[a-zA-Z0-9_-]+$/D', $fragment)) { $motif = 'ancre invalide'; return null; }
    return urlPageNavigation($page, $parametres) . ($fragment !== '' ? '#' . $fragment : '');
}

/**
 * Libellé d'un bouton de retour vers une destination déjà validée par validerUrlNavigation().
 * Le nom de l'école n'est pas repris (il peut être long) ; la valeur retournée n'est pas échappée.
 */
function libelleRetourNavigation(string $urlValidee): string {
    $page = parse_url($urlValidee, PHP_URL_PATH);
    return $page === 'detail-resultat-admission-par-ecole.php' ? 'Retour à la fiche école' : 'Retour aux résultats';
}

/** Rend une valeur contrôlée par le visiteur sûre pour une ligne de journal : ASCII visible, tronquée. */
function extraitJournalNavigation($valeur, int $max = 300): string {
    if (!is_string($valeur)) return '[' . gettype($valeur) . ']';
    $propre = preg_replace('/[^\x20-\x7E]/', '?', $valeur);
    return strlen($propre) > $max ? substr($propre, 0, $max) . '...(' . strlen($valeur) . ' octets)' : $propre;
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
        $url = validerUrlNavigation($parametres['retour'], 0, $motif);
        // Le site ne génère jamais de retour purement numérique : c'est un scanner qui injecte des valeurs
        // aléatoires dans les paramètres. Le repli est identique, mais on évite de polluer le journal.
        $bruitScanner = is_string($parametres['retour']) && ctype_digit($parametres['retour']);
        if ($url === null && !$bruitScanner) {
            // Contexte utile pour distinguer un bug (liens du site) d'un robot ou d'un lien partagé altéré.
            error_log('Navigation : destination de retour invalide (' . $motif . ') ; utilisation des critères disponibles. '
                . 'page=' . extraitJournalNavigation($serveur['REQUEST_URI'] ?? '', 200)
                . ' retour=' . extraitJournalNavigation($parametres['retour'], 400)
                . ' referer=' . extraitJournalNavigation($serveur['HTTP_REFERER'] ?? '', 200)
                . ' ua=' . extraitJournalNavigation($serveur['HTTP_USER_AGENT'] ?? '', 120));
        }
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
