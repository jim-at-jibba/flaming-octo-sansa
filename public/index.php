<?php
    session_cache_limiter( FALSE );
//    session_start(); //Start session for flash message use.
    date_default_timezone_set( 'UTC' );
    //error_reporting( -1 );
    ini_set( 'display_errors', 1 );
    ini_set( 'display_startup_errors', 1 );

    /**
     * Define some constants
     */
    define("DS", "/");
    define("ROOT", realpath(dirname(__DIR__)) . DS);
    define("VENDORDIR", ROOT . "vendor" . DS);
    define("ROUTEDIR", ROOT . "app" . DS . "routes" . DS);
    define("TEMPLATEDIR", ROOT . "app" . DS . "templates" . DS);


    require '../app/config/config.php';
    require '../vendor/autoload.php';

    // Create app
    $app = new \Slim\Slim( array (
        'templates.path'     => TEMPLATEDIR,
        // Debug is set to false to demonstrate custom error handling
        'debug'              => true,
        // Default identity storage is session storage. You MUST set the
        // following cookie encryption settings if you use the SessionCookie
        // middleware, which this example does
        'cookies.encrypt'    => true,
        'cookies.secret_key' => 'FZr2ucE7eu5AB31p73QsaSjSIG5jhnssjgABlxlVeNV3nRjLt',
    ) );

    // Add the session cookie middleware after auth to ensure it's executed first
    $app->add( new \Slim\Middleware\SessionCookie() );

    // Handle the possible 403 the middleware can throw
    $app->error(
        function ( \Exception $e ) use ( $app ) {
            if ($e instanceof HttpForbiddenException) {
                return $app->render( '403.twig', array ( 'e' => $e ), 403 );
            }
            // You should handle other exceptions here, not throw them
            throw $e;
        }
    );


    // Create monolog logger and store logger in container as singleton
    // (Singleton resources retrieve the same log resource definition each time)
    $app->container->singleton( 'log', function () {
            $log = new \Monolog\Logger( 'ctc-main-site' );
            $log->pushHandler( new \Monolog\Handler\StreamHandler( '../logs/app.log', \Monolog\Logger::DEBUG ) );

            return $log;
        }
    );

    // Prepare view
    $app->view( new \Slim\Views\Twig() );
    $app->view->parserOptions    = array ( 'charset'          => 'utf-8',
                                           'cache'            => realpath( '../templates/cache' ),
                                           'auto_reload'      => TRUE,
                                           'strict_variables' => FALSE,
                                           'autoescape'       => TRUE
    );
    $app->view->parserExtensions = array ( new \Slim\Views\TwigExtension() );

    // Define routes

    //get all
    $app->get( '/', function () use ( $app ) {
            // Sample log message
            $app->log->info( "ctc-main-site '/' route" );
            //Pull data from DB
            //$users = ORM::for_table('users')->find_result_set();
            // Render index view
            $app->render( 'index.twig' );
        }
    );

    /**
     * Include all files located in routes directory
     */
    foreach(glob(ROUTEDIR . '*.php') as $router) {
        require_once $router;
    }

    // Run app
    $app->run();


