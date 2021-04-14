<?php  // см. LESSON3_README.txt !!!

namespace app\controllers;

class ControlController 
{
    
    private function adaptSettings($param = null, $value = null) { // возвращаем 'ok' или 'nok'
        $settingsPath = ROOT_DIR . DS . 'config' . DS;
        $settings = file_get_contents("{$settingsPath}settings");
        if($settings == false) { return 'nok'; } // если не читается - выход с 'nok'
        $settings = json_decode($settings, true);
        if (!is_null($param) && !is_null($value)) {$settings[$param] = "".$value;}
        $settings = json_encode($settings);
        //return (var_dump($settings));
        $settings = file_put_contents("{$settingsPath}settings", $settings);         
        return (($settings != false) ? 'ok' : 'nok'); // если не пишется - выход с 'nok'
    }

    
    private function readSettings($param = null, $value = null) { // возвращаем массив или 'nok'
        $settingsPath = ROOT_DIR . DS . 'config' . DS;
        $settings = file_get_contents("{$settingsPath}settings");
        if($settings == false) { return 'nok'; } // если не читается - выход с 'nok'
        $settings = json_decode($settings, true);       
        return $settings;
    }    
    
    
    
    
    private function readGPIO($num) {
        $pathGPIO = PATHGPIO;
        return file_get_contents("{$pathGPIO}{$num}/value"); // false или текст
    }
    
    private function writeGPIO($num, $value) {
        $pathGPIO = PATHGPIO;
        $nok = false;
        
        // проверить, является ли выходом
        exec("cat {$pathGPIO}{$num}/direction", $output, $retval); 
        
        if ($retval != 0) {return 'nok';}
        if (!is_array($output)) {return 'nok';}
        if ($output[0] != 'out') {return 'nok';}
        
        // записать, проверить статус        
        exec("echo {$value} > {$pathGPIO}{$num}/value", $output, $retval);
        
        if ($retval != 0) {return 'nok';}
        //if (!is_array($output)) {return 'nok';}
        //if ($output[0] != $value) {return 'nok';}
        return 'ok';
    }
    
    
    public function actionVolume($params = null) {
        $result = 'ok';
        // hw:CARD=Headphones,DEV=0    hw:CARD=b1,DEV=0
        $params = json_decode($params, true);
        $echo = $params['echo'];
        $vol_L = $params['vol_L'];
        $vol_R = $params['vol_R'];
        //var_dump($params);
        //return;
        exec("sudo /home/www-data/volume {$vol_L}% {$vol_R}%", $output, $retval);
        if ($retval != 0) {$result = 'nok';}
        if ($this->adaptSettings('vol_L', $vol_L) == 'nok') {$result = 'nok';}
        if ($this->adaptSettings('vol_R', $vol_R) == 'nok') {$result = 'nok';}
        if ($echo == false) { // есть вызовы в GET, иногда нужно убрать echo
            return $result;
        } 
        echo $result;
    }
    
 
    public function actionUpdateSettings($param = null) {
        $result = 'ok';
        
        if (!is_null($param)) {
            $param = json_decode($param);
            foreach ($param as $key => $value) {
                //echo ($key);
                //echo ($value);
                $result = $this->adaptSettings($key, $value);
            }
        } else {
            $result = 'nok';
        }
        echo $result;
    }
    
    
    public function actionOn($params = null) {
        //echo('actionOn');
        $params = json_decode($params, true);
        $echo = $params['echo'];        
        $result = 'ok';
        if ($this->writeGPIO(17, 1) == 'nok') {$result = 'nok';}
        if ($this->adaptSettings('no_mute', "1") == 'nok') {$result = 'nok';}
        if ($echo == false) { // есть вызовы в GET, иногда нужно убрать echo
            return $result;
        } 
        echo $result;
    }
    
    public function actionOff($params = null) {
        //echo('actionOff');
        $params = json_decode($params, true);
        $echo = $params['echo'];
        $result = 'ok';
        if ($this->writeGPIO(17, 0) == 'nok') {$result = 'nok';}
        if ($this->adaptSettings('no_mute', "0") == 'nok') {$result = 'nok';}
        if ($echo == false) { // есть вызовы в GET, иногда нужно убрать echo
            return $result;
        } 
        echo $result;
    }

