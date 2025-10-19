import { usePage } from '@inertiajs/vue3';

export function useAuthChecks() {
    const page = usePage();
    const perms = new Set<string>(page.props.auth?.permissions ?? []);
    const roles = new Set<string>(page.props.auth?.roles ?? []);

    const can = (permiso: string) => perms.has(permiso);
    const is = (rol: string) => roles.has(rol);

    return { can, is };
}
