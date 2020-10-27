<?php
    $file = 'testverzeichnis/test.php';
    $needle = '<p class="blubb">';
    $replacement = '<p class="bla">';
    $whichOccurs = '1,3';
    
    $res = array();

    $replacement = new FileProcessor();
    if ($replacement->setFile($file) === true) {
        $replacement->setNeedle($needle);
        $replacement->setReplacement($replacement);
        $replacement->setWhichOccurs($whichOccurs);

        $res[] = $replacement->patch();
    }

    $chk_res = array_keys(array $res, true, true); // return array
    if (in_array('error', $chk_res)) $replacement->rollback();
