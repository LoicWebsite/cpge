/*
 * Module partagé de rendu des salaires d'une école.
 * Appelé par la page Salaires et par l'onglet Salaire de la fiche école.
 * Il reçoit les lignes JSON de l'endpoint AJAX, arrondit les valeurs, puis
 * construit le graphique Chart.js et le tableau Q1/médiane/Q3.
 * L'objet public window.SalaireEcole est utilisé après le chargement différé
 * de ce fichier par la fiche détail.
 */
(function (window) {
  'use strict';

  /** Convertit une valeur salariale en entier ou null si elle est absente. */
  function arrondir(v) {
    var n = parseFloat(v);
    return isNaN(n) || n <= 0 ? null : Math.round(n);
  }

  /** Échappe une chaîne avant son insertion dans le HTML du tableau. */
  function echapper(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /**
   * Crée le graphique des médianes par promotion.
   * options.chart permet de détruire le graphique précédent avant réutilisation
   * du même canvas, notamment lors d'un changement d'établissement.
   */
  function afficherGraphique(data, canvas, options) {
    var chart = options.chart || null;
    if (chart) chart.destroy();
    var couleurs = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc948','#b07aa1','#ff9da7','#9c755f','#bab0ac'];
    var datasets = data.map(function (row, i) {
      return {
        label: row.promotion,
        data: [arrondir(row.med_12), arrondir(row.med_18), arrondir(row.med_24), arrondir(row.med_30)],
        borderColor: couleurs[i % couleurs.length],
        backgroundColor: couleurs[i % couleurs.length] + '33',
        borderWidth: 2,
        tension: 0.3,
        pointRadius: 5,
        fill: false,
        spanGaps: false
      };
    });
    return new Chart(canvas.getContext('2d'), {
      type: 'line',
      data: { labels: ['12 mois', '18 mois', '24 mois', '30 mois'], datasets: datasets },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'bottom', labels: { boxWidth: 20, font: { size: 12 } } },
          tooltip: { callbacks: { label: function (ctx) {
            return ctx.dataset.label + ' : ' + (ctx.parsed.y !== null ? ctx.parsed.y + ' €' : 'n/d');
          } } }
        },
        scales: {
          y: { title: { display: true, text: 'Salaire mensuel net ETP (€)' }, ticks: { callback: function (v) { return v + ' €'; } } },
          x: { title: { display: true, text: 'Délai après obtention du diplôme' } }
        }
      }
    });
  }

  /** Construit dans conteneur le tableau des quartiles par promotion. */
  function construireTableau(data, conteneur, options) {
    var id = options.id || 'tableau-salaire-ecole';
    var titre = options.titre || '';
    var html = '<table id="' + id + '"><caption style="caption-side:top;"><small>';
    html += 'Salaires mensuels nets ETP (€) &ndash; ' + echapper(titre) + '.</small></caption>';
    html += '<thead class="text-center"><tr><th>Promotion</th><th>Indicateur</th><th>12 mois</th><th>18 mois</th><th>24 mois</th><th>30 mois</th></tr></thead><tbody>';
    data.forEach(function (row) {
      /** Formate une cellule monétaire et affiche n/d pour une valeur absente. */
      function cellule(v) {
        var n = arrondir(v);
        return '<td class="text-center">' + (n !== null ? n + '&nbsp;€' : '<span class="text-muted">n/d</span>') + '</td>';
      }
      html += '<tr><td rowspan="3" class="align-middle text-center fw-bold">' + echapper(row.promotion) + '</td><td>1er quartile (Q1)</td>' + cellule(row.q1_12) + cellule(row.q1_18) + cellule(row.q1_24) + cellule(row.q1_30) + '</tr>';
      html += '<tr><td><strong>Médiane</strong></td>' + cellule(row.med_12) + cellule(row.med_18) + cellule(row.med_24) + cellule(row.med_30) + '</tr>';
      html += '<tr><td>3e quartile (Q3)</td>' + cellule(row.q3_12) + cellule(row.q3_18) + cellule(row.q3_24) + cellule(row.q3_30) + '</tr>';
    });
    conteneur.innerHTML = html + '</tbody></table>';
  }

  window.SalaireEcole = {
    afficherGraphique: afficherGraphique,
    construireTableau: construireTableau,
    arrondir: arrondir
  };
}(window));
