import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::start
* @see app/Http/Controllers/DashboardController.php:109
* @route '/dashboard/business/actions/start-exports'
*/
export const start = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: start.url(options),
    method: 'post',
})

start.definition = {
    methods: ["post"],
    url: '/dashboard/business/actions/start-exports',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\DashboardController::start
* @see app/Http/Controllers/DashboardController.php:109
* @route '/dashboard/business/actions/start-exports'
*/
start.url = (options?: RouteQueryOptions) => {
    return start.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::start
* @see app/Http/Controllers/DashboardController.php:109
* @route '/dashboard/business/actions/start-exports'
*/
start.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: start.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DashboardController::start
* @see app/Http/Controllers/DashboardController.php:109
* @route '/dashboard/business/actions/start-exports'
*/
const startForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: start.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\DashboardController::start
* @see app/Http/Controllers/DashboardController.php:109
* @route '/dashboard/business/actions/start-exports'
*/
startForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: start.url(options),
    method: 'post',
})

start.form = startForm

const exports = {
    start: Object.assign(start, start),
}

export default exports