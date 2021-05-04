<?php

// Подсчитать практически количество шагов при поиске описанными в методичке алгоритмами.

// Линейный поиск
function LinearSearch ($myArray, $num) { // на выходе - массив [найдено/нет, кол-во шагов]
    $count = count($myArray);
    for ($i=0; $i < $count; $i++) {
        if ($myArray[$i] == $num) return array(1, $i+1); // 0-й счетчик = 1-ый шаг
            elseif ($myArray[$i] > $num) return array(0, $i+1);
    }
    return array(0, $i+1);
}

// Бинарный поиск
function binarySearch($myArray, $num) { // на выходе - массив [найдено/нет, кол-во шагов]
    //определяем границы массива
    $left = 0;
    $right = count($myArray) - 1;
    $steps = 0; // кол-во шагов
    while ($left <= $right) {
        $steps += 1;
        //находим центральный элемент с округлением индекса в меньшую сторону
        $middle = floor(($right + $left)/2);
        echo('$steps:' . $steps . ', $middle: ' . $middle . '<br>');
        //если центральный элемент и есть искомый
        if ($myArray[$middle] == $num) {
            return array(1, $steps);
        }
        elseif ($myArray[$middle] > $num) {
            //сдвигаем границы массива до диапазона от left до middle-1
            $right = $middle - 1;
        }
        elseif ($myArray[$middle] < $num) {
            $left = $middle + 1;
        }
    }
    return array(0, $steps);
}

// Интерполяционный поиск
function InterpolationSearch($myArray, $num) {
    $start = 0;
    $last = count($myArray) - 1;
    $steps = 0; // кол-во шагов
    while (($start <= $last) && ($num >= $myArray[$start]) && ($num <= $myArray[$last])) {
        $steps += 1;
        $pos = floor($start + ((($last - $start) / ($myArray[$last] - $myArray[$start])) * ($num - $myArray[$start])));
        echo('$steps:' . $steps . ', $pos: ' . $pos . '<br>');
        if ($myArray[$pos] == $num) {
            return array(1, $steps);
        }
        if ($myArray[$pos] < $num) {
            $start = $pos + 1;
        }
        else {
            $last = $pos - 1;
        }
    }
return array(0, $steps);
}



// вспом.функция - текстовый вывод результата
function reply($repl) {
    if ($repl[0] == 0) { 
        echo('Значение не найдено, шагов: ' . $repl[1]); 
    } elseif ($repl[0] == 1) {
        echo('Значение найдено, шагов: ' . $repl[1]);
    }
}

// --------------- Запуск ----------------------
$array = [];
$count = 10000; // макс. кол-во значений в массиве
$toSearch = 10000; // искомое значение (такого в массиве нет)

// !!! Отсортированный массив без задвоения ключей и значений в массиве:
for ($i=0; $i < $count; $i++) {
    $array[$i] = $i;
}
$array[$count - 1] = $count * 2;
$array[$count] = $count * 3;


echo('<pre><br>');
echo('Макс. кол-во значений в массиве: ' . $count . '<br>');
echo('Интересующее число: ' . $toSearch . '<br>');

// Линейный поиск
echo('<br><br><br> ---------- Линейный поиск ---------- <br><br>');
$repl = LinearSearch($array, $toSearch);
reply($repl);

// Бинарный поиск
echo('<br><br><br> ---------- Бинарный поиск ---------- <br><br>');
$repl = binarySearch($array, $toSearch);
reply($repl);

// Интерполяционный поиск
echo('<br><br><br> ---------- Интерполяционный поиск ---------- <br><br>');
$repl = InterpolationSearch($array, $toSearch);
reply($repl);


