<?php 
namespace App\Service;

class RedisService
{
    public static function client()
    {
        $setting = [
            'scheme'    => 'tcp',
            'host'      => env('GEOREDIS_HOST'),
            'port'      => env('GEOREDIS_PORT'),
            'user'      => env('GEOREDIS_USER'),
            'password'  => env('GEOREDIS_PASS'),
        ];
        
        $client = new \Predis\Client($setting);
        return $client;
    }

    public static function loadpickuppoint()
    {
        $client = RedisService::client();
        $events = \App\RefPickupPoints::all();
        foreach ($events as $v) {
            $response = $client->executeRaw(['GEOADD', redisprevix() . 'pickup', $v->long, $v->lat, $v->id]);
        }
    }

    public static function distance($key, $location1, $location2, $format = "km")
    {
        $client = RedisService::client();
        $response = $client->executeRaw(['GEOADD', redisprevix() . 'loc', $location1->long, $location1->lat, $location1->id]);
        // pre($response);
        $response = $client->executeRaw(['GEOADD', redisprevix() . 'loc', $location2->long, $location2->lat, $location2->id]);
        // pre($response);
        $response = $client->executeRaw(['GEODIST', redisprevix() . 'loc', $location1->id, $location2->id, $format]);
        // pre($response);
        // $response = $client->executeRaw(['GEOSEARCH', redisprevix() . 'loc', 'FROMLONLAT', $location1->long, $location1->lat, 'BYRADIUS', 100, 'km']);
        // pre($response);
        // exit();

        return $response;
    }


    public static function setDriverLocation($lat, $long, $driverid)
    {
        $client = RedisService::client();
        $response = $client->executeRaw(['GEOADD', redisprevix() . 'driverlocation', $long, $lat, $driverid]);
        return $response;
    }

    public static function nearDrivers($lat, $long, $km)
    {
        $client = RedisService::client();
        $response = $client->executeRaw(['GEORADIUS', redisprevix() . 'driverlocation', $long, $lat, (int)$km, "km"]);
        return $response;
    }

    public static function getDriverLocation($driverid)
    {
        $client = RedisService::client();
        $geopost = $client->executeRaw(['GEOPOS', redisprevix() . 'driverlocation', $driverid]);
        $response = [
            'long'  => $geopost[0][0],
            'lat'  => $geopost[0][1],
        ];
        return $response;
    }


    public static function setSpecialPlacePromo($lat, $long, $place_id)
    {
        $client = RedisService::client();
        $response = $client->executeRaw(['GEOADD', redisprevix() . 'specialplace', $long, $lat, $place_id]);
        return $response;
    }

    public static function nearSpecialPlacePromo($lat, $long, $km = 5)
    {
        $client = RedisService::client();
        $response = $client->executeRaw(['GEORADIUS', redisprevix() . 'specialplace', $long, $lat, (int)$km, "km"]);
        return $response;
    }
}
