import { terser } from 'rollup-plugin-terser';
import { nodeResolve } from '@rollup/plugin-node-resolve';

export default {
  input: 'js/app.js',
  output: {
    file: 'public/app.js',
    format: 'esm',
    plugins: [terser()],
  },
  plugins: [nodeResolve()],
};
