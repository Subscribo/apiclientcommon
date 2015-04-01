<?php namespace Subscribo\ApiClientCommon\Integration\Laravel;

use Illuminate\Routing\Router;
use Subscribo\Support\Arr;
use Subscribo\Support\ServiceProvider;

/**
 * Class ApiClientCommonServiceProvider
 *
 * @package Subscribo\ApiClientCommon
 */
class ApiClientCommonServiceProvider extends ServiceProvider
{

    protected $defer = false;

    protected $routesRegistered = false;

    public function register()
    {
        $this->app->register('Subscribo\\RestClient\\Integration\\Laravel\\RestClientServiceProvider');
    }

    public function boot()
    {
        $packageDir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        $this->loadViewsFrom($packageDir.'/resources/views', 'subscribo');
        $this->publishes([
            $packageDir.'/resources/views/apiclientcommon/' => base_path('resources/views/vendor/subscribo/apiclientcommon/'),
        ], 'view');

        $this->app->make('view')->composer('subscribo::apiclientcommon.questionary', 'Subscribo\\ApiClientCommon\\ViewComposers\\QuestionaryComposer');
        $this->registerTranslationResources('messages');
    }



    public function registerRoutes(Router $router, array $middleware, array $paths = array())
    {
        if ($this->routesRegistered) {
            return;
        }
        $defaultPaths = [
            'subscribo.serverRequest.questionary' => '/question',
            'subscribo.serverRequest.clientRedirect' => '/redirectback/{hash?}',
            'subscribo.generic.questionary' => '/question/{type}',
            'subscribo.generic.redirection' => '/redirection/{type}',
        ];
        $paths = Arr::mergeNatural($defaultPaths, $paths);

        $router->get($paths['subscribo.serverRequest.questionary'], ['as' => 'subscribo.serverRequest.questionary', 'middleware' => $middleware, 'uses' => '\\Subscribo\\ApiClientCommon\\Controllers\\QuestionaryController@getQuestionaryFromSession']);
        $router->post($paths['subscribo.serverRequest.questionary'], ['middleware' => $middleware, 'uses' => '\\Subscribo\\ApiClientCommon\\Controllers\\QuestionaryController@postQuestionary']);

        $router->get($paths['subscribo.serverRequest.clientRedirect'], ['as' => 'subscribo.serverRequest.clientRedirect', 'middleware' => $middleware, 'uses' => '\\Subscribo\\ApiClientCommon\\Controllers\\ClientRedirectionController@getClientRedirectionRedirectingBack'])->where(['hash' => '[A-Za-z0-9]+']);

        $router->get($paths['subscribo.generic.questionary'], ['as' => 'subscribo.generic.questionary', 'middleware' => $middleware, 'uses' => '\\Subscribo\\ApiClientCommon\\Controllers\\QuestionaryController@getQuestionaryByType'])->where(['type' => '[A-Za-z0-9]+']);
        $router->post($paths['subscribo.generic.questionary'], ['middleware' => $middleware, 'uses' => '\\Subscribo\\ApiClientCommon\\Controllers\\QuestionaryController@postQuestionary']);

        $router->get($paths['subscribo.generic.redirection'], ['as' => 'subscribo.generic.redirection', 'middleware' => $middleware, 'uses' => '\\Subscribo\\ApiClientCommon\\Controllers\\ClientRedirectionController@getRedirectionByType'])->where(['type' => '[A-Za-z0-9]+']);

        $this->routesRegistered = true;
    }

}
