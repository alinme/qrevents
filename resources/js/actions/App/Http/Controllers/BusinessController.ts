import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BusinessController::activate
* @see app/Http/Controllers/BusinessController.php:18
* @route '/dashboard/business/activate'
*/
export const activate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: activate.url(options),
    method: 'post',
})

activate.definition = {
    methods: ["post"],
    url: '/dashboard/business/activate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\BusinessController::activate
* @see app/Http/Controllers/BusinessController.php:18
* @route '/dashboard/business/activate'
*/
activate.url = (options?: RouteQueryOptions) => {
    return activate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::activate
* @see app/Http/Controllers/BusinessController.php:18
* @route '/dashboard/business/activate'
*/
activate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: activate.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::activate
* @see app/Http/Controllers/BusinessController.php:18
* @route '/dashboard/business/activate'
*/
const activateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: activate.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::activate
* @see app/Http/Controllers/BusinessController.php:18
* @route '/dashboard/business/activate'
*/
activateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: activate.url(options),
    method: 'post',
})

activate.form = activateForm

/**
* @see \App\Http\Controllers\BusinessController::onboarding
* @see app/Http/Controllers/BusinessController.php:34
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
* @see app/Http/Controllers/BusinessController.php:34
* @route '/dashboard/business/onboarding'
*/
onboarding.url = (options?: RouteQueryOptions) => {
    return onboarding.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::onboarding
* @see app/Http/Controllers/BusinessController.php:34
* @route '/dashboard/business/onboarding'
*/
onboarding.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: onboarding.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BusinessController::onboarding
* @see app/Http/Controllers/BusinessController.php:34
* @route '/dashboard/business/onboarding'
*/
onboarding.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: onboarding.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BusinessController::onboarding
* @see app/Http/Controllers/BusinessController.php:34
* @route '/dashboard/business/onboarding'
*/
const onboardingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: onboarding.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BusinessController::onboarding
* @see app/Http/Controllers/BusinessController.php:34
* @route '/dashboard/business/onboarding'
*/
onboardingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: onboarding.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\BusinessController::onboarding
* @see app/Http/Controllers/BusinessController.php:34
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
* @see app/Http/Controllers/BusinessController.php:60
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
* @see app/Http/Controllers/BusinessController.php:60
* @route '/dashboard/business/onboarding'
*/
storeOnboarding.url = (options?: RouteQueryOptions) => {
    return storeOnboarding.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::storeOnboarding
* @see app/Http/Controllers/BusinessController.php:60
* @route '/dashboard/business/onboarding'
*/
storeOnboarding.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeOnboarding.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::storeOnboarding
* @see app/Http/Controllers/BusinessController.php:60
* @route '/dashboard/business/onboarding'
*/
const storeOnboardingForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeOnboarding.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::storeOnboarding
* @see app/Http/Controllers/BusinessController.php:60
* @route '/dashboard/business/onboarding'
*/
storeOnboardingForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeOnboarding.url(options),
    method: 'post',
})

storeOnboarding.form = storeOnboardingForm

/**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
* @see app/Http/Controllers/BusinessController.php:92
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
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/wallet/checkout'
*/
createWalletCheckout.url = (options?: RouteQueryOptions) => {
    return createWalletCheckout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/wallet/checkout'
*/
createWalletCheckout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: createWalletCheckout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/wallet/checkout'
*/
const createWalletCheckoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: createWalletCheckout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\BusinessController::createWalletCheckout
* @see app/Http/Controllers/BusinessController.php:92
* @route '/dashboard/business/wallet/checkout'
*/
createWalletCheckoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: createWalletCheckout.url(options),
    method: 'post',
})

createWalletCheckout.form = createWalletCheckoutForm

const BusinessController = { activate, onboarding, storeOnboarding, createWalletCheckout }

export default BusinessController