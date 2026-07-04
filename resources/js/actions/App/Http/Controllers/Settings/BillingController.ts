import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\BillingController::show
 * @see app/Http/Controllers/Settings/BillingController.php:13
 * @route '/settings/billing'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/settings/billing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\BillingController::show
 * @see app/Http/Controllers/Settings/BillingController.php:13
 * @route '/settings/billing'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\BillingController::show
 * @see app/Http/Controllers/Settings/BillingController.php:13
 * @route '/settings/billing'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Settings\BillingController::show
 * @see app/Http/Controllers/Settings/BillingController.php:13
 * @route '/settings/billing'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Settings\BillingController::show
 * @see app/Http/Controllers/Settings/BillingController.php:13
 * @route '/settings/billing'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Settings\BillingController::show
 * @see app/Http/Controllers/Settings/BillingController.php:13
 * @route '/settings/billing'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Settings\BillingController::show
 * @see app/Http/Controllers/Settings/BillingController.php:13
 * @route '/settings/billing'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const BillingController = { show }

export default BillingController