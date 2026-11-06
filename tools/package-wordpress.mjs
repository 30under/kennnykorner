import { cp, mkdir, rm, writeFile } from "node:fs/promises"
import { execFile } from "node:child_process"
import { promisify } from "node:util"
import path from "node:path"
import { fileURLToPath } from "node:url"

const exec = promisify(execFile)
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..")
const dist = path.join(root, "dist")
const stage = path.join(dist, "stage")

await rm(stage, { recursive: true, force: true })
await mkdir(stage, { recursive: true })
await exec("node", ["tools/generate-media-manifest.mjs"], { cwd: root })
const { stdout } = await exec("php", [
  "-r",
  `echo json_encode(require ${JSON.stringify(path.join(root, "wordpress/plugins/kennys-korner-core/includes/default-content.php"))}, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);`,
])
await writeFile(path.join(root, "wordpress/plugins/kennys-korner-core/seed/content.json"), stdout + "\n")

await copyClean(path.join(root, "wordpress/themes/kennys-korner"), path.join(stage, "kennys-korner"))
await copyClean(path.join(root, "wordpress/plugins/kennys-korner-core"), path.join(stage, "kennys-korner-core"))
await zipDirectory(path.join(stage, "kennys-korner"), path.join(dist, "kennys-korner-theme.zip"))
await zipDirectory(path.join(stage, "kennys-korner-core"), path.join(dist, "kennys-korner-core.zip"))

const seedStage = path.join(stage, "seed")
await mkdir(seedStage, { recursive: true })
await cp(path.join(root, "public/images"), path.join(seedStage, "images"), { recursive: true })
await cp(path.join(root, "public/menu"), path.join(seedStage, "menu"), { recursive: true })
await cp(path.join(root, "public/videos"), path.join(seedStage, "videos"), { recursive: true })
await cp(path.join(root, "wordpress/plugins/kennys-korner-core/seed/media-manifest.json"), path.join(seedStage, "media-manifest.json"))
await zipDirectory(seedStage, path.join(dist, "kennys-korner-seed.zip"))
console.log("Wrote dist/kennys-korner-theme.zip")
console.log("Wrote dist/kennys-korner-core.zip")
console.log("Wrote dist/kennys-korner-seed.zip")

async function copyClean(from, to) {
  await cp(from, to, {
    recursive: true,
    filter: (source) => !source.split(path.sep).some((part) => part === "node_modules" || part === ".DS_Store"),
  })
}

async function zipDirectory(directory, destination) {
  await rm(destination, { force: true })
  await exec("zip", ["-r", "-q", destination, path.basename(directory)], { cwd: path.dirname(directory) })
}
