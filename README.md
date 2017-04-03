## Inforu SMS Class
This class create for Inforu SMS Service

## How to use?
- $inforu = new \Sidox\SMS\Inforu($username, $password, $sender);
- $msg = 'Your sms msg';
- //$nums = 'num1;num2...numN'; //demo1 nums
- $nums = ['num1', 'num2', ... 'numN']; //demo2 nums
- $inforu->createMessage($msg,$nums)->sendSMS();
