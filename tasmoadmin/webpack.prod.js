const common = require('./webpack.common');
const {merge} = require('webpack-merge');
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

module.exports = merge(common, {
    mode: 'production',
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader'
                ],
                exclude: /node_modules/
            }
        ]
    },
    plugins: [new MiniCssExtractPlugin({filename: '[name].css'})]
});
