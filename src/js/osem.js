/* OSEM
 * This script manages client-side browser interaction for OSEM.
 *
 * Note: Currently, several variables used in this script are defined outside of
 * it, as they have to be passed from SoSci. See "xml/layout.osem.xml".
 *
 */

//// SETUP

// IE8 compatibility

if (!Date.now) {
    Date.now = function() {
        return new Date().getTime();
        };
}

// Define variables

var i;
var spokes = $('.osemSpoke');
var type = $('#osemBar').attr('data-osem-type');
var timeLoad = Date.now();
var timeOffset = Math.floor(timeLoad / 1000) - timeNow
endTime = endTime + timeOffset

// Layout

$('.submitButtons').css('display','none');

// Disable context menu

$(document).contextmenu(function() {
    return false;
});

//// FUNCTIONS

// Save variables to SoSci

function saveToSoSci(value, question) {
    var questionID = $('#' + question)[0];
    questionID.value = value;
}

// Save click

function saveClick(selectionId, spokeId, qPrefix) {
    var timeElapsed = Date.now() - timeLoad;
    var q = qPrefix + selectionId;
    saveToSoSci(spokeId,q + '_01');
    saveToSoSci(timeElapsed,q + '_02');
    saveToSoSci(timeLoad,q + '_03');
}

// Get coordinates of spokes

function getCoords(spokes) {
    for (i = 0; i < spokes.length; i++) {
        var rect = spokes[i].getBoundingClientRect();
        var question;
        if (i < 9) {
            question = 'CO0' + (i + 1);
        } else {
            question = 'CO' + (i + 1);
        }
        saveToSoSci(rect.top, question + '_01');
        saveToSoSci(rect.right, question + '_02');
        saveToSoSci(rect.bottom, question + '_03');
        saveToSoSci(rect.left, question + '_04');
    }
}

// Get cursor position

var cursorPosition = {X:0,Y:0,Time:0}; // Starting values
var cursorInterval = 100; // Every 100 ms
var cursorCutOff = 300; // Maximum number of cursor positions saved

var cursorX = [0]; // Array containing x-positions
var cursorY = [0]; // Array containing y-positions
var cursorTime = [0]; // Array containing time in ms since hub load

// Update X and Y on mousemove

$(document).on('mousemove',function(e) {
    cursorPosition.X = e.pageX;
    cursorPosition.Y = e.pageY;
});

function getCursorPosition(cursorPosition) {

    // Update timestamp

    cursorPosition.Time = cursorPosition.Time + cursorInterval;

    // Check if cursor has moved since last interval

    if (cursorX.slice(-1)[0] != cursorPosition.X || cursorY.slice(-1)[0] != cursorPosition.Y) {
        cursorX.push(cursorPosition.X);
        cursorY.push(cursorPosition.Y);
        cursorTime.push(cursorPosition.Time);
    }

    // End if cutoff is reached

    if (cursorX.length > cursorCutOff) {
        window.clearInterval(updateCursorPosition);
    }
}

if(type === 'hub') {
    var updateCursorPosition = setInterval(function() {
        getCursorPosition(cursorPosition);
    }, cursorInterval);
}

// Get selection id

function getSelectionId(counter) {
    var selectionId;
    if (counter < 10) {
        selectionId = '0' + counter;
    } else {
        selectionId = '' + counter;
    }
    return selectionId;
}

// Save cursor position

function saveCursor(selectionId) {
    var q = 'MO' + selectionId;
    saveToSoSci(cursorX.join(),q + '_01');
    saveToSoSci(cursorY.join(),q + '_02');
    saveToSoSci(cursorTime.join(),q + '_03');
}

//// EVENTS: END CONDITIONS

// End by clicks

if(endClicks < counter) {
    // Update end condition checker
    saveToSoSci(1, 'OS01_05');
    SoSciTools.submitPage();
}

// End by time elapsed & display timer

var displayTimer = setInterval(function() { timerUpdate(endTime); }, 250);

function timerUpdate(endTime) {
    // Calculate time remaining
    var timeRemaining = endTime - Math.floor(Date.now() / 1000);

    // Display time remaining
    $('#osemBar .timerDisplay').text(timeRemaining);

    // Check if time is up
    if(timeRemaining < 0) {
        window.clearInterval(displayTimer);
        var selectionId = getSelectionId(counter);
        var qPrefix;
        if (type === 'spoke') {
            qPrefix = 'DT';
            saveClick(selectionId,osemId,qPrefix);
        } else {
            qPrefix = 'CL';
            saveClick(selectionId,osemId,qPrefix);
        }

        // Update end condition checker
        saveToSoSci(1,'OS01_05');
        SoSciTools.submitPage();
    }
}

// End by manual abort

$('.osemAbort').click(function(){
    var selectionId = getSelectionId(counter);
    var qPrefix;
    // Save dwell time if on spoke
    if (type === 'spoke') {
        qPrefix = 'DT';
        saveClick(selectionId,osemId,qPrefix);
    } else {
        qPrefix = 'CL';
        saveClick(selectionId,osemId,qPrefix);
    }

    // Update end condition checker
    saveToSoSci(1,'OS01_05');

    // Note that user manually aborted
    saveToSoSci(1,'OS03_01');

    // Submit page
    SoSciTools.submitPage();
});

//// EVENTS: SELECTION TASK START

// Get coordinates of spoke links, save Window dimensions

if (counter == 1) {
    //getCoords(spokes);
    //saveToSoSci($(window).width(), 'OS02_01');
    //saveToSoSci($(window).height(), 'OS02_02');
}

//// EVENTS: USER CLICKS

// Save variables on click and go to next page

$('.osemClickable').mouseup(function(){

    // Prepare question
    var selectionId = getSelectionId(counter);
    var qPrefix;

    // Save hub variables
    if (type === 'hub') {
        saveToSoSci(counter, 'OS01_01');
        var selectedSpoke = $(this).closest('.osemSpoke');
        var spokeId = selectedSpoke.attr('data-osem-spoke');
        qPrefix = 'CL';
        saveClick(selectionId, spokeId, qPrefix);
        saveToSoSci(spokeId, 'OS01_02');
        saveCursor(selectionId);
    }

    // Save spoke variables
    if (type === 'spoke') {
        qPrefix = 'DT';
        saveClick(selectionId, osemId, qPrefix);
        saveToSoSci(0, 'OS01_02');
    }

    // Submit page
    SoSciTools.submitPage();
});
