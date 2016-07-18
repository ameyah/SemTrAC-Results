/**
 * Created by Ameya on 7/14/2016.
 */

/**
 * Start creation of new row for group by website table
 */

function newWebsiteRow(table, instance, groupIndex) {
    var addRow = "<tr>" +
        "<td rowspan=" + instance.transformed_password.length + ">" + instance.url + "</td>" +
        "<td rowspan=" + instance.transformed_password.length + ">" + instance.website_importance + "</td>" +
        "<td rowspan=" + instance.transformed_password.length + ">" + instance.reset_count +
        "</td>";
    // add passwords for the same website
    for(var i = 0; i < passwordGroupings[groupIndex].length; i++) {
        for(var j = 0; j < passwordGroupings[groupIndex][i].length; j++) {
            var passwordIndex = passwordGroupings[groupIndex][i][j];
            if(i != 0 && j != 0) {
                addRow += "<tr>";
            }
            var auth_status = instance.auth_status[passwordIndex]? "Yes": "No";
            addRow += "<td>" + instance.transformed_username[passwordIndex].replace(/</, "&lt;").replace(/>/, "&gt;") + "</td><td>" +
                instance.transformed_password[passwordIndex].split(/\$\$\d+\$\$/)[0].replace(/</, "&lt;").replace(/>/, "&gt;") + "</td><td>" +
                displayPasswordGrammar(instance.password_segments[passwordIndex]) + "</td>" +
                "<td>" + parseFloat(instance.password_strength).toFixed(2) + "</td><td>" + auth_status + "</td></tr>";
        }

    }
    table.append(addRow);
}

/**
 * End creation of new row for group by website table
 */

/**
 * Start Display Password Grammar for Group by Websites table
 */

function displayPasswordGrammar(passwordSegments) {
    var displayStr = "";
    for(var i = 0; i < passwordSegments.length; i++) {
        displayStr += "(" + passwordSegments[i].grammar + ")";
    }
    return displayStr;
}

/**
 * End Display Password Grammar for Group by Websites table
 */


/**
 * Start creation of new row for group by password table
 */
function newPasswordRow(table, instance) {
    var addRow = "<tr>" +
        "<td rowspan=" + instance.password_count + ">" + instance.transformed_password.split(/\$\$\d+\$\$/)[0].replace(/</, "&lt;").replace(/>/, "&gt;") +
        "</td><td rowspan=" + instance.password_count + ">" + displayPasswordSegments(instance.password_segments) + "</td>" +
        "<td rowspan=" + instance.password_count + ">" + parseFloat(instance.password_strength).toFixed(2) + "</td>";
    // add details for the same password
    for (var i = 0; i < instance.password_count; i++) {
        if (i != 0) {
            addRow += "<tr>";
        }
        addRow += "<td>" + instance.transformed_username[i].replace(/</, "&lt;").replace(/>/, "&gt;") + "</td><td>" +
        instance.url[i] + "</td><td>" + instance.website_importance[i] + "</td><td>" + instance.reset_count[i] + "</td>" +
        "<td>" + instance.auth_status[i] + "</td></tr>";
    }
    table.append(addRow);
}

/**
 * End creation of new row for group by password table
 */

/**
 * Start Display Password Segments for Group by Passwords table
 */

function displayPasswordSegments(passwordSegments) {
    var displayStr = "";
    for(var i = 0; i < passwordSegments.length; i++) {
        displayStr += "(" + passwordSegments[i].segment.replace(/</, "&lt;").replace(/>/, "&gt;") + " - " + passwordSegments[i].grammar;
        if((passwordSegments[i].grammar.indexOf("number") == -1) && passwordSegments[i].grammar.indexOf("special") == -1) {
            displayStr += ", " + passwordSegments[i].capital + ", " + passwordSegments[i].special;
        }
        displayStr += ")<br/>";
    }
    return displayStr;
}

/**
 * End Display Password Segments for Group by Passwords table
 */

/**
 * Start creation of new row for pre-study questionnaire table
 */
function newPreStudyRow(table, instance) {
    var addRow = "<tr>" +
        "<td>" + instance.question + "</td>" +
        "<td>" + get5PointDescription(instance.response) + "</td></tr>";

    table.append(addRow);
}

/**
 * End creation of new row for pre-study questionnaire table
 */


/**
 * Start creation of new row for post-study questionnaire table
 */
function newPostStudyRow(table, instance, website) {
    instance.website_importance = instance.website_importance? "Yes": "No";
    var addRow = "<tr>" +
        "<td rowspan=" + instance.questionnaire.length + ">" + website + "</td>" +
        "<td rowspan=" + instance.questionnaire.length + ">" + instance.website_importance + "</td>" +
        "<td rowspan=" + instance.questionnaire.length + ">" + instance.reset_count + "</td>" +
        "<td>" + instance.questionnaire[0].question + "</td>" +
        "<td>" + get5PointDescription(instance.questionnaire[0].response) + "</td></tr>";

    for(var i = 1; i < instance.questionnaire.length; i++) {
        addRow += "<td>" + instance.questionnaire[i].question + "</td>" +
                "<td>" + get5PointDescription(instance.questionnaire[i].response) + "</td></tr>";
    }

    table.append(addRow);
}

