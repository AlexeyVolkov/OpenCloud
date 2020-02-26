/**
 * Set cookie, get cookie functions
 * @link https://plainjs.com/javascript/utilities/set-cookie-get-cookie-and-delete-cookie-5/
 */
function getCookie(name) {
    var v = document.cookie.match('(^|;) ?' + encodeURIComponent(name) + '=([^;]*)(;|$)');
    return v ? decodeURIComponent(v[2]) : null;
}

// function setCookie(name, value, days) {
//     var d = new Date();
//     d.setTime(d.getTime() + 24 * 60 * 60 * 1000 * days);
//     document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + "; path=/; expires=" + d.toGMTString() + ";SameSite=Strict" /*+ ";secure" + ";HttpOnly"*/;
// }

function deleteCookie(name) {
    setCookie(encodeURIComponent(name), '', -1);
}