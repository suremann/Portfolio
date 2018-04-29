<?php

namespace App\Http\Controllers;

class ViewController extends Controller
{
  public function portfolio()
  {
    return redirect('/portfolio');
  }

  public function popup()
  {
    return view('welcome');
  }
  public function info()
  {
    return view('info');
  }


}
