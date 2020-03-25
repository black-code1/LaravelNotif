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

# Section 12 Events

The available events `php artisan event:list`

Make a new event `php artisan make:event ProductPurchased`

Add `public $name` and add `$name` to the _constroctor_ and initialised it `$this->name = $name;` to the ProductPurchased event

Add To the store methode of the PaymentsController

---

public function store()
{

ProductPurchased::dispatch('toy');
// event(new ProductPurchased('toy'));
}

---

### Event processing

-   process the payment
-   unlock the purchase

-   listeners
-   notify the user about the payment
-   award achievements
-   send shareable coupon code

Create a listener `php artisan make:listener AwardAchievements or php artisan make:listener AwardAchievements -e ProductPurchased`

Add `var_dump('check for new achievements');` to the handle method of the AwardAchievements listener

Add `ProductPurchased::class => [AwardAchievements::class,]` to the _EventServiceProvider.php_ at the _\$listen variable_

Run `php artisan make:listener SendShareableCoupon -e ProductPurchased`

Add `var_dump('send shareable coupon');` to the handle method of the SendShareableCoupon listener

Add `ProductPurchased::class => [AwardAchievements::class,SendShareableCoupon::class]` to the _EventServiceProvider.php_ at the _\$listen variable_

Remove the above line and add:

---

public function shouldDiscoverEvents()
{
return true;
}

---

You could try once again

`php artisan make:listener DoOtherThing -e ProductPurchased`

Add `var_dump('do other thing');` to the handle method of the DoOtherThing listener

# Section 13 Authorization

_1.Create seeders to generate test data_ `php artisan make:seeder UsersTableSeeder` NB: userFactory already exist
`php artisan make:seeder ConversationsTableSeeder`

---

public function run()
{

factory(Conversation::class, 3)->create();
}

---

_2.Create factory_ `php artisan make:factory ConversationFactory`

---

$factory->define(Conversation::class, function (Faker $faker) {
return [

'user_id' => factory(User::class)->create(),
'title' => $faker->title,
'body' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
];
});

---

_3.Run migration_ `php artisan migrate:fresh`

_4.Run seeders_ `php artisan db:seed`

_5.If the user can update the current conversations._ We then display the form

---

@can('update-conversation',\$conversation)

<form action="/best-replies/{{ $reply->id }}" method="post">
<button type="submit" class="btn p-0 text-muted">Best Reply?</button>
</form>
@endcan

---

_6.Move to AuthServiceProvider boot function_ to register any authentication / authorization services

Use larave Gate class to get the user who wrote the current conversation.
`update-conversation` is the new key we defined that yo reference in your view previously on the `@can` blade directive

---

Gate::define('update-conversation', function (User $user, Conversation $conversation) {
return $conversation->user->is($user);
});

---

_7.Create a route for the best conversation reply_ `Route::post('best-replies/{reply}', 'ConversationBestReplyController@store');`

---

public function store(Reply $reply)
    {
        // authorize that the current user has permission to set the best reply
        // for the conversation
        $this->authorize('update-conversation', \$reply->conversation);

        // then set it
        $reply->conversation->best_reply_id = $reply->id;
        $reply->conversation->save();

        // redirect back to the conversation page
        return back();
    }

---

for fairly simple application, think about policy classes

_- Create a policy for a conversation_ `php artisan make:policy ConversationPolicy --model=Conversation`

Delete codes in the policy file from create to `use HandlesAuthorization;` without deleting the `use HandlesAuthorization;`

Just leaves this code

---

<?php

namespace App\Policies;

use App\Conversation;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConversationPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can update the conversation.
     *
     * @param  \App\User  $user
     * @param  \App\Conversation  $conversation
     * @return mixed
     */
    public function update(User $user, Conversation $conversation)
    {
        return $conversation->user->is($user);
    }
}
***



Return to your `AuthServiceProvider` and remove the following line
***
 Gate::define('update-conversation', function (User $user, Conversation $conversation) {
            return $conversation->user->is($user);
        });
***


Go Back to the controller
*We reference the policy method as follow* 

***
 public function store(Reply $reply)
    {
        // authorize that the current user has permission to set the best reply
        // for the conversation
        $this->authorize('update', $reply->conversation);

        // then set it
        $reply->conversation->best_reply_id = $reply->id;
        $reply->conversation->save();

        // redirect back to the conversation page
        return back();
    }
***


Go Back to the `Conversation` model and add the following method

***
public function setBestReply(Reply $reply)
    {
        $this->best_reply_id = $reply->id;
        $this->save();
    }
***

Finaly Modifie your controller as follow:

***
class ConversationBestReplyController extends Controller
{
    public function store(Reply $reply)
    {
        
        $this->authorize('update', $reply->conversation);

        $reply->conversation->setBestReply($reply);

        return back();
    }
}
***

Then return to our `Replies view` and modifie the `@can` directive to `@can('update',$conversation)`

and add the following code:

***
<header style="display:flex; justify-content: space-between;">
        <p class="m-0"><strong>{{ $reply->user->name }} said...</strong></p>

        @if ($conversation->best_reply_id === $reply->id)
        <span style="color: green;">Best Reply!!</span>
        @endif
    </header>
***

Move To the `Reply` Model and add the following:

***
public function isBest()
    {
        return $this->id === $this->conversation->best_reply_id;
    }
***

and to the `replies` view:

***
@if ($reply->isBest())
<span style="color: green;">Best Reply!!</span>
@endif
***
