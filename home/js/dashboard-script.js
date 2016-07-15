/**
 * Created by Ameya on 7/12/2016.
 */


function getStats() {
    var responseSuccess = function(data) {
        if(data.participants != undefined) {
            $("#participants-count").html(data.participants);
        }
        if(data.websites != undefined) {
            $("#websites-count").html(data.websites);
        }
        if(data.credentials!= undefined) {
            $("#credentials-count").html(data.credentials);
        }
    };

    var responseFailure = function() {

    };

    var options = {
        type: "GET",
        url: baseURI + "?getstats"
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}

$(function() {
    /* Get Stats */
    getStats();
});