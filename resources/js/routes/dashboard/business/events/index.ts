import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:51
* @route '/dashboard/business/events/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/dashboard/business/events/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:51
* @route '/dashboard/business/events/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:51
* @route '/dashboard/business/events/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:51
* @route '/dashboard/business/events/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:51
* @route '/dashboard/business/events/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:51
* @route '/dashboard/business/events/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:51
* @route '/dashboard/business/events/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:75
* @route '/dashboard/business/events'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/dashboard/business/events',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:75
* @route '/dashboard/business/events'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:75
* @route '/dashboard/business/events'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:75
* @route '/dashboard/business/events'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:75
* @route '/dashboard/business/events'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm
