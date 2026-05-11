<?php 
namespace App\Service;

class SampleDataService
{
    public static function getTours()
    {
        $tours[] = [
            'id' => 1, 
            'title' => 'Petualangan di Gunung Merapi', 
            'slug' => slug('Petualangan di Gunung Merapi Yogyakarta'),
            'thum_image' => uri('kawan/sample/photo-1612633375878-1e06ec7f5b33.avif'),
            'location' => 'Kali Gendol, Yogyakarta',
            'body' => '',
        ];
        $tours[] = [
            'id' => 1, 
            'title' => 'Mendaki Menembus Awan di Puncak Gunung Bromo', 
            'slug' => slug('Mendaki Menembus Awan di Puncak Gunung Bromo'),
            'thum_image' => uri('kawan/sample/photo-1565619109666-b8bfe0e95ceb.avif'),
            'location' => 'Bromo, Jawa Timur'
        ];
        $tours[] = [
            'id' => 1, 
            'title' => 'Menjelajahi Pulau Dewata Bali', 
            'slug' => slug('Menjelajahi Pulau Dewata Bali'),
            'thum_image' => uri('kawan/sample/photo-1537996194471-e657df975ab4.avif'),
            'location' => 'Danau Bratan, Bali'
        ];
        

        $tours[] = [
            'id' => 1, 
            'title' => 'Indahnya Pantai Karang Pasir Putih', 
            'slug' => slug('Indahnya Pantai Karang Pasir Putih'),
            'thum_image' => uri('kawan/sample/photo-1636995552231-7741d3bb8f3d.avif'),
            'location' => 'Gunung Kidul, Yogyakarta'
        ];
        $tours[] = [
            'id' => 1, 
            'title' => 'Menaklukkan Ombak di Lombok', 
            'slug' => slug('Menaklukkan Ombak di Lombok'),
            'thum_image' => uri('kawan/sample/photo-1588443193679-80a2c1331247.avif'),
            'location' => 'Gili, Lombok'
        ];
        $tours[] = [
            'id' => 1, 
            'title' => 'Candi Hindu Terbesar di Dunia', 
            'slug' => slug('Candi Hindu Terbesar di Dunia'),
            'thum_image' => uri('kawan/sample/premium_photo-1700954824012-08ce5362e6c6.avif'),
            'location' => 'Prambanan, Yogyakarta'
        ];

        return $tours;
    }

    public static function getCars()
    {
        $cars[] = [
            'id' => 1,
            'slug' => 'toyota-avanza',
            'image' => 'kawan/sample/cars/new-avanza.png',
            'title' => 'Toyota Avanza',
            'price' => 650000,
            'benefits' => [
                'BBM',
                'Driver',
                'Kapasitas 7 Penumpang',
                'Object Wisata Yogyakarta',
                'Manual / Matik',
            ]
        ];
        $cars[] = [
            'id' => 1,
            'slug' => 'toyota-hiace-commuter',
            'image' => 'kawan/sample/cars/hiace-komputer.png',
            'title' => 'Toyota HIACE COMMUTER',
            'price' => 1300000,
            'benefits' => [
                'BBM',
                'Driver',
                'Kapasitas 13 Sheet (Belakang)',
                'Kapasitas 1 Sheet (Depan)',
                'Object Wisata Yogyakarta',
                'Full Audio Karaoke',
                'Manual / Matik',
            ]
        ];
        $cars[] = [
            'id' => 1,
            'slug' => 'toyota-hiace-premio',
            'image' => 'kawan/sample/cars/hiace-premio.png',
            'title' => 'Toyota HIACE PREMIO',
            'price' => 1600000,
            'benefits' => [
                'BBM',
                'Driver',
                'Kapasitas 13 Sheet (Belakang)',
                'Kapasitas 1 Sheet (Depan)',
                'Object Wisata Yogyakarta',
                'Full Audio Karaoke',
                'Manual / Matik',
            ]
        ];
        $cars[] = [
            'id' => 1,
            'slug' => 'toyota-innova',
            'image' => 'kawan/sample/cars/new-innova.png',
            'title' => 'Toyota Innova',
            'price' => 800000,
            'benefits' => [
                'BBM',
                'Driver',
                'Kapasitas 7 Penumpang',
                'Object Wisata Yogyakarta',
                'Manual / Matik',
            ]
        ];
        $cars[] = [
            'id' => 1,
            'slug' => 'isuzu-elf-long',
            'image' => 'kawan/sample/cars/isuzu-long-elf.png',
            'title' => 'Isuzu Elf Long',
            'price' => 1550000,
            'benefits' => [
                'BBM',
                'Driver',
                'Kapasitas 17 Sheet (Belakang)',
                'Kapasitas 1 Sheet (Depan)',
                'Object Wisata Yogyakarta',
                'Full Audio Karaoke',
                'Manual / Matik',
            ]
        ];
        
        $cars[] = [
            'id' => 1,
            'slug' => 'toyota-new-fortuner',
            'image' => 'kawan/sample/cars/new-fortuner.png',
            'title' => 'Toyota New Fortuner',
            'price' => 1500000,
            'benefits' => [
                'BBM',
                'Driver',
                'Kapasitas 7 Penumpang',
                'Object Wisata Yogyakarta',
                'Manual / Matik',
            ]
        ];
        

        return $cars;
    }
}