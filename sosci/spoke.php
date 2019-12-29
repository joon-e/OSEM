// Spoke page

// DO NOT EDIT ANYTHING BELOW HERE (UNLESS YOU KNOW WHAT YOU'RE DOING)

// Layout

option('layout', 'OSEM');

// Load questions

$osemQuestions = [
                  'DT',
                  'OS'
                  ];

// Prepare variables

$osemType = 'spoke';

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
    setNextPage('osemHub');
} else {
    put('OS01_04', time()); // Save end time
    goToPage('osemEnd');
}

// Prepare variables

if(!isset($osemCounter)) {
    $osemCounter = 0;
    registerVariable('osemCounter');
} else {
    $osemCounter = value('OS01_01');
}

if ($osemCounter < 10) {
    $osemSpoke = value('CL0' . $osemCounter . '_01');
} else {
    $osemSpoke = value('CL' . $osemCounter . '_01');
}

// Replace JavaScript placeholders

replace('%osemCounter%',$osemCounter);
replace('%osemTime%',$osemTime);
replace('%osemTimeNow%',time());
replace('%osemSpoke%',$osemSpoke);
replace('%osemEndClicks%',$osemEnd['clicks']);
replace('%osemEndTime%',$osemEndTime);
replace('%osemEndManual%',$osemEnd['manual']);

// Prepare questions

$displayQuestions = [];
foreach ($osemQuestions as $qID) {
    $displayQuestions = array_merge($displayQuestions,getQuestions($qID));
}
foreach ($displayQuestions as $qID) {
    question($qID);
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
$spokeID = value('OS01_02');
registerVariable('spokeID');
