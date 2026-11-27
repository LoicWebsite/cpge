// nettoie le texte d'une cellule pour le CSV : les espaces insécables (&nbsp;) et les
// retours à la ligne deviennent des espaces simples, et une valeur contenant le
// séparateur ou un guillemet est entourée de guillemets (RFC 4180)
function csvCellule(texte) {
    let valeur = String(texte).replace(/[\s\u00a0]+/g, ' ').trim();
    if (/[;"]/.test(valeur)) {
        valeur = '"' + valeur.replace(/"/g, '""') + '"';
    }
    return valeur;
}

// transforme la table HTML au format CSV
function tableToCSV(idTableau, enTete) {

    // Variable to store the final csv data
    let csv_data = [];

    // Get each row data
    const tableau = document.querySelector(idTableau);
    let rows = tableau.getElementsByTagName('tr');

    // traitement de l'entête du tableau si pas passé en paramètre
    if (enTete) {
        csv_data.push(enTete);
    } else {
        let head = tableau.querySelector('thead');
        if (head) {
            let cols = head.querySelectorAll('th');
            let csvrow = [];
            for (let col of cols) {
                csvrow.push(csvCellule(col.textContent));
            }
            csv_data.push(csvrow.join(";"));
        }
    }

    // traitement du reste du tableau
    for (let i = 1; i < rows.length; i++) {

        // Get each column data
        let cols = rows[i].querySelectorAll('td');

        // Stores each csv row data
        let csvrow = [];
        for (let j = 0; j < cols.length; j++) {

            // data-csv permet de restreindre l'export à une partie du contenu
            // affiché (ex. une cellule combinant plusieurs informations visuelles)
            csvrow.push(csvCellule(cols[j].hasAttribute('data-csv') ? cols[j].getAttribute('data-csv') : cols[j].textContent));
        }

        // Combine each column value with comma
        csv_data.push(csvrow.join(";"));
    }

    // Combine each row data with new line character
    csv_data = csv_data.join('\n');

    // Call this function to download csv file 
    downloadCSVFile(csv_data, idTableau);

}

// créer le fichier CSV
function downloadCSVFile(csv_data, idTableau) {

    // Create CSV file object and feed
    // our csv_data into it
    // Le BOM UTF-8 (\uFEFF) permet à Excel de reconnaître l'encodage : sans lui, les
    // accents s'affichent mal (« Ã© » au lieu de « é »).
    CSVFile = new Blob(["\uFEFF" + csv_data], {
        type: "text/csv;charset=utf-8"
    });

    // Create to temporary link to initiate
    // download process
    let temp_link = document.createElement('a');

    // Download csv file
    temp_link.download = idTableau.substr(9) + ".csv";
    let url = window.URL.createObjectURL(CSVFile);
    temp_link.href = url;

    // This link should not be displayed
    temp_link.style.display = "none";
    document.body.appendChild(temp_link);

    // Automatically click the link to
    // trigger download
    temp_link.click();
    document.body.removeChild(temp_link);
}
