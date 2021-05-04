<?php

// * Выписав первые шесть простых чисел, получим 2, 3, 5, 7, 11 и 13. Очевидно, что 6-е простое число — 13. Какое число является 10001-м простым числом?

$primes = array(2, 3, 5, 7, 11, 13);

function isPrime($number) {
    // Получаем лимит для делителя
   $dividersLimit = $number / 2;

    // Проверяем делители с шагом 2
    for ($i = 3; $i < $dividersLimit; $i += 2) {
        if ($number % $i === 0) {
            return false;
        }
    }

    return true;
}

// Начнем поиск, например, с 15 с шагом 2 (так как кроме двойки парных простых чисел больше нет)
for ($i = 15; $i < PHP_INT_MAX; $i += 2) {
    // для тестирования
    $maxTrials = 1000000;
    if ($i > $maxTrials) { die('Выход по кол-ву циклов');  }
    
    if (isPrime($i)) {
        array_push($primes, $i);

        if (count($primes) === 10001) {
            echo('Результат: ' . $i);
            break;
        }
    }
}
