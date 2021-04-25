<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);

// -----------------------------

interface IPaymentVia {
    public function pay():void;
}

abstract class PaymentVia implements IPaymentVia {
    protected string $sum;
    protected string $phone;
    
    public function __construct(string $sum, string $phone) {
        $this->sum = $sum;
        $this->phone = $phone;
    }
    
    public function pay(): void { // переопределяется в наследниках
    }
}


class PaymentViaQiwi extends PaymentVia {
    public function pay(): void {
        echo ('Qiwi. Сумма: ' . $this->sum . ', тел.: ' . $this->phone . '<br>');
    }
}

class PaymentViaYandex extends PaymentVia {
    public function pay(): void {
        echo ('Яндекс. Сумма: ' . $this->sum . ', тел.: ' . $this->phone . '<br>');
    }
}

class PaymentViaWebMoney extends PaymentVia {
    public function pay(): void {
        echo ('WebMoney. Сумма: ' . $this->sum . ', тел.: ' . $this->phone . '<br>');
    }
}

// -----------------------------

class Order {
    protected PaymentVia $paymentMethod;
    
    public function __construct(PaymentVia $paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }
    
    public function pay(): void {
        $this->paymentMethod->pay();
    }
}


// тест - оплата через Яндекс

$order1 = new Order(new PaymentViaYandex('1000.00', '+7999999999'));
$order1->pay();


