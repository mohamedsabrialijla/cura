// Admin Pages
import AuthLayout from './components/Website/auth/AuthLayout.vue';
import LoginAdmin from './components/Website/adminAuth/Login.vue';
import Dashboard from './components/Dashboard/pages/Dashboard.vue';

import Admins from './components/Dashboard/pages/UserManagement/Admins.vue';
import Users from './components/Dashboard/pages/UserManagement/Users.vue';
import Roles from './components/Dashboard/pages/UserManagement/Roles.vue';

// HR Pages
import Department from './components/Dashboard/pages/HR/Department.vue';
import Section from './components/Dashboard/pages/HR/Section.vue';
import Position from './components/Dashboard/pages/HR/Position.vue';
import Employee from './components/Dashboard/pages/HR/Employee.vue';
import EmployeePosition from './components/Dashboard/pages/HR/EmployeePosition.vue';
import PositionHistory from './components/Dashboard/pages/HR/PositionHistory.vue';

// Master Data Pages
import ItemsCategories from './components/Dashboard/pages/MasterData/ItemsCategories.vue';
import ItemsObjects from './components/Dashboard/pages/MasterData/ItemsObjects.vue';
import ResumeTemplate from './components/Dashboard/pages/MasterData/ResumeTemplate.vue';

// Settings Pages
import Page from './components/Dashboard/pages/Setting/Page.vue';
import Edit from './components/Dashboard/pages/Setting/Edit.vue';
import Contact from './components/Dashboard/pages/Setting/contact.vue';
import Report from './components/Dashboard/pages/Setting/Report.vue';
import Notification from './components/Dashboard/pages/Setting/Notification.vue';
import LandingSlider from './components/Dashboard/pages/Setting/LandingSlider.vue';
import TechnicalSlider from './components/Dashboard/pages/Setting/TechnicalSlider.vue';
import CategoryStrip from './components/Dashboard/pages/Setting/CategoryStrip.vue';
import BrandSlider from './components/Dashboard/pages/Setting/BrandSlider.vue';
import Carts from './components/Dashboard/pages/Carts/Carts.vue';
import Coupons from './components/Dashboard/pages/Coupons/Coupons.vue';
import ShippingMethods from './components/Dashboard/pages/Shipping/ShippingMethods.vue';
import PaymentMethods from './components/Dashboard/pages/PaymentMethods/PaymentMethods.vue';
import Faqs from './components/Dashboard/pages/Faqs/Faqs.vue';

//Ads pages
import Ad from './components/Dashboard/pages/Ads/Ad.vue';


// Orders 
import Order from './components/Dashboard/pages/Orders/Order.vue';



export default [
 {
        path: '/:locale/admin/login',
        component: AuthLayout,
        children: [
            { path: '', component: LoginAdmin, meta: { hideHeaderFooter: true } }
        ]
    },

    // Admin Dashboard Routes
    { 
        path: '/:locale/dashboard', 
        component: Dashboard, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'dashboard' } 
    },
    { 
        path: '/:locale/dashboard/user_management/users', 
        component: Users, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'users' } 
    },
    { 
        path: '/:locale/dashboard/user_management/admins', 
        component: Admins, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'admins' } 
    },
    { 
        path: '/:locale/dashboard/user_management/roles', 
        component: Roles, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'roles' } 
    },

    // HR Routes
    { 
        path: '/:locale/dashboard/HR/departments', 
        component: Department, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'departments' } 
    },
    { 
        path: '/:locale/dashboard/HR/sections', 
        component: Section, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'sections' } 
    },
    { 
        path: '/:locale/dashboard/HR/positions', 
        component: Position, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'positions' } 
    },
    { 
        path: '/:locale/dashboard/HR/employees', 
        component: Employee, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'employees' } 
    },
    { 
        path: '/:locale/dashboard/HR/employee_positions', 
        component: EmployeePosition, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'employee_positions' } 
    },
    { 
        path: '/:locale/dashboard/HR/position_history', 
        component: PositionHistory, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'position_history' } 
    },

    // Master Data Routes
    {
        path: '/:locale/dashboard/master_data/category/:category',
        component: ItemsCategories,
        props: true,
        meta: { requiresAuth: true, role: 'admin' },
        beforeEnter: (to, from, next) => {
            to.meta.breadcrumbKey = 'master_data_category';
            to.meta.breadcrumbParam = to.params.category;
            next();
        }
    },
    { 
        path: '/:locale/dashboard/master_data/resume_template', 
        component: ResumeTemplate, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'resume_template' } 
    },
    {
        path: '/:locale/dashboard/master_data/:object',
        component: ItemsObjects,
        props: true,
        meta: { requiresAuth: true, role: 'admin' },
        beforeEnter: (to, from, next) => {
            to.meta.breadcrumbKey = 'master_data_object';
            to.meta.breadcrumbParam = to.params.object;
            next();
        }
    },



    // Settings Routes
    { 
        path: '/:locale/dashboard/setting/pages', 
        component: Page, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'pages' } 
    },
    { 
        path: '/:locale/dashboard/setting/edit', 
        component: Edit, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'settings' } 
    },


    { 
        path: '/:locale/dashboard/setting/contact', 
        component: Contact, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'contact' } 
    },

    { 
        path: '/:locale/dashboard/setting/report', 
        component: Report, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'report' } 
    },

    { 
        path: '/:locale/dashboard/setting/notifications', 
        component: Notification, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'notifications' } 
    },
    {
        path: '/:locale/dashboard/setting/landing-slider',
        component: LandingSlider,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'landing_slider' }
    },
    {
        path: '/:locale/dashboard/setting/technical-slider',
        component: TechnicalSlider,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'technical_slider' }
    },
    {
        path: '/:locale/dashboard/setting/category-strip',
        component: CategoryStrip,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'category_strip' }
    },
    {
        path: '/:locale/dashboard/setting/brand-slider',
        component: BrandSlider,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'brand_slider' }
    },
    {
        path: '/:locale/dashboard/carts/cart',
        component: Carts,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'carts' }
    },
    {
        path: '/:locale/dashboard/coupons/coupon',
        component: Coupons,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'coupons' }
    },
    {
        path: '/:locale/dashboard/shipping/methods',
        component: ShippingMethods,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'shipping' }
    },
    {
        path: '/:locale/dashboard/payment-methods',
        component: PaymentMethods,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'payment_methods' }
    },
    {
        path: '/:locale/dashboard/faqs',
        component: Faqs,
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'faq' }
    },


    // Ads Routes


    { 
        path: '/:locale/dashboard/ads/ad', 
        component: Ad, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'ads' } 
    },


    { 
        path: '/:locale/dashboard/orders/order', 
        component: Order, 
        meta: { requiresAuth: true, role: 'admin', breadcrumbKey: 'orders' } 
    },


];
