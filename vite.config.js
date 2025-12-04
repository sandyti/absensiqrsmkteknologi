import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    const resolveNumber = (value, fallback) => {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    };

    const resolveAppHost = () => {
        try {
            return new URL(env.APP_URL ?? 'http://localhost').hostname;
        } catch {
            return 'localhost';
        }
    };

    const devServerHost = env.VITE_DEV_SERVER_HOST || '0.0.0.0';
    const devServerPort = resolveNumber(env.VITE_DEV_SERVER_PORT, 5173);
    const hmrHost = env.VITE_HMR_HOST || resolveAppHost();
    const hmrPort = resolveNumber(env.VITE_HMR_PORT, devServerPort);
    const useHttps = (env.VITE_DEV_SERVER_HTTPS || '').toLowerCase() === 'true';
    const devProtocol = useHttps ? 'https' : 'http';
    const devOrigin =
        env.VITE_DEV_SERVER_ORIGIN || `${devProtocol}://${hmrHost}:${hmrPort}`;

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
        server: {
            host: devServerHost,
            port: devServerPort,
            origin: devOrigin,
            hmr: {
                host: hmrHost,
                port: hmrPort,
                protocol: devProtocol,
            },
        },
    };
});
