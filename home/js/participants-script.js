/**
 * Created by Ameya on 7/13/2016.
 */

var baseURI = "http://localhost:81/semtrac-results/api.php";

function getParticipants() {
    var responseSuccess = function(data) {
        if(data.length > 0) {
            var participantsTable = $("#participants-table");
            data.forEach(function(participantData) {
                newParticipantRow(participantsTable, participantData)
            });
        }
    };

    var responseFailure = function() {

    };

    var options = {
        type: "GET",
        url: baseURI + "?getparticipants"
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}

function newParticipantRow(table, data) {
    var addRow = "<tr><td>" + data.participant_id + "</td><td>" + data.total_websites + "</td><td>" + data.websites_logged_in +
        "</td><td>" + data.total_tries + "</td><td>" + data.unique_credentials + "</td><td><div class='btn-group'>" +
        "<a class='btn btn-default' href='participant.html?id=" + data.participant_id+ "'><i class='icon_info'></i></a>" +
        "</div></td></tr>";
    table.append(addRow);
}

$(function() {
    /* Get Participants */
    getParticipants();
});
