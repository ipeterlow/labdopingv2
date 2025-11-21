<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Beaker, Book, BookUser, FilePenLine, LayoutGrid, TestTube } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [

    {
        title: 'Recepcion Muestras',
        href: '/dopingsample',
        icon: TestTube,
    },
    {
        title: 'Informes Muestras',
        href: '/reportsample',
        icon: FilePenLine,
    },
    {
        title: 'Reporte Muestras',
        href: '/sample',
        icon: Beaker,
    },
    {
        title: 'Libro Ingre. Orina',
        href: '/bookurinesample',
        icon: Book,
    },
    {
        title: 'Libro Ingre. Pelo',
        href: '/booksample/hair',
        icon: Book,
    },
    {
        title: 'Libro Ingre. Saliva',
        href: '/booksample/saliva',
        icon: Book,
    },
      {
        title: 'Empresas',
        href: '/company',
        icon: BookUser,
    },
];

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
