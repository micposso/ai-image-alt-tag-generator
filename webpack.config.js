const path = require("path");

module.exports = {
  entry: "./src/index.js", // Entry point for your JS
  output: {
    filename: "bundle.js", // Output filename
    path: path.resolve(__dirname, "dist"), // Output directory
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ["@babel/preset-env"], // Transpile ES6+
          },
        },
      },
    ],
  },
  mode: "production", // Can also be 'development'
};
