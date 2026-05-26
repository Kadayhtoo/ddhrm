import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/layouts/MainLayout.vue';
import AdminPanelPage from '@/pages/AdminPanelPage.vue';
import DashboardPage from '@/pages/DashboardPage.vue';
import LoginPage from '@/pages/LoginPage.vue';
import PlaceholderPage from '@/pages/PlaceholderPage.vue';
import RoleDetailPage from '@/pages/RoleDetailPage.vue';
import RoleListViewPage from '@/pages/RoleListViewPage.vue';
import StaffManagePage from '@/pages/StaffManagePage.vue';
import ProfilePage from '@/pages/ProfilePage.vue';
import DepartmentManagePage from '@/pages/DepartmentManagePage.vue';
import LeaveRuleManagePage from '@/pages/LeaveRuleManagePage.vue';
import LeaveRequestManagePage from '@/pages/LeaveRequestManagePage.vue';
import LeaveBalancePage from '@/pages/LeaveBalancePage.vue';
import PositionManagePage from '@/pages/PositionManagePage.vue';
import StaffDetailPage from '@/pages/StaffDetailPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: LoginPage,
            meta: { guestOnly: true },
        },
        {
            path: '/',
            component: MainLayout,
            meta: { requiresAuth: true },
            children: [
                {
                    path: '',
                    name: 'dashboard',
                    component: DashboardPage,
                    meta: { title: 'Dashboard', permission: 'dashboard.view' },
                },
                {
                    path: 'admin',
                    name: 'admin',
                    component: AdminPanelPage,
                    meta: { title: 'Admin panel', permission: 'admin.access' },
                },
                {
                    path: 'department',
                    name: 'department',
                    component: DepartmentManagePage,
                    meta: { title: 'Department', permission: 'departments.view' },
                },
                {
                    path: 'position',
                    name: 'position',
                    component: PositionManagePage,
                    meta: { title: 'Position', permission: 'positions.view' },
                },
                {
                    path: 'roles',
                    name: 'roles',
                    component: RoleListViewPage,
                    meta: { title: 'Roles', permission: 'roles.view' },
                },
                {
                    path: 'staff',
                    name: 'staff',
                    component: StaffManagePage,
                    meta: { title: 'Staff', permission: 'staff.view' },
                },
                {
                    path: '/staff/:user', 
                    name: 'staff.detail', 
                    component:StaffDetailPage, 
                    meta: { requiresAuth: true }
                },
                {
                    path: 'attendance',
                    name: 'attendance',
                    component: PlaceholderPage,
                    meta: { title: 'Attendance' },
                    props: { title: 'Attendance', subtitle: 'Clock in/out, reports, and rules (per your PDF MVP).' },
                },
                {
                    path: 'payroll',
                    name: 'payroll',
                    component: PlaceholderPage,
                    meta: { title: 'Payroll', permission: ['payroll.view'] },
                    props: { title: 'Payroll', subtitle: 'Monthly payroll, payslip PDF, linked to attendance.' },
                },
                {
                    path: 'leave-rules',
                    name: 'leave-rules',
                    component: LeaveRuleManagePage,
                    meta: { title: 'Leave Rule' },
                    props: { title: 'Leave', subtitle: 'Leave rules, applications, approvals.' },
                },
                {
                    path: 'leave-requests',
                    name: 'leave-requests',
                    component: LeaveRequestManagePage,
                    meta: { title: 'Leave Request' },
                },
                {
                    path: 'leave-balances',
                    name: 'leave-balances',
                    component: LeaveBalancePage,
                    meta: { title: 'Leave Balance' },
                },
                {
                    path: 'invoices',
                    name: 'invoices',
                    component: PlaceholderPage,
                    meta: { title: 'Invoices', permission: ['invoices.view'] },
                    props: { title: 'Client invoices', subtitle: 'Clients, projects, invoices, payments.' },
                },
                {
                    path: 'profile', 
                    name: 'profile',
                    component: ProfilePage,
                    meta: { title: 'Account Profile' },
                },
            ],
        },
    ],

});

router.beforeEach(async (to, _from, next) => {
    const auth = useAuthStore();

    if (to.meta.requiresAuth && !auth.token) {
        return next({ name: 'login', query: { redirect: to.fullPath } });
    }

    if (to.meta.guestOnly && auth.token) {
        return next({ name: 'dashboard' });
    }

    if (to.meta.requiresAuth && auth.token && !auth.user) {
        try {
            await auth.fetchMe();
        } catch {
            auth.clearSession();
            return next({ name: 'login' });
        }
    }

    if (to.meta.requiresAuth && auth.token && to.meta.permission) {
        const need = to.meta.permission;
        const list = Array.isArray(need) ? need : [need];
        if (!auth.canAny(list) && !auth.hasRoleSlug('admin')) {
            return next({ name: 'dashboard' });
        }
    }

    return next();
});

export default router;