/**
 * End creation of new row for post-study questionnaire table
 */



/**
 * Start creation of new row for show websites table
 */
function newShowWebsiteRow(table, instance) {
    instance.website_important = instance.website_important? "Yes": "No";
    instance.date = instance.date? instance.date: "";
    instance.auth_status = instance.auth_status? "Yes": "No";
    var addRow = "<tr>" +
        "<td>" + instance.website_text + "</td>" +
        "<td>" + instance.website_important + "</td>" +
        "<td>" + instance.avg_importance + "</td>" +
        "<td>" + instance.date + "</td>" +
        "<td>" + instance.total_tries + "</td>" +
        "<td>" + instance.auth_status + "</td></tr>";

    table.append(addRow);
}

/**
 * End creation of new row for show websites table
 */


/**
 * Start creation of new row for show discussion table
 */
function newShowDiscussionRow(table, instance) {
    var addRow = "<tr>" +
        "<td>" + instance.question + "</td>" +
        "<td>" + instance.response + "</td></tr>";

    table.append(addRow);
}

/**
 * End creation of new row for show discussion table
 */


/**
 * Start table render group by password function
 */

function resultGroupByPassword(oldResult) {
    var result = [];

    //modify passwords temporarily to detect unique passwords considering capital and special char information
    if(!passwordsModified) {
        oldResult = modifyPasswordsTemp(oldResult);
        passwordsModified = true;
    }
    var foundFlag = false;
    for (var i = 0; i < oldResult.length; i++) {
        foundFlag = false;
        for (var j = 0; j < result.length; j++) {
            if (result[j].transformed_password == oldResult[i].password_text) {
                // calculate levenshtein distance for oldResult[i][3] and all usernames from result[j]
                var tempDifferences = [];
                for (var k = 0; k < result[j].transformed_username.length; k++) {
                    var distArray = levenshteinenator(result[j].transformed_username[k], oldResult[i].username_text);
                    var dist = distArray[distArray.length - 1][distArray[distArray.length - 1].length - 1];
                    tempDifferences.push(dist);
                }
                var minDist = tempDifferences.reduce(function (a, b, i, arr) {
                    return Math.min(a, b)
                });
                /*var currentIndex = -1;
                 var indexCount = 0;
                 while((currentIndex = tempDifferences.indexOf(minDist, currentIndex + 1)) != -1) {
                 indexCount++;
                 }*/
                var minIndex = tempDifferences.indexOf(minDist);
                // insert new info after minIndex of respective keys
                // add url of this object to corresponding result object
                result[j].url.splice(minIndex, 0, oldResult[i].website_text);
                // add website importance of this object to corresponding result object
                result[j].website_importance.splice(minIndex, 0, parseInt(oldResult[i].website_probability) ? "Yes" : "No");
                // add reset count of this object to corresponding result object
                result[j].reset_count.splice(minIndex, 0, oldResult[i].password_reset_count);
                // add username of this object to corresponding result object
                result[j].transformed_username.splice(minIndex, 0, oldResult[i].username_text);
                // add auth status of this object to corresponding result object
                result[j].auth_status.splice(minIndex, 0, parseInt(oldResult[i].auth_status) ? "Yes" : "No");
                //increment password count
                result[j].password_count++;
                foundFlag = true;
            }
        }
        if (!foundFlag) {
            // create new password object and add it to result array
            var passwordObj = {
                url: [oldResult[i].website_text],
                website_importance: [parseInt(oldResult[i].webite_probability) ? "Yes" : "No"],
                reset_count: [oldResult[i].password_reset_count],
                transformed_username: [oldResult[i].username_text],
                transformed_password: oldResult[i].password_text,
                password_strength: oldResult[i].password_strength,
                password_segments: oldResult[i].password_segments,
                auth_status: [parseInt(oldResult[i].auth_status) ? "Yes" : "No"],
                password_count: 1
            };
            result.push(passwordObj);
        }
    }

    //sort result as per password count
    result = result.sort(function (a, b) {
        return a.password_count - b.password_count;
    });

    // Re-order usernames as per maximum similarity
    for (var i = 0; i < result.length; i++) {
        var distanceGroups = [];
        var similarityIndex = {
            startIndex: 0,
            endIndex: 0
        };
        // sort transformed_usernames alphabetically
        result[i].transformed_username = result[i].transformed_username.sort();
        for (var j = 0; j < result[i].transformed_username.length; j++) {
            var current_username = result[i].transformed_username[j];
            var next_username = result[i].transformed_username[j + 1];
            if (next_username != undefined && current_username != undefined) {
                current_username = result[i].transformed_username[j].split("@")[0];
                next_username = result[i].transformed_username[j + 1].split("@")[0];
                var distArray = levenshteinenator(current_username, next_username);
                var dist = distArray[distArray.length - 1][distArray[distArray.length - 1].length - 1];
                if (dist <= 5) {
                    similarityIndex.endIndex = j + 1;
                } else {
                    distanceGroups.push(similarityIndex);
                    similarityIndex = {
                        startIndex: j + 1,
                        endIndex: j + 1
                    };
                }
            } else {
                similarityIndex.endIndex = j;
                distanceGroups.push(similarityIndex);
            }
        }
        console.log(distanceGroups);
        var tempResultTransformedUsernames = {
            url: [],
            website_importance: [],
            reset_count: [],
            transformed_username: [],
            auth_status: []
        };
        while (distanceGroups.length > 0) {
            var maxSimilarityGroup = {
                startIndex: 0,
                endIndex: 0
            };
            var maxSimilarityGroupIndex;
            for (j = 0; j < distanceGroups.length; j++) {
                var maxSimilarity = maxSimilarityGroup.endIndex - maxSimilarityGroup.startIndex;
                if ((distanceGroups[j].endIndex - distanceGroups[j].startIndex) >= maxSimilarity) {
                    maxSimilarityGroup = distanceGroups[j];
                    maxSimilarityGroupIndex = j;
                }
            }
            distanceGroups.splice(maxSimilarityGroupIndex, 1);
            for (j = maxSimilarityGroup.startIndex; j <= maxSimilarityGroup.endIndex; j++) {
                tempResultTransformedUsernames.url.push(result[i].url[j]);
                tempResultTransformedUsernames.website_importance.push(result[i].website_importance[j]);
                tempResultTransformedUsernames.reset_count.push(result[i].reset_count[j]);
                tempResultTransformedUsernames.transformed_username.push(result[i].transformed_username[j]);
                tempResultTransformedUsernames.auth_status.push(result[i].auth_status[j]);
            }
        }
        result[i].url = tempResultTransformedUsernames.url;
        result[i].website_importance = tempResultTransformedUsernames.website_importance;
        result[i].reset_count = tempResultTransformedUsernames.reset_count;
        result[i].transformed_username = tempResultTransformedUsernames.transformed_username;
        result[i].auth_status = tempResultTransformedUsernames.auth_status;
    }
    return result;
}
/**
 * End table render group by password function
 */