    public function actionShutdown($dummy = null) {
        echo('ok');  // ok или nok
        exec("sudo /home/www-data/shutdownRPi", $output, $retval);
    }    
    
    public function actionBlow($params = null) {
        // hw:CARD=Headphones,DEV=0    hw:CARD=b1,DEV=0
        $root_dir = ROOT_DIR;
        $params = json_decode($params, true);
        $echo = $params['echo'];
        $vol_L = $params['vol_L'];
        $vol_R = $params['vol_R'];
        $times = $params['times'];
        $sound = $params['sound']; 

        $this->adaptSettings('running', '1');
        
        for ($i=1; $i<=$times; $i++) {    
            
            if ($sound == 10) { // 
                $incycle = rand(3, 5);
                for ($j=1; $j<=$incycle; $j++) {
                    $sound = rand(6, 9);
                    exec("sudo /home/www-data/singlblow {$root_dir}/sounds/{$sound}.wav", $output, $retval);
                }
                $sound = 10;
            } elseif ($sound == 20) { // 
                $incycle = rand(5, 10);
                for ($j=1; $j<=$incycle; $j++) {
                    $sound = rand(1, 5);
                    exec("sudo /home/www-data/singlblow {$root_dir}/sounds/{$sound}.wav", $output, $retval);
                }
                $sound = 20;
            } elseif ($sound == 7) {
                    exec("sudo /home/www-data/singlblow {$root_dir}/sounds/{$sound}.wav", $output, $retval);             
            } else {
                exec("sudo /home/www-data/singlblow {$root_dir}/sounds/{$sound}.wav", $output, $retval);
            }
            
        }
        $this->adaptSettings('running', '0');
        
        $status = (($retval != 0) ? 'nok' : 'ok');
        if ($echo == true) {
            echo $status;
        }
        return $status;
    }
    
