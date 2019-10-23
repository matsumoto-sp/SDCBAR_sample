module.exports = {
  devServer: {
    public: '0.0.0.0:8080',
    proxy: {
      '^/api': {
        target: 'http://sdcbar_php:8080',
        ws: true,
      },
    }
  },
  outputDir: '/output/html'
}