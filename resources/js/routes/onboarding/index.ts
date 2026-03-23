import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:21
* @route '/onboarding'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/onboarding',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:21
* @route '/onboarding'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:21
* @route '/onboarding'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:21
* @route '/onboarding'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:21
* @route '/onboarding'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:21
* @route '/onboarding'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::create
* @see app/Http/Controllers/EventOnboardingController.php:21
* @route '/onboarding'
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
* @see app/Http/Controllers/EventOnboardingController.php:42
* @route '/onboarding'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/onboarding',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:42
* @route '/onboarding'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:42
* @route '/onboarding'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:42
* @route '/onboarding'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::store
* @see app/Http/Controllers/EventOnboardingController.php:42
* @route '/onboarding'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\EventOnboardingController::creating
* @see app/Http/Controllers/EventOnboardingController.php:103
* @route '/onboarding/{event}/creating'
*/
export const creating = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creating.url(args, options),
    method: 'get',
})

creating.definition = {
    methods: ["get","head"],
    url: '/onboarding/{event}/creating',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventOnboardingController::creating
* @see app/Http/Controllers/EventOnboardingController.php:103
* @route '/onboarding/{event}/creating'
*/
creating.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return creating.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventOnboardingController::creating
* @see app/Http/Controllers/EventOnboardingController.php:103
* @route '/onboarding/{event}/creating'
*/
creating.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creating.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::creating
* @see app/Http/Controllers/EventOnboardingController.php:103
* @route '/onboarding/{event}/creating'
*/
creating.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: creating.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::creating
* @see app/Http/Controllers/EventOnboardingController.php:103
* @route '/onboarding/{event}/creating'
*/
const creatingForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: creating.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::creating
* @see app/Http/Controllers/EventOnboardingController.php:103
* @route '/onboarding/{event}/creating'
*/
creatingForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: creating.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::creating
* @see app/Http/Controllers/EventOnboardingController.php:103
* @route '/onboarding/{event}/creating'
*/
creatingForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: creating.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

creating.form = creatingForm

/**
* @see \App\Http\Controllers\EventOnboardingController::photos
* @see app/Http/Controllers/EventOnboardingController.php:119
* @route '/onboarding/{event}/photos'
*/
export const photos = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photos.url(args, options),
    method: 'get',
})

photos.definition = {
    methods: ["get","head"],
    url: '/onboarding/{event}/photos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventOnboardingController::photos
* @see app/Http/Controllers/EventOnboardingController.php:119
* @route '/onboarding/{event}/photos'
*/
photos.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return photos.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventOnboardingController::photos
* @see app/Http/Controllers/EventOnboardingController.php:119
* @route '/onboarding/{event}/photos'
*/
photos.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: photos.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::photos
* @see app/Http/Controllers/EventOnboardingController.php:119
* @route '/onboarding/{event}/photos'
*/
photos.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: photos.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::photos
* @see app/Http/Controllers/EventOnboardingController.php:119
* @route '/onboarding/{event}/photos'
*/
const photosForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photos.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::photos
* @see app/Http/Controllers/EventOnboardingController.php:119
* @route '/onboarding/{event}/photos'
*/
photosForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photos.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::photos
* @see app/Http/Controllers/EventOnboardingController.php:119
* @route '/onboarding/{event}/photos'
*/
photosForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: photos.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

photos.form = photosForm

/**
* @see \App\Http\Controllers\EventOnboardingController::ready
* @see app/Http/Controllers/EventOnboardingController.php:139
* @route '/onboarding/{event}/ready'
*/
export const ready = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ready.url(args, options),
    method: 'get',
})

ready.definition = {
    methods: ["get","head"],
    url: '/onboarding/{event}/ready',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventOnboardingController::ready
* @see app/Http/Controllers/EventOnboardingController.php:139
* @route '/onboarding/{event}/ready'
*/
ready.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return ready.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventOnboardingController::ready
* @see app/Http/Controllers/EventOnboardingController.php:139
* @route '/onboarding/{event}/ready'
*/
ready.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ready.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::ready
* @see app/Http/Controllers/EventOnboardingController.php:139
* @route '/onboarding/{event}/ready'
*/
ready.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ready.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::ready
* @see app/Http/Controllers/EventOnboardingController.php:139
* @route '/onboarding/{event}/ready'
*/
const readyForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ready.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::ready
* @see app/Http/Controllers/EventOnboardingController.php:139
* @route '/onboarding/{event}/ready'
*/
readyForm.get = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ready.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventOnboardingController::ready
* @see app/Http/Controllers/EventOnboardingController.php:139
* @route '/onboarding/{event}/ready'
*/
readyForm.head = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ready.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ready.form = readyForm

const onboarding = {
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    creating: Object.assign(creating, creating),
    photos: Object.assign(photos, photos),
    ready: Object.assign(ready, ready),
}

export default onboarding