import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import exports from './exports'
import assets from './assets'
import settings69f00b from './settings'
import billing from './billing'
import collaborators from './collaborators'
import album3d2484 from './album'
/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:60
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
* @see app/Http/Controllers/EventController.php:60
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
* @see app/Http/Controllers/EventController.php:60
* @route '/events/{event}'
*/
show.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:60
* @route '/events/{event}'
*/
show.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:60
* @route '/events/{event}'
*/
const showForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:60
* @route '/events/{event}'
*/
showForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:60
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
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:71
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
* @see app/Http/Controllers/EventController.php:71
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
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}/media'
*/
media.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}/media'
*/
media.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: media.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}/media'
*/
const mediaForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:71
* @route '/events/{event}/media'
*/
mediaForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:71
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
* @see app/Http/Controllers/EventController.php:265
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
* @see app/Http/Controllers/EventController.php:265
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
* @see app/Http/Controllers/EventController.php:265
* @route '/events/{event}/settings'
*/
settings.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:265
* @route '/events/{event}/settings'
*/
settings.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:265
* @route '/events/{event}/settings'
*/
const settingsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:265
* @route '/events/{event}/settings'
*/
settingsForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:265
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
* @see app/Http/Controllers/EventController.php:887
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
* @see app/Http/Controllers/EventController.php:887
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
* @see app/Http/Controllers/EventController.php:887
* @route '/a/{shareToken}'
*/
album.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:887
* @route '/a/{shareToken}'
*/
album.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: album.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:887
* @route '/a/{shareToken}'
*/
const albumForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:887
* @route '/a/{shareToken}'
*/
albumForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:887
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
* @see app/Http/Controllers/EventController.php:1688
* @route '/wall/{shareToken}'
*/
export const wall = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wall.url(args, options),
    method: 'get',
})

wall.definition = {
    methods: ["get","head"],
    url: '/wall/{shareToken}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:1688
* @route '/wall/{shareToken}'
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
* @see app/Http/Controllers/EventController.php:1688
* @route '/wall/{shareToken}'
*/
wall.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:1688
* @route '/wall/{shareToken}'
*/
wall.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: wall.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:1688
* @route '/wall/{shareToken}'
*/
const wallForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:1688
* @route '/wall/{shareToken}'
*/
wallForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:1688
* @route '/wall/{shareToken}'
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
    media: Object.assign(media, media),
    exports: Object.assign(exports, exports),
    assets: Object.assign(assets, assets),
    settings: Object.assign(settings, settings69f00b),
    billing: Object.assign(billing, billing),
    collaborators: Object.assign(collaborators, collaborators),
    album: Object.assign(album, album3d2484),
    wall: Object.assign(wall, wall),
}

export default events