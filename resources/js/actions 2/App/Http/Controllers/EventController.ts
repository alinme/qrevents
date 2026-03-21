import { queryParams,    applyUrlDefaults } from './../../../../wayfinder'
import type {RouteQueryOptions, RouteDefinition, RouteFormDefinition} from './../../../../wayfinder';
/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:34
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
* @see app/Http/Controllers/EventController.php:34
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
* @see app/Http/Controllers/EventController.php:34
* @route '/events/{event}'
*/
show.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:34
* @route '/events/{event}'
*/
show.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:34
* @route '/events/{event}'
*/
const showForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:34
* @route '/events/{event}'
*/
showForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::show
* @see app/Http/Controllers/EventController.php:34
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
* @see app/Http/Controllers/EventController.php:44
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
* @see app/Http/Controllers/EventController.php:44
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
* @see app/Http/Controllers/EventController.php:44
* @route '/events/{event}/media'
*/
media.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:44
* @route '/events/{event}/media'
*/
media.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: media.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:44
* @route '/events/{event}/media'
*/
const mediaForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:44
* @route '/events/{event}/media'
*/
mediaForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: media.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::media
* @see app/Http/Controllers/EventController.php:44
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
* @see app/Http/Controllers/EventController.php:51
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
* @see app/Http/Controllers/EventController.php:51
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
* @see app/Http/Controllers/EventController.php:51
* @route '/events/{event}/settings'
*/
settings.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:51
* @route '/events/{event}/settings'
*/
settings.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:51
* @route '/events/{event}/settings'
*/
const settingsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:51
* @route '/events/{event}/settings'
*/
settingsForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: settings.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::settings
* @see app/Http/Controllers/EventController.php:51
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
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:62
* @route '/events/{event}/settings'
*/
export const updateSettings = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateSettings.url(args, options),
    method: 'patch',
})

updateSettings.definition = {
    methods: ["patch"],
    url: '/events/{event}/settings',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:62
* @route '/events/{event}/settings'
*/
updateSettings.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateSettings.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:62
* @route '/events/{event}/settings'
*/
updateSettings.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateSettings.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:62
* @route '/events/{event}/settings'
*/
const updateSettingsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateSettings.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::updateSettings
* @see app/Http/Controllers/EventController.php:62
* @route '/events/{event}/settings'
*/
updateSettingsForm.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateSettings.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateSettings.form = updateSettingsForm

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:364
* @route '/events/{event}/collaborators'
*/
export const storeCollaborator = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCollaborator.url(args, options),
    method: 'post',
})

storeCollaborator.definition = {
    methods: ["post"],
    url: '/events/{event}/collaborators',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:364
* @route '/events/{event}/collaborators'
*/
storeCollaborator.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return storeCollaborator.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:364
* @route '/events/{event}/collaborators'
*/
storeCollaborator.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCollaborator.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:364
* @route '/events/{event}/collaborators'
*/
const storeCollaboratorForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCollaborator.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::storeCollaborator
* @see app/Http/Controllers/EventController.php:364
* @route '/events/{event}/collaborators'
*/
storeCollaboratorForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCollaborator.url(args, options),
    method: 'post',
})

storeCollaborator.form = storeCollaboratorForm

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:436
* @route '/collaborator-invites/{collaborator}/accept'
*/
export const acceptCollaboratorInvite = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: acceptCollaboratorInvite.url(args, options),
    method: 'get',
})

acceptCollaboratorInvite.definition = {
    methods: ["get","head"],
    url: '/collaborator-invites/{collaborator}/accept',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:436
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInvite.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return acceptCollaboratorInvite.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:436
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInvite.get = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: acceptCollaboratorInvite.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:436
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInvite.head = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: acceptCollaboratorInvite.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:436
* @route '/collaborator-invites/{collaborator}/accept'
*/
const acceptCollaboratorInviteForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: acceptCollaboratorInvite.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:436
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInviteForm.get = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: acceptCollaboratorInvite.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::acceptCollaboratorInvite
* @see app/Http/Controllers/EventController.php:436
* @route '/collaborator-invites/{collaborator}/accept'
*/
acceptCollaboratorInviteForm.head = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: acceptCollaboratorInvite.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

acceptCollaboratorInvite.form = acceptCollaboratorInviteForm

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:468
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
export const completeCollaboratorInviteRegistration = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeCollaboratorInviteRegistration.url(args, options),
    method: 'post',
})

completeCollaboratorInviteRegistration.definition = {
    methods: ["post"],
    url: '/collaborator-invites/{collaborator}/complete-register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:468
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeCollaboratorInviteRegistration.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return completeCollaboratorInviteRegistration.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:468
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeCollaboratorInviteRegistration.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeCollaboratorInviteRegistration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:468
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
const completeCollaboratorInviteRegistrationForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeCollaboratorInviteRegistration.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteRegistration
* @see app/Http/Controllers/EventController.php:468
* @route '/collaborator-invites/{collaborator}/complete-register'
*/
completeCollaboratorInviteRegistrationForm.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeCollaboratorInviteRegistration.url(args, options),
    method: 'post',
})

