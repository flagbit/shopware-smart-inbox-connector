const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const path = require("path");

const isProd = process.env.mode === 'production';

module.exports = {
    mode: isProd ? 'production' : 'development',
    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    warnings: false,
                    output: 6
                },
                parallel: true
            }),
            new OptimizeCSSAssetsPlugin()
        ]
    },

    resolve: {
        extensions: ['.js', '.vue', '.json', '.less', '.twig'],
    },

    module: {
        rules: [
            {
                test: /\.(html|twig)$/,
                loader: 'html-loader'
            },
        ]
    },

    entry: {
        main: './src/Resources/app/administration/src/main.js',
    },

    output: {
        path: path.resolve(__dirname, 'src/Resources/public/administration/js'),
        filename: 'mail-m-smart-inbox-connector.js',
    },
};
