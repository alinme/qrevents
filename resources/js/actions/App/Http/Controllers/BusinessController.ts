import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BusinessController::dashboard
 * @see app/Http/Controllers/BusinessController.php:20
 * @route '/dashboard/business'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/dashboard/business',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BusinessController::dashboard
 * @see app/Http/Controllers/BusinessController.php:20
 * @route '/dashboard/business'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::dashboard
 * @see app/Http/Controllers/BusinessController.php:20
 * @route '/dashboard/business'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BusinessController::dashboard
 * @see app/Http/Controllers/BusinessController.php:20
 * @route '/dashboard/business'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BusinessController::dashboard
 * @see app/Http/Controllers/BusinessController.php:20
 * @route '/dashboard/business'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BusinessController::dashboard
 * @see app/Http/Controllers/BusinessController.php:20
 * @route '/dashboard/business'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BusinessController::dashboard
 * @see app/Http/Controllers/BusinessController.php:20
 * @route '/dashboard/business'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:107
 * @route '/dashboard/business/onboarding'
 */
export const onboarding = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: onboarding.url(options),
    method: 'get',
})

onboarding.definition = {
    methods: ["get","head"],
    url: '/dashboard/business/onboarding',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:107
 * @route '/dashboard/business/onboarding'
 */
onboarding.url = (options?: RouteQueryOptions) => {
    return onboarding.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:107
 * @route '/dashboard/business/onboarding'
 */
onboarding.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: onboarding.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:107
 * @route '/dashboard/business/onboarding'
 */
onboarding.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: onboarding.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:107
 * @route '/dashboard/business/onboarding'
 */
    const onboardingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: onboarding.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:107
 * @route '/dashboard/business/onboarding'
 */
        onboardingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: onboarding.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:107
 * @route '/dashboard/business/onboarding'
 */
        onboardingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: onboarding.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    onboarding.form = onboardingForm
/**
* @see \App\Http\Controllers\BusinessController::storeOnboarding
 * @see app/Http/Controllers/BusinessController.php:146
 * @route '/dashboard/business/onboarding'
 */
export const storeOnboarding = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeOnboarding.url(options),
    method: 'post',
})

storeOnboarding.definition = {
    methods: ["post"],
    url: '/dashboard/business/onboarding',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BusinessController::storeOnboarding
 * @see app/Http/Controllers/BusinessController.php:146
 * @route '/dashboard/business/onboarding'
 */
storeOnboarding.url = (options?: RouteQueryOptions) => {
    return storeOnboarding.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::storeOnboarding
 * @see app/Http/Controllers/BusinessController.php:146
 * @route '/dashboard/business/onboarding'
 */
storeOnboarding.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeOnboarding.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BusinessController::storeOnboarding
 * @see app/Http/Controllers/BusinessController.php:146
 * @route '/dashboard/business/onboarding'
 */
    const storeOnboardingForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeOnboarding.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BusinessController::storeOnboarding
 * @see app/Http/Controllers/BusinessController.php:146
 * @route '/dashboard/business/onboarding'
 */
        storeOnboardingForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeOnboarding.url(options),
            method: 'post',
        })
    
    storeOnboarding.form = storeOnboardingForm
/**
* @see \App\Http\Controllers\BusinessController::cancelOnboarding
 * @see app/Http/Controllers/BusinessController.php:134
 * @route '/dashboard/business/onboarding/cancel'
 */
export const cancelOnboarding = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancelOnboarding.url(options),
    method: 'post',
})

cancelOnboarding.definition = {
    methods: ["post"],
    url: '/dashboard/business/onboarding/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BusinessController::cancelOnboarding
 * @see app/Http/Controllers/BusinessController.php:134
 * @route '/dashboard/business/onboarding/cancel'
 */
cancelOnboarding.url = (options?: RouteQueryOptions) => {
    return cancelOnboarding.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::cancelOnboarding
 * @see app/Http/Controllers/BusinessController.php:134
 * @route '/dashboard/business/onboarding/cancel'
 */
cancelOnboarding.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancelOnboarding.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BusinessController::cancelOnboarding
 * @see app/Http/Controllers/BusinessController.php:134
 * @route '/dashboard/business/onboarding/cancel'
 */
    const cancelOnboardingForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancelOnboarding.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BusinessController::cancelOnboarding
 * @see app/Http/Controllers/BusinessController.php:134
 * @route '/dashboard/business/onboarding/cancel'
 */
        cancelOnboardingForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancelOnboarding.url(options),
            method: 'post',
        })
    
    cancelOnboarding.form = cancelOnboardingForm
/**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
 * @see app/Http/Controllers/BusinessController.php:180
 * @route '/dashboard/business/wallet/checkout'
 */
export const createWalletCheckout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: createWalletCheckout.url(options),
    method: 'post',
})

createWalletCheckout.definition = {
    methods: ["post"],
    url: '/dashboard/business/wallet/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
 * @see app/Http/Controllers/BusinessController.php:180
 * @route '/dashboard/business/wallet/checkout'
 */
createWalletCheckout.url = (options?: RouteQueryOptions) => {
    return createWalletCheckout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
 * @see app/Http/Controllers/BusinessController.php:180
 * @route '/dashboard/business/wallet/checkout'
 */
createWalletCheckout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: createWalletCheckout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
 * @see app/Http/Controllers/BusinessController.php:180
 * @route '/dashboard/business/wallet/checkout'
 */
    const createWalletCheckoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: createWalletCheckout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
 * @see app/Http/Controllers/BusinessController.php:180
 * @route '/dashboard/business/wallet/checkout'
 */
        createWalletCheckoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: createWalletCheckout.url(options),
            method: 'post',
        })
    
    createWalletCheckout.form = createWalletCheckoutForm
const BusinessController = { dashboard, onboarding, storeOnboarding, cancelOnboarding, createWalletCheckout }

export default BusinessController