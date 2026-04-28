<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const cargando = ref(false)
const error = ref('')

const alertas = ref([])
const modo = ref('mis')

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 10
})

const filtros = ref({
    estado: 'ACTIVA',
    leido: '',
    expediente_id: '',
    usuario_destino_id: '',
    fecha_desde: '',
    fecha_hasta: '',
    per_page: 10,
    page: 1
})

const cargarAlertas = async (page = 1) => {
    cargando.value = true
    error.value = ''
    filtros.value.page = page

    try {
        const params = {}

        Object.entries(filtros.value).forEach(([key, value]) => {
            if (value !== '' && value !== null && value !== undefined) {
                params[key] = value
            }
        })

        const endpoint = modo.value === 'mis' ? '/mis-alertas' : '/alertas'
        const { data } = await api.get(endpoint, { params })

        alertas.value = data.data || []
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            per_page: data.per_page
        }
    } catch (e) {
        error.value = 'No se pudieron cargar las alertas.'
    } finally {
        cargando.value = false
    }
}

const cambiarModo = async (nuevoModo) => {
    modo.value = nuevoModo
    await cargarAlertas()
}

const limpiarFiltros = () => {
    filtros.value = {
        estado: 'ACTIVA',
        leido: '',
        expediente_id: '',
        usuario_destino_id: '',
        fecha_desde: '',
        fecha_hasta: '',
        per_page: 10,
        page: 1
    }

    cargarAlertas()
}

const cambiarPagina = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        cargarAlertas(page)
    }
}

const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleString('es-PE')
}

const verExpediente = (alerta) => {
    router.push(`/expedientes/${alerta.expediente_id}`)
}

const marcarLeida = async (alerta) => {
    try {
        await api.patch(`/expedientes/${alerta.expediente_id}/alertas/${alerta.id}/leer`)
        await cargarAlertas(pagination.value.current_page)
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo marcar como leída.')
    }
}

const atenderAlerta = async (alerta) => {
    const confirmar = confirm('¿Confirmas que esta alerta fue atendida?')
    if (!confirmar) return

    try {
        await api.patch(`/expedientes/${alerta.expediente_id}/alertas/${alerta.id}/atender`)
        await cargarAlertas(pagination.value.current_page)
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo atender la alerta.')
    }
}

const anularAlerta = async (alerta) => {
    const motivo = prompt('Ingrese el motivo de anulación:')
    if (!motivo || motivo.length < 5) {
        alert('Debe ingresar un motivo válido.')
        return
    }

    try {
        await api.patch(`/expedientes/${alerta.expediente_id}/alertas/${alerta.id}/anular`, {
            motivo
        })

        await cargarAlertas(pagination.value.current_page)
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo anular la alerta.')
    }
}

const claseEstado = (estado) => {
    if (estado === 'ACTIVA') return 'danger'
    if (estado === 'ATENDIDA') return 'success'
    return 'primary'
}

onMounted(() => {
    cargarAlertas()
})
</script>

