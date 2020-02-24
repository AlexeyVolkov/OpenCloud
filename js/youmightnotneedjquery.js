// @link http://youmightnotneedjquery.com/

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


