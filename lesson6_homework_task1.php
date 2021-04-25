<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', E_ALL);


// -----------------------------

interface IApplicant {
    public function notifyMe($details): void;
}

class Applicant implements IApplicant {
    private string $firstName;
    private string $email; // уникальный $email соискателя
    private string $lengthOfWork;
    
    public function __construct(string $firstName, string $email, string $lengthOfWork) {
        $this->firstName = $firstName;
        $this->email = $email;
        $this->lengthOfWork = $lengthOfWork;        
    }
    
    public function notifyMe($details): void { // метод уведомления соискателя (e-mail, сообщение на экране и т.п.). На входе - информация о вакансии
        // do dome stuff
        echo ('Уведомление для ' . $this->firstName . ' о вакансии ' . $details . '.<br>');
    }
}

// -----------------------------

interface IVacancy {
    public function subscribe(IApplicant $applicantObj): void;
    public function unsubscribe(IApplicant $applicantObj): void;
    public function notify(): void;
}

class Vacancy implements IVacancy {
    private $details; // информация о вакансии (массив, json, текст и т.п.)
    private array $applicants;
    
    public function __construct($details) {
        $this->details = $details;
    }
    
    public function subscribe(IApplicant $applicantObj): void {
        $this->applicants[] = $applicantObj;
    }
    
    public function unsubscribe(IApplicant $applicantObj): void {
        foreach($this->applicants as &$appl) {
            if ($appl == $applicantObj) {
                unset($appl);
            }
        };
    }
    
    public function notify(): void {
        foreach($this->applicants as $appl) {
            $appl->notifyMe($this->details);
        };       
    }
}


// тест - 3 вакансии, 3 соискателя, все подписываются на все, должно быть 9 echo - ok

$appl1 = new Applicant('Vasja', 'Vasja@test.com', '2');
$appl2 = new Applicant('Olga', 'Olga@test.com', '3');
$appl3 = new Applicant('Svetlana', 'Svetlana@test.com', '1');

$vac1 = new Vacancy('Вакансия 1');
$vac2 = new Vacancy('Вакансия 2');
$vac3 = new Vacancy('Вакансия 3');

$vac1->subscribe($appl1); // подписка всех на уведомления вакансии 1
$vac1->subscribe($appl2);
$vac1->subscribe($appl3);

$vac2->subscribe($appl1); // подписка всех на уведомления вакансии 2
$vac2->subscribe($appl2);
$vac2->subscribe($appl3);

$vac3->subscribe($appl1); // подписка всех на уведомления вакансии 3
$vac3->subscribe($appl2);
$vac3->subscribe($appl3);

$vac1->notify(); // отправка уведомлений
$vac2->notify();
$vac3->notify();
