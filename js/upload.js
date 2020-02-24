ready(function () {
    // Uploading Form Path
    const formQuery = '.upload';
    const filesListQuery = '.files';

    /**
     * UPLOAD FORM HANDLER
     */
    // grab reference to form
    const formUploadElem = document.querySelector(formQuery);
    // if the form exists
    if (null == formUploadElem || undefined == formUploadElem) {
        console.debug("Cannot find form: " + formQuery);
        return;
    }
    // form submit handler
    formUploadElem.addEventListener('submit', (e) => {
        // on form submission, prevent default
        e.preventDefault();

        // AJAX Form Submit Framework
        console.debug('AJAX Form Submit Framework');
        AJAXSubmit(formUploadElem);
        // return false;
    });

    /**
     * FILES` LIST HANDLER
     */
    // grab reference to table
    const tableFilesElem = document.querySelector(filesListQuery);
    // if the form exists
    if (null == tableFilesElem || undefined == tableFilesElem) {
        console.debug("Cannot find form: " + filesListQuery);
        return;
    }
    // Create an empty <tr> element and add it to the 1st position of the table:
    let row = tableFilesElem.insertRow(0);

    // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
    let cell1 = row.insertCell(0);
    let cell2 = row.insertCell(1);

    // Add some text to the new cells:
    cell1.innerHTML = "NEW CELL1";
    cell2.innerHTML = "NEW CELL2";
});