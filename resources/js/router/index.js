import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import MainLayout from '@/layouts/MainLayout.vue';
import AdminPanelPage from '@/pages/AdminPanelPage.vue';
import AdminAttendanceTablePage from '@/pages/AdminAttendanceTablePage.vue';
import AttendanceDetailPage from '@/pages/AttendanceDetailPage.vue';
import AttendanceReportsPage from '@/pages/AttendanceReportsPage.vue';
import AttendanceSettingsPage from '@/pages/AttendanceSettingsPage.vue';
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
import ClientManagePage from '@/pages/ClientManagePage.vue';
import InvoiceManagePage from '@/pages/InvoiceManagePage.vue';
import EstimateManagePage from '@/pages/EstimateManagePage.vue';
import ClientDetailPage from '@/pages/ClientDetailPage.vue';
import InvoicePreviewPage from '@/pages/InvoicePreviewPage.vue';
import EstimatePreviewPage from '@/pages/EstimatePreviewPage.vue';
import AboutUsPage from '@/pages/AboutUsPage.vue';

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
                    path: 'company-info',
                    name: 'company-info',
                    component: AboutUsPage,
                    meta: { title: 'Company Info' },
                    props: { title: 'Company Information', subtitle: 'Manage invoice company profile and contact details.' },
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
                    meta: { title: 'Staff Detail' }
                },
                {
                    path: 'attendance',
                    name: 'attendance',
                    component: AdminAttendanceTablePage,
                    meta: { title: 'Attendance', permission: 'attendance.view' },
                },
                {
                    path: 'attendance/settings',
                    name: 'attendance.settings',
                    component: AttendanceSettingsPage,
                    meta: { title: 'Attendance Settings', permission: 'attendance.manage' },
                },
                {
                    path: 'attendance/reports',
                    name: 'attendance.reports',
                    component: AttendanceReportsPage,
                    meta: { title: 'Attendance Reports', permission: 'attendance.manage' },
                },
                {
                    path: 'attendance/:id',
                    name: 'attendance.details',
                    component: AttendanceDetailPage,
                    meta: { title: 'Attendance Details', permission: 'attendance.view' },
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
                    path: 'clients',
                    name: 'clients',
                    component: ClientManagePage,
                    meta: { title: 'Clients' },
                    props: { title: 'Client management', subtitle: 'Manage clients, projects, and related info.' },
                },
                {
                    path: '/clients/:id', 
                    name: 'clients-detail', 
                    component: ClientDetailPage,
                    meta:{ title: 'Client Detail' }, 
                },
                {
                    path: 'invoices',
                    name: 'invoices',
                    component: InvoiceManagePage,
                    meta: { title: 'Invoices', permission: ['invoices.view'] },
                    props: { title: 'Client invoices', subtitle: 'Clients, projects, invoices, payments.' },
                },
                {
                    path:'estimates',
                    name:'estimates',
                    component: EstimateManagePage,
                    meta: { title: 'Estimates', permission: ['estimates.view'] },
                    props: { title: 'Estimates', subtitle: 'Client estimates, linked to projects and invoices.' },
                },
                {
                    path: '/invoices/:id/preview',
                    name: 'InvoicePreview',
                    component: InvoicePreviewPage,
                    meta: {title: 'Invoice Preview'}
                },
                {
                    path: '/estimates/:id/preview',
                    name: 'EstimatePreview',
                    component: EstimatePreviewPage,
                    meta: { title: 'Estimate Preview' }
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
