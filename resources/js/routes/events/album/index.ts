import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import guestProfile from './guest-profile'
import assetLike from './asset-like'
import assetComments from './asset-comments'
import assetCommentLike from './asset-comment-like'
/**
* @see \App\Http\Controllers\EventController::assets
* @see app/Http/Controllers/EventController.php:1299
* @route '/a/{shareToken}/assets'
*/
export const assets = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assets.url(args, options),
    method: 'get',
})

assets.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/assets',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::assets
* @see app/Http/Controllers/EventController.php:1299
* @route '/a/{shareToken}/assets'
*/
assets.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return assets.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::assets
* @see app/Http/Controllers/EventController.php:1299
* @route '/a/{shareToken}/assets'
*/
assets.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assets.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assets
* @see app/Http/Controllers/EventController.php:1299
* @route '/a/{shareToken}/assets'
*/
assets.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: assets.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::assets
* @see app/Http/Controllers/EventController.php:1299
* @route '/a/{shareToken}/assets'
*/
const assetsForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assets.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assets
* @see app/Http/Controllers/EventController.php:1299
* @route '/a/{shareToken}/assets'
*/
assetsForm.get = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assets.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assets
* @see app/Http/Controllers/EventController.php:1299
* @route '/a/{shareToken}/assets'
*/
assetsForm.head = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assets.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

assets.form = assetsForm

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1400
* @route '/a/{shareToken}/uploads'
*/
export const upload = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

upload.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/uploads',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1400
* @route '/a/{shareToken}/uploads'
*/
upload.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return upload.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1400
* @route '/a/{shareToken}/uploads'
*/
upload.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1400
* @route '/a/{shareToken}/uploads'
*/
const uploadForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::upload
* @see app/Http/Controllers/EventController.php:1400
* @route '/a/{shareToken}/uploads'
*/
uploadForm.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(args, options),
    method: 'post',
})

upload.form = uploadForm

/**
* @see \App\Http\Controllers\EventController::textPost
* @see app/Http/Controllers/EventController.php:1594
* @route '/a/{shareToken}/text-posts'
*/
export const textPost = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: textPost.url(args, options),
    method: 'post',
})

textPost.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/text-posts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::textPost
* @see app/Http/Controllers/EventController.php:1594
* @route '/a/{shareToken}/text-posts'
*/
textPost.url = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shareToken: args }
    }

    if (Array.isArray(args)) {
        args = {
            shareToken: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        shareToken: args.shareToken,
    }

    return textPost.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::textPost
* @see app/Http/Controllers/EventController.php:1594
* @route '/a/{shareToken}/text-posts'
*/
textPost.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: textPost.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::textPost
* @see app/Http/Controllers/EventController.php:1594
* @route '/a/{shareToken}/text-posts'
*/
const textPostForm = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: textPost.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::textPost
* @see app/Http/Controllers/EventController.php:1594
* @route '/a/{shareToken}/text-posts'
*/
textPostForm.post = (args: { shareToken: string | number } | [shareToken: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: textPost.url(args, options),
    method: 'post',
})

textPost.form = textPostForm

/**
* @see \App\Http\Controllers\EventController::assetDownload
* @see app/Http/Controllers/EventController.php:1950
* @route '/a/{shareToken}/assets/{asset}/download'
*/
export const assetDownload = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assetDownload.url(args, options),
    method: 'get',
})

assetDownload.definition = {
    methods: ["get","head"],
    url: '/a/{shareToken}/assets/{asset}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EventController::assetDownload
* @see app/Http/Controllers/EventController.php:1950
* @route '/a/{shareToken}/assets/{asset}/download'
*/
assetDownload.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return assetDownload.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::assetDownload
* @see app/Http/Controllers/EventController.php:1950
* @route '/a/{shareToken}/assets/{asset}/download'
*/
assetDownload.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: assetDownload.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assetDownload
* @see app/Http/Controllers/EventController.php:1950
* @route '/a/{shareToken}/assets/{asset}/download'
*/
assetDownload.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: assetDownload.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EventController::assetDownload
* @see app/Http/Controllers/EventController.php:1950
* @route '/a/{shareToken}/assets/{asset}/download'
*/
const assetDownloadForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assetDownload.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assetDownload
* @see app/Http/Controllers/EventController.php:1950
* @route '/a/{shareToken}/assets/{asset}/download'
*/
assetDownloadForm.get = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assetDownload.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EventController::assetDownload
* @see app/Http/Controllers/EventController.php:1950
* @route '/a/{shareToken}/assets/{asset}/download'
*/
assetDownloadForm.head = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: assetDownload.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

assetDownload.form = assetDownloadForm

/**
* @see \App\Http\Controllers\EventController::assetDelete
* @see app/Http/Controllers/EventController.php:1968
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
export const assetDelete = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assetDelete.url(args, options),
    method: 'post',
})

assetDelete.definition = {
    methods: ["post"],
    url: '/a/{shareToken}/assets/{asset}/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EventController::assetDelete
* @see app/Http/Controllers/EventController.php:1968
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
assetDelete.url = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return assetDelete.definition.url
            .replace('{shareToken}', parsedArgs.shareToken.toString())
            .replace('{asset}', parsedArgs.asset.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EventController::assetDelete
* @see app/Http/Controllers/EventController.php:1968
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
assetDelete.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assetDelete.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::assetDelete
* @see app/Http/Controllers/EventController.php:1968
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
const assetDeleteForm = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: assetDelete.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EventController::assetDelete
* @see app/Http/Controllers/EventController.php:1968
* @route '/a/{shareToken}/assets/{asset}/delete'
*/
assetDeleteForm.post = (args: { shareToken: string | number, asset: number | { id: number } } | [shareToken: string | number, asset: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: assetDelete.url(args, options),
    method: 'post',
})

assetDelete.form = assetDeleteForm

const album = {
    assets: Object.assign(assets, assets),
    guestProfile: Object.assign(guestProfile, guestProfile),
    upload: Object.assign(upload, upload),
    textPost: Object.assign(textPost, textPost),
    assetLike: Object.assign(assetLike, assetLike),
    assetComments: Object.assign(assetComments, assetComments),
    assetCommentLike: Object.assign(assetCommentLike, assetCommentLike),
    assetDownload: Object.assign(assetDownload, assetDownload),
    assetDelete: Object.assign(assetDelete, assetDelete),
}

export default album