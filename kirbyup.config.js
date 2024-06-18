import { defineConfig } from 'kirbyup/config'
import { fileURLToPath } from 'node:url'
import { resolve } from 'node:path'

const currentDir = fileURLToPath(new URL('.', import.meta.url))

export default defineConfig({
  alias: {
    '@/': `${resolve(currentDir, 'kirby/panel/src')}/`,
  },
  vite: {},
})
