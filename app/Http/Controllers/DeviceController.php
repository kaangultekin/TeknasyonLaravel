<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Device;
use Illuminate\Support\Facades\Hash;


class DeviceController extends Controller
{
    public function register(Request $request){
        //boş bir sonuç dizisi tanımlandı.
        $result = [];
        //Gönderilen alanları zorunlu kıldık. Boş ise işlem engellendi.
        if(empty($request->uid) or empty($request->appId) or empty($request->language) or empty($request->operatingSystem)){
            $result = [
                'register'  => 'NO',
                'message'   => 'Boş veya eksik parametre gönderdiniz.'
                ];
            return $result;
        }
        // device tablosunda, gelen uid var mı kontrol edildi.
        $device = DB::table('device')
        ->where('uid', $request->uid)
        ->where('appId', $request->appId)
        ->first();
        // Geçerli bir token oluşturuldu.
        $token = hash::make($request->uid.$request->appId.time());
        // Kayıt Var ise
        if(!empty($device)){
            // Kayıt var ise geçerli bir token atanmış demektir. Gönderilen token ile kayıtlı token eşleşmelidir.
            if($device->token == $request->token){
                $updateToken = Device::where('uid', $request->uid)
                    ->where('appId', $request->appId)
                    ->update(['token' => $token]);

                if($updateToken){ // Token Güncelleme başarılı ise
                    $result = [
                        'register' => 'OK',
                        'token'    => $token
                    ];
                } else { // Token Güncelleme başarısız ise
                    $result = [
                        'register' => 'NO',
                        'message'  => 'Token güncellenemedi. İşleme devam edilemiyor.'
                    ];
                }

            } else { // Token eşlemiyor ise işleme devam edilemez.
                $result = [
                    'register' => 'NO',
                    'message'  => 'Token eşleştirilemedi.'
                ];
            }
        } else { // Kayıt Yoksa ise
            //device tablosu çağırıldı.
            $device = new Device;
            // Kaydetme işlemi yapıldı.
            $device->uid             = $request->uid;
            $device->appId           = $request->appId;
            $device->language        = $request->language;
            $device->operatingSystem = $request->operatingSystem;
            $device->token           = $token;
            //Kaydetme başarılı ise
            if($device->save()){
                $result = [
                    'register' => 'OK',
                    'token'    => $token
                ];
            } else { // Kaydetme başarılı değil ise
                $result = [
                    'register' => 'NO',
                    'message'  => 'Kaydetme işlemi gerçekleşmedi.'
                ];
            }
        }
        return $result;
    }
}
