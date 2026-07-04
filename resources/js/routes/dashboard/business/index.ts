import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import onboardingC947a0 from './onboarding'
import wallet from './wallet'
/**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:159
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
 * @see app/Http/Controllers/BusinessController.php:159
 * @route '/dashboard/business/onboarding'
 */
onboarding.url = (options?: RouteQueryOptions) => {
    return onboarding.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:159
 * @route '/dashboard/business/onboarding'
 */
onboarding.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: onboarding.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:159
 * @route '/dashboard/business/onboarding'
 */
onboarding.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: onboarding.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:159
 * @route '/dashboard/business/onboarding'
 */
    const onboardingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: onboarding.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:159
 * @route '/dashboard/business/onboarding'
 */
        onboardingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: onboarding.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BusinessController::onboarding
 * @see app/Http/Controllers/BusinessController.php:159
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
const business = {
    onboarding: Object.assign(onboarding, onboardingC947a0),
wallet: Object.assign(wallet, wallet),
}

export default business