import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:338
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
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
run.url = (options?: RouteQueryOptions) => {
    return run.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
run.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: run.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
    const runForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: run.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::run
 * @see app/Http/Controllers/Admin/SettingsController.php:338
 * @route '/admin/settings/devtools/run'
 */
        runForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: run.url(options),
            method: 'post',
        })
    
    run.form = runForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::backup
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
export const backup = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: backup.url(options),
    method: 'get',
})

backup.definition = {
    methods: ["get","head"],
    url: '/admin/settings/devtools/sql-backup',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::backup
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
backup.url = (options?: RouteQueryOptions) => {
    return backup.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::backup
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
backup.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: backup.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\SettingsController::backup
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
backup.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: backup.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::backup
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
    const backupForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: backup.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::backup
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
        backupForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: backup.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\SettingsController::backup
 * @see app/Http/Controllers/Admin/SettingsController.php:365
 * @route '/admin/settings/devtools/sql-backup'
 */
        backupForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: backup.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    backup.form = backupForm
const devtools = {
    run: Object.assign(run, run),
backup: Object.assign(backup, backup),
}

export default devtools