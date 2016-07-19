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


/*
 * Function to modify passwords temporarily to detect unique passwords considering
 * capital and special character information
 */

function modifyPasswordsTemp(oldResult) {
    var sequenceArr = [];
    for(var i = 0; i < oldResult.length; i++) {
        // First calculate sequence, check if sequence is in array, push it if not in array
        var capitalSpecialCharSeq = "";
        for(var j = 0; j < oldResult[i].password_segments.length; j++) {
            capitalSpecialCharSeq += oldResult[i].password_segments[j].capital;
            capitalSpecialCharSeq += oldResult[i].password_segments[j].special;
        }
        var sequenceIndex = jQuery.inArray(capitalSpecialCharSeq, sequenceArr);
        if(sequenceIndex == -1) {
            sequenceIndex = sequenceArr.push(capitalSpecialCharSeq); // returns length of sequenceArr
            sequenceIndex--; // actual index of recently pushed element
        }
        // Now, modify the original password with corresponding sequence index appended
        oldResult[i].password_text += "$$" + sequenceIndex + "$$";
    }
    return(oldResult);
}


/**
 * Function to group a factor as per its similarity with its peers
 */
function groupElements(result, groupingKey, splitChar) {
    passwordDistance = [];
    passwordGroupings = [];
    for (var i = 0; i < result.length; i++) {
        if (passwordDistance[i] == undefined) {
            passwordDistance[i] = [];
            passwordGroupings[i] = [];
        }
        for (var j = 0; j < result[i][groupingKey].length; j++) {
            if (passwordDistance[i][j] == undefined) {
                passwordDistance[i][j] = [];
            }
            var passwordDistanceAvg = 0;
            for (var k = j + 1; k < result[i][groupingKey].length; k++) {
                if(splitChar != undefined) {
                    var distArray = levenshteinenator(result[i][groupingKey][j].split(splitChar)[0],
                        result[i][groupingKey][k].split(splitChar)[0]);
                } else {
                    var distArray = levenshteinenator(result[i][groupingKey][j], result[i][groupingKey][k]);
                }
                var dist = distArray[distArray.length - 1][distArray[distArray.length - 1].length - 1];
                if (passwordDistance[i][j][k] == undefined) {
                    passwordDistance[i][j][k] = [];
                }
                passwordDistance[i][j][k] = dist;
                passwordDistanceAvg += dist;
            }
            passwordDistanceAvg /= (k - j - 1);
            if (result[i][groupingKey].length == 1) {
                passwordGroupings[i].push([0]);
            } else {
                for (k = j + 1; k < result[i][groupingKey].length; k++) {
                    var currentPasswordDistance = passwordDistance[i][j][k];
                    if (currentPasswordDistance <= passwordDistanceAvg) {
                        var jGroup = getPasswordGroup(passwordGroupings[i], j);
                        var kGroup = getPasswordGroup(passwordGroupings[i], k);
                        if (jGroup == null || kGroup == null) {
                            if (jGroup == null && kGroup == null) {
                                passwordGroupings[i].push([j, k]);
                            } else if (jGroup == null) {
                                //Insert j into kGroup
                                passwordGroupings[i][kGroup].push(j);
                            } else {
                                //Insert k into jGroup
                                passwordGroupings[i][jGroup].push(k);
                            }
                        } else {
                            if (jGroup != kGroup) {
                                var jGroupMinDistance = getMinimumDistance(passwordDistance[i], passwordGroupings[i][jGroup], j);
                                var kGroupMinDistance = getMinimumDistance(passwordDistance[i], passwordGroupings[i][kGroup], k);
                                if (jGroupMinDistance >= currentPasswordDistance || kGroupMinDistance >= currentPasswordDistance) {
                                    if (jGroupMinDistance >= currentPasswordDistance && kGroupMinDistance >= currentPasswordDistance) {
                                        passwordGroupings[i][jGroup] = removeFromPasswordGroup(passwordGroupings[i][jGroup], j);
                                        passwordGroupings[i][kGroup] = removeFromPasswordGroup(passwordGroupings[i][kGroup], k);
                                        passwordGroupings[i].push([j, k]);
                                    } else {
                                        if (jGroupMinDistance <= currentPasswordDistance) {
                                            //Add k to jGroup
                                            passwordGroupings[i][kGroup] = removeFromPasswordGroup(passwordGroupings[i][kGroup], k);
                                            passwordGroupings[i][jGroup] = addToPasswordGroup(passwordGroupings[i][jGroup], k);
                                        } else {
                                            //Add j to kGroup
                                            passwordGroupings[i][jGroup] = removeFromPasswordGroup(passwordGroupings[i][jGroup], j);
                                            passwordGroupings[i][kGroup] = addToPasswordGroup(passwordGroupings[i][kGroup], j);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * Functions to support element similarity groupings function
 */
function getPasswordGroup(passwordGroup, key) {
    for (var i = 0; i < passwordGroup.length; i++) {
        if ($.inArray(key, passwordGroup[i]) > -1) {
            return i;
        }
    }
    return null;
}

function getMinimumDistance(passwordDistance, passwordGroup, key) {
    var minDist = 999;
    for (var i = 0; i < passwordGroup.length; i++) {
        if (key != passwordGroup[i]) {
            if (passwordDistance[key] != undefined) {
                if (passwordDistance[key][passwordGroup[i]] != undefined) {
                    if (passwordDistance[key][passwordGroup[i]] < minDist) {
                        minDist = passwordDistance[key][passwordGroup[i]];
                    }
                } else {
                    if (passwordDistance[passwordGroup[i]] != undefined) {
                        if (passwordDistance[passwordGroup[i]][key] < minDist) {
                            minDist = passwordDistance[passwordGroup[i]][key];
                        }
                    }
                }
            } else if (passwordDistance[passwordGroup[i]] != undefined) {
                if (passwordDistance[passwordGroup[i]][key] < minDist) {
                    minDist = passwordDistance[passwordGroup[i]][key];
                }
            }
        }
    }
    return minDist;
}

function removeFromPasswordGroup(passwordGroup, key) {
    var index = passwordGroup.indexOf(key);
    if (index > -1) {
        passwordGroup.splice(index, 1);
        return passwordGroup;
    }
}

function addToPasswordGroup(passwordGroup, key) {
    passwordGroup.push(key);
    return passwordGroup;
}