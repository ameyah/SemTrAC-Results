/**
 * Created by Ameya on 7/12/2016.
 */

var baseURI = "http://localhost:81/semtrac-results/api.php";

/* AJAX framework */
var __makeAjaxRequest = function(options, successCallback, failureCallback){
    return $.ajax(options)
        .done(successCallback)
        .fail(failureCallback);
};
