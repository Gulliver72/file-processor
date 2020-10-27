<?php
namespace RobinTheHood\ModifiedModuleLoaderClient;

use RobinTheHood\ModifiedModuleLoaderClient\ModuleInstaller;

class FileProcessor
    {
    
        private $file; // Filename with directory
        private $content; // Filecontent
        private $partsOfContent; // Array von Teilstücken des Contents wenn nicht das erste Vorkommen ersetzt werden soll
        private $needleArray;
        private $needle;
        private $replacement;
        private $whichOccurs; // Array mit Vorkommen, die bearbeitet werden sollen // leer wenn alle Vorkommen bearbeitet werden sollen
        
        function __construct() {
        
            $this->reset();
        }
        
        public function patch() {
        
            // Original File sichern
            $src = 'ModifiedModuleLoader/meinmodul/backup/old/' . $this->file; // ToDo den korrekten Pfad übergeben

            $moduleInstaller = new ModuleInstaller();
            $moduleInstaller->installFile($src, $this->file, false);
            
            // content bearbeiten
            $this->content = FileHelper::readFile($this->file);

            $this->breakContent();

            if (empty($this->partsOfContent)) {
                
                $count = 0;
                foreach ($this->whichOccurs as $part) { // which needle should be replaced
                    
                    $this->replaceContent($part, $count);
                    $count++;
                }
            } else {
                
                $this->replaceContent(0);
            }

            $this->content = $this->composeContent();
            
            $src = 'ModifiedModuleLoader/meinmodul/backup/new/' . $this->file; // ToDo den korrekten Pfad übergeben
            
            $datei_handle = fopen($src, "w+");
            if (!fputs($datei_handle, $this->content)){
                $this->rollback();
                exit;
            }
            fclose($datei_handle);

            // bearbeitetes File sichern
            unlink($this->file); // Original-File löschen
            $moduleInstaller->installFile($this->file, $src, false);

            $this->reset();
        }

        public function patchWithRegex() {
        
            // Original File sichern
            $src = 'ModifiedModuleLoader/meinmodul/backup/old/' . $this->file; // ToDo den korrekten Pfad übergeben

            $moduleInstaller = new ModuleInstaller();
            $moduleInstaller->installFile($src, $this->file, false);
            
            // content bearbeiten
            $this->content = FileHelper::readFile($this->file);

            $this->pregReplaceContent(-1);
            
            $src = 'ModifiedModuleLoader/meinmodul/backup/new/' . $this->file; // ToDo den korrekten Pfad übergeben
            
            $datei_handle = fopen($src, "w+");
            if (!fputs($datei_handle, $this->content)){
                $this->rollback();
                exit;
            }
            fclose($datei_handle);

            // bearbeitetes File sichern
            unlink($this->file); // Original-File löschen
            $moduleInstaller->installFile($this->file, $src, false);

            $this->reset();
        }
           
        public function composeContent() {

            if (empty($this->partsOfContent)) {

                return $this->content;
            }
            
            $temp_array = array();
            
            if (isset($this->partsOfContent['first'])) $temp_array[] = $this->partsOfContent['first']; // if a part of the content is before the first needle
            
            $count1 = count($this->partsOfContent);
            $count2 = count($this->needleArray);
            $count = $count1 > $count2 ? $count1 : $count2; // set the counter to the larger value
            
            for ($i = 0; $i < $count; $i++) {
                // putting content and needles back together
                if (isset($this->needleArray[$i])) $temp_array[] = $this->needleArray[$i]; 
                if (isset($this->partsOfContent[$i])) $temp_array[] = $this->partsOfContent[$i];
            }

            return implode('', $temp_array);
        }
        
        private function breakContent() {

            if ($this->checkIfNeedleExist() === true) {

                $content = $this->content;
                if (substr_count($content, $this->needle) > 1) { // break content only if needle occurs more than once
                
                    $this->partsOfContent['first'] = strstr($content, (string)$this->needle, TRUE); // returns the part of $content that is before the first occurrence of $needle (without $needle itself)

                    $content = strstr($content, (string)$this->needle); // rest of $content
                    $content = substr($content, 0, strlen($this->needle)); // $needle must be removed from the rest of the content
                    
                    $this->needleArray[] = $this->needle;
                    
                    $count_needle = substr_count($content, $this->needle); // how often is $needle included? is needed for the for loop
                    
                    for ($i = 0; $i < $count_needle; $i++) {
                    
                        $this->partsOfContent[] = strstr($content, (string)$this->needle, TRUE); // returns the part of $content that is before the first occurrence of $needle (without $needle itself)
                    
                        $content = strstr($content, (string)$this->needle); // rest of $content
                        $content = substr($content, 0, strlen($this->needle)); // $needle must be removed from the rest of the content

                        $this->needleArray[] = $this->needle;
                    }
                    
                    if ($content != '') $this->partsOfContent[] = $content; // if $content still contains something
                }
            }
        }
        
        private function rollback() {
        
            
        }
        
        private function replaceContent(int $part, int $count = 0) {

            if (empty($this->partsOfContent)) {
                $this->needleArray[$part] = str_replace($this->needle, $this->replacement, $this->needleArray[$part], $count);
            } else {
                $this->content = str_replace($this->needle, $this->replacement, $this->content); // if only one needle in content
            }
        }

        private function pregReplaceContent(int $limit = -1, int $count = 0) {
    
            if (preg_match($this->needle, $this->content) == 1) {
         
                $content = preg_replace($this->needle, $this->replacement, $this->content, $limit, &$count);
         
                if (false !== $content) {
                    $this->content = $content;
                }
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

        public setNeedle(string $needle) {

            $this->needle = $needle;
        }

        public setReplacement(string $replacement) {

            $this->replacement = $replacement;
        }

        public setWhichOccurs(string $whichOccurs) {
        
            $this->whichOccurs = explode(',', $whichOccurs);
        }

        public function getContent() {
        
            return $this->content;
        }    
 
        function reset() {

            $this->file = '';
            $this->content = '';
            $this->partsOfContent = array();
            $this->needleArray = array(0); // the first current entry in logic has key 1
            $this->needle = '';
            $this->replacement = '';
            $this->whichOccurs = array();
        }
    }
