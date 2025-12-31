import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const permissions = computed<string[]>(() => {
        const perms = page.props.auth?.permissions;
        return Array.isArray(perms) ? perms : [];
    });

    const roles = computed<string[]>(() => {
        const r = page.props.auth?.roles;
        return Array.isArray(r) ? r : [];
    });

    const user = computed(() => page.props.auth?.user ?? null);

    /**
     * Verifica si el usuario tiene un permiso específico
     */
    const can = (permission: string): boolean => {
        return permissions.value.includes(permission);
    };

    /**
     * Verifica si el usuario tiene alguno de los permisos especificados
     */
    const canAny = (perms: string[]): boolean => {
        return perms.some((p) => permissions.value.includes(p));
    };

    /**
     * Verifica si el usuario tiene todos los permisos especificados
     */
    const canAll = (perms: string[]): boolean => {
        return perms.every((p) => permissions.value.includes(p));
    };

    /**
     * Verifica si el usuario tiene un rol específico
     */
    const hasRole = (role: string): boolean => {
        return roles.value.includes(role);
    };

    /**
     * Verifica si el usuario tiene alguno de los roles especificados
     */
    const hasAnyRole = (roleList: string[]): boolean => {
        return roleList.some((r) => roles.value.includes(r));
    };

    /**
     * Verifica si el usuario es super-admin o administrador
     */
    const isSuperAdmin = computed(() => {
        return roles.value.includes('Super Admin') || roles.value.includes('super-admin') || roles.value.includes('Administrador');
    });

    return {
        permissions,
        roles,
        user,
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole,
        isSuperAdmin,
    };
}
