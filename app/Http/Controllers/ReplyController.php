<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Sesihook;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function notRegistered(){
        $buttons = [
            ['buttonId' => 'id2', 'buttonText' => ['displayText' => 'REGISTER'], 'type' => 1], // button 2
        ];
        $text = 
'
Maaf, Nomor anda tidak terdaftar di sistem kami, Jika anda merasa sudah order dan terdaftar , silahkan kaitkan nomor anda dengan klik *REGISTER* 
---------------------------------------
Sorry, Your number not registered in our system, if you feel you have register ,please link your number by click *REGISTER*
    ';
        $message = [
            "text" => $text,
            "footer" => "https://m-pedia.id | 6282298859671",
            "buttons" => $buttons,
           
           ];
           return json_encode($message);
    }

    public function newRegister($sender){
       
       if(License::whereCustomerMobile($sender)->count() < 1){
           Sesihook::create(['from' => $sender,'type' => 'newregister']);
           
$text = 
'
Anda akan mengaitkan nomor anda, *'.$sender.'* Silahkan tulis email anda :
-------------------------------------
You will link your number, *'.$sender.'* Please write your email :
';

        return json_encode(["text" => $text]);
    
    }
    }

    public function emailNotRegistered(){
        $text =
'
Maaf, email tidak terdaftar di system kami.
-----------------------------
Sorry, your email not registered in our system.
' ;
return json_encode(['text' => $text]);       
    }


    public function notValidEmail(){
        $text =
'
Email tidak valid!
----------------
Invalid email!
' ;
return json_encode(['text' => $text]);       
    }


    public function otpSent(){
        $text = 
'
Kami telah mengirim kode ke email anda, silahkan ketik kode otp tersebut :
---------------------------------
We have sent the otp to your email, please type it :
';       

return json_encode(['text' => $text]);
    }

public function orderProducts(){

     $templateButtons = [
       ["index"=> 1, "urlButton"=>["displayText"=> '⭐ Source Code!', "url"=> 'https://m-pedia.id']],
       ["index"=> 2, "urlButton"=>["displayText"=> '⭐ Followers Sosial Media!', "url"=> 'https://smm.m-pedia.id']],
       ["index"=> 3, "callButton"=>["displayText"=> 'Call me!', "phoneNumber"=> '+6282298859671']],
      // ["index"=> 4, "quickReplyButton"=>["displayText"=> 'This is a reply, just like normal buttons!', "id" => 'id-like-buttons-message']]
     ];
     
    
   $templateMessage =[
        "text"=> "Beberapa layanan dan produk tersedia di kami, yuk check",
        "footer"=> 'm pedia',
        "templateButtons"=> $templateButtons
   ];
   return json_encode($templateMessage);
}
    public function info($name){
        $sections = [
            [ // start list
               "title" => "License Management",
               "rows" => [
               ["title" => "Check License", "rowId" => "l1", "description" => "Check your info license"],
               ["title" => "Activate Domain", "rowId" => "l2", "description" => "Input domain to license"],
               ["title" => "Deactivate Domain", "rowId" => "l3", "description" => "delete domain from license"],
            ]
               ],
            [ // start list
               "title" => "Other",
               "rows" => [
              // ["title" => "Info Account ", "rowId" => "l4", "description" => "Check your account info"],
               ["title" => "Order Products", "rowId" => "l5", "description" => "Many products are available for you!"],
                ]
            ]
            
            ];
            
        $text =
    '
    Hallo *'.$name.'* 
    Terimakasih telah menghubungi M Pedia, berikut menu yang tersedia :
    ----------------------------------------
    Hallo *'.$name.'* 
    Thanks for contacting M Pedia, The following menus are available :
    ';
    
    $msg = [
    "text" => $text,
    "title" => "GOOD!",
    "footer" => "https://m-pedia.id | 6282298859671",
    "buttonText" => "Lists Menu",
    "sections" => $sections
    ];
    return json_encode($msg);
    }

