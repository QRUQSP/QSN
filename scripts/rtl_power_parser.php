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
    $date = $elements[0];
    $time = $elements[1];
    $start = $elements[2];
    $step = $elements[4];

    for($i=6;$i<count($elements);$i++) {
        $freq = $start + (($i-6) * $step);
        print $date . ' ' . $time . ' ' . $freq . ' ' . $elements[$i] . "\n";
    }
    print "\n";
}

?>
