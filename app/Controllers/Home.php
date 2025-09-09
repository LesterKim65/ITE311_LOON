<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('index'); // loads app/Views/template.php
    }
    public function about()
    {
        return view('about'); // loads app/Views/template.php
    }   public function contact()
    {
        return view('contact'); // loads app/Views/contact.php
    }

}
