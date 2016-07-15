/**
 * Created by Ameya on 7/13/2016.
 */

var passwordDistance = [];
var passwordDistanceAvg = [];
var passwordGroupings = [];

var participant_info;

var groupByPasswordTableFlag = false;
var groupByWebsiteTableFlag = false;


/**
 * Start Ajax Calls for participant data
 */
function getParticipantInfo(participant_id) {
    var responseSuccess = function (data) {
        participant_info = data;
        if (data.length > 0) {
            var passwordTable = $("#password-data-group-password");
            var transformed_passwords = resultGroupByPassword(data);
            console.log(transformed_passwords);
            buildTable(passwordTable, transformed_passwords).done(function () {
                $("#passwords-table-group-password").DataTable();
            });
        }
    };

    var responseFailure = function () {

    };

    var options = {
        type: "GET",
        url: baseURI + "?participant-info=" + participant_id
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}


function getPreStudyQuestionnaire(participant_id) {
    var responseSuccess = function (data) {
        if (data.length > 0) {
            var preStudyTable = $("#pre-study-data");
            for(var i = 0; i < data.length; i++) {
                newPreStudyRow(preStudyTable, data[i]);
            }
        }
    };

    var responseFailure = function () {

    };

    var options = {
        type: "GET",
        url: baseURI + "?get-pre-study=" + participant_id
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}

function getCurrentPracticeQuestionnaire(participant_id) {
    var responseSuccess = function (data) {
        if (data.length > 0) {
            var preStudyTable = $("#pre-study-data");
            for(var i = 0; i < data.length; i++) {
                newPreStudyRow(preStudyTable, data[i]);
            }
        }
    };

    var responseFailure = function () {

    };

    var options = {
        type: "GET",
        url: baseURI + "?get-current-practice=" + participant_id
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}

function getRiskPerceptionQuestionnaire(participant_id) {
    var responseSuccess = function (data) {
        if (data.length > 0) {
            var preStudyTable = $("#pre-study-data");
            for(var i = 0; i < data.length; i++) {
                newPreStudyRow(preStudyTable, data[i]);
            }
        }
    };

    var responseFailure = function () {

    };

    var options = {
        type: "GET",
        url: baseURI + "?get-risk-perception=" + participant_id
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}

function getPostStudyQuestionnaire(participant_id) {
    var responseSuccess = function (data) {
        console.log(data);
        if (data.length > 0) {
            var postStudyTable = $("#post-study-data");
            for(var i = 0; i < data.length; i++) {
                for(var website in data[i]) {
                    newPostStudyRow(postStudyTable, data[i][website], website);
                }
            }
        }
    };

    var responseFailure = function () {

    };

    var options = {
        type: "GET",
        url: baseURI + "?get-post-study=" + participant_id
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}

/**
 * End Ajax Calls for participant data
 */

function buildTable(passwordTable, data) {
    var d = $.Deferred();
    for (var i = data.length - 1; i >= 0; i--) {
        newPasswordRow(passwordTable, data[i]);
    }
    return d.resolve();
}

function clearElementData(element) {
    element.html("");
}

function get5PointDescription(point) {
    if(point == 1) {
        return("Strongly Disagree");
    } else if(point == 2) {
        return("Disagree");
    } else if(point == 3) {
        return("Neutral");
    } else if(point == 4) {
        return("Agree");
    } else if(point == 5) {
        return("Strongly Agree");
    } else {
        return(point);
    }
}

function disableAllButtons() {
    $("#group-website-btn").removeClass("active");
    $("#group-password-btn").removeClass("active");

    $("#pre-study-questionnaire-btn").removeClass("active");
    $("#current-practice-questionnaire-btn").removeClass("active");
    $("#risk-perception-questionnaire-btn").removeClass("active");
    $("#post-study-questionnaire-btn").removeClass("active");
}

function hideAllTables() {
    $("#passwords-table-group-password").css("display", "none");
    $("#passwords-table-group-website").css("display", "none");
    $("#pre-study-table").css("display", "none");
    $("#post-study-table").css("display", "none");
}

$(function () {
    /* Update Participant ID */
    var getParams = window.location.search.replace("?", "");
    var participant_id = getParams.split("id=")[1];
    var numberPattern = /\d+/g;
    var formatted_participant_id = participant_id.match(numberPattern)[0];
    $("#participant-id").html(formatted_participant_id);

    /* Get Participant Info */
    getParticipantInfo(formatted_participant_id);

    $("#group-website-btn").click(function() {
        if(groupByWebsiteTableFlag) {
            hideAllTables();
            $("#passwords-table-group-website").css("display", "block");
            disableAllButtons();
            $(this).addClass("active");
            return;
        }
        clearElementData($("#password-data-group-website"));
        hideAllTables();
        $("#passwords-table-group-website").css("display", "block");
        disableAllButtons();
        $(this).addClass("active");

        var transformed_passwords = resultGroupByWebsite(participant_info);
        for(var i = 0; i < transformed_passwords.length; i++) {
            newWebsiteRow($("#password-data-group-website"), transformed_passwords[i], i);
        }
        groupByWebsiteTableFlag = true;
    });

    $("#group-password-btn").click(function() {
        if(groupByPasswordTableFlag) {
            hideAllTables();
            $("#passwords-table-group-password").css("display", "block");
            disableAllButtons();
            $(this).addClass("active");
            return;
        }
        clearElementData($("#password-data-group-password"));
        hideAllTables();
        $("#passwords-table-group-password").css("display", "block");
        disableAllButtons();
        $(this).addClass("active");

        var transformed_passwords = resultGroupByPassword(participant_info);
        for (var i = transformed_passwords.length - 1; i >= 0; i--) {
            newPasswordRow($("#password-data-group-password"), transformed_passwords[i]);
        }
        groupByPasswordTableFlag = true;
    });

    $("#pre-study-questionnaire-btn").click(function() {
        clearElementData($("#pre-study-data"));
        getPreStudyQuestionnaire(formatted_participant_id);
        disableAllButtons();
        hideAllTables();
        $(this).addClass("active");
        $("#pre-study-table").css("display", "block");
    });

    $("#current-practice-questionnaire-btn").click(function() {
        clearElementData($("#pre-study-data"));
        getCurrentPracticeQuestionnaire(formatted_participant_id);
        disableAllButtons();
        hideAllTables();
        $(this).addClass("active");
        $("#pre-study-table").css("display", "block");
    });

    $("#risk-perception-questionnaire-btn").click(function() {
        clearElementData($("#pre-study-data"));
        getRiskPerceptionQuestionnaire(formatted_participant_id);
        disableAllButtons();
        hideAllTables();
        $(this).addClass("active");
        $("#pre-study-table").css("display", "block");
    });

    $("#post-study-questionnaire-btn").click(function() {
        clearElementData($("#post-study-data"));
        getPostStudyQuestionnaire(formatted_participant_id);
        disableAllButtons();
        hideAllTables();
        $(this).addClass("active");
        $("#post-study-table").css("display", "block");
    });
});
