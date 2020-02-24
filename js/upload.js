ready(function () {
    // Uploading Form Path
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
        console.debug('AJAX Form Submit Framework');
        AJAXSubmit(formUploadElem);
        // return false;
    });
});