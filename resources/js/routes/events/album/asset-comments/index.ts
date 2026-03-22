import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::index
* @see app/Http/Controllers/EventController.php:1492
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
export const index = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/assets/{asset}/comments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::index
* @see app/Http/Controllers/EventController.php:1492
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
index.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return index.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::index
* @see app/Http/Controllers/EventController.php:1492
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
index.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::index
* @see app/Http/Controllers/EventController.php:1492
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
index.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::index
* @see app/Http/Controllers/EventController.php:1492
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
const indexForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::index
* @see app/Http/Controllers/EventController.php:1492
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
indexForm.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::index
* @see app/Http/Controllers/EventController.php:1492
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
indexForm.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1526
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
export const store = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/assets/{asset}/comments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1526
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
store.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
            asset: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
        asset: typeof args.asset === 'object'
        ? args.asset.id
        : args.asset,
    }

    return store.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1526
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
store.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1526
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
const storeForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::store
* @see app/Http/Controllers/EventController.php:1526
* @route '/a/{shareToken}/assets/{asset}/comments'
*/
storeForm.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

const assetComments = {
    index: Object.assign(index, index),
    store: Object.assign(store, store),
}

export default assetComments