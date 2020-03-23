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

# Section 11 Notifications

## Database Notifications

_1. Run _ `php artisan notifications:table` and `php artisan migrate`

_2. In PaymentReceived.php modify the via function_ `return ['email', 'database'];`

_3. store methode_ `request()->user()->notify(new PaymentReceived(900))`

_4. Add To PaymentReceived.php_ `protected $amount;` and

---

public function \_\_construct($amount)
    {
        //
        $this->amount = \$amount;
}

---

finaly _toArray_ function add `return ['amount' => $this->amount];`

Give it a test.

Now move on and add

_5. Add To web.php_ `Route::get('notifications', 'UserNotificationsController@show')->middleware('auth');` and create the dedicated controller

_6. Add To the controller created above_

---

public function show()
{
return view('notifications.show');
}

---

_7. Create a view called show.blade.php_ add the following code to it

---

 <ul>
        @forelse ($notifications as $notification)
            <li>
              @if ($notification->type === 'App\Notifications\PaymentReceived')
                We have received a payment of ${{ $notification->data['amount'] / 100 }} from you.
              @endif
            </li>

        @empty
          <li>You have no unread notifications at this time.</li>
        @endforelse
      </ul>

---

_8. Add to the controller_ the following code

---

public function show()
{
return view('notifications.show',[
'notifications' => tap(auth()->user()->unreadNotifications)->markAsRead()
]);
}

---

OR

---

public function show()
{
\$notifications = tap(auth()->user()->unreadNotifications)->markAsRead();

return view('notifications.show',[
'notifications' => $notifications
]);
}

---

## Send SMS Notifications in 5 Minutes

_1. Subscribe to nexmo by following the link provide in the laravel documentation_

_2. Run the command_ `composer require laravel/nexmo-notification-channel`

_3. Add to the .env file_ `NEXMO_KEY` and `NEXMO_SECRET`

_4. Add the following to config/service.php_

---

'nexmo' => [
'sms_from' => 'your phone number',
],

---

_5. Add To PaymentReceived.php the following_
`return ['mail', 'database', 'nexmo']` to the _via methode_

_6. Add the following code channel to PaymentRecieved.php_

---

public function toNexmo(\$notifiable)
{
return (new NexmoMessage())
->content('shadow did not meet shadow master because of their strength in coding!, lol Mon message laravel');
}

---

_7. Add To your user model_ the following code

---

public function routeNotificationForNexmo(\$notifiable)
{
return 'your phone number'
}

---
