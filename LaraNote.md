# Section 10 Mail

## Notifications Versus Mailables

_1. Set authentication system_ `composer require laravel/ui:^1.0 --dev`

_2. Define the following route in the web.php_

---

Route::view('/', 'welcome');

Route::get('payments/create', 'PaymentsController@create')->middleware('auth');

Route::post('payments', 'PaymentsController@store')->middleware('auth');

Auth::routes();

---

Then move to the PaymentsController and add The following code

---

use App\Notifications\PaymentReceived;
use Illuminate\Support\Facades\Notification;

public function create(){
return view('payments.create');
}

public function store(){
Notification::send(request()->user(), new PaymentReceived());
}

---

### Create A Notification

`php artisan make:notification PaymentReceived`

You are ready now to get the user notified.

In the _PaymentReceived.php_ you could chain `->greeting("What's Up?")` to the _toMail_ function where each `line` represents a paragraph

`->subject('Your Laracasts Payment Was Received')`

To notify One `User`, Instead of `Notification::send(request()->user(), new PaymentReceived());` You could use `request()->user()->notify(new PaymentReceived())` in the store function of the dedicated controller
