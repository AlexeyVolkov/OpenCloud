/**
 * Functions Run After Refresh
 */
function runRefresh() {
    startProgress();
    if (loggedin()) {
        refreshTable();
    }
    unblockLogin(loggedin());
    endProgress();
}
/**
 * Informing that some proccess started
 * 
 * @returns void
 */
function startProgress() {
    document.body.style.cursor = 'progress';
}
/**
 * Informing that some proccess finished
 *
 * @returns void
 */
function endProgress() {
    document.body.style.cursor = 'default';
}
function runAfterJSReady() {
    startProgress();

    removeLinkConfirm();
    renameLinkPrompt();

    endProgress();
}
/**
 * UPLOAD FORM HANDLER
 */
function handleUploadForm(formQuery = '.upload') {
    // grab reference to form
    const formUploadElem = document.querySelector(formQuery);
    // if the form exists
    if (!formUploadElem || null == formUploadElem || undefined == formUploadElem) {
        console.debug("Cannot find form: " + formQuery);
        return;
    }
    // form submit handler
    formUploadElem.addEventListener('submit', (e) => {
        // on form submission, prevent default
        e.preventDefault();
        document.body.style.cursor = 'progress';
        // AJAX Form Submit Framework
        console.debug('AJAX Form sent');
        if (loggedin()) {
            AJAXSubmit(formUploadElem);
        } else {
            console.warn('please, login to upload files');
        }
    });
}

/**
 * FILES` LIST HANDLER
 */
function refreshTable(filesListQuery = '.files tbody', user_id = 1, parent_folder_id = 0) {
    if (!loggedin()) {
        console.warn('please, login to see your files');
        return;
    }
    // grab reference to table
    const tableFilesElem = document.querySelector(filesListQuery);
    // if the table exists
    if (null == tableFilesElem || undefined == tableFilesElem) {
        console.debug("Cannot find table: " + filesListQuery);
        return;
    }
    // clear the table
    tableFilesElem.innerHTML = '';
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
            let files = JSON.parse(this.response);
            // if the files exists
            if (!files || null == files || undefined == files || 0 == files.length) {
                console.debug("Cannot send request: ");
                console.debug(formData);
                return;
            }
            if (files[0]['error_text'] && 0 < files[0]['error_text'].length) {
                console.debug("Error: " + files[0]['error_text']);
            }
            if (files[0]['status'] && false == files[0]['status']) {
                console.debug('Cannot show files');
                return;
            }
            files.forEach(element => {
                // Create an empty <tr> element and add it to the 1st position of the table:
                let row = tableFilesElem.insertRow(0);
                row.className = 'table__tr';

                // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:
                let cell2 = row.insertCell(0);
                let cell3 = row.insertCell(1);
                let cell4 = row.insertCell(2);
                // let cell4 = row.insertCell(3);
                cell2.className = 'table__td';
                cell3.className = 'table__td';
                cell4.className = 'table__td';

                // Add some text to the new cells:
                // cell1.innerHTML = element['upload_date'];
                if ('folder' == element['type']) {
                    cell2.innerHTML = '<a href="#folder' +
                        element['id'] + '" class="link link_folder">' + element['real_name'] + '</a>';
                } else {
                    cell2.innerHTML = '<a href="php/download.php?download_file__id=' +
                        element['id'] + '" class="link link_download" title="Download ' + element['real_name'] + '">' + element['real_name'] + '</a>';
                }
                cell3.innerHTML = '<a href="#rename__file-' + element['id'] + '" class="link link_rename" data-file__id="' + element['id'] + '" data-file__name="' + element['real_name'] + '">Rename</a>';
                cell4.innerHTML = '<a href="php/remove.php?remove_file__id=' + element['id'] + '" class="link link_remove" data-file_id="' + element['id'] + '" data-real_name="' + element['real_name'] + '" title="Remove ' + element['real_name'] + '">Remove</a>';
            });

            // run content-rely code
            runAfterJSReady();
        } else {
            // We reached our target server, but it returned an error
            return false;
        }
    };
    if (loggedin()) {
        request.send(formData);
    } else {
        console.warn('please, login to see your files');
    }
}

/**
 * Add event to all Rename File links.
 * Sends AJAX for renaming file.
 * 
 * @param string renameLinksQuery
 * 
 * @return void
 */
