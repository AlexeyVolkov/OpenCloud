/**
 * A little vanilla framework
 * @link https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/Using_XMLHttpRequest#A_little_vanilla_framework
 */
"use strict";
/*\
|*|
|*|  :: XMLHttpRequest.prototype.sendAsBinary() Polyfill ::
|*|
|*|  https://developer.mozilla.org/en-US/docs/DOM/XMLHttpRequest#sendAsBinary()
\*/

if (!XMLHttpRequest.prototype.sendAsBinary) {
  XMLHttpRequest.prototype.sendAsBinary = function (sData) {
    var nBytes = sData.length,
        ui8Data = new Uint8Array(nBytes);

    for (var nIdx = 0; nIdx < nBytes; nIdx++) {
      ui8Data[nIdx] = sData.charCodeAt(nIdx) & 0xff;
    }
    /* send as ArrayBufferView...: */


    this.send(ui8Data);
    /* ...or as ArrayBuffer (legacy)...: this.send(ui8Data.buffer); */
  };
}
/*\
|*|
|*|  :: AJAX Form Submit Framework ::
|*|
|*|  https://developer.mozilla.org/en-US/docs/DOM/XMLHttpRequest/Using_XMLHttpRequest
|*|
|*|  This framework is released under the GNU Public License, version 3 or later.
|*|  https://www.gnu.org/licenses/gpl-3.0-standalone.html
|*|
|*|  Syntax:
|*|
|*|   AJAXSubmit(HTMLFormElement);
\*/


var AJAXSubmit = function () {
  function ajaxSuccess() {
    // console.log("AJAXSubmit - Success!");
    console.log(this.responseText); // location.reload();

    runRefresh();
    /* you can get the serialized data through the "submittedData" custom property: */

    /* console.log(JSON.stringify(this.submittedData)); */
  }

  function submitData(oData) {
    /* the AJAX request... */
    var oAjaxReq = new XMLHttpRequest();
    oAjaxReq.submittedData = oData;
    oAjaxReq.onload = ajaxSuccess;

    if (oData.technique === 0) {
      /* method is GET */
      oAjaxReq.open("get", oData.receiver.replace(/(?:\?.*)?$/, oData.segments.length > 0 ? "?" + oData.segments.join("&") : ""), true);
      oAjaxReq.send(null);
    } else {
      /* method is POST */
      oAjaxReq.open("post", oData.receiver, true);

      if (oData.technique === 3) {
        /* enctype is multipart/form-data */
        var sBoundary = "---------------------------" + Date.now().toString(16);
        oAjaxReq.setRequestHeader("Content-Type", "multipart\/form-data; boundary=" + sBoundary);
        oAjaxReq.sendAsBinary("--" + sBoundary + "\r\n" + oData.segments.join("--" + sBoundary + "\r\n") + "--" + sBoundary + "--\r\n");
      } else {
        /* enctype is application/x-www-form-urlencoded or text/plain */
        oAjaxReq.setRequestHeader("Content-Type", oData.contentType);
        oAjaxReq.send(oData.segments.join(oData.technique === 2 ? "\r\n" : "&"));
      }
    }
  }

  function processStatus(oData) {
    if (oData.status > 0) {
      return;
    }
    /* the form is now totally serialized! do something before sending it to the server... */

    /* doSomething(oData); */

    /* console.log("AJAXSubmit - The form is now serialized. Submitting..."); */


    submitData(oData);
  }

  function pushSegment(oFREvt) {
    this.owner.segments[this.segmentIdx] += oFREvt.target.result + "\r\n";
    this.owner.status--;
    processStatus(this.owner);
  }

  function plainEscape(sText) {
    /* How should I treat a text/plain form encoding?
       What characters are not allowed? this is what I suppose...: */

    /* "4\3\7 - Einstein said E=mc2" ----> "4\\3\\7\ -\ Einstein\ said\ E\=mc2" */
    return sText.replace(/[\s\=\\]/g, "\\$&");
  }

  function SubmitRequest(oTarget) {
    var nFile,
        sFieldType,
        oField,
        oSegmReq,
        oFile,
        bIsPost = oTarget.method.toLowerCase() === "post";
    /* console.log("AJAXSubmit - Serializing form..."); */

    this.contentType = bIsPost && oTarget.enctype ? oTarget.enctype : "application\/x-www-form-urlencoded";
    this.technique = bIsPost ? this.contentType === "multipart\/form-data" ? 3 : this.contentType === "text\/plain" ? 2 : 1 : 0;
    this.receiver = oTarget.action;
    this.status = 0;
    this.segments = [];
    var fFilter = this.technique === 2 ? plainEscape : escape;

    for (var nItem = 0; nItem < oTarget.elements.length; nItem++) {
      oField = oTarget.elements[nItem];

      if (!oField.hasAttribute("name")) {
        continue;
      }

      sFieldType = oField.nodeName.toUpperCase() === "INPUT" ? oField.getAttribute("type").toUpperCase() : "TEXT";

      if (sFieldType === "FILE" && oField.files.length > 0) {
        if (this.technique === 3) {
          /* enctype is multipart/form-data */
          for (nFile = 0; nFile < oField.files.length; nFile++) {
            oFile = oField.files[nFile];
            oSegmReq = new FileReader();
            /* (custom properties:) */

            oSegmReq.segmentIdx = this.segments.length;
            oSegmReq.owner = this;
            /* (end of custom properties) */

            oSegmReq.onload = pushSegment;
            this.segments.push("Content-Disposition: form-data; name=\"" + oField.name + "\"; filename=\"" + oFile.name + "\"\r\nContent-Type: " + oFile.type + "\r\n\r\n");
            this.status++;
            oSegmReq.readAsBinaryString(oFile);
          }
        } else {
          /* enctype is application/x-www-form-urlencoded or text/plain or
             method is GET: files will not be sent! */
          for (nFile = 0; nFile < oField.files.length; this.segments.push(fFilter(oField.name) + "=" + fFilter(oField.files[nFile++].name)));
        }
      } else if (sFieldType !== "RADIO" && sFieldType !== "CHECKBOX" || oField.checked) {
        /* NOTE: this will submit _all_ submit buttons. Detecting the correct one is non-trivial. */

        /* field type is not FILE or is FILE but is empty */
        this.segments.push(this.technique === 3 ?
        /* enctype is multipart/form-data */
        "Content-Disposition: form-data; name=\"" + oField.name + "\"\r\n\r\n" + oField.value + "\r\n" :
        /* enctype is application/x-www-form-urlencoded or text/plain or method is GET */
        fFilter(oField.name) + "=" + fFilter(oField.value));
      }
    }

    processStatus(this);
  }

  return function (oFormElement) {
    if (!oFormElement.action) {
      return;
    }

    new SubmitRequest(oFormElement);
  };
}(); // @link http://youmightnotneedjquery.com/

