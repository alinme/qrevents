import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::update
 * @see app/Http/Controllers/EventController.php:1241
 * @route '/events/{event}/billing'
 */
export const update = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/events/{event}/billing',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\EventController::update
 * @see app/Http/Controllers/EventController.php:1241
 * @route '/events/{event}/billing'
 */
update.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::update
 * @see app/Http/Controllers/EventController.php:1241
 * @route '/events/{event}/billing'
 */
update.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\EventController::update
 * @see app/Http/Controllers/EventController.php:1241
 * @route '/events/{event}/billing'
 */
    const updateForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/EventController.php:1241
 * @route '/events/{event}/billing'
 */
        updateForm.patch = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\EventController::checkout
 * @see app/Http/Controllers/EventController.php:1312
 * @route '/events/{event}/billing/checkout'
 */
export const checkout = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(args, options),
    method: 'post',
})

checkout.definition = {
    methods: ["post"],
    url: '/events/{event}/billing/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::checkout
 * @see app/Http/Controllers/EventController.php:1312
 * @route '/events/{event}/billing/checkout'
 */
checkout.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return checkout.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::checkout
 * @see app/Http/Controllers/EventController.php:1312
 * @route '/events/{event}/billing/checkout'
 */
checkout.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\EventController::checkout
 * @see app/Http/Controllers/EventController.php:1312
 * @route '/events/{event}/billing/checkout'
 */
    const checkoutForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: checkout.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\EventController::checkout
 * @see app/Http/Controllers/EventController.php:1312
 * @route '/events/{event}/billing/checkout'
 */
        checkoutForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: checkout.url(args, options),
            method: 'post',
        })
    
    checkout.form = checkoutForm
/**
* @see \App\Http\Controllers\EventController::payCredits
 * @see app/Http/Controllers/EventController.php:1274
 * @route '/events/{event}/billing/pay-with-credits'
 */
export const payCredits = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: payCredits.url(args, options),
    method: 'post',
})

payCredits.definition = {
    methods: ["post"],
    url: '/events/{event}/billing/pay-with-credits',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::payCredits
 * @see app/Http/Controllers/EventController.php:1274
 * @route '/events/{event}/billing/pay-with-credits'
 */
payCredits.url = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return payCredits.definition.url
            .replace('{event}', parsedArgs.event.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::payCredits
 * @see app/Http/Controllers/EventController.php:1274
 * @route '/events/{event}/billing/pay-with-credits'
 */
payCredits.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: payCredits.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\EventController::payCredits
 * @see app/Http/Controllers/EventController.php:1274
 * @route '/events/{event}/billing/pay-with-credits'
 */
    const payCreditsForm = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: payCredits.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\EventController::payCredits
 * @see app/Http/Controllers/EventController.php:1274
 * @route '/events/{event}/billing/pay-with-credits'
 */
        payCreditsForm.post = (args: { event: number | { id: number } } | [event: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: payCredits.url(args, options),
            method: 'post',
        })
    
    payCredits.form = payCreditsForm
const billing = {
    update: Object.assign(update, update),
checkout: Object.assign(checkout, checkout),
payCredits: Object.assign(payCredits, payCredits),
}

export default billing