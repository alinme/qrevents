import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:262
* @route '/guest-list/{shareToken}'
*/
export const show = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/guest-list/{shareToken}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:262
* @route '/guest-list/{shareToken}'
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
* @see app/Http/Controllers/EventController.php:262
* @route '/guest-list/{shareToken}'
*/
show.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:262
* @route '/guest-list/{shareToken}'
*/
show.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:262
* @route '/guest-list/{shareToken}'
*/
const showForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:262
* @route '/guest-list/{shareToken}'
*/
showForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:262
* @route '/guest-list/{shareToken}'
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
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:276
* @route '/guest-list/{shareToken}/{guestParty}'
*/
export const update = (args: { shareToken: string | number, guestParty: string | number | { id: string | number } } | [shareToken: string | number, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/guest-list/{shareToken}/{guestParty}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:276
* @route '/guest-list/{shareToken}/{guestParty}'
*/
update.url = (args: { shareToken: string | number, guestParty: string | number | { id: string | number } } | [shareToken: string | number, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            guestParty: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        guestParty: typeof args.guestParty === 'object'
        ? args.guestParty.id
        : args.guestParty,
    }

    return update.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{guestParty}', parsedArgs.guestParty.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:276
* @route '/guest-list/{shareToken}/{guestParty}'
*/
update.patch = (args: { shareToken: string | number, guestParty: string | number | { id: string | number } } | [shareToken: string | number, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:276
* @route '/guest-list/{shareToken}/{guestParty}'
*/
const updateForm = (args: { shareToken: string | number, guestParty: string | number | { id: string | number } } | [shareToken: string | number, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:276
* @route '/guest-list/{shareToken}/{guestParty}'
*/
updateForm.patch = (args: { shareToken: string | number, guestParty: string | number | { id: string | number } } | [shareToken: string | number, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

const publicList = {
    show: Object.assign(show, show),
    update: Object.assign(update, update),
}

export default publicList