    public function actionGetblowsignal($param = null) {
        $result = 'ok';
        
        if (!is_null($param)) {
            $param = json_decode($param, true);
            if (isset($param['monitor'])) {
                $result = $this->adaptSettings('monitor', $param['monitor']); // запись статуса monitor = 1|0
            } else { $result = 'nok'; }
        } else { $result = 'nok'; }
        
        if ($result == 'nok') { // предварительная проверка. Если не ok, то нет смысла продолжать. Выход
            echo $result;
            return;
        }
        
        if ($param['monitor'] == 0) { // если мониторинг выключен, то выход. Иначе цикл.
            echo $result;
            return;            
        }
        
        $currentTime = date('His');
        $cycle = 30; // длительность цикла в секундах (idle, без учета звуков и т.п.)
        $delay = 0.2; // задержка между чтениями в секундах
        $controlCyclesLimit = 10; // через сколько циклов проверять, есть ли останов
        $controlCycles = 9;
        
        while (true) {
        // в конце цикла запоминаем значение входа GPIO как предыдущее. В начале цикла читаем новое значение входа GPIO и сравниваем с предыдущим. Если фронт 0 -> 1, то обработать. В первом цикле предыдущее значение равно считанному. Выход их цикла - по результатам чтения файла с параметрами.
            
            $controlCycles = $controlCycles + 1;
            if ($controlCycles >= $controlCyclesLimit) { // примерно раз в 2 сек. чтение параметров
                $controlCycles = 0;
                
                $currentTime = explode(':', date('H:i:s'));
                $currentWeekDay = ('N');
                //07:00:00 - 13:00:00, 13:00:00 - 15:00:00, 15:00:00- 23:00:00, 23:00:00 - 07:00:00  
                
                // считать monitor из файла. Если 0, то выход с 'ok', если не читается, то выход с 'nok'
                $settings = $this->readSettings();
                if ($settings == 'nok') {
                    echo('nok'); //echo('1');
                    return;
                }
                
                if ($settings['monitor'] == 0) {
                    //echo('монитор = 0 при проверке по idle - ok');
                    echo('ok');
                    return;                    
                }
                
                $vol_L = $settings['vol_L'];
                $vol_R = $settings['vol_R'];
                
                $settingsPath = ROOT_DIR . DS . 'config' . DS;
                //$toWrite = $toWrite . 'previousTimeStamp: ' . $previousTimeStamp . ' timeStamp: ' . $timeStamp . ' curr - prev: ' . ($timeStamp - $previousTimeStamp) . ' count: ' . $blCount . PHP_EOL;
                //$toWrite = 1 . PHP_EOL;
                //file_put_contents("{$settingsPath}log", $toWrite, FILE_APPEND);
                
                set_time_limit(0);
                if (!isset($vol_R_temp)) {$vol_R_temp = $vol_R;}
                if (isset($previousTimeStamp)) {
                    
                    /*$toWrite = 'previousTimeStamp: ' . (microtime(true) - $previousTimeStamp) . PHP_EOL;
                    file_put_contents("{$settingsPath}log", $toWrite, FILE_APPEND);*/
                    
                    if ((microtime(true) - $previousTimeStamp) > 30) {
                        if ($vol_R_temp > $vol_R) {
                            $vol_R_temp -= 5;
                            exec("sudo /home/www-data/volume {$vol_L}% {$vol_R_temp}%", $output, $retval);
                        }
                    }
                }
                
                
            }
            
            
            $readValue = $this->readGPIO(23); 
            if ($readValue == false) { // возврат кода ошибки, если не удалось прочитать значение
                echo('nok');
                return;
            }
            
            $readValue = substr($readValue, 0, 1);
            if (!isset($previousValue)) {$previousValue = $readValue;}
            
            if ($readValue == "1" && $previousValue == "0") {
                // обработать сигнал
                
                $timeStamp = microtime(true); // unix-время сигнала.
                if (!isset($previousTimeStamp)) {$previousTimeStamp = $timeStamp;} //  unix-время пред. сигнала
                
//$toWrite = 'blowTime: ' . $timeStamp . PHP_EOL;
//file_put_contents("{$settingsPath}log", $toWrite, FILE_APPEND);

                if (($timeStamp - $previousTimeStamp) < 3) { // шумоподавление
                    // do nothing
                } else {
                    
//$toWrite = 'blowTime - Prev: ' . ($timeStamp - $previousTimeStamp) . PHP_EOL;
//file_put_contents("{$settingsPath}log", $toWrite, FILE_APPEND);                    
                    
                    if ($vol_R_temp <= 95) {
                        $vol_R_temp += 5;
                        exec("sudo /home/www-data/volume {$vol_L}% {$vol_R_temp}%", $output, $retval);
                    }
                    
                $settings['echo'] = false; // подавить эхо 

//$toWrite = 'StartPlaying: ' . microtime(true) . PHP_EOL;
//file_put_contents("{$settingsPath}log", $toWrite, FILE_APPEND);                

                sleep(rand($settings['delay_min'], $settings['delay_max']));                    
                    
                if ($this->actionBlow(json_encode($settings)) != 'ok') {
                    echo('nok');
                    return;                    
                } //else {echo('проигр. - ok');};

                $previousTimeStamp = microtime(true);
                
//$toWrite = 'StopPlaying: ' . $previousTimeStamp . PHP_EOL;
//file_put_contents("{$settingsPath}log", $toWrite, FILE_APPEND);                     
                    
                }
                
                
                /*$settingsPath = ROOT_DIR . DS . 'config' . DS;
                $toWrite = $toWrite . 'previousTimeStamp: ' . $previousTimeStamp . ' timeStamp: ' . $timeStamp . ' curr - prev: ' . ($timeStamp - $previousTimeStamp) . PHP_EOL;
                file_put_contents("{$settingsPath}log", $toWrite, FILE_APPEND);*/
         
                
                    
            }
            
            
            $previousValue = $readValue;
            usleep($delay * 1000000); // задержка в микросекундах        
        }        
        echo('ok'); // end of loop
    }    
    
    
    
}