import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/user-web.css", "resources/js/user-web.js"],
            refresh: ["resources/views/user-web/**/*.blade.php", "app/Http/Controllers/UserWeb/**/*.php"],
        }),
    ],
});
