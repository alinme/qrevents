import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1116
* @route '/events/{event}/collaborators'
*/
export const store = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/events/{event}/collaborators',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1116
* @route '/events/{event}/collaborators'
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
* @see app/Http/Controllers/EventController.php:1116
* @route '/events/{event}/collaborators'
*/
store.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1116
* @route '/events/{event}/collaborators'
*/
const storeForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1116
* @route '/events/{event}/collaborators'
*/
storeForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\EventController::accept
* @see app/Http/Controllers/EventController.php:1188
* @route '/collaborator-invites/{collaborator}/accept'
*/
export const accept = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accept.url(args, options),
    method: 'get',
})

accept.definition = {
    methods: ["get","head"],
    url: '/collaborator-invites/{collaborator}/accept',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::accept
* @see app/Http/Controllers/EventController.php:1188
* @route '/collaborator-invites/{collaborator}/accept'
*/
accept.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { collaborator: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { collaborator: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            collaborator: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        collaborator: typeof args.collaborator === 'object'
        ? args.collaborator.id
        : args.collaborator,
    }

    return accept.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::accept
* @see app/Http/Controllers/EventController.php:1188
* @route '/collaborator-invites/{collaborator}/accept'
*/
accept.get = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accept.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::accept
* @see app/Http/Controllers/EventController.php:1188
* @route '/collaborator-invites/{collaborator}/accept'
*/
accept.head = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: accept.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::accept
* @see app/Http/Controllers/EventController.php:1188
* @route '/collaborator-invites/{collaborator}/accept'
*/
const acceptForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: accept.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::accept
* @see app/Http/Controllers/EventController.php:1188
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptForm.get = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: accept.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::accept
* @see app/Http/Controllers/EventController.php:1188
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptForm.head = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: accept.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

accept.form = acceptForm

/**
* @see \App\Http\Controllers\EventController::completeRegister
* @see app/Http/Controllers/EventController.php:1220
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
export const completeRegister = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeRegister.url(args, options),
    method: 'post',
})

completeRegister.definition = {
    methods: ["post"],
    url: '/collaborator-invites/{collaborator}/complete-register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::completeRegister
* @see app/Http/Controllers/EventController.php:1220
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeRegister.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { collaborator: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { collaborator: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            collaborator: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        collaborator: typeof args.collaborator === 'object'
        ? args.collaborator.id
        : args.collaborator,
    }

    return completeRegister.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::completeRegister
* @see app/Http/Controllers/EventController.php:1220
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeRegister.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeRegister.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeRegister
* @see app/Http/Controllers/EventController.php:1220
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
const completeRegisterForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeRegister.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeRegister
* @see app/Http/Controllers/EventController.php:1220
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeRegisterForm.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeRegister.url(args, options),
    method: 'post',
})

completeRegister.form = completeRegisterForm

/**
* @see \App\Http\Controllers\EventController::completeLogin
* @see app/Http/Controllers/EventController.php:1258
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
export const completeLogin = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeLogin.url(args, options),
    method: 'post',
})

completeLogin.definition = {
    methods: ["post"],
    url: '/collaborator-invites/{collaborator}/complete-login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::completeLogin
* @see app/Http/Controllers/EventController.php:1258
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeLogin.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { collaborator: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { collaborator: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            collaborator: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        collaborator: typeof args.collaborator === 'object'
        ? args.collaborator.id
        : args.collaborator,
    }

    return completeLogin.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::completeLogin
* @see app/Http/Controllers/EventController.php:1258
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeLogin.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeLogin.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeLogin
* @see app/Http/Controllers/EventController.php:1258
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
const completeLoginForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeLogin.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeLogin
* @see app/Http/Controllers/EventController.php:1258
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeLoginForm.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeLogin.url(args, options),
    method: 'post',
})

completeLogin.form = completeLoginForm

const collaborators = {
    store: Object.assign(store, store),
    accept: Object.assign(accept, accept),
    completeRegister: Object.assign(completeRegister, completeRegister),
    completeLogin: Object.assign(completeLogin, completeLogin),
}

export default collaborators