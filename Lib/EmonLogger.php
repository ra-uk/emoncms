<?php
/*
 All Emoncms code is released under the GNU Affero General Public License.
 See COPYRIGHT.txt and LICENSE.txt.

 ---------------------------------------------------------------------
 Emoncms - open source energy visualisation
 Part of the OpenEnergyMonitor project:
 http://openenergymonitor.org
 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

class EmonLogger
{
    private $logfile = "";
    private $caller = "";
    private $logenabled = false;

    public function __construct($clientFileName)
    {
        global $log_filename, $log_enabled;
		if (!$log_enabled) {
			$this->logenabled = false;
		}
        else if ($log_filename) {
            $this->logfile = $log_filename;
            $this->caller = basename($clientFileName);
            if (!file_exists($this->logfile))
            {
                $fh = @fopen($this->logfile,"a");
                @fclose($fh);
            }
            if (is_writable($this->logfile)) $this->logenabled = true;
        }
    }

    public function info ($message){
        $this->write("INFO",$message);
    }

    public function warn ($message){
        $this->write("WARN",$message);
    }
    
    public function error ($message){
        $this->write("ERROR",$message);
    }
    
    private function write($type,$message){
        if (!$this->logenabled) return;
        
        $now = microtime(true);
        $micro = sprintf("%03d",($now - ($now >> 0)) * 1000);
        $now = DateTime::createFromFormat('U', (int)$now); // Only use UTC for logs
        $now = $now->format("Y-m-d H:i:s").".$micro";
        clearstatcache($this->logfile); 
        // Clear log file if more than 1MB (temporary solution)
        if (filesize($this->logfile)>(1024*1024)) {
            $fh = @fopen($this->logfile,"w");
            @fclose($fh);
        }
        if ($fh = @fopen($this->logfile,"a")) {
            @fwrite($fh,$now."|$type|$this->caller|".$message."\n");
            @fclose($fh);
        }       
    }
    
}