/**
 * $(document).ready(function(){});
 */


function ready(fn) {
  if (document.readyState != 'loading') {
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', fn);
  }
}
/**
 * $.ajax({
 *   type: 'POST',
 *   url: '/my/url',
 *   data: data
 * });
 */


function post_ajax(url, data) {
  var request = new XMLHttpRequest();
  request.open('POST', url, true);

  request.onload = function () {
    if (this.status >= 200 && this.status < 400) {
      // Success!
      let downloaded_data = JSON.parse(this.response);
      return downloaded_data;
    } else {
      // We reached our target server, but it returned an error
      return false;
    }
  };

  request.send(data);
}
/**
 * Set cookie, get cookie functions
 * @link https://plainjs.com/javascript/utilities/set-cookie-get-cookie-and-delete-cookie-5/
 */


function getCookie(name) {
  var v = document.cookie.match('(^|;) ?' + encodeURIComponent(name) + '=([^;]*)(;|$)');
  return v ? decodeURIComponent(v[2]) : null;
} // function setCookie(name, value, days) {
//     var d = new Date();
//     d.setTime(d.getTime() + 24 * 60 * 60 * 1000 * days);
//     document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + "; path=/; expires=" + d.toGMTString() + ";SameSite=Strict" /*+ ";secure" + ";HttpOnly"*/;
// }


function deleteCookie(name) {
  setCookie(encodeURIComponent(name), '', -1);
}
/**
 * Functions Run After Refresh
 */


function runRefresh() {
  document.body.style.cursor = 'progress';

  if (loggedin()) {
    fillFileTable();
  }

  document.body.style.cursor = 'default';
}

function runAfterJSReady() {
  handleRenameLinks();
}
/**
 * UPLOAD FORM HANDLER
 */


