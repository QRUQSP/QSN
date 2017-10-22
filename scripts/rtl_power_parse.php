<?php

$cmd = 'rtl_power -f 145M:148M:5k -g 2 -i 10s -P';
//$cmd = 'rtl_power -f 145.45M:145.60M:5k -g 2 -i 10s -P';
//$cmd = 'rtl_power -f 144.38M:144.42M:5k -g 5 -i 30s -P';
//$cmd = 'rtl_power -f 144M:148.05M:4k -g 20 -i 10s';

$handle = popen($cmd, "r");
$exit = 'no';
$line = '';
while( $exit == 'no' ) {
    $byte = fread($handle, 1);

    if( $byte  == "\n" ) {
        processLine($line);
        $line = '';
    } else {
        $line .= $byte;
    }

    if( feof($handle) ) {
        break;
    }
}

function processLine($line) {
    $elements = explode(',', $line);
    $date = array_shift($elements);
    $time = array_shift($elements);
    $start = array_shift($elements);
    $stop = array_shift($elements);
    $step = array_shift($elements);
    $samples = array_shift($elements);

    $highest = -100000;
    $lowest = 0;
    $sum = 0;
    for($i=0;$i<count($elements);$i++) {
        $sum += $elements[$i];
        if ($elements[$i] > 0) {
            print "JUNK: " . $elements[$i] . "\n";
        }
        if ($highest < $elements[$i]) {
            $highest = $elements[$i];
        }
        if ($lowest > $elements[$i]) {
            $lowest = $elements[$i];
        }
    }
    $average = ($sum / count($elements));
    $threshold = (($highest + $lowest) * .8);

    print "threshold=" . $threshold . "highest=" . $highest . " average=" . $average . " lowest=" . $lowest . "\n";
//    for($i=0;$i<count($elements);$i++) {
//        $freq = $start + (($i-6) * $step);
//        print $date . ' ' . $time . ' ' . $freq . ' ' . $elements[$i] . "\n";
//    }
    print "\n";
}

?>
