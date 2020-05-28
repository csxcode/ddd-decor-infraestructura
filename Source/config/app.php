<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'es',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        /* App Utils */
        Collective\Html\HtmlServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,

        /* Services Providers */
        App\Providers\PermissionsServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

        'Form' => Collective\Html\FormFacade::class,
        'Html' => Collective\Html\HtmlFacade::class,
        'LinkHelper' => App\Helpers\LinkHelper::class,
        'FunctionHelper' => App\Helpers\FunctionHelper::class,
        'DatetimeHelper' => App\Helpers\DatetimeHelper::class,
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Customs Keys
    |--------------------------------------------------------------------------
    |
    */
    'app_name' => 'Decor Infraestructura',
    'paginate_limit' => env('APP_PAGINATE_LIMIT'),
    'paginate_default_per_page' => env('APP_PAGINATE_DEFAULT_PER_PAGE'),
    'paginate_default_page' => env('APP_PAGINATE_DEFAULT_PAGE'),
    'cookie_token_name' => 'decor_infra',
    'url_download_app_android' => '',
    'path_log_api' => storage_path() . '/logs/api/log.log',
    'path_checklist_photos' => storage_path() . '/files/checklist/',
    'path_ticket_photos' => storage_path() . '/files/ticket/',
    'path_woq_files' => storage_path() . '/files/woq/',
    'path_wo_files' => storage_path() . '/files/wo/',
    'path_woh_files' => storage_path() . '/files/woh/',
    'web_url' => env('APP_URL'),
    'web_photo_path' => '/public/{module}/{id}/photos/{guid}',
    'web_video_path' => '/public/{module}/{id}/videos/{guid}',
    'web_file_path' => '/public/{module}/{id}/files/{guid}',
    'utc_offset' => '-05:00', // America/Lima
    'decorcenter_store_id' => 17,

    // Mail Errors Configuration
    'mail_error_host' => env('APP_MAIL_ERROR_HOST'),
    'mail_error_port' => env('APP_MAIL_ERROR_PORT'),
    'mail_error_username' => env('APP_MAIL_ERROR_USERNAME'),
    'mail_error_password' => env('APP_MAIL_ERROR_PASSWORD'),
    'mail_error_encryption' => env('APP_MAIL_ERROR_ENCRYPTION'),

    // Mail Main Configuration
    'mail_main_host' => env('APP_MAIL_MAIN_HOST'),
    'mail_main_port' => env('APP_MAIL_MAIN_PORT'),
    'mail_main_username' => env('APP_MAIL_MAIN_USERNAME'),
    'mail_main_password' => env('APP_MAIL_MAIN_PASSWORD'),
    'mail_main_encryption' => env('APP_MAIL_MAIN_ENCRYPTION'),

    // Mail Main Parameters
    'mail_from' => 'no-reply@decorcenter.pe',

    // Mail Errors Parameters
    'mail_error_from' => 'no-reply@decorcenter.pe',
    'mail_error_from_head' => 'Soporte Decorcenter (Visual Tiendas)',
    'mail_error_to' => ['dd_support@pa.pe'],
    'mail_error_subject' => 'Ha ocurrido un error procesando una solicitud',

    // Mail Completed Checklist Parameters
    'mail_completed_checklist_from_head' => 'Checklist Completado',
    'mail_completed_checklist_subject' => 'Checklist :number fue completado',

    // Mail Approved or Rejected Checklist Parameters
    'mail_appro_rejec_checklist_from_head' => 'Checklist :status',
    'mail_appro_rejec_checklist_subject' => 'El Checklist :number fue :status',

    // [Mail] Work Order Quote Invitation
    'mail_invitation_woq_from_head' => 'Cotizar OT',
    'mail_invitation_woq_subject' => 'Invitación a cotizar en OT # :number',

    // [Mail] Work Order Quote Assignment
    'mail_assignment_woq_from_head' => 'Cotización Aprobada',
    'mail_assignment_woq_subject' => 'Su cotización fue aproba - OT # :number',

    // Work Order params
    'wo_email_terminado_from_head' => 'OT Terminado',
    'wo_email_terminado_subject' => 'OT # :number fue Terminado',

    'wo_email_confirmado_from_head' => 'OT Confirmado',
    'wo_email_confirmado_subject' => 'Visto bueno recibido - OT # :number',

    'wo_email_cerrado_from_head' => 'OT Cerrado',
    'wo_email_cerrado_subject' => 'OT # :number ha sido cerrada',

    'wo_email_reapertura_from_head' => 'OT Reaperturada',
    'wo_email_reapertura_subject' => 'Reapertura de la OT # :number',

    'wo_email_pausado_from_head' => 'OT Pausado',
    'wo_email_pausado_subject' => 'La ejecución de la OT # :number fue pausada',

    'wo_email_inconforme_from_head' => 'OT Inconforme',
    'wo_email_inconforme_subject' => 'Trabajo inconforme en la OT # :number',

    // Work Order Quote params
    'wo_quote_email_submit_from_head' => 'Cotización recibida',
    'wo_quote_email_submit_subject' => 'Cotización recibida :vendor_name - OT # :wo_number',

    // Ticket params
    'ticket_email_confirmed_from_head' => 'Ticket Confirmado',
    'ticket_email_confirmed_subject' => 'Ticket # :ticket_number fue confirmado',

    'ticket_email_annulled_from_head' => 'Ticket Anulado',
    'ticket_email_annulled_subject' => 'Ticket # :ticket_number fue anulado',

    'ticket_email_new_from_head' => 'Nuevo Ticket',
    'ticket_email_new_subject' => 'Nuevo Ticket :ticket_number fue creado',


];
