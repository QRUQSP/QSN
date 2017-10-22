#!/bin/sh
echo INPUT: rtl_fm Device 1>&2
PPM=0
FREQ=162.500M
GAIN=42
until /usr/local/bin/rtl_fm -f ${FREQ} -M fm -s 22050 -E dc -p ${PPM} -g ${GAIN} - | /usr/local/bin/multimon-ng -t raw -a EAS --timestamp --label /dev/stdin; do
    echo Restarting... >&2
    sleep 2
done

# Usage: multimon-ng [file] [file] [file] ...
#   If no [file] is given, input will be read from your default sound
#   hardware. A filename of "-" denotes standard input.
#   -t <type>  : Input file type (any other type than raw requires sox)
#   -a <demod> : Add demodulator
#   -s <demod> : Subtract demodulator
#   -c         : Remove all demodulators (must be added with -a <demod>)
#   -q         : Quiet
#   -v <level> : Level of verbosity (e.g. '-v 3')
#                For POCSAG and MORSE_CW '-v1' prints decoding statistics.
#   -h         : This help
#   -A         : APRS mode (TNC2 text output)
#   -m         : Mute SoX warnings
#   -r         : Call SoX in repeatable mode (e.g. fixed random seed for dithering)
#   -n         : Don't flush stdout, increases performance.
#   -e         : POCSAG: Hide empty messages.
#   -u         : POCSAG: Heuristically prune unlikely decodes.
#   -i         : POCSAG: Inverts the input samples. Try this if decoding fails.
#   -p         : POCSAG: Show partially received messages.
#   -f <mode>  : POCSAG: Disables auto-detection and forces decoding of data as <mode>
#                        (<mode> can be 'numeric', 'alpha' and 'skyper')
#   -b <level> : POCSAG: BCH bit error correction level. Set 0 to disable, default is 2.
#                        Lower levels increase performance and lower false positives.
#   -o         : CW: Set threshold for dit detection (default: 500)
#   -d         : CW: Dit length in ms (default: 50)
#   -g         : CW: Gap length in ms (default: 50)
#   -x         : CW: Disable auto threshold detection
#   -y         : CW: Disable auto timing detection
#   --timestamp: Add a time stamp in front of every printed line
#   --label    : Add a label to the front of every printed line
#    Raw input requires one channel, 16 bit, signed integer (platform-native)
#    samples at the demodulator's input sampling rate, which is
#    usually 22050 Hz. Raw input is assumed and required if piped input is used.
