<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);

class getFileStructure {
    private string $directory;
    private bool $reverse;
    private array $arr; // все объекты интересующей директории
    
    public function __construct(string $directory, bool $reverse = false) {
        $this->directory = $directory;
        $this->reverse = $reverse;
        $this->arr = array();
    }
    
    public function retFileStructure(): ?array { // возвращает массив с ключами, соответствующими именам файлов, значениями в виде объектов SplFileInfo. При любой сортировке по именам (по возрастанию или по убыванию) сначала отображаются имена файлов, затем - имена поддиректорий 
        // алгоритм: 
        // - убедиться, что интересующая директория существует. Иначе - вернуть null
        // - убедиться, что интересующая директория не пуста. Иначе - вернуть пустой массив
        // - получить список директорий, отсортировать, поместить в целевой конечный массив
        // - для каждой из поддиректории из массива получить список файлов, отсортировать, поместить в целевой конечный массив

        // - убедиться, что интересующая директория существует. Иначе - вернуть null
        if (!file_exists($this->directory)) { return null; }
        if (!is_dir($this->directory)) { return null; }
        
        // убедиться, что интересующая директория не пуста. Иначе - вернуть пустой массив
        if (count(scandir($this->directory)) == 0) { return $this->arr; }
        
        // получить список директорий, отсортировать, поместить в массив
        $this->arr = $this->getFolders($this->directory);
        
        // - для каждой из поддиректории из массива получить список файлов, отсортировать, поместить в массив. Через array_splice не получилось, поэтому через промежуточный массив
        $interim_array = array();
        foreach ($this->arr as $key => $value) {
            $interim_array[$key] = $value;
            $interim_array = array_merge($interim_array, $this->getFiles($key));
        }

        $this->arr = $interim_array;        
        return $this->arr;
    }

    private function getFiles(string $dir):?array { 
        $arr = array(); // массив для возврата   
        
        $iterator = new RecursiveDirectoryIterator($dir); 

        foreach ( $iterator as $path ) {        
            if ($path->isFile()) {
                $arr[substr($path->__toString(), 0)] = $path; // SplFileInfo
            }
        }
        if ($this->reverse == true) {
            $arr = array_reverse($arr);
        }
        return $arr;
    }
    
    private function getFolders(string $dir):?array { 
        $arr = array(); // массив для возврата
        
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST ); 

        foreach ( $iterator as $path ) {
            if (!(($path->getFilename() == '.') OR ($path->getFilename() == '..'))) {
                // пропускаем '.' и '..'        
                if ($path->isDir()) {
                    $arr[substr($path->__toString(), 0)] = $path; // SplFileInfo
                }
            }
        }
        
        if ($this->reverse == true) {
            $arr = array_reverse($arr);
        }
        
        return $arr;
    }
    
}


// ----------------- использование / проверка ----------------
echo('<pre><br>');

$directory = '../';
$reverse = false;

echo('Листинг директории ' . $directory . '<br><br>');

$explorer = new getFileStructure($directory, $reverse);
$arr_to_display = $explorer->retFileStructure(); // массив с ключами, соответствующими именам файлов, значениями в виде объектов SplFileInfo. При любой сортировке по именам (по возрастанию или по убыванию) сначала отображаются имена файлов, затем - имена поддиректорий 

if (is_null($arr_to_display)) {
    echo 'Директория ' . $directory . ' не существует или в действительности является файлом.';
    die();
}

if (count($arr_to_display) == 0) {
    echo 'Директория ' . $directory . ' пуста.';
} else {
    foreach ($arr_to_display as $key => $value) {
        echo $key . '<br>';
    }
}



