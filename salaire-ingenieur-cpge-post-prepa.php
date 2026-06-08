<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Salaires des diplômés des écoles d'ingénieurs - médiane et quartiles par promotion">

    <?php include "php/favicon.php"; ?>
    <title>Salaires des ingénieurs diplômés</title>
    <?php include "php/style.php"; ?>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
      th, td { font-size: 100%; }
      #zone-tableau { overflow-x: auto; }
      #zone-tableau table { width: 100%; }
      .legende-couleur { display: inline-block; width: 14px; height: 14px; border-radius: 3px; margin-right: 5px; vertical-align: middle; }
      #zone-graphique { position: relative; max-width: 900px; margin: 20px auto; }
    </style>
  </head>
  <body id="hautdepage">

    <?php include "php/menu.php"; ?>

    <nav class="container" style="margin-top:80px;" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php">
            <i class="bi bi-house-door-fill"></i>
          </a>
        </li>
        <li class="breadcrumb-item">Palmarès</li>
        <li class="breadcrumb-item active" aria-current="page">Salaires</li>
      </ol>
    </nav>

    <header class="container">
      <h1 class="h3"><i class="bi bi-cash-stack"></i>&nbsp;&nbsp;&nbsp;Salaires des diplômés des écoles d'ingénieurs<br>
        <small>de 12 à 30 mois après la diplomation</small>
      </h1>
      <br>

      <p>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail" onclick="changerTexte(this)">
          +&nbsp; Voir l'explication
        </button>
      </p>

      <div class="collapse" id="detail" style="margin-left:20px;">
        <p>
          Les données proviennent du dispositif <strong>InserSup</strong> du Ministère de l'Enseignement Supérieur,
          qui mesure l'insertion professionnelle des diplômés de l'enseignement supérieur <small>(source : <a href="https://data.enseignementsup-recherche.gouv.fr/explore/assets/fr-esr-insersup/" target="_blank" rel="noopener">InserSup – data.gouv.fr</a>)</small>.
          <br>
          Les salaires sont exprimés en <strong>salaire mensuel net en équivalent temps plein (ETP)</strong>,
          mesurés à <strong>12, 18, 24 et 30 mois</strong> après l'obtention du diplôme. Les rémunérations proviennent des déclarations sociales des emloyeurs (DSN) et sont donc fiables (ils ne proviennent pas d'une enquête).<br><br>
          Trois indicateurs sont affichés :
        </p>
        <ul>
          <li><strong>1er quartile (Q1)</strong> : 25 % des diplômés gagnent moins que cette valeur.</li>
          <li><strong>Médiane</strong> : la moitié des diplômés gagnent moins, l'autre moitié gagne plus.</li>
          <li><strong>3e quartile (Q3)</strong> : 75 % des diplômés gagnent moins que cette valeur.</li>
        </ul>
        <p>
          Notes : 
          <ul>
            <li>Seules les promotions pour lesquelles au moins une donnée est disponible sont affichées.</li>
            <li>Les écoles intégrées aux université (comme par exemple les Polytech) apparaissent sous le nom de leur université (exemple Université de la Réunion pour ESIROI ou Université de Rennes pour Polytech Rennes).</li>
            <li>Les écoles sont listées par leur nom entier et non par leur sigle (exemple Institut National des Sciences Appliquées de Lyon et pas INSA Lyon).</li> 
          </ul>
        </p>
        <br>
      </div>
    </header>

    <main class="container">

      <!-- Onglets -->
      <ul class="nav nav-tabs mb-4" id="onglets" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-classement-btn" data-bs-toggle="tab" data-bs-target="#tab-classement" type="button" role="tab">
            <i class="bi bi-trophy"></i>&nbsp; Classement
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-ecole-btn" data-bs-toggle="tab" data-bs-target="#tab-ecole" type="button" role="tab">
            <i class="bi bi-building"></i>&nbsp; Par établissement
          </button>
        </li>
      </ul>

      <div class="tab-content">

        <!-- ── Onglet 1 : Par établissement ── -->
        <div class="tab-pane fade" id="tab-ecole" role="tabpanel">

          <div class="row mb-4">
            <div class="col-md-1"></div>
            <div class="col-md-3">
              <label for="select-etablissement" class="form-label fw-bold">
                <i class="bi bi-building"></i>&nbsp; Choisir un établissement
              </label>
            </div>
            <div class="col-md-7">
              <input type="text" id="filtre-etablissement" class="form-control mb-1"
                     placeholder="&#128269; Tapez un nom pour filtrer la liste..."
                     autocomplete="off">
              <select id="select-etablissement" class="form-select">
                <option value="">-- Sélectionner un établissement --</option>
              </select>
              <small class="text-muted">Filtrez par saisie, puis sélectionnez dans la liste.</small>
            </div>
          </div>

          <!-- Légende graphique -->
          <div id="zone-legende" class="mb-2" style="display:none;">
            <small class="text-muted">Salaire mensuel net médian en équivalent temps plein (€) &mdash; une courbe par promotion</small>
          </div>

          <!-- Graphique Chart.js -->
          <div id="zone-graphique" style="display:none;">
            <canvas id="canvas-salaire"></canvas>
          </div>

          <!-- Tableau détaillé -->
          <div id="zone-tableau" class="mt-4" style="display:none;"></div>

          <!-- Spinner de chargement -->
          <div id="zone-chargement" class="text-center my-4" style="display:none;">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Chargement…</span>
            </div>
          </div>

          <!-- Message d'erreur -->
          <div id="zone-erreur" class="alert alert-danger mt-3" style="display:none;"></div>

        </div><!-- /tab-ecole -->

        <!-- ── Onglet 2 : Classement ── -->
        <div class="tab-pane fade show active" id="tab-classement" role="tabpanel">

          <div class="row mb-4">
            <div class="col-md-1"></div>
            <div class="col-md-3">
              <label for="select-promotion" class="form-label fw-bold">Promotion</label>
            </div>
            <div class="col-md-3">
              <select id="select-promotion" class="form-select">
                <option value="">-- Toutes --</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="select-horizon" class="form-label fw-bold">Horizon</label>
            </div>
            <div class="col-md-2">
              <select id="select-horizon" class="form-select">
                <option value="12">12 mois</option>
                <option value="18">18 mois</option>
                <option value="24">24 mois</option>
                <option value="30">30 mois</option>
              </select>
            </div>
          </div>

          <!-- Spinner classement -->
          <div id="zone-chargement-classement" class="text-center my-4" style="display:none;">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Chargement…</span>
            </div>
          </div>

          <!-- Tableau classement -->
          <div id="zone-classement" style="overflow-x:auto;"></div>

          <!-- Message d'erreur classement -->
          <div id="zone-erreur-classement" class="alert alert-danger mt-3" style="display:none;"></div>

        </div><!-- /tab-classement -->

      </div><!-- /tab-content -->

    </main>

    <div style="margin-bottom: 80px;"></div>

    <?php include "php/librairie.php"; ?>

    <script src="js/tableToCSV.js"></script>
    <script>
    /**
     * Toute la logique de la page est encapsulée dans une IIFE (Immediately
     * Invoked Function Expression) pour éviter de polluer le scope global.
     * Les deux seules fonctions exposées globalement sont trierClassement()
     * et changerTexte(), appelées depuis des attributs onclick dans le HTML.
     */
    (function () {
      'use strict';

      // ──────────────────────────────────────────────────────────────────
      // Utilitaires
      // ──────────────────────────────────────────────────────────────────

      /**
       * Arrondit v à l'entier le plus proche.
       * Retourne null si la valeur est invalide ou <= 0, car la BDD InserSup
       * utilise 0 comme sentinelle pour les données non renseignées.
       * Chart.js interprète null comme une valeur absente (→ trou dans la courbe
       * avec spanGaps:false), contrairement à 0 qui serait affiché.
       */
      function round(v) {
        var n = parseFloat(v);
        return (isNaN(n) || n <= 0) ? null : Math.round(n);
      }

      function afficher(selector) { document.querySelector(selector).style.display = ''; }
      function masquer(selector)  { document.querySelector(selector).style.display = 'none'; }

      // ──────────────────────────────────────────────────────────────────
      // Onglet 1 – chargement de la liste déroulante
      //
      // Le PHP retourne un tableau d'objets { label, col, val } :
      //   - label : texte affiché dans le <select>
      //       ex. 'Telecom Paris (Institut Mines-Télécom)'
      //   - col   : 'etablissement' ou 'source' (colonne SQL à utiliser)
      //   - val   : valeur à passer en paramètre à la requête AJAX
      // On stocke col dans data-col de l'<option> pour le retrouver au moment
      // du changement de sélection.
      // ──────────────────────────────────────────────────────────────────

      var _tousEtablissements = []; // copie complète pour le filtre

      function remplirSelect(items) {
        var sel = document.getElementById('select-etablissement');
        sel.innerHTML = '<option value="">-- Sélectionner un établissement --</option>';
        items.forEach(function (item) {
          var opt = document.createElement('option');
          opt.value = item.val;
          opt.textContent = item.label;
          opt.dataset.col = item.col;
          sel.appendChild(opt);
        });
      }

      fetch('php/ajax/load_salaire.php?etablissement=__liste__')
        .then(function (r) {
          return r.json().then(function (payload) {
            if (!r.ok) {
              var message = (payload && payload.error) ? payload.error : ('Erreur HTTP ' + r.status);
              throw new Error(message);
            }
            return payload;
          });
        })
        .then(function (liste) {
          if (!Array.isArray(liste)) {
            throw new Error('Format de réponse invalide pour la liste des établissements.');
          }
          _tousEtablissements = liste;
          remplirSelect(liste);
        })
        .catch(function (err) {
          var sel = document.getElementById('select-etablissement');
          sel.insertAdjacentHTML('afterend',
            '<div class="alert alert-danger mt-2">Impossible de charger la liste des établissements : ' + escHtml(err.message) + '.</div>');
        });

      document.getElementById('filtre-etablissement').addEventListener('input', function () {
        var terme = this.value.toLowerCase();
        var filtres = terme
          ? _tousEtablissements.filter(function (item) {
              return item.label.toLowerCase().indexOf(terme) !== -1;
            })
          : _tousEtablissements;
        remplirSelect(filtres);
        if (filtres.length === 1) {
          var sel = document.getElementById('select-etablissement');
          sel.selectedIndex = 1;
          sel.dispatchEvent(new Event('change'));
        }
      });

      // Référence à l'instance Chart.js active.
      // Conservée pour pouvoir appeler destroy() avant de créer un nouveau
      // graphique (sinon Chart.js empilement les canvas et génère des artefacts).
      var monChart = null;

      // ──────────────────────────────────────────────────────────────────
      // Handler de sélection d'établissement (onglet 1)
      //
      // Lit col depuis data-col de l'<option> sélectionné pour savoir si la
      // requête AJAX doit filtrer sur 'etablissement' ou 'source'.
      // Passe les deux paramètres à l'URL : ?etablissement=VAL&col=COL
      // Le label (texte affiché) est passé à afficherTableau() pour le titre.
      // ──────────────────────────────────────────────────────────────────

      document.getElementById('select-etablissement').addEventListener('change', function () {
        var selectedOpt = this.options[this.selectedIndex];
        var val = selectedOpt.value;
        var col = selectedOpt.dataset.col || 'etablissement';
        var label = selectedOpt.textContent;

        // Nettoyage
        masquer('#zone-legende');
        masquer('#zone-graphique');
        masquer('#zone-tableau');
        masquer('#zone-erreur');
        document.getElementById('zone-tableau').innerHTML = '';
        document.getElementById('zone-erreur').textContent = '';

        if (val === '') return;

        afficher('#zone-chargement');

        fetch('php/ajax/load_salaire.php?etablissement=' + encodeURIComponent(val) + '&col=' + encodeURIComponent(col))
          .then(function (r) {
            if (!r.ok) throw new Error('Erreur HTTP ' + r.status);
            return r.json();
          })
          .then(function (data) {
            masquer('#zone-chargement');

            if (!Array.isArray(data) || data.length === 0) {
              afficher('#zone-erreur');
              document.getElementById('zone-erreur').textContent =
                'Aucune donnée disponible pour cet établissement.';
              return;
            }

            afficherGraphique(data);
            afficherTableau(data, label);
          })
          .catch(function (err) {
            masquer('#zone-chargement');
            afficher('#zone-erreur');
            document.getElementById('zone-erreur').textContent =
              'Erreur lors du chargement des données : ' + err.message;
          });
      });

      // ──────────────────────────────────────────────────────────────────
      // afficherGraphique(data)
      //
      // Construit un graphique Chart.js de type 'line'.
      // data : tableau de lignes SQL, une par promotion.
      // Chaque promotion devient une courbe de 4 points (12/18/24/30 mois)
      // représentant la médiane.
      //
      // spanGaps:false  → la courbe s'interrompt si une valeur est null
      //                   (donnée manquante) plutôt que d'interpoler.
      // fill:false      → pas de remplissage sous la courbe.
      // ──────────────────────────────────────────────────────────────────

      function afficherGraphique(data) {
        var labels = ['12 mois', '18 mois', '24 mois', '30 mois'];

        // Une courbe par promotion, sur les 4 horizons en abscisse
        var datasets = [];
        // Palette de couleurs : cyclée si plus de 10 promotions
        var couleurs = [
          '#4e79a7','#f28e2b','#e15759','#76b7b2',
          '#59a14f','#edc948','#b07aa1','#ff9da7',
          '#9c755f','#bab0ac'
        ];

        data.forEach(function (row, i) {
          var col = couleurs[i % couleurs.length];
          var promo = row.promotion;

          // round() convertit 0 en null pour éviter les courbes qui tombent à 0
          var med = [round(row.med_12), round(row.med_18), round(row.med_24), round(row.med_30)];

          // Médiane uniquement (une courbe par promotion)
          // col + '33' = même couleur avec 20% d'opacité pour backgroundColor
          datasets.push({
            label: promo,
            data: med,
            borderColor: col,
            backgroundColor: col + '33',
            borderWidth: 2,
            tension: 0.3,
            pointRadius: 5,
            fill: false,
            spanGaps: false
          });
        });

        afficher('#zone-legende');
        afficher('#zone-graphique');

        // Destruction de l'instance précédente si elle existe
        // (obligatoire avant de réutiliser le même <canvas>)
        if (monChart) {
          monChart.destroy();
        }

        var ctx = document.getElementById('canvas-salaire').getContext('2d');
        monChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: datasets
          },
          options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
              legend: { position: 'bottom', labels: { boxWidth: 20, font: { size: 12 } } },
              tooltip: {
                callbacks: {
                  label: function (ctx) {
                    return ctx.dataset.label + ' : ' +
                      (ctx.parsed.y !== null ? ctx.parsed.y + ' €' : 'n/d');
                  }
                }
              }
            },
            scales: {
              y: {
                title: { display: true, text: 'Salaire mensuel net ETP (€)' },
                ticks: {
                  callback: function (val) { return val + ' €'; }
                }
              },
              x: {
                title: { display: true, text: 'Délai après obtention du diplôme' }
              }
            }
          }
        });
      }

      // ──────────────────────────────────────────────────────────────────
      // afficherTableau(data, etab)
      //
      // Construit un tableau HTML (sans classes Bootstrap pour conserver le
      // style CSS défini dans concours.css : th { background: DarkSlateBlue }).
      // Structure : une ligne par promotion avec rowspan=3, puis 3 sous-lignes
      // Q1 / Médiane / Q3, chacune avec les 4 horizons en colonnes.
      // Un bouton CSV permet l'export via la fonction tableToCSV() du fichier
      // js/tableToCSV.js.
      // ──────────────────────────────────────────────────────────────────

      function afficherTableau(data, etab) {
        var idT = 'tableau-salaire';
        var html = '<table id="' + idT + '">';
        html += '<caption style="caption-side:top;"><small>';
        html += 'Salaires mensuels nets ETP (€) &ndash; ' + escHtml(etab) + '.';
        html += '<br>Cliquer pour télécharger au format CSV&nbsp;: </small>';
        html += '<button type="button" class="btn btn-secondary btn-sm" ';
        html += 'onclick="tableToCSV(\'#' + idT + '\',\'promotion;indicateur;12 mois;18 mois;24 mois;30 mois\')"><i class="bi bi-download"></i> csv</button>';
        html += '</caption>';
        html += '<thead class="text-center"><tr>';
        html += '<th>Promotion</th>';
        html += '<th>Indicateur</th>';
        html += '<th>12 mois</th>';
        html += '<th>18 mois</th>';
        html += '<th>24 mois</th>';
        html += '<th>30 mois</th>';
        html += '</tr></thead><tbody>';

        data.forEach(function (row) {
          function cel(v) {
            var n = round(v);
            return '<td class="text-center">' + (n !== null ? n + '&nbsp;€' : '<span class="text-muted">n/d</span>') + '</td>';
          }

          // Q1
          html += '<tr>';
          html += '<td rowspan="3" class="align-middle text-center fw-bold">' + escHtml(row.promotion) + '</td>';
          html += '<td>1er quartile (Q1)</td>';
          html += cel(row.q1_12) + cel(row.q1_18) + cel(row.q1_24) + cel(row.q1_30);
          html += '</tr>';
          // Médiane
          html += '<tr>';
          html += '<td><strong>Médiane</strong></td>';
          html += cel(row.med_12) + cel(row.med_18) + cel(row.med_24) + cel(row.med_30);
          html += '</tr>';
          // Q3
          html += '<tr>';
          html += '<td>3e quartile (Q3)</td>';
          html += cel(row.q3_12) + cel(row.q3_18) + cel(row.q3_24) + cel(row.q3_30);
          html += '</tr>';
        });

        html += '</tbody></table>';

        var zone = document.getElementById('zone-tableau');
        zone.innerHTML = html;
        afficher('#zone-tableau');
      }

      // ──────────────────────────────────────────────────────────────────
      // Helpers
      // ──────────────────────────────────────────────────────────────────

      /**
       * Eschappe les caractères HTML spéciaux dans une chaîne.
       * Utilisé pour insérer des valeurs venant de la BDD (noms d'établissements)
       * dans du HTML construit par concaténation, afin d'éviter les injections XSS.
       */
      function escHtml(str) {
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
      }

      // ──────────────────────────────────────────────────────────────────
      // changerTexte(btn)
      // Appelée depuis l'attribut onclick du bouton 'Voir l'explication'.
      // Bascule le libellé du bouton entre '+ Voir' et '− Masquer'.
      // ──────────────────────────────────────────────────────────────────

      function changerTexte(btn) {
        if (btn.textContent.trim().startsWith('+')) {
          btn.textContent = '−  Masquer l\'explication';
        } else {
          btn.textContent = '+  Voir l\'explication';
        }
      }
      window.changerTexte = changerTexte;

      // ──────────────────────────────────────────────────────────────────
      // Correctif responsive Chart.js
      // Par défaut Chart.js réduit le canvas quand la fenêtre rétrécit mais ne
      // le ragrandit pas. monChart.resize() force le recalcul à la bonne taille.
      // ──────────────────────────────────────────────────────────────────

      window.addEventListener('resize', function () {
        if (monChart) monChart.resize();
      });

      // ══════════════════════════════════════════════════════════════════
      // Onglet 2 – Classement
      // ══════════════════════════════════════════════════════════════════

      // Remplissage de la liste des promotions, puis chargement immédiat du
      // classement par défaut (toutes promos, horizon 12 mois)
      fetch('php/ajax/load_salaire.php?etablissement=__promotions__')
        .then(function (r) {
          return r.json().then(function (payload) {
            if (!r.ok) {
              var message = (payload && payload.error) ? payload.error : ('Erreur HTTP ' + r.status);
              throw new Error(message);
            }
            return payload;
          });
        })
        .then(function (liste) {
          if (!Array.isArray(liste)) {
            throw new Error('Format de réponse invalide pour la liste des promotions.');
          }
          var sel = document.getElementById('select-promotion');
          liste.forEach(function (p) {
            var opt = document.createElement('option');
            opt.value = p;
            opt.textContent = p;
            sel.appendChild(opt);
          });
          // Sélectionner par défaut l'année la plus récente (premier élément, tri DESC)
          if (liste.length > 0) {
            sel.value = liste[0];
          }
          // Charger le classement avec l'année la plus récente
          chargerClassement();
        })
        .catch(function (err) {
          document.getElementById('zone-erreur-classement').textContent =
            'Impossible de charger les promotions : ' + err.message;
          document.getElementById('zone-erreur-classement').style.display = '';
          document.getElementById('zone-chargement-classement').style.display = 'none';
        });

      document.getElementById('select-promotion').addEventListener('change', chargerClassement);
      document.getElementById('select-horizon').addEventListener('change', chargerClassement);

      // État de tri courant du classement
      // colonne : 'etab', 'q1', 'med' (défaut), ou 'q3'
      // direction : 'asc' ou 'desc'
      var etatTriClassement = { colonne: 'med', direction: 'desc' };

      /**
       * chargerClassement()
       * Lit les selects promotion et horizon, puis appelle l'endpoint
       * __classement__ pour rafraîchir le tableau.
       */
      function chargerClassement() {
        var promo   = document.getElementById('select-promotion').value;
        var horizon = document.getElementById('select-horizon').value;

        document.getElementById('zone-classement').innerHTML = '';
        document.getElementById('zone-erreur-classement').style.display = 'none';
        document.getElementById('zone-chargement-classement').style.display = '';

        var url = 'php/ajax/load_salaire.php?etablissement=__classement__&horizon=' + encodeURIComponent(horizon);
        if (promo !== '') url += '&promotion=' + encodeURIComponent(promo);

        fetch(url)
          .then(function (r) {
            if (!r.ok) throw new Error('Erreur HTTP ' + r.status);
            return r.json();
          })
          .then(function (data) {
            document.getElementById('zone-chargement-classement').style.display = 'none';
            if (!Array.isArray(data) || data.length === 0) {
              document.getElementById('zone-erreur-classement').textContent = 'Aucune donnée disponible.';
              document.getElementById('zone-erreur-classement').style.display = '';
              return;
            }
            afficherClassement(data, horizon);
          })
          .catch(function (err) {
            document.getElementById('zone-chargement-classement').style.display = 'none';
            document.getElementById('zone-erreur-classement').textContent = 'Erreur : ' + err.message;
            document.getElementById('zone-erreur-classement').style.display = '';
          });
      }

      /**
       * afficherClassement(data, horizon)
       * Construit le tableau HTML du classement.
       * data : tableau d'objets { etablissement, q1, med, q3 } triés par médiane
       *        décroissante (tri initial effectué côté PHP).
       * Les boutons de tri appellent trierClassement() en onclick.
       * btn-primary = colonne de tri actif, btn-secondary = inactif.
       */
      function afficherClassement(data, horizon) {
        etatTriClassement = { colonne: 'med', direction: 'desc' };
        var idT = 'tableau-classement';
        var labelH = horizon + ' mois';

        var html = '<table id="' + idT + '">';
        html += '<caption style="caption-side:top;"><small>';
        html += 'Salaire mensuel net ETP (€) à ' + labelH + '.';
        html += '<br>Cliquer pour télécharger au format CSV&nbsp;: </small>';
        html += '<button type="button" class="btn btn-secondary btn-sm" ';
        html += 'onclick="tableToCSV(\'#' + idT + '\',\'rang;établissement;Q1;médiane;Q3\')"><i class="bi bi-download"></i> csv</button>';
        html += '</caption>';
        html += '<thead class="text-center"><tr>';
        html += '<th>Rang</th>';
        html += '<th>&nbsp;<button id="btn-tri-etab" type="button" class="btn btn-secondary btn-sm" onclick="trierClassement(\'etab\')">&darr;</button>&nbsp; Établissement</th>';
        html += '<th>&nbsp;<button id="btn-tri-q1"   type="button" class="btn btn-secondary btn-sm" onclick="trierClassement(\'q1\')">&darr;</button>&nbsp; Q1</th>';
        html += '<th>&nbsp;<button id="btn-tri-med"  type="button" class="btn btn-primary   btn-sm" onclick="trierClassement(\'med\')">&darr;</button>&nbsp; Médiane</th>';
        html += '<th>&nbsp;<button id="btn-tri-q3"   type="button" class="btn btn-secondary btn-sm" onclick="trierClassement(\'q3\')">&darr;</button>&nbsp; Q3</th>';
        html += '</tr></thead><tbody id="tbody-classement">';

        data.forEach(function (row, i) {
          function cel(v) {
            var n = round(v);
            return '<td class="text-center">' + (n !== null ? n + '&nbsp;€' : '<span class="text-muted">n/d</span>') + '</td>';
          }
          html += '<tr>';
          html += '<td class="text-center">' + (i + 1) + '</td>';
          html += '<td style="padding-left:6px"><strong>' + escHtml(row.etablissement) + '</strong></td>';
          html += cel(row.q1) + cel(row.med) + cel(row.q3);
          html += '</tr>';
        });

        html += '</tbody></table>';
        document.getElementById('zone-classement').innerHTML = html;
      }

      /**
       * trierClassement(colonne)
       * Tri client-side du tableau de classement (sans requête réseau).
       * colonne : 'etab' (alphabétique), 'q1', 'med' ou 'q3' (numérique).
       * Inverse la direction si la même colonne est cliquée deux fois.
       * Après le tri, les cellules de rang sont renumtérotées.
       * Exposée globalement (window.trierClassement) car appelée en onclick.
       */
      function trierClassement(colonne) {
        var tbody = document.getElementById('tbody-classement');
        if (!tbody) return;
        var lignes = Array.from(tbody.querySelectorAll('tr'));

        var direction;
        if (etatTriClassement.colonne === colonne) {
          direction = etatTriClassement.direction === 'asc' ? 'desc' : 'asc';
        } else {
          direction = (colonne === 'etab') ? 'asc' : 'desc';
        }
        etatTriClassement = { colonne: colonne, direction: direction };

        // Mise à jour des boutons
        ['etab','q1','med','q3'].forEach(function (c) {
          var btn = document.getElementById('btn-tri-' + c);
          if (!btn) return;
          btn.textContent = c === colonne ? (direction === 'asc' ? '↑' : '↓') : '↓';
          btn.className = 'btn btn-sm ' + (c === colonne ? 'btn-primary' : 'btn-secondary');
        });

        lignes.sort(function (a, b) {
          if (colonne === 'etab') {
            var cmp = a.cells[1].textContent.trim().localeCompare(b.cells[1].textContent.trim(), 'fr');
            return direction === 'asc' ? cmp : -cmp;
          }
          var idx = { q1: 2, med: 3, q3: 4 }[colonne];
          var vA = parseFloat(a.cells[idx].textContent) || 0;
          var vB = parseFloat(b.cells[idx].textContent) || 0;
          return direction === 'asc' ? (vA - vB) : (vB - vA);
        });

        // Renumérotation du rang
        lignes.forEach(function (tr, i) {
          tr.cells[0].textContent = i + 1;
          tbody.appendChild(tr);
        });
      }
      window.trierClassement = trierClassement;

    }());
    </script>

  </body>
</html>
