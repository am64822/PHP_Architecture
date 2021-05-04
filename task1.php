<?php
set_time_limit(180); 

// 1. Создать массив на миллион элементов и отсортировать его различными способами. Сравнить скорости.
// Сортировка пузырьком
// Шейкерная сортировка
// Быстрая сортировка
// Пирамидальная сортировка
// Cортировка встроенными функциями php


// Функции соритировки ----------------------------------
// Сортировка пузырьком
function bubbleSort($array){
    for($i=0; $i<count($array); $i++) {
        $count = count($array);
        for($j=$i+1; $j<$count; $j++) {
            if($array[$i]>$array[$j]) {
                $temp = $array[$j];
                $array[$j] = $array[$i];
                $array[$i] = $temp;
            }
        }
    }
    return $array;
}


// Шейкерная сортировка
function shakerSort($array) {
    $n = count($array);
    $left = 0;
    $right = $n - 1;
    do {
        for ($i = $left; $i < $right; $i++) {
            if ($array[$i] > $array[$i + 1]) {
                list($array[$i], $array[$i + 1]) = array($array[$i + 1],
            $array[$i]);
            }
        }
        $right -= 1;
        for ($i = $right; $i > $left; $i--) {
            if ($array[$i] < $array[$i - 1]) {
                list($array[$i], $array[$i - 1]) = array($array[$i - 1],
            $array[$i]);
            }
        }
        $left += 1;
    } while ($left <= $right);
    return $array;
}

// Быстрая сортировка
function quickSort(array &$arr, $low, $high)
{
    $i = $low;
    $j = $high;
    $middle = $arr[($low + $high) / 2];   // middle – опорный элемент; в нашей реализации он находится посередине между low и high
    do {
        while ($arr[$i] < $middle) {
            ++$i;
        }     // Ищем элементы для правой части
        while ($arr[$j] > $middle) {
            --$j;
        }     // Ищем элементы для левой части
        if ($i <= $j) {
            // Перебрасываем элементы
            $temp = $arr[$i];
            $arr[$i] = $arr[$j];
            $arr[$j] = $temp;
            // Следующая итерация
            $i++;
            $j--;
        }
    } while ($i < $j);

    if ($low < $j) {
        // Рекурсивно вызываем сортировку для левой части
        quickSort($arr, $low, $j);
    }

    if ($i < $high) {
        // Рекурсивно вызываем сортировку для правой части
        quickSort($arr, $i, $high);
    }

}

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




// Запуск ----------------------------------
echo('<pre>');

$initArray = [];
$count = 50000; // кол-во значений в массиве

for ($i=0; $i < $count; $i++) {
    $initArray[$i] = $count - $i;
}

echo('Кол-во значений в массиве: ' . $count . '<br>');
//var_dump($initArray);


echo('<br><br><br> ---------- Сортировка пузырьком ---------- <br><br>');
$startTime = hrtime(true);
$sortedArray = bubbleSort($initArray);
$endTime = hrtime(true);
echo('Время выполнения ' . ($endTime - $startTime)/1000000000 . ' секунд');
//var_dump($sortedArray);

echo('<br><br><br> ---------- Шейкерная сортировка ---------- <br><br>');
$startTime = hrtime(true);
$sortedArray = shakerSort($initArray);
$endTime = hrtime(true);
echo('Время выполнения ' . ($endTime - $startTime)/1000000000 . ' секунд');
//var_dump($sortedArray);

echo('<br><br><br> ---------- Быстрая сортировка ---------- <br><br>');
$sortedArray = $initArray;
$lastIndex = count($sortedArray) - 1;
$startTime = hrtime(true);
quickSort($sortedArray, 0, $lastIndex);
$endTime = hrtime(true);
echo('Время выполнения ' . ($endTime - $startTime)/1000000000 . ' секунд');
//var_dump($sortedArray);

echo('<br><br><br> ---------- Пирамидальная сортировка ---------- <br><br>');
$startTime = hrtime(true);
$sortedArray = treeSort($initArray);
$endTime = hrtime(true);
echo('Время выполнения ' . ($endTime - $startTime)/1000000000 . ' секунд');
//var_dump($sortedArray);

echo('<br><br><br> ---------- Cортировка встроенными функциями php ---------- <br><br>');
$sortedArray = $initArray;
$startTime = hrtime(true);
sort($sortedArray);
$endTime = hrtime(true);
echo('Время выполнения ' . ($endTime - $startTime)/1000000000 . ' секунд');
//var_dump($sortedArray);