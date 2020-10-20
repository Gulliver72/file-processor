<?php
    $file = 'testverzeichnis/test.php';
    $needle = '<p class="blubb">';
    $replacement = '<p class="bla">';
    $whichOccurs = '1,3';
    $hash = 'HJGHKHJGXDFHJ258632154'; // md5-hash der Datei

    $res = array();

    $replacement = new replaceCodeParts();
    if ($replacement->setFile($file) === true) {
        if ($hash == $replacement->getFileHash()) {
            $replacement->setNeedle($needle);
            $replacement->setReplacement($replacement);
            $replacement->setWhichOccurs($whichOccurs);

            $res[] = $replacement->patch();

        } else {

            $res[] = array('file' => $file,
                           'needle' => $needle,
                           'replacement' => $replacement,
                           'whichOccurs' => $whichOccurs,
                           'message' => 'Datei nicht original! Die Installation muß manuell abgeschlossen werden.',
                           'error' => true
                           ); // todo auslagern in Klasse zur Verwaltung und Formatierung der Rückmeldung

        }
    }

    $chk_res = array_keys(array $res, true, true); // return array
    if (in_array('error', $chk_res)) $replacement->rollback();
