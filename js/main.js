ready(function () {
    /**
     * UPLOAD FORM HANDLER
     */
    const formQuery = '.upload';
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
        console.debug('AJAX Form sent');
        AJAXSubmit(formUploadElem);
        // return false;
    });
});
ready(function () {
    /**
     * FILES` LIST HANDLER
     */
    const filesListQuery = '.files';
    // grab reference to table
    const tableFilesElem = document.querySelector(filesListQuery);
    // if the table exists
    if (null == tableFilesElem || undefined == tableFilesElem) {
        console.debug("Cannot find table: " + filesListQuery);
        return;
    }
    //
    // AJAX get list of Files
    //
    // 1. form request
    let formData = new FormData();
    formData.append("files_list", "true");
    let url = 'php/download.php';
    // 2. send request
    var request = new XMLHttpRequest();
    request.open('POST', url, true);
    request.onload = function () {
        if (this.status >= 200 && this.status < 400) {
            // 3. Success!
            // console.debug(this.response);
            let files = JSON.parse(this.response);
            // if the files exists
            if (!files || null == files || undefined == files || 0 == files.length) {
                console.debug("Cannot send request: ");
                console.debug(formData);
                return;
            }
            files.forEach(element => {
                // Create an empty <tr> element and add it to the 1st position of the table:
                let row = tableFilesElem.insertRow(0);

                // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
                let cell1 = row.insertCell(0);
                let cell2 = row.insertCell(1);

                // Add some text to the new cells:
                cell1.innerHTML = element['upload_date'];
                cell2.innerHTML = '<a href="php/download.php?download_file__id=' + element['id'] + '" class="link link_download">' + element['real_name'] + '</a>';
                setDownloadLinksHandler();
                // console.debug(element);
            });
        } else {
            // We reached our target server, but it returned an error
            return false;
        }
    };
    request.send(formData);
});
function setDownloadLinksHandler() {
    // /**
    //  * FILES DOWNLOAD HANDLER
    //  */
    // const downloadLinkQuery = '.link_download';
    // // grab reference to links
    // const downloadLinks = document.querySelectorAll(downloadLinkQuery);
    // // if the links exist
    // if (!downloadLinks || null == downloadLinks || undefined == downloadLinks || 0 == downloadLinks.length) {
    //     console.debug("Cannot find download links: " + downloadLinkQuery);
    //     return;
    // }
    // downloadLinks.forEach(element => {
    //     element.addEventListener('click', function () {
    //         console.debug(this.dataset.file__id);
    //     });
    // });
}