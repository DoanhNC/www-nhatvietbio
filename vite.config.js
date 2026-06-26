import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";
import dotenv from "dotenv";
import fg from "fast-glob";

dotenv.config();

// Cấu hình các entry points động
const entryPatterns = ["resources/js/**", "resources/css/**"];

// Convert *** thành file thật
const inputFiles = fg
    .sync(entryPatterns, {
        onlyFiles: true,
        cwd: process.cwd(),
    })
    .sort();

// console.log("inputFiles+++-----------", inputFiles);

export default defineConfig({
    plugins: [
        laravel({
            input: inputFiles,
            // Thêm Controller và Model vào mảng refresh
            refresh: [
                ...refreshPaths, // Giữ lại các đường dẫn mặc định của Laravel
                "app/Http/Controllers/Admin/**", // Theo dõi tất cả file trong thư mục Controllers
                "app/Http/Controllers/Web/**", // Theo dõi tất cả file trong thư mục Controllers
                // Thêm dòng này để watch folder common services vì không được import qua view nên vite không được chạy
                "resources/js/web/common/services/**",
            ],
        }),
    ],
    // cấu hình để chạy vite
    server: {
        host: "0.0.0.0",
        port: 5173,
        // origin để Laravel Vite Plugin tạo URL đúng port 8173 trong HTML
        origin: "http://localhost:5873",
        // HMR cho Docker: clientPort là port bên ngoài container
        hmr: {
            host: "localhost",
            port: 5173, // Port bên trong container
            clientPort: 5873, // Port mà browser kết nối (đã map ra ngoài)
            protocol: "ws",
        },
        // Watch config cho Docker (file system polling)
        watch: {
            usePolling: true,
        },
    },
    define: {
        __BaseUrl__: JSON.stringify(process.env.APP_URL || ""),
    },
});
