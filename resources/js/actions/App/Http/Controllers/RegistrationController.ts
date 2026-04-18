import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\RegistrationController::business
* @see app/Http/Controllers/RegistrationController.php:12
* @route '/register/business'
*/
export const business = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: business.url(options),
    method: 'get',
})

business.definition = {
    methods: ["get","head"],
    url: '/register/business',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\RegistrationController::business
* @see app/Http/Controllers/RegistrationController.php:12
* @route '/register/business'
*/
business.url = (options?: RouteQueryOptions) => {
    return business.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RegistrationController::business
* @see app/Http/Controllers/RegistrationController.php:12
* @route '/register/business'
*/
business.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RegistrationController::business
* @see app/Http/Controllers/RegistrationController.php:12
* @route '/register/business'
*/
business.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: business.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RegistrationController::business
* @see app/Http/Controllers/RegistrationController.php:12
* @route '/register/business'
*/
const businessForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RegistrationController::business
* @see app/Http/Controllers/RegistrationController.php:12
* @route '/register/business'
*/
businessForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RegistrationController::business
* @see app/Http/Controllers/RegistrationController.php:12
* @route '/register/business'
*/
businessForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: business.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

business.form = businessForm

const RegistrationController = { business }

export default RegistrationController