function handleUploadForm(formQuery = '.upload') {
  // grab reference to form
  const formUploadElem = document.querySelector(formQuery); // if the form exists

  if (!formUploadElem || null == formUploadElem || undefined == formUploadElem) {
    console.debug("Cannot find form: " + formQuery);
    return;
  } // form submit handler


  formUploadElem.addEventListener('submit', e => {
    // on form submission, prevent default
    e.preventDefault();
    document.body.style.cursor = 'progress'; // AJAX Form Submit Framework

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


function fillFileTable(filesListQuery = '.files tbody', user_id = 1, parent_folder_id = 0) {
  if (!loggedin()) {
    console.warn('please, login to see your files');
    return;
  } // grab reference to table


  const tableFilesElem = document.querySelector(filesListQuery); // if the table exists

  if (null == tableFilesElem || undefined == tableFilesElem) {
    console.debug("Cannot find table: " + filesListQuery);
    return;
  } //
  // AJAX get list of Files
  //
  // 1. form request


  let formData = new FormData();
  formData.append("files_list", "true");
  let url = 'php/download.php'; // 2. send request

  var request = new XMLHttpRequest();
  request.open('POST', url, true);

  request.onload = function () {
    if (this.status >= 200 && this.status < 400) {
      // 3. Success!
      let files = JSON.parse(this.response); // if the files exists

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
        if (element['status']) {} // continue;// output just files
        // Create an empty <tr> element and add it to the 1st position of the table:


        let row = tableFilesElem.insertRow(0); // Insert new cells (<td> elements) at the 1st and 2nd position of the "new" <tr> element:

        let cell2 = row.insertCell(0);
        let cell4 = row.insertCell(1); // let cell3 = row.insertCell(2);
        // let cell4 = row.insertCell(3);
        // Add some text to the new cells:
        // cell1.innerHTML = element['upload_date'];

        if ('folder' == element['type']) {
          cell2.innerHTML = '<a href="#folder' + element['id'] + '" class="link link_folder">' + element['real_name'] + '</a>';
        } else {
          cell2.innerHTML = '<a href="php/download.php?download_file__id=' + element['id'] + '" class="link link_download">' + element['real_name'] + '</a>';
        } // cell3.innerHTML = '<a href="#" class="link link_rename" data-file__id="' + element['id'] + '" data-file__name="' + element['real_name'] + '">Rename</a>';


        cell4.innerHTML = '<a href="php/remove.php?remove_file__id=' + element['id'] + '" class="link link_remove">Remove</a>'; // run content-rely code

        runAfterJSReady();
      });
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
 * Rename Links Event
 */


function handleRenameLinks(linksQuery = '.link_rename') {
  // grab reference to form
  const linksElem = document.querySelectorAll(linksQuery); // if the form exists

  if (!linksElem || null == linksElem || undefined == linksElem || 0 == linksElem.length) {
    console.debug("Cannot find links: " + linksQuery);
    return;
  }

  linksElem.forEach(function (linkElem) {
    // rename click handler
    linkElem.addEventListener('click', function (event) {
      event.stopPropagation();
      event.preventDefault();
      console.debug(event);
      let sign = prompt("Rename File", linkElem.dataset.file__name);
      console.log(sign);
      return;
    }, true);
  });
}
/**
 * New Folder Event
 */


function handleAddFolder(addFolderQuery = '.button_add-folder') {
  // grab reference to form
  const buttonElem = document.querySelector(addFolderQuery); // if the form exists

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
    } //
    // AJAX add new folder
    //
    // 1. form request


    let formData = new FormData();
    formData.append("add_folder", "true");
    formData.append("add_folder__name", add_folder__name);
    let url = 'php/upload.php'; // 2. send request

    var request = new XMLHttpRequest();
    request.open('POST', url, true);

    request.onload = function () {
      if (this.status >= 200 && this.status < 400) {
        // 3. Success!
        // console.debug(this.response);
        let answer = JSON.parse(this.response); // if the files exists

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
    return true;
  }

  return false;
}
/**
 * Login Handler
 */


function loginHandler(formLoginQuery = '#login') {
  // grab reference to form
  const formLoginElem = document.querySelector(formLoginQuery); // if the form exists

  if (null == formLoginElem || undefined == formLoginElem) {
    console.debug("Cannot find Login form: " + formLoginQuery);
    return;
  } // form submit handler


  formLoginElem.addEventListener('submit', e => {
    // on form submission, prevent default
    e.preventDefault();
    document.body.style.cursor = 'progress'; // AJAX Form Submit Framework

    console.debug('Login Form sent via AJAX');
    AJAXSubmit(formLoginElem);
  });
}

function unblockLogin() {
  let loggedInBlocksQuery = '.logged_in_user_block'; // grab reference to form

  const loggedInElems = document.querySelectorAll(loggedInBlocksQuery); // if the form exists

  if (!loggedInElems || null == loggedInElems || undefined == loggedInElems) {
    console.debug("Cannot find logged in users` blocks: " + loggedInBlocksQuery);
    return;
  }

  loggedInElems.forEach(loggedInElem => {
    loggedInElem.style.display = 'block';
  });
}

ready(function () {
  loginHandler();

  if (loggedin()) {
    unblockLogin();
    fillFileTable();
    handleUploadForm();
    handleAddFolder();
  }
});
//# sourceMappingURL=script.js.map