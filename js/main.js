ready(function () {
    startProgress();

    loginHandler();
    refreshTable();
    handleUploadForm();
    // handleAddFolder();
    unblockLogin(loggedin());

    endProgress();
});