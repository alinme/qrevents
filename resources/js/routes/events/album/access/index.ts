import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:76
* @route '/album'
*/
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/album',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:76
* @route '/album'
*/
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:76
* @route '/album'
*/
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:76
* @route '/album'
*/
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:76
* @route '/album'
*/
const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:76
* @route '/album'
*/
showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:76
* @route '/album'
*/
showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\EventController::resolve
* @see app/Http/Controllers/EventController.php:87
* @route '/album'
*/
export const resolve = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resolve.url(options),
    method: 'post',
})

resolve.definition = {
    methods: ["post"],
    url: '/album',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::resolve
* @see app/Http/Controllers/EventController.php:87
* @route '/album'
*/
resolve.url = (options?: RouteQueryOptions) => {
    return resolve.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::resolve
* @see app/Http/Controllers/EventController.php:87
* @route '/album'
*/
resolve.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resolve.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::resolve
* @see app/Http/Controllers/EventController.php:87
* @route '/album'
*/
const resolveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resolve.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::resolve
* @see app/Http/Controllers/EventController.php:87
* @route '/album'
*/
resolveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resolve.url(options),
    method: 'post',
})

resolve.form = resolveForm
