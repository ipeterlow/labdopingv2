// resources/js/types/inertia.d.ts
import '@inertiajs/core';

declare module '@inertiajs/core' {
    interface PageProps {
        auth: {
            user: { id: number; name: string; email: string } | null;
            roles: string[];
            permissions: string[];
        };
        // Puedes agregar también tu paginación de users aquí:
        users?: {
            data?: import('@/types').User[]; // ajusta la ruta a tu tipo User
            current_page?: number;
            last_page?: number;
            total?: number;
            per_page?: number;
        };
    }
}
