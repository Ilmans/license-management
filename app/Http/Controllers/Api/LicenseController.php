<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
class LicenseController extends Controller
{
    
   


    public function issue(Request $request){
       $validator = Validator::make($request->all(),[
            'product_id' => ['required',''],
            'licensekey' => ['required','min:10'],
            'customer_email' => ['required','email','unique:licenses' ],
            'customer_name' => ['required','min:4'],
            'customer_mobile' => ['required'],
            'type' => ['required','in:lifetime,trial,duration'], 
            'purchase_code' => ['required'],
        ]);

     if($validator->fails()){
         return response()->json([
             'status' => Response::HTTP_BAD_REQUEST ,
             'data' => [
                 'message' => 'Something wrong in your request',
                 'errors' => $validator->errors(),
             ],
         ],Response::HTTP_BAD_REQUEST);
     }



     License::create($request->all());
     return response()->json([
         'status' => Response::HTTP_ACCEPTED,
            'data' => [
                'message' => 'License issued!',
                'license_info' => $request->all(),
            ],
        ],Response::HTTP_ACCEPTED);



    }

    public function activate(Request $request){
       
        $validator = Validator::make($request->all(),[
            'licensekey' => ['required'],
            'email' => ['required'], 
            'host' => ['required'],
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST ,
                'data' => [
                    'message' => 'Something wrong in your request',
                    'errors' => $validator->errors(),
                ],
            ],Response::HTTP_BAD_REQUEST);
        }

      $check = License::whereCustomerEmail($request->email)->first();



    
      if(!$check){
        return response()->json([
            'status' => Response::HTTP_BAD_REQUEST ,
            'data' => [
                'message' => 'Your email not match with your license',
            ],
        ]);
      }

      if($check->licensekey !== $request->licensekey){
        return response()->json([
            'status' => Response::HTTP_BAD_REQUEST ,
            'data' => [
                'message' => 'Your email not match with your license 2',
            ],
        ]);
      }

      if($check->host === '*'){
        return response()->json([
            'status' => Response::HTTP_ACCEPTED ,
            'data' => [
                'message' => 'Your host successfully added to this license',
                'new_host' => $request->host,
                'all_host' => 'unlimited'
            ],
        ]);
      }

      if($check->host === null){
          $check->host = $request->host;
          $check->save();
          return response()->json([
            'status' => Response::HTTP_ACCEPTED ,
            'data' => [
                'message' => 'Your host successfully added to this license',
                'new_host' => $request->host,
                'all_host' => $check->host
            ],
        ]);
      } else {

        $host = explode(',',$check->host);
       

        if(in_array($request->host,$host)){
            return response()->json([
                'status' => Response::HTTP_ACCEPTED ,
                'data' => [
                    'message' => 'Your host successfully added to this license',
                    'new_host' => $request->host,
                    'all_host' => $check->host
                ],
            ]);
        }

        if(count($host) >= 3){
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST ,
                'data' => [
                    'message' => 'Your Host / domain already limited,contact admin to add!',
                ],
            ]);
        }

        $check->host = $check->host .','.$request->host;
        $check->save();
        return response()->json([
            'status' => Response::HTTP_ACCEPTED ,
            'data' => [
                'message' => 'Your host successfully added to this license',
                'new_host' => $request->host,
                'all_host' => $check->host
            ],
        ]);

      }

    }


    public function check(Request $request){

        $check = License::where('licensekey',$request->licensekey);
        if($check->count() > 0){
            $data = $check->first();
           // return $data->host;
            if($data->host === '*'){
                return response()->json([
                    'status' => 200,
                    'data' => [
                        'message' => 'License Is Valid',
                    ],
                ],200);
            }
         //   return
            $host = explode(',',$data->host);
         //   return $request->host;
            
            if(!in_array($request->host,$host)){
                return response()->json([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'data' => [
                        'message' => 'Invalid license',
                    ],
                ],Response::HTTP_UNAUTHORIZED);
            }
            return response()->json([
                'status' => 200,
                'data' => [
                    'message' => 'License Is Valid',
                ],
            ],200);
        }

        return response()->json([
            'status' => Response::HTTP_BAD_REQUEST ,
            'data' => [
                'message' => 'License Not Valid!',
              
            ],
        ],Response::HTTP_BAD_REQUEST);
    }

}
