<?php
// 2. Реализовать удаление элемента массива по его значению. Обратите внимание на возможные дубликаты!


// Пирамидальная сортировка
function treeSort(array $list): array
{
    $tree = new SplMinHeap();
    foreach ($list as $n) {
        $tree->insert($n);
    }
    $list = [];
    while ($tree->valid()) {
        $list[] = $tree->top();
        $tree->next();
    }
    return $list;
}

// Бинарный поиск
function binarySearch($myArray, $num) { // на выходе - индекс элемента с интересующим значением или null
    //определяем границы массива
    $left = 0;
    $right = count($myArray) - 1;
    $steps = 0; // кол-во шагов
    while ($left <= $right) {
        $steps += 1;
        //находим центральный элемент с округлением индекса в меньшую сторону
        $middle = floor(($right + $left)/2);
        //echo('$steps:' . $steps . ', $middle: ' . $middle . '<br>');
        //если центральный элемент и есть искомый
        if ($myArray[$middle] == $num) {
            return $middle;
        }
        elseif ($myArray[$middle] > $num) {
            //сдвигаем границы массива до диапазона от left до middle-1
            $right = $middle - 1;
        }
        elseif ($myArray[$middle] < $num) {
            $left = $middle + 1;
        }
    }
    return null;
}


// Запуск ----------------------------------------
// echo('<pre>');

$initArray = [];
$count = 10; // кол-во значений в массиве
$toSearch = 5; // искомое значение для удаления

for ($i=0; $i < $count; $i++) {
    $initArray[$i] = rand(0, $count);
}
$initArray[$toSearch] = $toSearch; // чтобы гарантированно иметь пару значений для удаления
$initArray[$toSearch + 1] = $toSearch;

echo('Кол-во значений в массиве: ' . $count . '<br>');
echo('Искомое значение для удаления: ' . $toSearch . '<br>');
echo('<br>Исходный массив<br>');
var_dump($initArray);

// Сортировка
$initArray = treeSort($initArray);
echo('<br><br>Массив после сортировки<br>');
var_dump($initArray);

// Бинарный поиск
$index = binarySearch($initArray, $toSearch);
if (is_null($index)) { 
    die('<br>Значение не найдено'); 
} 

// Поиск влево и вправо
$indexLeft = $index;
$indexRight = $index;
$lastIndex = count($initArray) - 1; // echo ('<br>$lastIndex: ' . $lastIndex . '<br>');

while (($indexLeft >= 1) AND ($initArray[$indexLeft - 1] ==  $toSearch)) {
    $indexLeft -= 1;
}

while (($indexRight <= $lastIndex - 1) AND ($initArray[$indexRight + 1] ==  $toSearch)) {
    $indexRight += 1;
}

echo ('<br><br>Значение найдено бинарным поиском по индексу: ' . $indexLeft);
echo ('<br>Удалять с индекса: ' . $indexLeft);
echo ('<br>по индекс: ' . $indexRight);

// Удаление
array_splice($initArray, $indexLeft, $indexRight - $indexLeft + 1);
echo('<br><br>Массив после удаления<br>');
var_dump($initArray);