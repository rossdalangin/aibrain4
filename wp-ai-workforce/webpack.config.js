const path = require('path');

module.exports = {
  entry: './src/UI/Dashboard.js',
  output: {
    path: path.resolve(__dirname, 'assets/js'),
    filename: 'admin.js',
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react'],
          },
        },
      },
    ],
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  externals: {
    'react': 'React',
    'react-dom': 'ReactDOM',
  },
};