<template>
    <div class="alertas-page">
        <div class="page-title dashboard-title-row">
            <div>
                <h2>Alertas</h2>
                <p>Seguimiento de alertas generales, personales y vencimientos.</p>
            </div>

            <button class="btn-refresh" @click="cargarAlertas()">
                <i class="pi pi-refresh"></i>
                Actualizar
            </button>
        </div>

        <div v-if="error" class="alert-error">
            {{ error }}
        </div>

        <section class="filter-card">
            <div class="filter-header">
                <div>
                    <h3>Vista de alertas</h3>
                    <p>Consulta tus alertas o el tablero general.</p>
                </div>

                <div class="mode-switch">
                    <button :class="{ active: modo === 'mis' }" @click="cambiarModo('mis')">
                        Mis alertas
                    </button>

                    <button :class="{ active: modo === 'todas' }" @click="cambiarModo('todas')">
                        Todas
                    </button>
                </div>
            </div>

            <div class="filters-grid">
                <div class="form-group">
                    <label>Estado</label>
                    <select v-model="filtros.estado">
                        <option value="">Todos</option>
                        <option value="ACTIVA">Activa</option>
                        <option value="ATENDIDA">Atendida</option>
                        <option value="ANULADA">Anulada</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Lectura</label>
                    <select v-model="filtros.leido">
                        <option value="">Todos</option>
                        <option value="false">No leídas</option>
                        <option value="true">Leídas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Desde</label>
                    <input v-model="filtros.fecha_desde" type="date" />
                </div>

                <div class="form-group">
                    <label>Hasta</label>
                    <input v-model="filtros.fecha_hasta" type="date" />
                </div>
            </div>

            <div class="filter-actions">
                <button class="btn-light" @click="limpiarFiltros">
                    <i class="pi pi-filter-slash"></i>
                    Limpiar
                </button>

                <button class="btn-primary-sm" @click="cargarAlertas()">
                    <i class="pi pi-search"></i>
                    Buscar
                </button>
            </div>
        </section>

        <section class="table-card">
            <div class="table-toolbar">
                <div>
                    <h3>{{ modo === 'mis' ? 'Mis alertas' : 'Todas las alertas' }}</h3>
                    <p>{{ pagination.total }} alertas encontradas</p>
                </div>

                <div class="table-size">
                    <label>Mostrar</label>
                    <select v-model="filtros.per_page" @change="cargarAlertas()">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>
            </div>

            <div v-if="cargando" class="loading-box">
                Cargando alertas...
            </div>

            <div v-else-if="alertas.length === 0" class="empty-box">
                No se encontraron alertas.
            </div>

            <div v-else class="alerta-list">
                <div v-for="alerta in alertas" :key="alerta.id" class="alerta-card" :class="{
                    activa: alerta.estado === 'ACTIVA',
                    atendida: alerta.estado === 'ATENDIDA',
                    anulada: alerta.estado === 'ANULADA'
                }">
                    <div class="alerta-icon">
                        <i class="pi pi-bell"></i>
                    </div>

                    <div class="alerta-content">
                        <div class="alerta-head">
                            <div>
                                <strong>{{ alerta.tipo_alerta?.nombre || 'Alerta' }}</strong>
                                <p>{{ alerta.mensaje }}</p>
                            </div>

                            <div class="alerta-badges">
                                <span class="badge" :class="claseEstado(alerta.estado)">
                                    {{ alerta.estado }}
                                </span>

                                <span class="badge" :class="alerta.leido ? 'success' : 'warning'">
                                    {{ alerta.leido ? 'Leída' : 'No leída' }}
                                </span>
                            </div>
                        </div>

                        <div class="alerta-meta">
                            <span>
                                <i class="pi pi-calendar"></i>
                                {{ formatoFecha(alerta.fecha_alerta) }}
                            </span>

                            <span>
                                <i class="pi pi-folder"></i>
                                {{ alerta.expediente?.numero_expediente || 'Expediente' }}
                            </span>

                            <span>
                                <i class="pi pi-user"></i>
                                {{ alerta.usuario_destino?.nombre_completo || 'General' }}
                            </span>
                        </div>

                        <div class="alerta-actions">
                            <button class="btn-light" @click="verExpediente(alerta)">
                                <i class="pi pi-folder-open"></i>
                                Ver expediente
                            </button>

                            <button v-if="!alerta.leido" class="btn-light" @click="marcarLeida(alerta)">
                                <i class="pi pi-eye"></i>
                                Marcar leída
                            </button>

                            <button v-if="alerta.estado === 'ACTIVA'" class="btn-light success-button"
                                @click="atenderAlerta(alerta)">
                                <i class="pi pi-check"></i>
                                Atender
                            </button>

                            <button v-if="alerta.estado !== 'ANULADA'" class="btn-light warning-button"
                                @click="anularAlerta(alerta)">
                                <i class="pi pi-ban"></i>
                                Anular
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pagination-box">
                <button class="btn-light" :disabled="pagination.current_page <= 1"
                    @click="cambiarPagina(pagination.current_page - 1)">
                    Anterior
                </button>

                <span>
                    Página {{ pagination.current_page }} de {{ pagination.last_page }}
                </span>

                <button class="btn-light" :disabled="pagination.current_page >= pagination.last_page"
                    @click="cambiarPagina(pagination.current_page + 1)">
                    Siguiente
                </button>
            </div>
        </section>
    </div>
</template>