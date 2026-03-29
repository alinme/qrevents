import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EventController::toggle
* @see app/Http/Controllers/EventController.php:2012
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
export const toggle = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggle.url(args, options),
    method: 'post',
})

toggle.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/assets/{asset}/likes/toggle',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::toggle
* @see app/Http/Controllers/EventController.php:2012
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
toggle.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return toggle.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::toggle
* @see app/Http/Controllers/EventController.php:2012
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
toggle.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggle.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::toggle
* @see app/Http/Controllers/EventController.php:2012
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
const toggleForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggle.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::toggle
* @see app/Http/Controllers/EventController.php:2012
* @route '/a/{shareToken}/assets/{asset}/likes/toggle'
*/
toggleForm.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggle.url(args, options),
    method: 'post',
})

toggle.form = toggleForm

const assetLike = {
    toggle: Object.assign(toggle, toggle),
}

export default assetLike