ready(function () {
    loginHandler();
    if (loggedin()) {
        unblockLogin();
        fillFileTable();
        handleUploadForm();
        handleAddFolder();
    }
});