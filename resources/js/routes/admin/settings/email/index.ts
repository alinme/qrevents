import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\SettingsController::update
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/settings/email',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::update
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::update
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
update.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::update
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::update
 * @see app/Http/Controllers/Admin/SettingsController.php:113
 * @route '/admin/settings/email'
 */
        updateForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\SettingsController::test
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
export const test = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: test.url(options),
    method: 'post',
})

test.definition = {
    methods: ["post"],
    url: '/admin/settings/email/test',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\SettingsController::test
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
test.url = (options?: RouteQueryOptions) => {
    return test.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\SettingsController::test
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
test.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: test.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\SettingsController::test
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
    const testForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: test.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\SettingsController::test
 * @see app/Http/Controllers/Admin/SettingsController.php:140
 * @route '/admin/settings/email/test'
 */
        testForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: test.url(options),
            method: 'post',
        })
    
    test.form = testForm
const email = {
    update: Object.assign(update, update),
test: Object.assign(test, test),
}

export default email