const { defineConfig } = require('@vue/cli-service')
const webpack = require('webpack')

module.exports = defineConfig({
  transpileDependencies: [],
  devServer: {
    port: 8080,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      }
    }
  },
  configureWebpack: {
    resolve: {
      fallback: {
        buffer: require.resolve('buffer/'),
        process: require.resolve('process/browser'),
        stream: require.resolve('stream-browserify'),
        url: require.resolve('url/'),
        util: require.resolve('util/')
      }
    },
    plugins: [
      new webpack.ProvidePlugin({
        Buffer: ['buffer', 'Buffer'],
        process: 'process/browser',
      })
    ]
  }

})
