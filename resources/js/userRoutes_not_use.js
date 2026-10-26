import userDashboard from '@/components/User/Pages/Dashboard.vue';
import userFAQ from '@/components/User/Pages/FAQ.vue';
import userHelp from '@/components/User/Pages/Help.vue';
import userCV from '@/components/User/Pages/userCV.vue';
import Payment from '@/components/User/Pages/Payment.vue';


export default [ 
  {
    path: '/:locale/app/info',
    component: userDashboard,
    meta: { requiresAuth: true, role: 'user' , isMenu: true },
  },

  {
    path: '/:locale/app/faq',
    component: userFAQ,
    meta: { requiresAuth: true, role: 'user' , isMenu: true },
  },



  {
    path: '/:locale/app/contact',
    component: userHelp,
    meta: { requiresAuth: true, role: 'user' , isMenu: true },
  },


  {
    path: '/:locale/app/cv-templates',
    component: userCV,
    meta: { requiresAuth: true, role: 'user' , isMenu: true },
  },


   {
    path: '/:locale/app/payment',
    component: Payment,
    meta: { requiresAuth: true, role: 'user' , isMenu: true },
  },





 
];