public function checkLicense($lic){
    $sections = [
        [ // start list
           "title" => "License Management",
           "rows" => [
           ["title" => "Activate Domain", "rowId" => "l2", "description" => "Input domain to license"],
           ["title" => "Deactivate Domain", "rowId" => "l3", "description" => "delete domain from license"],
        ]
           ]
        
        ];
        
    $host = $lic->host === '*' ? 'Unlimited' : $lic->host;
    $text =
'
*Customer Name* : '. $lic->customer_name .'
*Customer Email* : '. $lic->customer_email .'
*License Key* : '. $lic->licensekey .'
*Type* : '. $lic->type .'
*Host* : '. $host .'
';
    
    $msg = [
        "text" => $text,
        "title" => "LICENSE INFORMATION",
        "footer" => "https://m-pedia.id | 6282298859671",
        "buttonText" => "Lists Menu",
        "sections" => $sections
        ];
        return json_encode($msg);
    
}

    public function unlimitedDomain($licensekey){
        $text =
'
License kamu *'.$licensekey.'* Adalah unlimited license, tidak perlu aktivasi.
---------------------------------
Your License *'.$licensekey.'* is unlimited license,no need to activate.
';

return ['text' => $text];
    }


    public function maximalDomain($licensekey){
        $text =
        '
License kamu *'.$licensekey.'* Sudah terbatas (3 subdomain/domain), silahkan non aktifkan salah satu terlebih dahulu.
---------------------------------
Your License *'.$licensekey.'* is limited (3 subdomain/domain),please deactive one of them first.
        ';  
        return ['text' => $text];
    }
    public function domainNotValid(){
        $text =
'
Invalid domain /subdomain /ip! , Aktivasi Dibatalkan!
-------------------------
Invalid domian /subdomain /ip!, Activate canceled!
';  
        return ['text' => $text];
    }


    public function successActivateDomain($msg){
        $buttons = [
            ['buttonId' => 'id2', 'buttonText' => ['displayText' => 'Check License'], 'type' => 1], // button 2
        ];
        $text =
'
Berhasil Aktivasi domain *'. $msg .'* Klik check license untuk melihat informasi license.
-----------------------------
Success Activate domain *'. $msg.'* Tap check license to view your license information.
';
$message = [
    "text" => $text,
    "footer" => "https://m-pedia.id | 6282298859671",
    "buttons" => $buttons,
   
   ];
   return json_encode($message);

    }

    public function successRegister(){
        $buttons = [
            ['buttonId' => 'id2', 'buttonText' => ['displayText' => 'INFO'], 'type' => 1], // button 2
        ];
        $text =
'
Berhasil, Registrasi berhasil dan nomor sudah dikaitkan
-----------------------------
Success, Done register and the number has linked.
';
$message = [
    "text" => $text,
    "footer" => "https://m-pedia.id | 6282298859671",
    "buttons" => $buttons,
   
   ];
   return json_encode($message);

    }

    public function successDeactivateDomain($msg){
        $buttons = [
            ['buttonId' => 'id2', 'buttonText' => ['displayText' => 'Check License'], 'type' => 1], // button 2
        ];
        $text =
'
Domain *'. $msg .'* Berhasil dihapus,Klik check license untuk melihat informasi license.
-----------------------------
Domain *'. $msg.'* Deleted,  Tap check license to view your license information.
';
$message = [
    "text" => $text,
    "footer" => "https://m-pedia.id | 6282298859671",
    "buttons" => $buttons,
   
   ];
   return json_encode($message);

    }
    public function invalidDeactivateDomain($msg){
        $buttons = [
            ['buttonId' => 'id2', 'buttonText' => ['displayText' => 'INFO'], 'type' => 1], // button 2
        ];
        $text =
'
Domain *'. $msg .'* tidak terdaftar di license anda, nonaktif dibatalkan.
-----------------------------
Domain *'. $msg.'* Not registered in your license,  Deactivate canceled.
';
$message = [
    "text" => $text,
    "footer" => "https://m-pedia.id | 6282298859671",
    "buttons" => $buttons,
   
   ];
   return json_encode($message);

    }
}
