<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const username = ref('')
const password = ref('')
const cargando = ref(false)
const error = ref('')

const iniciarSesion = async () => {
  error.value = ''
  cargando.value = true

  try {
    await auth.login({
      username: username.value,
      password: password.value
    })

    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo iniciar sesión.'
  } finally {
    cargando.value = false
  }
}
</script>

<template>
    <main class="login-page">
        <section class="login-left">
        <div class="brand-box">
            <div class="brand-icon">
            <i class="pi pi-briefcase"></i>
            </div>

            <h1>SIGEP Procuraduría</h1>
            <p>
            Sistema Integral de Gestión y Seguimiento de Expedientes de la
            Procuraduría Pública Regional.
            </p>
        </div>
        </section>

        <section class="login-right">
        <form class="login-card" @submit.prevent="iniciarSesion">
            <div class="login-header">
            <h2>Bienvenido</h2>
            <p>Ingresa tus credenciales institucionales</p>
            </div>

            <div v-if="error" class="alert-error">
            {{ error }}
            </div>

            <div class="form-group">
            <label>Usuario</label>
            <input
                v-model="username"
                type="text"
                placeholder="admin.procuraduria"
                autocomplete="username"
            />
            </div>

            <div class="form-group">
            <label>Contraseña</label>
            <input
                v-model="password"
                type="password"
                placeholder="••••••••"
                autocomplete="current-password"
            />
            </div>

            <button class="btn-login" type="submit" :disabled="cargando">
            <span v-if="!cargando">Ingresar al sistema</span>
            <span v-else>Validando...</span>
            </button>

            <div class="login-footer">
            Gobierno Regional de Huánuco · Procuraduría Pública Regional
            </div>
        </form>
        </section>
    </main>
</template>