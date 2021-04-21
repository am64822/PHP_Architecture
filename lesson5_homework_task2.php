<?php

/* 2. Реализовать паттерн Адаптер для связи внешней библиотеки (классы SquareAreaLib и CircleAreaLib) вычисления площади квадрата (getSquareArea) и площади круга (getCircleArea) с интерфейсами ISquare и ICircle имеющегося кода. Примеры классов даны ниже. Причём во внешней библиотеке используются для расчётов формулы нахождения через диагонали фигур, а в интерфейсах квадрата и круга — формулы, принимающие значения одной стороны и длины окружности соответственно. */

// решение

// в методе getSquareArea адаптируемого класса SquareAreaLib на входе - целое число. При целом значении длины диагонали квадрата сторона квадрата никогда не может быть целым числом, что, однако, требуется согласно интерфейсу, к которому необходимо адаптировать. В связи с этим с целью уменьшения ошибок округления принят подход с переопределением метода адаптируемого класса (через промежуточный класс)

class SquareAreaLibFloatDiagonal extends SquareAreaLib { // переопределением метода адаптируемого класса для изменения типа значения на входе метода (с int на float)
   public function getSquareArea(float $diagonal) {
       $area = ($diagonal**2)/2;
       return $area;
   } 
}

class SquareAreaAdapter implements ISquare {
    public function squareArea(int $sideSquare) {
        return (new SquareAreaLibFloatDiagonal())->getSquareArea(sqrt(($sideSquare**2)*2));
    }
}

// аналогичная ситуация c окружностью
class CircleAreaLibFloatDiagonal extends CircleAreaLib { // переопределением метода адаптируемого класса для изменения типа значения на входе метода (с int на float)
   public function getCircleArea(float $diagonal) {
       $area = ((M_PI * $diagonal**2))/4;
       return $area;
   }
}

class CircleAreaAdapter implements ICircle {
    public function circleArea(int $circumference) {
        return (new CircleAreaLibFloatDiagonal())->getCircleArea($circumference / M_PI);
    }
}




// Внешняя библиотека:
class CircleAreaLib {
   public function getCircleArea(int $diagonal) {
       $area = ((M_PI * $diagonal**2))/4;
       return $area;
   }
}

class SquareAreaLib {
   public function getSquareArea(int $diagonal) {
       $area = ($diagonal**2)/2;
       return $area;
   }
}

//Имеющиеся интерфейсы:
interface ISquare {
    public function squareArea(int $sideSquare);
}

interface ICircle {
    public function circleArea(int $circumference);
}


// тест
echo ((new SquareAreaAdapter())->squareArea(10)); // 100
echo '<br>';
echo ((new CircleAreaAdapter())->circleArea(10)); // 7,96



