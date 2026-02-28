// transforme la table HTML au format CSV
function tableToCSV(idTableau, enTete) {

    // Variable to store the final csv data
    let csv_data = [];

    // Get each row data
    const tableau = document.querySelector(idTableau);
    let rows = tableau.getElementsByTagName('tr');

    // traitement de l'entête du tableau si pas passé en paramètre
    if (enTete !== "") {
        csv_data.push(enTete);
    } else {
        let head = tableau.querySelector('thead');
        if (head) {
            let cols = head.querySelectorAll('th');
            let csvrow = [];
            for (let col of cols) {
                csvrow.push(col.textContent.trim());
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

            // Get the text data of each cell
            // of a row and push it to csvrow
            csvrow.push(cols[j].textContent);
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
    CSVFile = new Blob([csv_data], {
        type: "text/csv"
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
