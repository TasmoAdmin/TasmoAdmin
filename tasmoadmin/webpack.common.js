const path = require('path');
const webpack = require('webpack')


module.exports = {
    entry: {
        app: './resources/js/app.js',
        device_config: './resources/js/device_config.js',
        device_update: './resources/js/device_update.js',
        devices: './resources/js/devices.js',
        devices_details: './resources/js/devices_details.js',
        start: './resources/js/start.js',
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader'],
                exclude: /node_modules/
            }
        ]
    },
    output: {
        filename: '[name].bundle.js',
        path: path.resolve(__dirname, 'resources/dist/js'),
    },
    watch: true,
};
