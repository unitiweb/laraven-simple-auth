## Token-Auth Library

### Install

### Configuration

1st you'll need to add the token_auth.php file in your `/config` folder. 
It is located in the `Config` folder inside this library.

The `token_auth.php` file is self documented on the settings, so I won't go over them here.

**Database Migration**

This library requires a tokens table. The migration file should contain this:

```php
Schema::create('tokens', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->bigInteger('user_id');
    $table->string('token_type', 30); // example: access | refresh | email | etc...
    $table->text('token');
    $table->dateTime('expires_at');
    $table->timestamps();
});
```

### Usage

#### Login

To login and get your access and refresh tokens you simply need to do this.

```php
$credentials = new Credentials('myusername', 'mypassword');
$tokenAuth = new TokenAuth();
$tokens = $tokenAuth->authenticate($credentials);
```

You could then return something like this as your response.

```php
return response()->json([
    'user' => new UserResource($tokenAuth->getUser()),
    'tokens' => [
        'access' => [
            'ttl' => $tokens->getAccessToken()->getTtl(),
            'token' => $tokens->getAccessToken()->getToken(),
        ],
        'refresh' => [
            'ttl' => $tokens->getRefreshToken()->getTtl(),
            'token' => $tokens->getRefreshToken()->getToken(),
        ],
    ],
]);
```

#### Authorization

Anytime a secured endpoint is called it will need to include an `Authorization` header with `Bearer {access token}`

In order to have your api have secure endpoints you will need to do the following.

**In your `bootstrap/app.php` file**

Uncomment the auth middleware.

```php
 $app->routeMiddleware([
     'auth' => App\Http\Middleware\Authenticate::class,
 ]);
```

You may also want to uncomment the facade and eloquent features.

```php
$app->withFacades();
$app->withEloquent();
```

Uncomment the AuthServiceProvider

```php
$app->register(App\Providers\AuthServiceProvider::class);
```

Now, your `AuthServiceProvider.php` should look something like this.

```php
$this->app['auth']->viaRequest('api', function ($request) {
    if ($token = $request->header('authorization')) {
        return (new AccessToken)->validate($token);
    }
});
```

We say that if a request uses the `auth` middleware we need to get the 
jwt token from the `Authorization` header and validate it.

The `(new AccessToken)->validate($token)` will return either the authenticated user model or null.

Lastly, in your routes file `routes/web.php` you will add a group that will contain all the secured routes.

```php
$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/test1', function () use ($router) {});
    $router->get('/test2', function () use ($router) {});
    $router->get('/test3', function () use ($router) {});
});
```

#### Refresh Token

When your access token is about ready to expire (you can know this by tracking the token ttl time) you will need to 
refresh the authentication using the refresh token given during initial login.

All you need to do is use the refresh token to request new access and refresh tokens. When this is done the previous refresh token will be removed.

```php
$tokenAuth = new TokenAuth;
$tokens = $tokenAuth->refresh($refreshToken);
```

#### Simple Tokens

Simple tokens can be used for verifying emails, registration, or what ever you'd like to do.

Here's the process

**Create a Simple Token**

First, you will need to make sure the simple token you want to use is configured in the `token_auth.php` config file.

The example will create a verification code 8 characters long.

- **max**: is the max number of concurrent tokens for a single user that is allowed. In this case only 1 at a time. If another code is created the previous code will be deleted.
- **expires**: is the number of seconds before the code will expire. In this case it will be `60 * 5` which is 300 seconds or 5 minutes. 
- **algorithm**: the encoding type. In this case is an 8 character random code. you may also use `uuid`, or any compatible `hash` algorithm.

```php
'simple_tokens' => [
    'email_verify' => [
        'max' => 1,
        'expires' => 60 * 5,
        'algorithm' => 'code:8',
    ]
],
```

You can create a simple token and send it to your users where they can verify it later
to perform what ever task you need them to.

```php
// Get the user (this must be the same model used to authenticate with
$user = User::where('id', 1)->first();

// Instantiate the SimpleToken with the current user and the token type configured in the config file.
$simple = new Simpletoken($user, 'email_verify');

// Generate a token and store it in the database
$token = $simple->generate();
```

This will return the Token database model `TokenAuth\Models\Token`. 
You can get the token by calling `$token->token`;

