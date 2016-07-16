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