function renameLinkPrompt(renameLinksQuery = '.link_rename') {
    // grab reference to rename links
    const renameLinkElems = document.querySelectorAll(renameLinksQuery);
    // if the rename links exists
    if (null === renameLinkElems || undefined === renameLinkElems || 0 >= renameLinkElems.length) {
        console.log("Cannot find rename links: " + renameLinksQuery);
        return;
    }
    renameLinkElems.forEach(renameLinkElem => {
        // rename Links handler
        renameLinkElem.addEventListener('click', function (e) {
            // if AJAX - stop redirect
            e.preventDefault();
            var newFileName = window.prompt("New name:", renameLinkElem.dataset.file__name);
            if (!newFileName || newFileName == renameLinkElem.dataset.file__name) {
                return false;
            }
            startProgress();
            //
            // AJAX rename file
            //
            // 1. form request
            let formData = new FormData();
            formData.append("file__rename", 'true');
            formData.append("file__id", renameLinkElem.dataset.file__id);
            formData.append("file__name", newFileName);
            let url = 'php/update.php';
            // 2. send request
            var request = new XMLHttpRequest();
            request.open('POST', url, true);
            request.onload = function () {
                if (this.status >= 200 && this.status < 400) {
                    // 3. Success!
                    runRefresh();
                } else {
                    console.debug('We reached our target server, but it returned an error');
                    return false;
                }
            };
            request.send(formData);
        });
    });
}

/**
 * New Folder Event
 */
function handleAddFolder(addFolderQuery = '.button_add-folder') {
    // grab reference to form
    const buttonElem = document.querySelector(addFolderQuery);
    // if the form exists
    if (!buttonElem || null == buttonElem || undefined == buttonElem) {
        console.debug("Cannot find button: " + addFolderQuery);
        return;
    }

    buttonElem.addEventListener('click', function (event) {
        let add_folder__name = prompt("New Folder Name", 'New Folder');
        document.body.style.cursor = 'progress';
        if (!add_folder__name || null == add_folder__name || undefined == add_folder__name || 0 == add_folder__name.length) {
            console.debug('Empty new name');
            document.body.style.cursor = 'default';
            return;
        }
        //
        // AJAX add new folder
        //
        // 1. form request
        let formData = new FormData();
        formData.append("add_folder", "true");
        formData.append("add_folder__name", add_folder__name);
        let url = 'php/upload.php';
        // 2. send request
        var request = new XMLHttpRequest();
        request.open('POST', url, true);
        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                // 3. Success!
                // console.debug(this.response);
                let answer = JSON.parse(this.response);
                // if the files exists
                if (!answer || null == answer || undefined == answer || 0 == answer.length) {
                    console.debug("Cannot get answer from server with data:");
                    console.debug(formData);
                    return;
                }
                console.debug(answer);
                runRefresh();
            } else {
                console.debug('We reached our target server, but it returned an error');
                return false;
            }
        };
        request.send(formData);
    });

}

/**
 * Logged In?
 */
function loggedin() {
    let loggedinVar = getCookie('user__loggedin');

    if (loggedinVar && null != loggedinVar && undefined != loggedinVar && 1 == loggedinVar) {
        return true
    }
    return false;
}

/**
 * Login Handler
 */
function loginHandler(formLoginQuery = '#login') {
    // grab reference to form
    const formLoginElem = document.querySelector(formLoginQuery);
    // if the form exists
    if (null == formLoginElem || undefined == formLoginElem) {
        console.debug("Cannot find Login form: " + formLoginQuery);
        return;
    }
    // form submit handler
    formLoginElem.addEventListener('submit', (e) => {
        // on form submission, prevent default
        e.preventDefault();
        document.body.style.cursor = 'progress';
        // AJAX Form Submit Framework
        console.debug('Login Form sent via AJAX');
        AJAXSubmit(formLoginElem);
    });
}

function unblockLogin(isPrivate) {
    /**
     * MAKE IT VISIBLE
     */
    let privateBlocksQuery = '.block_private';
    let publicInBlocksQuery = '.block_public';
    // grab reference to form
    let privateElems = document.querySelectorAll(privateBlocksQuery);
    let publicElems = document.querySelectorAll(publicInBlocksQuery);

    if (isPrivate) {// user is logged in
        // show hidden blocks
        if (privateElems || null != privateElems || undefined != privateElems) {
            privateElems.forEach(privateElem => {
                privateElem.classList.remove('block_private');
            });
        }
        // hide public blocks
        if (publicElems || null != publicElems || undefined != publicElems) {
            publicElems.forEach(publicElem => {
                publicElem.classList.remove('block_public');
                publicElem.classList.add('block_hidden');
            });
        }
    } else {// user is not logged in

    }

}

/**
 * Add event to all Remove File links.
 * Confirm removing.
 * 
 * @param string removeLinksQuery 
 */
function removeLinkConfirm(removeLinksQuery = '.link_remove') {
    // grab reference to remove links
    const removeLinkElems = document.querySelectorAll(removeLinksQuery);
    // if the remove links exists
    if (null === removeLinkElems || undefined === removeLinkElems || 0 >= removeLinkElems.length) {
        console.log("Cannot find remove links: " + removeLinksQuery);
        return;
    }
    removeLinkElems.forEach(removeLinkElem => {
        // remove Links handler
        removeLinkElem.addEventListener('click', function (e) {
            var confirmation = window.confirm("Do you really want to remove " + removeLinkElem.dataset.real_name + " ?");
            startProgress();
            if (!confirmation) {
                // stop removing file
                e.preventDefault();
                endProgress();
            }
        });
    });
}