completeCollaboratorInviteRegistration.form = completeCollaboratorInviteRegistrationForm

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:506
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
export const completeCollaboratorInviteLogin = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeCollaboratorInviteLogin.url(args, options),
    method: 'post',
})

completeCollaboratorInviteLogin.definition = {
    methods: ["post"],
    url: '/collaborator-invites/{collaborator}/complete-login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:506
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeCollaboratorInviteLogin.url = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return completeCollaboratorInviteLogin.definition.url
            .replace('{collaborator}', parsedArgs.collaborator.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:506
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeCollaboratorInviteLogin.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: completeCollaboratorInviteLogin.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:506
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
const completeCollaboratorInviteLoginForm = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeCollaboratorInviteLogin.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::completeCollaboratorInviteLogin
* @see app/Http/Controllers/EventController.php:506
* @route '/collaborator-invites/{collaborator}/complete-login'
*/
completeCollaboratorInviteLoginForm.post = (args: { collaborator: number | { id: number } } | [collaborator: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: completeCollaboratorInviteLogin.url(args, options),
    method: 'post',
})

completeCollaboratorInviteLogin.form = completeCollaboratorInviteLoginForm

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:538
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
* @see app/Http/Controllers/EventController.php:538
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
* @see app/Http/Controllers/EventController.php:538
* @route '/a/{shareToken}'
*/
album.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:538
* @route '/a/{shareToken}'
*/
album.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: album.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:538
* @route '/a/{shareToken}'
*/
const albumForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:538
* @route '/a/{shareToken}'
*/
albumForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: album.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::album
* @see app/Http/Controllers/EventController.php:538
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
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:616
* @route '/a/{shareToken}/uploads'
*/
export const upload = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

upload.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/uploads',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:616
* @route '/a/{shareToken}/uploads'
*/
upload.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return upload.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:616
* @route '/a/{shareToken}/uploads'
*/
upload.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:616
* @route '/a/{shareToken}/uploads'
*/
const uploadForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:616
* @route '/a/{shareToken}/uploads'
*/
uploadForm.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(args, options),
    method: 'post',
})

upload.form = uploadForm

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:744
* @route '/a/{shareToken}/text-posts'
*/
export const postText = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: postText.url(args, options),
    method: 'post',
})

postText.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/text-posts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:744
* @route '/a/{shareToken}/text-posts'
*/
postText.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return postText.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:744
* @route '/a/{shareToken}/text-posts'
*/
postText.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: postText.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:744
* @route '/a/{shareToken}/text-posts'
*/
const postTextForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: postText.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::postText
* @see app/Http/Controllers/EventController.php:744
* @route '/a/{shareToken}/text-posts'
*/
postTextForm.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: postText.url(args, options),
    method: 'post',
})

postText.form = postTextForm

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:857
* @route '/a/{shareToken}/assets/{asset}/download'
*/
export const downloadPublicAsset = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPublicAsset.url(args, options),
    method: 'get',
})

downloadPublicAsset.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/assets/{asset}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:857
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAsset.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return downloadPublicAsset.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:857
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAsset.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPublicAsset.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:857
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAsset.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPublicAsset.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:857
* @route '/a/{shareToken}/assets/{asset}/download'
*/
const downloadPublicAssetForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadPublicAsset.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:857
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAssetForm.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadPublicAsset.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::downloadPublicAsset
* @see app/Http/Controllers/EventController.php:857
* @route '/a/{shareToken}/assets/{asset}/download'
*/
downloadPublicAssetForm.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: downloadPublicAsset.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

downloadPublicAsset.form = downloadPublicAssetForm

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:871
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
export const deletePublicAsset = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deletePublicAsset.url(args, options),
    method: 'post',
})

deletePublicAsset.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/assets/{asset}/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:871
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
deletePublicAsset.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return deletePublicAsset.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:871
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
deletePublicAsset.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deletePublicAsset.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:871
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
const deletePublicAssetForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: deletePublicAsset.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::deletePublicAsset
* @see app/Http/Controllers/EventController.php:871
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
deletePublicAssetForm.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: deletePublicAsset.url(args, options),
    method: 'post',
})

deletePublicAsset.form = deletePublicAssetForm

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:910
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
* @see app/Http/Controllers/EventController.php:910
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
* @see app/Http/Controllers/EventController.php:910
* @route '/wall/{shareToken}'
*/
wall.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:910
* @route '/wall/{shareToken}'
*/
wall.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: wall.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:910
* @route '/wall/{shareToken}'
*/
const wallForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:910
* @route '/wall/{shareToken}'
*/
wallForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: wall.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::wall
* @see app/Http/Controllers/EventController.php:910
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

const EventController = { show, media, settings, updateSettings, storeCollaborator, acceptCollaboratorInvite, completeCollaboratorInviteRegistration, completeCollaboratorInviteLogin, album, upload, postText, downloadPublicAsset, deletePublicAsset, wall }

export default EventController