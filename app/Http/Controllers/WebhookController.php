<?php

namespace App\Http\Controllers;


use App\Mail\OtpRegister;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Sesihook;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{

    private $reply;
    public function __construct()
    {
        $this->reply = new ReplyController;
    }
    
    public function inbox(Request $request){
        $from = $request->from;
        $msg = $request->message;
        $message = strtolower($msg);
        Storage::append('inbox.txt',json_encode(['date'=> now(),'from' => $from,'msg' => $msg]));
        
        if($message === 'info'){
            $name = License::whereCustomerMobile($from)->first()->name;
            return $this->reply->info($name);
        }
        if($message === 'register'){
                return $this->reply->newRegister($from);
        }
        if($message === 'check license'){
            return $this->checkLicense($from);
        }
        if($message === 'activate domain'){
            return $this->activateDomain($from);
        }
        if($message === 'deactivate domain'){
            return $this->deactivateDomain($from);
        }
        if($message === 'upgrade max domain'){
            return $this->upgradeDomain($from);
        }
        if($message === 'order products'){
            return $this->reply->orderProducts();
        }
        if($message === 'dokumentasi'){
            return $this->reply->documentation();
        }
      
        if(Sesihook::whereFrom($from)->count() > 0){
          
            $type = Sesihook::whereFrom($from)->first()->type;
           switch ($type) {
               case 'newregister':
                Sesihook::whereFrom($from)->delete();
                  return $this->processregister($from,$msg);
                break;
               case 'verifemail':
             
                if(substr($message,0,2) === '86'){
                    return $this->checkOtp($from,$message);
                }
                    Sesihook::whereFrom($from)->delete();
                    return json_encode(['text' => 'Invalid OTP, Register dibatalkan! / Invalid OTP, Register cancelled!']);
                break;
                case 'activatedomain' :
                    Sesihook::whereFrom($from)->delete();
                    return $this->processActivateDomain($from,$message);
                break;
                case 'deactivatedomain' :
                    Sesihook::whereFrom($from)->delete();
                    return $this->processDeactivateDomain($from,$message);
                break;
               default:
           
               return json_encode(false);
                   break;
                   
                }
        }
        return json_encode(false);
    }


    public function processregister($from,$msg){
       if(filter_var($msg,FILTER_SANITIZE_EMAIL)){
           Sesihook::whereFrom($from)->delete();
            if(License::whereCustomerEmail($msg)->count() < 1){
                return $this->reply->emailNotRegistered();
            } 
           
            $otp = '86'.random_int(111111,999999);
            Mail::to($msg)->send(new OtpRegister($otp));
            Sesihook::create(['from' => $from, 'type' => 'verifemail','email' => $msg,'otp' => $otp]);
            return $this->reply->otpSent();


       }
       return $this->reply->notValidEmail();
       
    }

    public function checkOtp($from,$message){
           $data = Sesihook::where('from' , $from)->whereOtp($message)->first();
           Log::info('Success initialize '. $data->email);
            if($data->count() > 0){
               $lic = License::whereCustomerEmail($data->email)->first();
               $lic->customer_mobile = $from;
               $lic->save();
               $data->delete();

               return $this->reply->successRegister();
            }
           
            return json_encode(['text' => 'INVALID OTP']);
    }

    public function checkLicense($from){
        $lic = License::whereCustomerMobile($from)->first();
       return $this->reply->checkLicense($lic);
      
    }

    public function activateDomain($from){
        $lic = License::whereCustomerMobile($from)->first();
        if($lic->host === '*'){
            return $this->reply->unlimitedDomain($lic->licensekey);
        }
        $ex = explode(',',$lic->host);
        $total = count($ex);
        if($total >= $lic->max_links){
            return $this->reply->maximalDomain($lic->licensekey);
        }
        Sesihook::create([
            'from' => $from,
            'type' => 'activatedomain',
        ]);
        return json_encode(['text' => 
"Silahkan tulis domain/subdomain/ip yang ingin di aktifkan : 
 --------------------------------
 Please write domain/subdomain/ip that you want to activate :   
"]);
    }


    public function processActivateDomain($from,$message){
        Sesihook::whereFrom($from)->delete();
        if(filter_var($message,FILTER_VALIDATE_DOMAIN) || filter_var($message,FILTER_VALIDATE_IP)){
            $data = License::whereCustomerMobile($from)->first();
            if($data->host === null){
                $data->host = $message;
            } else {
                $data->host = $data->host .','.$message;
            }
            $data->save();
            return $this->reply->successActivateDomain($message);
        }
        return $this->reply->domainNotValid();
    }


    public function deactivateDomain($from){
        $data = License::whereCustomerMobile($from)->first();
        if($data->host === null){
            return json_encode(['text' => 'Gagal menonaktifkan , tidak ada domain aktif di license anda! / Deactivate Failed, there is no active domain in your license!']);
        }
        if($data->host === '*'){
            return json_encode(['text' => 'Gagal menonaktifkan , License anda unlimited domain! / Deactivate Failed, Your license is for unlimited domain!']);
        }
        Sesihook::create(['from' => $from, 'type' => 'deactivatedomain']);
        $text =
'
Domain terdaftar : *'.$data->host.'*
Silahkan ketik yang mau di non aktifkan :
----------------------------
Registered Domain : *'.$data->host.'*    
Write which you want to deactivate.
';
        return json_encode(['text' => $text]);
    }

    public function processDeactivateDomain($from,$msg){
        $data = License::whereCustomerMobile($from)->first();
        $host = explode(',',$data->host);
        if(in_array($msg,$host)){
           
            for ($i=0; $i < count($host); $i++) { 
                if($host[$i] === $msg){
                    unset($host[$i]);
                }
            }
            $newhost = implode(',',$host);
            $data->host = $newhost == '' ? null : $newhost;
            $data->save();
            return $this->reply->successDeactivateDomain($msg);
        } 
        return $this->reply->invalidDeactivateDomain($msg);

    }

    public function upgradeDomain($from) {
$codeUnik = rand(111,999);
$text =
"*Harga* : Rp 120.000 / domain
Jika ingin melanjutkan silahkan transfer
ke rekening berikut sesuai *jumlah domain + kode unik*
*Rekening* : 1671481688 an Ilman Sunanuddin
*Kode Unik* : $codeUnik
";
        return json_encode(["text" => $text]);
    }

    
}
