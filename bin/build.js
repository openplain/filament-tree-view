import * as esbuild from 'esbuild'

const isDev = process.argv.includes('--dev')

async function compile(options) {
    const context = await esbuild.context(options)

    if (isDev) {
        console.log('👀 Watching for changes...')
        await context.watch()
    } else {
        console.log('📦 Building for production...')
        await context.rebuild()
        await context.dispose()
        console.log('✅ Build complete!')
    }
}

const defaultOptions = {
    define: {
        'process.env.NODE_ENV': isDev ? `'development'` : `'production'`,
    },
    bundle: true,
    mainFields: ['module', 'main'],
    platform: 'neutral',
    sourcemap: isDev ? 'inline' : false,
    sourcesContent: isDev,
    treeShaking: true,
    target: ['es2020'],
    minify: !isDev,
    logLevel: 'info',
}

compile({
    ...defaultOptions,
    entryPoints: ['./resources/js/index.js'],
    outfile: './resources/dist/filament-tree-view.js',
    format: 'iife',
})
