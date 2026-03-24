import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import invitationSettings from './invitation-settings'
import invitation from './invitation'
import publicInvitation from './public-invitation'
/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/guests'
*/
export const store = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/events/{event}/guests',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/guests'
*/
store.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/guests'
*/
store.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/guests'
*/
const storeForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:111
* @route '/events/{event}/guests'
*/
storeForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\EventController::importMethod
* @see app/Http/Controllers/EventController.php:146
* @route '/events/{event}/guests/import'
*/
export const importMethod = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(args, options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/events/{event}/guests/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::importMethod
* @see app/Http/Controllers/EventController.php:146
* @route '/events/{event}/guests/import'
*/
importMethod.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return importMethod.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::importMethod
* @see app/Http/Controllers/EventController.php:146
* @route '/events/{event}/guests/import'
*/
importMethod.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::importMethod
* @see app/Http/Controllers/EventController.php:146
* @route '/events/{event}/guests/import'
*/
const importMethodForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importMethod.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::importMethod
* @see app/Http/Controllers/EventController.php:146
* @route '/events/{event}/guests/import'
*/
importMethodForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importMethod.url(args, options),
    method: 'post',
})

importMethod.form = importMethodForm

/**
* @see \App\Http\Controllers\EventController::exportMethod
* @see app/Http/Controllers/EventController.php:193
* @route '/events/{event}/guests/export'
*/
export const exportMethod = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(args, options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/events/{event}/guests/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::exportMethod
* @see app/Http/Controllers/EventController.php:193
* @route '/events/{event}/guests/export'
*/
exportMethod.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return exportMethod.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::exportMethod
* @see app/Http/Controllers/EventController.php:193
* @route '/events/{event}/guests/export'
*/
exportMethod.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::exportMethod
* @see app/Http/Controllers/EventController.php:193
* @route '/events/{event}/guests/export'
*/
exportMethod.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::exportMethod
* @see app/Http/Controllers/EventController.php:193
* @route '/events/{event}/guests/export'
*/
const exportMethodForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::exportMethod
* @see app/Http/Controllers/EventController.php:193
* @route '/events/{event}/guests/export'
*/
exportMethodForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::exportMethod
* @see app/Http/Controllers/EventController.php:193
* @route '/events/{event}/guests/export'
*/
exportMethodForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportMethod.form = exportMethodForm

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:123
* @route '/events/{event}/guests/{guestParty}'
*/
export const update = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/events/{event}/guests/{guestParty}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:123
* @route '/events/{event}/guests/{guestParty}'
*/
update.url = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            event: args[0],
            guestParty: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
        guestParty: typeof args.guestParty === 'object'
        ? args.guestParty.id
        : args.guestParty,
    }

    return update.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{guestParty}', parsedArgs.guestParty.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:123
* @route '/events/{event}/guests/{guestParty}'
*/
update.patch = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::update
* @see app/Http/Controllers/EventController.php:123
* @route '/events/{event}/guests/{guestParty}'
*/
const updateForm = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see app/Http/Controllers/EventController.php:123
* @route '/events/{event}/guests/{guestParty}'
*/
updateForm.patch = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
export const destroy = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/events/{event}/guests/{guestParty}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
destroy.url = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            event: args[0],
            guestParty: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        event: typeof args.event === 'object'
        ? args.event.id
        : args.event,
        guestParty: typeof args.guestParty === 'object'
        ? args.guestParty.id
        : args.guestParty,
    }

    return destroy.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace('{guestParty}', parsedArgs.guestParty.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
destroy.delete = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
const destroyForm = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::destroy
* @see app/Http/Controllers/EventController.php:136
* @route '/events/{event}/guests/{guestParty}'
*/
destroyForm.delete = (args: { event: number | { id: number }, guestParty: string | number | { id: string | number } } | [event: number | { id: number }, guestParty: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const guests = {
    store: Object.assign(store, store),
    import: Object.assign(importMethod, importMethod),
    export: Object.assign(exportMethod, exportMethod),
    invitationSettings: Object.assign(invitationSettings, invitationSettings),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
    invitation: Object.assign(invitation, invitation),
    publicInvitation: Object.assign(publicInvitation, publicInvitation),
}

export default guests