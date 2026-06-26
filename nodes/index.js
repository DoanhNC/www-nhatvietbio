import { exec } from "child_process";

const child = exec("npm run dev", {
    cwd: process.cwd(),
    env: process.env,
});

child.stdout.on("data", (d) => console.log(d.toString()));
child.stderr.on("data", (d) => console.error(d.toString()));
