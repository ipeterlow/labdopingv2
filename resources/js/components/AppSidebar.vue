<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Beaker, Book, BookUser, FilePenLine, Key, Shield, TestTube, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const { can, canAny, isSuperAdmin } = usePermissions();

// Definición de todos los items con sus permisos requeridos
const allNavItems: (NavItem & { permission?: string; permissions?: string[] })[] = [
    {
        title: 'Recepcion Muestras',
        href: '/dopingsample',
        icon: TestTube,
        permission: 'dopingsample.index',
    },
    {
        title: 'Informes Muestras',
        href: '/reportsample',
        icon: FilePenLine,
        permission: 'reportsample.index',
    },
    {
        title: 'Reporte Muestras',
        href: '/sample',
        icon: Beaker,
        permission: 'sample.index',
    },
    {
        title: 'Libros de Ingreso',
        icon: Book,
        isActive: false,
        permissions: ['bookurinesample.index', 'bookhairsample.index', 'booksalivasample.index'],
        items: [
            {
                title: 'Libro Orina',
                href: '/bookurinesample',
            },
            {
                title: 'Libro Pelo',
                href: '/bookhairsample',
            },
            {
                title: 'Libro Saliva',
                href: '/booksalivasample',
            },
        ],
    },
    {
        title: 'Empresas',
        href: '/company',
        icon: BookUser,
        permission: 'company.index',
    },
    {
        title: 'Usuarios',
        href: '/users',
        icon: Users,
        permission: 'users.index',
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: Shield,
        permission: 'roles.index',
    },
    {
        title: 'Permisos',
        href: '/permissions',
        icon: Key,
        permission: 'permissions.index',
    },
];

// Filtrar items según permisos del usuario
const mainNavItems = computed<NavItem[]>(() => {
    // Si es super-admin, mostrar todo
    if (isSuperAdmin.value) {
        return allNavItems;
    }

    return allNavItems.filter((item) => {
        // Si tiene un permiso específico, verificar
        if (item.permission) {
            return can(item.permission);
        }
        // Si tiene múltiples permisos (para grupos), verificar si tiene al menos uno
        if (item.permissions) {
            return canAny(item.permissions);
        }
        // Si no tiene restricción, mostrar
        return true;
    });
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
