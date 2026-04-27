import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token'),
        usuario: JSON.parse(localStorage.getItem('usuario') || 'null')
    }),

    getters: {
        autenticado: (state) => !!state.token,
        permisos: (state) => state.usuario?.permisos || []
    },

    actions: {
        async login(credenciales) {
        const { data } = await api.post('/auth/login', credenciales)

        this.token = data.token
        this.usuario = data.usuario

        localStorage.setItem('token', data.token)
        localStorage.setItem('usuario', JSON.stringify(data.usuario))

        return data
        },

        async logout() {
        try {
            await api.post('/auth/logout')
        } catch (error) {
            console.error(error)
        }

        this.token = null
        this.usuario = null

        localStorage.removeItem('token')
        localStorage.removeItem('usuario')
        },

        tienePermiso(permiso) {
        return this.permisos.includes(permiso)
        }
    }
})