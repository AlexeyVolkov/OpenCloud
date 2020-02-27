ready(function () {
    loginHandler();
    fillFileTable();
    handleUploadForm();
    // handleAddFolder();
    if (loggedin()) {
        unblockLogin();
    }
});