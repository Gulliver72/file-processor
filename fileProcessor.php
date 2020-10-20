<?php
namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\ModuleInstaller;

class fileProcessor
    {
    
        private $file; // Filename with directory
        private $content; // Filecontent
        private $partsOfContent; // Array von Teilstücken des Contents wenn nicht das erste Vorkommen ersetzt werden soll
        private $needleArray;
        private $needle;
        private $replacement;
        private $whichOccurs; // Array mit Vorkommen, die bearbeitet werden sollen // leer wenn alle Vorkommen bearbeitet werden sollen
        
        function __construct() {
        
            $this->file = '';
            $this->content = '';
            $this->partsOfContent = array();
            $this->needle = '';
            $this->replacement = '';
            $this->whichOccurs = array();
        }
        
        public function patch() {
        
            // Original File sichern
            $src = 'ModifiedModuleLoader/meinmodul/backup/old/' . $this->file;

            $moduleInstaller = new ModuleInstaller();
            $moduleInstaller->installFile($src, $this->file, false);
            
            // content bearbeiten
            $this->content = FileHelper::readFile($this->file);
            $this->checkIfNeedleExist();
            $this->breakContent(); // Logik für $this->breakContent() muß noch erstellt werden
            
            $count = 0;
            foreach ($this->whichOccurs as $part) {
                $this->replaceContent($this->partsOfContent[$count], $count);
                
                $count++;
            }
            
            $content = $this->composeContent();
            
            $src = 'ModifiedModuleLoader/meinmodul/backup/new/' . $this->file;
            
            $datei_handle = fopen($src, "w+");
            if (!fputs($datei_handle, $content)){
                $this->rollback();
                exit;
            }
            fclose($datei_handle);
             
            // bearbeitetes File sichern
            unlink($this->file); // Original-File löschen
            $moduleInstaller->installFile($this->file, $src, false);

        }
           
        public function composeContent() {
        
            if (empty($this->parts)) {
            
                return $this->content;
            }
            
            return implode('', $this->parts);
        }
        
        private function breakContent() {
        
        }
        
        private function rollback() {
        
            
        }
        
        private function replaceContent(string $partOfContent, int $count): mixed {
        
            if (in_array($count, $this->whichOccurs)) {
                $this->partsOfContent[$count] = str_replace(mixed $this->needle, mixed $this->replacement, mixed $this->partsOfContent[$count], int $count);
            }
        }
        
        private function checkIfNeedleExist(): bool {
        
            $pos = strpos($this->content, $needle);
            
            if ($pos !== false) {
                
                return true;
            }
            
            return false;
        }
    
        private function checkFileIsWritable(string $file): bool {
        
            if (file_exists($file)): bool {
                
                return is_writable($file);
            }
            
            return false;
        }
        
        public setFile(string $file) {
                
            if (false === $this->checkFileIsWritable()) {
            
                return false;
                
            } else {
                
                $this->file = $file;
                
                return true;
            }
        }
        
        public setWhichOccurs(string $whichOccurs) {
        
            $this->whichOccurs = explode(',', $whichOccurs);
        }
        
        public function getContent() {
        
            return $this->content;
        }    
    }
    
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
?>
