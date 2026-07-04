import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:320
 * @route '/admin/settings/devtools/run'
 */
export const run = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: run.url(options),
    method: 'post',
})

run.definition = {
    methods: ["post"],
    url: '/admin/settings/devtools/run',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:320
 * @route '/admin/settings/devtools/run'
 */
run.url = (options?: RouteQueryOptions) => {
    return run.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:320
 * @route '/admin/settings/devtools/run'
 */
run.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: run.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:320
 * @route '/admin/settings/devtools/run'
 */
    const runForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: run.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:320
 * @route '/admin/settings/devtools/run'
 */
        runForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: run.url(options),
            method: 'post',
        })
    
    run.form = runForm
const devtools = {
    run: Object.assign(run, run),
}

export default devtools