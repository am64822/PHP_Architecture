<?php


class SendNotifications { // декоратор, позволяющий отправлять уведомления несколькими различными способам 
    private $sendSMS; // флаг необходимости отправки SMS
    private $sendEmail; // флаг необходимости отправки Email
    private $sendCN; // // флаг необходимости отправки через CN   
    private $smsMessage;
    private $smsRecipient;
    private $emailMessage;
    private $emailRecipient;
    private $CnMessage;
    private $CnRecipient;
    
    public function __construct($sendSMS = false, $sendEmail = false, $sendCN = false, $smsMessage = null, $smsRecipient = null, $emailMessage = null, $emailRecipient = null, $CnMessage = null, $CnRecipient = null) {
        $this->sendSMS = $sendSMS;
        $this->sendEmail = $sendEmail;
        $this->sendCN = $sendCN;
        $this->smsMessage = $smsMessage;
        $this->smsRecipient = $smsRecipient;
        $this->emailMessage = $emailMessage;
        $this->emailRecipient = $emailRecipient;
        $this->CnMessage = $CnMessage;
        $this->CnRecipient = $CnRecipient;        
    }
    
    public function send(): string {
        $status = 'testStatus';
        // do something ....
        
        if ($this->sendSMS == true) {
            $smsObj = new SmsNotification($this->smsMessage, $this->smsRecipient);           
            $statusSMS = $smsObj->send();
        }
        if ($this->sendEmail == true) {
            $emailObj = new EmailNotification($this->smsMessage, $this->smsRecipient);  
            $statusEmail = $emailObj->send();
        }
        if ($this->sendCN == true) {
            $CnObj = new CnNotification($this->CnMessage, $this->CnRecipient);  
            $statusCN = $CnObj->send();           
        }        
        
        // evaluate status ...
        
        return $status;
    }
}


interface INotification {
    public function send(): bool; // ok или не-ok
}



abstract class Notification {
    protected $message;
    protected $recipient;
    public function __construct($message, $recipient) {
        $this->message = $message;
        $this->recipient = $recipient;
    }
    public function send(): bool {} // переопределяется в дочерних классах, непосредственно отвечающих за отправку сообщений того или иного типа 
}

class SmsNotification extends Notification {
    public function send(): bool {
        $status = true;
        // send SMS;
            // для теста
            echo ('sms sent<br>');
        return $status;        
    }
}

class EmailNotification extends Notification {
    public function send(): bool {
        $status = true;
        // send Email;
            // для теста
            echo ('e-mail sent<br>');            
        return $status;
    }    
}

class CnNotification extends Notification {
    public function send(): bool {
        $status = true;
        // send via CN
            // для теста
            echo ('via CN sent<br>');
        return $status;
    }
}


// - тест - ok

$test = new SendNotifications(true, false, true, 'smsMessage', 'smsRecipient', 'emailMessage', 'emailRecipient', 'CnMessage', 'CnRecipient');
$test->send();

// на экран должно быть выведено
// sms sent
// via CN sent

