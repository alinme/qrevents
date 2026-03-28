import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import onboardingC947a0 from './onboarding'
import wallet from './wallet'
import exports from './exports'
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
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:262
* @route '/dashboard/business/actions/billing-queue'
*/
export const billingQueue = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: billingQueue.url(options),
    method: 'get',
})

billingQueue.definition = {
    methods: ["get","head"],
    url: '/dashboard/business/actions/billing-queue',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:262
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueue.url = (options?: RouteQueryOptions) => {
    return billingQueue.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:262
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueue.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: billingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:262
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueue.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: billingQueue.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:262
* @route '/dashboard/business/actions/billing-queue'
*/
const billingQueueForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:262
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueueForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billingQueue.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::billingQueue
* @see app/Http/Controllers/DashboardController.php:262
* @route '/dashboard/business/actions/billing-queue'
*/
billingQueueForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: billingQueue.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

billingQueue.form = billingQueueForm

const business = {
    activate: Object.assign(activate, activate),
    onboarding: Object.assign(onboarding, onboardingC947a0),
    wallet: Object.assign(wallet, wallet),
    exports: Object.assign(exports, exports),
    billingQueue: Object.assign(billingQueue, billingQueue),
}

export default business