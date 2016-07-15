/**
 * Created by Ameya on 7/12/2016.
 */

/* AJAX framework */
var __makeAjaxRequest = function(options, successCallback, failureCallback){
    return $.ajax(options)
        .done(successCallback)
        .fail(failureCallback);
};
