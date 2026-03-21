import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
const Controller980bb49ee7ae63891f1d891d2fbcf1c9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

Controller980bb49ee7ae63891f1d891d2fbcf1c9.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9.url = (options?: RouteQueryOptions) => {
    return Controller980bb49ee7ae63891f1d891d2fbcf1c9.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
const Controller980bb49ee7ae63891f1d891d2fbcf1c9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/'
*/
Controller980bb49ee7ae63891f1d891d2fbcf1c9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller980bb49ee7ae63891f1d891d2fbcf1c9.form = Controller980bb49ee7ae63891f1d891d2fbcf1c9Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/weddings'
*/
const Controller25be3eace7e6c519633939c937264249 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller25be3eace7e6c519633939c937264249.url(options),
    method: 'get',
})

Controller25be3eace7e6c519633939c937264249.definition = {
    methods: ["get","head"],
    url: '/weddings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/weddings'
*/
Controller25be3eace7e6c519633939c937264249.url = (options?: RouteQueryOptions) => {
    return Controller25be3eace7e6c519633939c937264249.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/weddings'
*/
Controller25be3eace7e6c519633939c937264249.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller25be3eace7e6c519633939c937264249.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/weddings'
*/
Controller25be3eace7e6c519633939c937264249.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller25be3eace7e6c519633939c937264249.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/weddings'
*/
const Controller25be3eace7e6c519633939c937264249Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller25be3eace7e6c519633939c937264249.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/weddings'
*/
Controller25be3eace7e6c519633939c937264249Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller25be3eace7e6c519633939c937264249.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/weddings'
*/
Controller25be3eace7e6c519633939c937264249Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controller25be3eace7e6c519633939c937264249.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controller25be3eace7e6c519633939c937264249.form = Controller25be3eace7e6c519633939c937264249Form
/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/businesses'
*/
const Controllerdbafd3d0e11e643988df98e8602254af = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerdbafd3d0e11e643988df98e8602254af.url(options),
    method: 'get',
})

Controllerdbafd3d0e11e643988df98e8602254af.definition = {
    methods: ["get","head"],
    url: '/businesses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/businesses'
*/
Controllerdbafd3d0e11e643988df98e8602254af.url = (options?: RouteQueryOptions) => {
    return Controllerdbafd3d0e11e643988df98e8602254af.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/businesses'
*/
Controllerdbafd3d0e11e643988df98e8602254af.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerdbafd3d0e11e643988df98e8602254af.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/businesses'
*/
Controllerdbafd3d0e11e643988df98e8602254af.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerdbafd3d0e11e643988df98e8602254af.url(options),
    method: 'head',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/businesses'
*/
const Controllerdbafd3d0e11e643988df98e8602254afForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerdbafd3d0e11e643988df98e8602254af.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/businesses'
*/
Controllerdbafd3d0e11e643988df98e8602254afForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerdbafd3d0e11e643988df98e8602254af.url(options),
    method: 'get',
})

/**
* @see \Inertia\Controller::__invoke
* @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
* @route '/businesses'
*/
Controllerdbafd3d0e11e643988df98e8602254afForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: Controllerdbafd3d0e11e643988df98e8602254af.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

Controllerdbafd3d0e11e643988df98e8602254af.form = Controllerdbafd3d0e11e643988df98e8602254afForm

const Controller = {
    '/': Controller980bb49ee7ae63891f1d891d2fbcf1c9,
    '/weddings': Controller25be3eace7e6c519633939c937264249,
    '/businesses': Controllerdbafd3d0e11e643988df98e8602254af,
}

export default Controller