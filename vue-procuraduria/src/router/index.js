import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import LoginView from '@/views/auth/LoginView.vue'
import DashboardView from '@/views/dashboard/DashboardView.vue'
import NoAutorizadoView from '@/views/errores/NoAutorizadoView.vue'
import ExpedienteListView from '@/views/expedientes/ExpedienteListView.vue'
import ExpedienteCreateView from '@/views/expedientes/ExpedienteCreateView.vue'
import ExpedienteShowView from '@/views/expedientes/ExpedienteShowView.vue'
import AppLayout from '@/layouts/AppLayout.vue'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: LoginView,
            meta: {
                public: true
            }
        },
        {
            path: '/',
            component: AppLayout,
            meta: {
                requiresAuth: true
            },
            children: [
                {
                    path: '',
                    name: 'dashboard',
                    component: DashboardView,
                    meta: {
                        permiso: 'dashboard.ver'
                    }
                },
                {
                    path: 'expedientes',
                    name: 'expedientes',
                    component: ExpedienteListView,
                    meta: {
                        permiso: 'expedientes.ver'
                    }
                },
                {
                    path: 'expedientes/nuevo',
                    name: 'expedientes-nuevo',
                    component: ExpedienteCreateView,
                    meta: {
                        permiso: 'expedientes.crear'
                    }
                },
                {
                    path: 'expedientes/:id',
                    name: 'expedientes-show',
                    component: ExpedienteShowView,
                    meta: {
                        permiso: 'expedientes.ver'
                    }
                },
            ]
        },
        {
            path: '/no-autorizado',
            name: 'no-autorizado',
            component: NoAutorizadoView
        }
    ]
})

router.beforeEach((to) => {
    const auth = useAuthStore()

    if (to.meta.public) {
        return true
    }

    if (to.meta.requiresAuth && !auth.autenticado) {
        return '/login'
    }

    if (to.meta.permiso && !auth.tienePermiso(to.meta.permiso)) {
        return '/no-autorizado'
    }

    return true
})

export default router