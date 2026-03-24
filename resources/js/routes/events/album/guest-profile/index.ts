import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:1328
* @route '/a/{shareToken}/guest-profile'
*/
export const show = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/guest-profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:1328
* @route '/a/{shareToken}/guest-profile'
*/
show.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return show.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:1328
* @route '/a/{shareToken}/guest-profile'
*/
show.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:1328
* @route '/a/{shareToken}/guest-profile'
*/
show.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:1328
* @route '/a/{shareToken}/guest-profile'
*/
const showForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:1328
* @route '/a/{shareToken}/guest-profile'
*/
showForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:1328
* @route '/a/{shareToken}/guest-profile'
*/
showForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\EventController::upsert
* @see app/Http/Controllers/EventController.php:1360
* @route '/a/{shareToken}/guest-profile'
*/
export const upsert = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upsert.url(args, options),
    method: 'post',
})

upsert.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/guest-profile',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::upsert
* @see app/Http/Controllers/EventController.php:1360
* @route '/a/{shareToken}/guest-profile'
*/
upsert.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return upsert.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::upsert
* @see app/Http/Controllers/EventController.php:1360
* @route '/a/{shareToken}/guest-profile'
*/
upsert.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upsert.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upsert
* @see app/Http/Controllers/EventController.php:1360
* @route '/a/{shareToken}/guest-profile'
*/
const upsertForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upsert.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upsert
* @see app/Http/Controllers/EventController.php:1360
* @route '/a/{shareToken}/guest-profile'
*/
upsertForm.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upsert.url(args, options),
    method: 'post',
})

upsert.form = upsertForm

const guestProfile = {
    show: Object.assign(show, show),
    upsert: Object.assign(upsert, upsert),
}

export default guestProfile