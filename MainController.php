<?php

namespace app\controllers;
use app\controllers\{ControlController};
use app\engine\{InitializeGPIO};

class MainController 
{

    private static $defaultController = '';
    private static $defaultAction = '';
    private static $defaultLayout = 'main';
    
    public function run() {
        // определить параметры запроса (POST)
            // (c)control: / (a)on/off/single
            // (c)display: / (a)request
            // (c)user: (a)login / logout
        // определить контроллер и метод. Если не определяется - не делать ничего
        // вызвать контроллер. Возвращает шаблон и контент
        // вызвать рендерер
        
        // определить параметры запроса (POST)
        $doNothing = false;
        $controller = static::$defaultController;
        $action = static::$defaultAction;
        
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['c'])) {
                $controllerInterim = __NAMESPACE__ .'\\'.ucfirst($_POST['c'].'Controller');
                if (class_exists($controllerInterim)) {
                    $controller = $controllerInterim;
                } else {$doNothing = true;}   
            } else {$doNothing = true;}
            
            if (isset($_POST['a']) && ($controller != static::$defaultController)) {
                $actionInterim = 'action' . (ucfirst($_REQUEST['a']));
                if (method_exists($controller, $actionInterim)) {
                    $action = $actionInterim;
                } else {$doNothing = true;}   
            } else {$doNothing = true;}

            //echo(json_encode(['c'=>$controller, 'a'=>$action], JSON_PRETTY_PRINT));
            //echo ('Controller ' . $controller . '<br>'); 
            //echo ('Method ' . $action . '<br>'); */   
            
            (new $controller)->$action(isset($_POST['v']) ? $_POST['v'] : null);
            
            //var_dump(get_class_methods(get_class($test)));
            
            
        } 
        
        
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            
            InitializeGPIO::run();
            
            $settingsPath = ROOT_DIR . DS . 'config' . DS;
            $settings = file_get_contents("{$settingsPath}settings");

            if($settings == false) { /* settings reading error */ }
            $settings = json_decode($settings, true);
            
            $no_mute = $settings['no_mute'];
            $running = $settings['running'];
            $monitor = $settings['monitor'];
            $sound = $settings['sound']; // 1-5 - ремонт (20-сер.),  6-9 - собака (10-сер.)
            $times = $settings['times']; // циклов
            $vol_L = $settings['vol_L'];
            $vol_R = $settings['vol_R'];
            $delay_min = $settings['delay_min'];
            $delay_max = $settings['delay_max'];
            
            if ($no_mute == 0) {
                (new ControlController())->actionOff('{"echo" : false}');
            } elseif ($no_mute == 1) {
                (new ControlController())->actionOn('{"echo" : false}');
            }            
                     
            (new ControlController())->actionVolume('{"echo" : false, "vol_L" : '. $vol_L.', "vol_R" : '. $vol_R .'}');
            
        // переделать на нормальный рендер    
            
            $template = 'main.php';
            $content = 'controls.php';  
            include ROOT_DIR.DS.'views'.DS.$template;            
        } 
    } // end of run method
    
    
    
    
    
    
}
    
    
    
    
