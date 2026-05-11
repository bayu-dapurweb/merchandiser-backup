<?php 
namespace App\Service;

class AddressBookService
{

    public static function getAddressbook($user_id)
    {
        $addres_list = \App\RefAddressBooks::where("ref_users_id", $user_id)->get();

        foreach ($addres_list as $v) {
            if (empty($v->address)) {
                $v->address = \App\Service\LocationService::getAddress($v->lat, $v->long);
            }
            $v->save();
        }

        return $addres_list;
    }

    public static function findAddressbook($user_id, $id)
    {
        return \App\RefAddressBooks::where("ref_users_id", $user_id)->find($id);
    }

    public static function addAddressbook($user_id, $param)
    {
        $address = new \App\RefAddressBooks;
        $address->ref_users_id = $user_id;
        $address->label = $param['label'];
        $address->lat = $param['lat'];
        $address->long = $param['long'];
        $address->address = \App\Service\LocationService::getAddress($param['lat'], $param['long']);
        $address->save();

        return $address;
    }

    public static function deleteAddressbook($user_id, $id)
    {
        $address = \App\RefAddressBooks::find($id);
        if (empty($address) || $address->ref_users_id != $user_id) {
            return false;
        }
        $address->delete();

        return $address;
        
    }
}
