<?php

namespace App\Http\Controllers;

use App\Notifications\PaymentReceived;
// use Illuminate\Support\Facades\Notification;
use App\Events\ProductPurchased;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    //
    public function create()
    {
        return view('payments.create');
    }

    // public function store()
    // {
    //     // Notification::send(request()->user(), new PaymentReceived());
    //     request()->user()->notify(new PaymentReceived(90));
    // }
  
    // Managing event
    public function store()
    {
       
        ProductPurchased::dispatch('toy');
        // event(new ProductPurchased('toy'));
    }
}
