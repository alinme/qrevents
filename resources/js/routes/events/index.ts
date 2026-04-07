import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import printPack01bcc6 from './print-pack'
import inviteStudio25eb90 from './invite-studio'
import guestsD791f3 from './guests'
import tables from './tables'
import exports from './exports'
import assets from './assets'
import settings69f00b from './settings'
import billing from './billing'
import collaborators from './collaborators'
import album3d2484 from './album'
import wall86584a from './wall'
/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:100
* @route '/events/{event}'
*/
export const show = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/events/{event}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:100
* @route '/events/{event}'
*/
show.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return show.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:100
* @route '/events/{event}'
*/
show.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:100
* @route '/events/{event}'
*/
show.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:100
* @route '/events/{event}'
*/
const showForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:100
* @route '/events/{event}'
*/
showForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:100
* @route '/events/{event}'
*/
showForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\EventController::printPack
* @see app/Http/Controllers/EventController.php:133
* @route '/events/{event}/print-pack'
*/
export const printPack = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printPack.url(args, options),
    method: 'get',
})

printPack.definition = {
    methods: ["get","head"],
    url: '/events/{event}/print-pack',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::printPack
* @see app/Http/Controllers/EventController.php:133
* @route '/events/{event}/print-pack'
*/
printPack.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return printPack.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::printPack
* @see app/Http/Controllers/EventController.php:133
* @route '/events/{event}/print-pack'
*/
printPack.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: printPack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::printPack
* @see app/Http/Controllers/EventController.php:133
* @route '/events/{event}/print-pack'
*/
printPack.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: printPack.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::printPack
* @see app/Http/Controllers/EventController.php:133
* @route '/events/{event}/print-pack'
*/
const printPackForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printPack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::printPack
* @see app/Http/Controllers/EventController.php:133
* @route '/events/{event}/print-pack'
*/
printPackForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printPack.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::printPack
* @see app/Http/Controllers/EventController.php:133
* @route '/events/{event}/print-pack'
*/
printPackForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: printPack.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

printPack.form = printPackForm

/**
* @see \App\Http\Controllers\EventController::inviteStudio
* @see app/Http/Controllers/EventController.php:151
* @route '/events/{event}/invite-studio'
*/
export const inviteStudio = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inviteStudio.url(args, options),
    method: 'get',
})

inviteStudio.definition = {
    methods: ["get","head"],
    url: '/events/{event}/invite-studio',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::inviteStudio
* @see app/Http/Controllers/EventController.php:151
* @route '/events/{event}/invite-studio'
*/
inviteStudio.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return inviteStudio.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::inviteStudio
* @see app/Http/Controllers/EventController.php:151
* @route '/events/{event}/invite-studio'
*/
inviteStudio.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inviteStudio.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::inviteStudio
* @see app/Http/Controllers/EventController.php:151
* @route '/events/{event}/invite-studio'
*/
inviteStudio.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: inviteStudio.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::inviteStudio
* @see app/Http/Controllers/EventController.php:151
* @route '/events/{event}/invite-studio'
*/
const inviteStudioForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: inviteStudio.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::inviteStudio
* @see app/Http/Controllers/EventController.php:151
* @route '/events/{event}/invite-studio'
*/
inviteStudioForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: inviteStudio.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::inviteStudio
* @see app/Http/Controllers/EventController.php:151
* @route '/events/{event}/invite-studio'
*/
inviteStudioForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: inviteStudio.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

inviteStudio.form = inviteStudioForm

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:171
* @route '/events/{event}/guests'
*/
export const guests = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guests.url(args, options),
    method: 'get',
})

guests.definition = {
    methods: ["get","head"],
    url: '/events/{event}/guests',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:171
* @route '/events/{event}/guests'
*/
guests.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return guests.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:171
* @route '/events/{event}/guests'
*/
guests.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: guests.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:171
* @route '/events/{event}/guests'
*/
guests.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: guests.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:171
* @route '/events/{event}/guests'
*/
const guestsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guests.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:171
* @route '/events/{event}/guests'
*/
guestsForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guests.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::guests
* @see app/Http/Controllers/EventController.php:171
* @route '/events/{event}/guests'
*/
guestsForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: guests.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

guests.form = guestsForm

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/media'
*/
export const media = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: media.url(args, options),
    method: 'get',
})

media.definition = {
    methods: ["get","head"],
    url: '/events/{event}/media',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/media'
*/
media.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return media.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/media'
*/
media.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/media'
*/
media.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: media.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/media'
*/
const mediaForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/media'
*/
mediaForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/media'
*/
mediaForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

media.form = mediaForm

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:860
* @route '/events/{event}/settings'
*/
export const settings = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(args, options),
    method: 'get',
})

settings.definition = {
    methods: ["get","head"],
    url: '/events/{event}/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:860
* @route '/events/{event}/settings'
*/
settings.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { event: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { event: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            event: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
    }

    return settings.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:860
* @route '/events/{event}/settings'
*/
settings.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:860
* @route '/events/{event}/settings'
*/
settings.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:860
* @route '/events/{event}/settings'
*/
const settingsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:860
* @route '/events/{event}/settings'
*/
settingsForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:860
* @route '/events/{event}/settings'
*/
settingsForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

settings.form = settingsForm

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1515
* @route '/a/{shareToken}'
*/
export const album = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: album.url(args, options),
    method: 'get',
})

album.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1515
* @route '/a/{shareToken}'
*/
album.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return album.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1515
* @route '/a/{shareToken}'
*/
album.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1515
* @route '/a/{shareToken}'
*/
album.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: album.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1515
* @route '/a/{shareToken}'
*/
const albumForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1515
* @route '/a/{shareToken}'
*/
albumForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:1515
* @route '/a/{shareToken}'
*/
albumForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

album.form = albumForm

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2345
* @route '/w/{shareToken}'
*/
export const wall = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wall.url(args, options),
    method: 'get',
})

wall.definition = {
    methods: ["get","head"],
    url: '/w/{shareToken}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2345
* @route '/w/{shareToken}'
*/
wall.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return wall.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2345
* @route '/w/{shareToken}'
*/
wall.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2345
* @route '/w/{shareToken}'
*/
wall.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: wall.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2345
* @route '/w/{shareToken}'
*/
const wallForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2345
* @route '/w/{shareToken}'
*/
wallForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:2345
* @route '/w/{shareToken}'
*/
wallForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

wall.form = wallForm

const events = {
    show: Object.assign(show, show),
    printPack: Object.assign(printPack, printPack01bcc6),
    inviteStudio: Object.assign(inviteStudio, inviteStudio25eb90),
    guests: Object.assign(guests, guestsD791f3),
    tables: Object.assign(tables, tables),
    media: Object.assign(media, media),
    exports: Object.assign(exports, exports),
    assets: Object.assign(assets, assets),
    settings: Object.assign(settings, settings69f00b),
    billing: Object.assign(billing, billing),
    collaborators: Object.assign(collaborators, collaborators),
    album: Object.assign(album, album3d2484),
    wall: Object.assign(wall, wall86584a),
}

export default events