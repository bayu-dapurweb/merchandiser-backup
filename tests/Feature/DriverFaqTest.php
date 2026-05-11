<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DriverFaqTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        //preparation

        //action
        $response =  $this->getJson("api/driver/faq/list");

        //assert
        $response->assertStatus(200);        
        $response->assert();
    }
}
