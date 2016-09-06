/**
 * Created by Ameya on 7/13/2016.
 */

var passwordDistance = [];
var passwordDistanceAvg = [];
var passwordGroupings = [];

var participant_info;
var participant_info_successful_cred;

var groupByPasswordTableFlag = false;
var groupByWebsiteTableFlag = false;
var passwordsModified = false;


/**
 * Start Ajax Calls for participant data
 */
function getParticipantInfo(participant_id) {
    var responseSuccess = function (data) {
        participant_info = data;
        participant_info_successful_cred = get_successful_cred(participant_info);
        if (data.length > 0) {
            var passwordTable = $("#password-data-group-password");
            var transformed_passwords = resultGroupByPassword(data);
            console.log(transformed_passwords);
            buildTable(passwordTable, transformed_passwords).done(function () {
                groupByPasswordTableFlag = true;
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
                newPreStudyRow(preStudyTable, data[i], true);
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
                newPreStudyRow(preStudyTable, data[i], true);
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

function getWebsites(participant_id) {
    var responseSuccess = function (data) {
        console.log(data);
        if (data.length > 0) {
            var postStudyTable = $("#websites-data");
            for(var i = 0; i < data.length; i++) {
                newShowWebsiteRow(postStudyTable, data[i]);
            }
        }
    };

    var responseFailure = function () {

    };

    var options = {
        type: "GET",
        url: baseURI + "?get-websites=" + participant_id
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}

function getDiscussion(participant_id) {
    var responseSuccess = function (data) {
        if (data.length > 0) {
            var discussionTable = $("#discussion-data");
            for(var i = 0; i < data.length; i++) {
                newShowDiscussionRow(discussionTable, data[i]);
            }
        }
    };

    var responseFailure = function () {

    };

    var options = {
        type: "GET",
        url: baseURI + "?get-discussion=" + participant_id
    };

    __makeAjaxRequest(options, responseSuccess, responseFailure);
}

/**
 * End Ajax Calls for participant data
 */


function get_successful_cred(allCred) {
    var successfulCred = [];
    allCred.forEach(function(cred) {
        if(cred.auth_status) {
            successfulCred.push(cred);
        }
    });
    return successfulCred;
}

function buildTable(passwordTable, data) {
    var d = $.Deferred();
    for (var i = data.length - 1; i >= 0; i--) {
        newPasswordRow(passwordTable, data[i], i);
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
    $("#show-websites-btn").removeClass("active");
    $("#show-discussion-btn").removeClass("active");
}

function hideAllTables() {
    $("#passwords-table-group-password").css("display", "none");
    $("#passwords-table-group-website").css("display", "none");
    $("#pre-study-table").css("display", "none");
    $("#post-study-table").css("display", "none");
    $("#websites-table").css("display", "none");
    $("#discussion-table").css("display", "none");
}

$(function () {
    /* Update Participant ID */
    var getParams = window.location.search.replace("?", "");
    var participant_id = getParams.split("id=")[1];
    var numberPattern = /\d+/g;
    var formatted_participant_id = participant_id.match(numberPattern)[0];
    $("#participant-id").html(formatted_participant_id);

    var authSwitch = document.querySelector("#auth-switch");
    var switchery = new Switchery(authSwitch, {size: 'small', jackColor: '#eeeeee', jackSecondaryColor: '#eeeeee'});

    /* "Auth Successful?" switch event handlers */
    authSwitch.onchange = function() {
        if(authSwitch.checked) {
            var passwordTable = $("#password-data-group-password");
            clearElementData(passwordTable);
            var transformed_passwords = resultGroupByPassword(participant_info_successful_cred);
            buildTable(passwordTable, transformed_passwords).done(function () {
                groupByPasswordTableFlag = false;
            });
        } else {
            var passwordTable = $("#password-data-group-password");
            clearElementData(passwordTable);
            var transformed_passwords = resultGroupByPassword(participant_info);
            buildTable(passwordTable, transformed_passwords).done(function () {
                groupByPasswordTableFlag = true;
            });
        }
    };

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
        // reset auth successful switch
        var authSwitch = document.querySelector('#auth-switch');
        if(authSwitch.checked) {
            authSwitch.click();
        }

        if(groupByPasswordTableFlag) {
            hideAllTables();
            $("#passwords-table-group-password").css("display", "block");
            disableAllButtons();
            $(this).addClass("active");
            return;
        }
        var passwordTable = $("#password-data-group-password");
        clearElementData(passwordTable);
        hideAllTables();
        passwordTable.css("display", "block");
        disableAllButtons();
        $(this).addClass("active");

        var transformed_passwords = resultGroupByPassword(participant_info);
        buildTable(passwordTable, transformed_passwords).done(function () {
            groupByPasswordTableFlag = true;
        });
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

    $("#show-websites-btn").click(function() {
        clearElementData($("#websites-data"));
        getWebsites(formatted_participant_id);
        disableAllButtons();
        hideAllTables();
        $(this).addClass("active");
        $("#websites-table").css("display", "block");
    });

    $("#show-discussion-btn").click(function() {
        clearElementData($("#discussion-data"));
        getDiscussion(formatted_participant_id);
        disableAllButtons();
        hideAllTables();
        $(this).addClass("active");
        $("#discussion-table").css("display", "block");
    });
});
