<?php
// Hub page

// Options

$osemEnd = [
    "clicks" => 20,
    "time" => 300,
    "manual" => TRUE
];

$osemBar = [
    'showBar' => TRUE,
    'showAbort' => TRUE,
    'showBackToHub' => TRUE,
    'showTimer' => TRUE,
    'showClicks' => TRUE,

    'textAbort' => 'End selection task',
    'textBackToHub' => 'Back to hub',
    'textTimerBefore' => '',
    'textTimerAfter' => ' seconds left',
    'textClicksBefore' => '',
    'textClicksAfter' => ' selections left'
];

// DO NOT EDIT ANYTHING BELOW HERE (UNLESS YOU KNOW WHAT YOU'RE DOING)

// Register options

registerVariable('osemEnd');
registerVariable('osemBar');

// Layout

option('layout', 'OSEM');

// Load questions

$osemQuestions = ['CL',
                  'CO',
                  'OS',
                  'MO'];

// Prepare variables

$osemType = 'hub';

if(!isset($osemCounter)) {
    $osemCounter = 1;
    registerVariable('osemCounter');
} else {
    $osemCounter = value('OS01_01') + 1;
}

if(!isset($osemTime)) {
    $osemTime = time();
    put('OS01_03', $osemTime); // Save start time
    registerVariable('osemTime');
}

if(!isset($osemEndTime)) {
    $osemEndTime = $osemTime + $osemEnd['time'];
    registerVariable('osemEndTime');
}

$osemSpoke = 0;

// Set OSEM loop

if(!isset($osemLoop)) {
    $osemLoop = TRUE;
    registerVariable('osemLoop');
    put('OS01_05', 0);
}

// Break OSEM Loop

if($osemEnd["clicks"] < $osemCounter) {
    $osemLoop = FALSE;
}

if($osemEnd['time']) {
    if(value('OS01_05') == 1) {
        $osemLoop = FALSE;
    }
}

if($osemEnd['manual']) {
    if(value('OS01_05') == 1) {
        $osemLoop = FALSE;
    }
}

// Navigate to Spoke/End

if($osemLoop) {
    setNextPage('osemSpokes');
} else {
    put('OS01_04', time()); // Save end time
    goToPage('osemEnd');
}

// Replace JavaScript placeholders

replace('%osemCounter%', $osemCounter);
replace('%osemTime%', $osemTime);
replace('%osemTimeNow%', time());
replace('%osemSpoke%', $osemSpoke);
replace('%osemEndClicks%', $osemEnd['clicks']);
replace('%osemEndTime%', $osemEndTime);
replace('%osemEndManual%', $osemEnd['manual']);

// Prepare questions

if($osemLoop) {
    $displayQuestions = [];
    foreach ($osemQuestions as $qID) {
        $displayQuestions = array_merge($displayQuestions, getQuestions($qID));
    }
    foreach ($displayQuestions as $qID) {
        question($qID);
    }
}

// Display OSEM bar

$osemBarContent = '<div id="osemBar" data-osem-type="%s" %s>%s %s %s %s</div>';

//// Default bar elements

$osemBE = [
    'barStyle' => '',
    'abortButton' => '',
    'backButton' => '',
    'timer' => '',
    'clickCounter' => ''
];

//// Add user input

if (! $osemBar['showBar']) {
    $osemBE['barStyle'] = 'style="display: none"';
}

if ($osemBar['showAbort'] && $osemEnd['manual']) {
    $osemBE['abortButton'] = sprintf(
        '<button class="osemAbort">%s</button>',
        $osemBar['textAbort']);
}

if ($osemBar['showBackToHub'] && $osemType == 'spoke') {
    $osemBE['backButton'] = sprintf(
        '<button class="osemClickable">%s</button>',
        $osemBar['textBackToHub']);
}

if ($osemBar['showTimer']) {
    $osemBE['timer'] = sprintf(
        '| %s<span class="timerDisplay"></span>%s',
        $osemBar['textTimerBefore'],
        $osemBar['textTimerAfter']);
}

if ($osemBar['showClicks']) {
    $osemBE['clickCounter'] = sprintf(
        '| %s<span class="clickDisplay">%d</span>%s',
        $osemBar['textClicksBefore'],
        $osemEnd['clicks'] - $osemCounter + 1,
        $osemBar['textClicksAfter']);
}

//// Create bar string

$osemDisplayBar = sprintf($osemBarContent,
    $osemType,
    $osemBE['barStyle'],
    $osemBE['abortButton'],
    $osemBE['backButton'],
    $osemBE['timer'],
    $osemBE['clickCounter']);

//// Display on page

html($osemDisplayBar);

?>
