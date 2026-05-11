<?php

namespace App\Http\Controllers\Fe;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function home()
    {
        $data = [];
        
        $cars = \App\Service\SampleDataService::getCars();

        $tours = \App\TrxPosts::where('post_type', 'tour')->limit(6)->get()->map(function($r){
            return [
                'slug' => $r->slug,
                'title' => $r->title,
                'location' => $r->meta()->sub_title,
                'thum_image' => uri($r->thum_image)
            ];
        });
        $data['cars'] = $cars;
        $data['tours'] = $tours;
        return view('fe/pages/home', $data);
    }

    public function aboutus()
    {
        $data = [];
        return view('fe/pages/about', $data);
    }

    public function cars()
    {
        $data = [];
        $cars = \App\Service\SampleDataService::getCars();
        $data['cars'] = $cars;
        return view('fe/pages/cars', $data);
    }

    public function tours()
    {
        $data = [];
        $tours = \App\TrxPosts::where('post_type', 'tour')->limit(6)->get()->map(function($r){
            return [
                'slug' => $r->slug,
                'title' => $r->title,
                'location' => $r->meta()->sub_title,
                'thum_image' => uri($r->thum_image)
            ];
        });
        $data['tours'] = $tours;
        return view('fe/pages/tours', $data);
    }

    public function tourdetail($slug)
    {
        $data = [];
    
        $tour = \App\TrxPosts::where([
            ['post_type', 'tour'],
            ['slug', $slug]
        ])->first()->toArray();

        $tour['location'] = get_post_meta('sub_title', $tour['meta']);
        $tour['seo'] = json_decode($tour['seo'], true);
        $data['tour'] = $tour;

        return view('fe/pages/tour', $data);
    }

    public function sitemap()
    {
        $data['sites'] = [];
        $data['sites'][] = [
            'link' => route('get.home'),
            'time' => date("Y-m-d H:i:s")
        ];
        $data['sites'][] = [
            'link' => route('get.about-us'),
            'time' => date("Y-m-d H:i:s")
        ];
        $data['sites'][] = [
            'link' => route('get.cars'),
            'time' => date("Y-m-d H:i:s")
        ];
        $data['sites'][] = [
            'link' => route('get.tours'),
            'time' => date("Y-m-d H:i:s")
        ];

        $tours = \App\TrxPosts::where('post_type', 'tour')->get();
        foreach ($tours as $v) {
            $data['sites'][] = [
                'link' => route('get.tour.detail', ['slug' => $v->slug]),
                'time' => date("Y-m-d H:i:s")
            ];
        }

        return response()->view('fe/pages/sitemap', $data)->header('Content-Type', 'text/xml');
    }
}