/**
 * Start table render group by website function
 */
function resultGroupByWebsite(oldResult) {
    var result = [];
    var previousWebsite = "";
    for (var i = 0; i < oldResult.length; i++) {
        if (oldResult[i].website_text == previousWebsite) {
            // add username of this object to corresponding result object
            result[result.length - 1].transformed_username.push(oldResult[i].username_text);
            // add password of this object to corresponding result object
            result[result.length - 1].transformed_password.push(oldResult[i].password_text);
            // add password grammar of this object to corresponding result object
            result[result.length - 1].password_segments.push(oldResult[i].password_segments);
            // add auth status of this object to corresponding result object
            result[result.length - 1].auth_status.push(oldResult[i].auth_status);
        } else {
            // create new password object and add it to result array
            var passwordObj = {
                url: oldResult[i].website_text,
                website_importance: parseInt(oldResult[i].website_probability) ? "Yes" : "No",
                reset_count: oldResult[i].password_reset_count,
                transformed_username: [oldResult[i].username_text],
                transformed_password: [oldResult[i].password_text],
                password_strength: [oldResult[i].password_strength],
                password_segments: [oldResult[i].password_segments],
                auth_status: [oldResult[i].auth_status]
            };
            result.push(passwordObj);
            previousWebsite = oldResult[i].website_text;
        }
    }
    //Calculate levenshtein distance and group passwords as per their similarity for each website
    for (i = 0; i < result.length; i++) {
        if (passwordDistance[i] == undefined) {
            passwordDistance[i] = [];
            passwordGroupings[i] = [];
        }
        for (var j = 0; j < result[i].transformed_password.length; j++) {
            if (passwordDistance[i][j] == undefined) {
                passwordDistance[i][j] = [];
            }
            var passwordDistanceAvg = 0;
            for (var k = j + 1; k < result[i].transformed_password.length; k++) {
                var distArray = levenshteinenator(result[i].transformed_password[j], result[i].transformed_password[k]);
                var dist = distArray[distArray.length - 1][distArray[distArray.length - 1].length - 1];
                if (passwordDistance[i][j][k] == undefined) {
                    passwordDistance[i][j][k] = [];
                }
                passwordDistance[i][j][k] = dist;
                passwordDistanceAvg += dist;
            }
            passwordDistanceAvg /= (k - j - 1);
            if (result[i].transformed_password.length == 1) {
                passwordGroupings[i].push([0]);
            } else {
                for (k = j + 1; k < result[i].transformed_password.length; k++) {
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
    return result;
}


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

/**
 * End table render group by website function
 */