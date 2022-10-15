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
    output: {
        filename: '[name].bundle.js',
        path: path.resolve(__dirname, 'resources/dist'),
    },
};
