import { cp, mkdir } from 'node:fs/promises'
import { fileURLToPath } from 'node:url'
import path from 'node:path'

const repositoryRoot = fileURLToPath(new URL('../', import.meta.url))
const appRoot = path.join(repositoryRoot, 'v1.23.0')
const outputRoot = path.join(appRoot, 'dist')

await mkdir(outputRoot, { recursive: true })
await Promise.all(['assets', 'vendors'].map((folder) =>
  cp(path.join(appRoot, folder), path.join(outputRoot, folder), { recursive: true }),
))
