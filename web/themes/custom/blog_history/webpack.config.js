const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const FileManagerPlugin = require('filemanager-webpack-plugin')

const path = require('path');
const glob = require('glob');

const componentsSourceEntry = iterateEntryFiles(glob.sync("./js/**/*.js"));
const componentsStylesEntry = iterateEntryFiles(glob.sync("./scss/components**/*.scss"));

const mainEntries =  {
  './js/scripts': './js/main.js',
  './css/styles': './scss/main.scss'
};

const allEntries = {
  ...mainEntries,
  ...componentsSourceEntry,
  ...componentsStylesEntry
};

module.exports = (env, argv) => {
  const isDevMode = argv.mode === "development";
  return {

    mode: isDevMode ? "development" : "production",
    devtool: isDevMode ? "source-map" : false,
    entry: allEntries,
    module: {
      rules: [
        {
          test: /\.s[ac]ss$/i,
          use: [
            {
              loader: MiniCssExtractPlugin.loader
            },
            {
              loader: "css-loader",
              options: {
                sourceMap: true,
                modules: {
                  mode: 'global'
                },
                importLoaders: 1,
                url: false
              }
            },
            {
              loader: 'postcss-loader',
              options: {
                sourceMap: true
              }
            },
            {
              loader: "resolve-url-loader",
              options: {
                sourceMap: true
              }
            },
            {
              loader: 'sass-loader',
              options: {
                sourceMap: true,
                implementation: require("sass")
              }
            },
            {
              loader: "sass-resources-loader",
              options: {
                resources: [
                  path.resolve(__dirname, "scss/config/global.scss"),
                ],
              },
            }
          ]
        },
        {
          test: /\.js$/,
          exclude: /(node_modules|bower_components)/,
          use: {
            loader: "babel-loader",
            options: {
              presets: [["@babel/preset-env", { modules: false }]]
            }
          }
        },
      ],
    },
    output: {
      path: path.resolve(__dirname, 'dist'),
      filename: "[name].js",
      publicPath: "/"
    },
    externals: {
      jquery: 'jQuery'
    },
    plugins: [
      new FileManagerPlugin({
        events: {
          onEnd: [
            {
              delete: ['./dist/css/**/*.js', './dist/css/**/*.js.map']
            }
          ]
        }
      }),
      new MiniCssExtractPlugin()
    ]
  }
}

function normalizeEntryOutput (string) {
  return string
    .replace(/\.[^.]*$/,'')
    .replace(/([a-z])([A-Z])/g, '$1-$2')
    .replace(/[\s_]+/g, '-')
    .replace('./scss', './css')
    .replace('./js', './js')
    .toLowerCase();
}

function iterateEntryFiles (paths) {
  let entry = {};

  paths.forEach(function(path) {
    entry[normalizeEntryOutput(path)] = path;
  });

  return entry;
}
