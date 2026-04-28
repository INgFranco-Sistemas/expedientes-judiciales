<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const sidebarOpen = ref(false)

const salir = async () => {
  await auth.logout()
  router.push('/login')
}

const puede = (permiso) => auth.tienePermiso(permiso)

const cerrarMenuMovil = () => {
  sidebarOpen.value = false
}
</script>

<template>
  <div class="app-shell">
    <div
      v-if="sidebarOpen"
      class="mobile-overlay"
      @click="cerrarMenuMovil"
    ></div>

    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-brand">
        <div class="sidebar-logo">
          <i class="pi pi-shield"></i>
        </div>

        <div>
          <h2>SIGEP</h2>
          <span>Procuraduría Regional</span>
        </div>
      </div>

      <nav class="sidebar-menu">
        <RouterLink
          v-if="puede('dashboard.ver')"
          to="/"
          class="menu-item"
          exact-active-class="active"
          @click="cerrarMenuMovil"
        >
          <i class="pi pi-chart-line"></i>
          <span>Dashboard</span>
        </RouterLink>

        <RouterLink
          v-if="puede('expedientes.ver')"
          to="/expedientes"
          class="menu-item"
          active-class="active"
          @click="cerrarMenuMovil"
        >
          <i class="pi pi-folder-open"></i>
          <span>Expedientes</span>
        </RouterLink>

        <RouterLink
          v-if="puede('usuarios.ver')"
          to="/usuarios"
          class="menu-item"
          active-class="active"
          @click="cerrarMenuMovil"
        >
          <i class="pi pi-users"></i>
          <span>Usuarios</span>
        </RouterLink>

        <RouterLink
          v-if="puede('alertas.ver')"
          to="/alertas"
          class="menu-item"
          active-class="active"
          @click="cerrarMenuMovil"
        >
          <i class="pi pi-bell"></i>
          <span>Alertas</span>
        </RouterLink>

        <RouterLink
          v-if="puede('auditoria.ver')"
          to="/auditoria"
          class="menu-item"
          active-class="active"
          @click="cerrarMenuMovil"
        >
          <i class="pi pi-history"></i>
          <span>Auditoría</span>
        </RouterLink>
      </nav>

      <div class="sidebar-footer">
        <small>Gobierno Regional de Huánuco</small>
      </div>
    </aside>

    <section class="main-area">
      <header class="topbar">
        <button class="btn-menu-mobile" @click="sidebarOpen = true">
          <i class="pi pi-bars"></i>
        </button>

        <div class="topbar-title">
          <h1>Sistema de Gestión de Expedientes</h1>
          <p>Control, seguimiento y trazabilidad institucional</p>
        </div>

        <div class="user-box">
          <div class="avatar-circle">
            {{ auth.usuario?.nombre_completo?.charAt(0) || 'U' }}
          </div>

          <div class="user-info">
            <strong>{{ auth.usuario?.nombre_completo }}</strong>
            <span>{{ auth.usuario?.perfil }}</span>
          </div>

          <button class="btn-logout" @click="salir" title="Cerrar sesión">
            <i class="pi pi-sign-out"></i>
          </button>
        </div>
      </header>

      <main class="content">
        <RouterView />
      </main>
    </section>
  </div>
</template>