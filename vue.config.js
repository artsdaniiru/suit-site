const {
  defineConfig
} = require('@vue/cli-service');

const path = require("path");
module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    static: {
      directory: path.join(__dirname, 'public'),
      watch: false, // Отключаем наблюдение за всеми файлами в папке public
    },
    watchFiles: ['src/**/*'], // Следим только за файлами в src
  },
  css: {
    loaderOptions: {
      sass: {
        // @/ is an alias to src/
        additionalData: `@import "@/assets/main.scss";`
      }
    }
  }
})

// module.exports = {
//   publicPath: "/~k237034"
// }