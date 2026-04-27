<script setup>
import { onMounted, ref, computed } from 'vue'
import api from '@/services/api'
import Chart from 'primevue/chart'

const cargando = ref(true)
const error = ref('')

const resumen = ref({})
const expedientesPorTipo = ref([])
const expedientesPorEstado = ref([])
const proximosVencimientos = ref([])
const alertasActivas = ref([])
const ultimosExpedientes = ref([])
const ultimasActuaciones = ref([])

const cargarDashboard = async () => {
    cargando.value = true
    error.value = ''

    try {
        const { data } = await api.get('/dashboard/completo')

        resumen.value = data.resumen || {}
        expedientesPorTipo.value = data.expedientes_por_tipo || []
        expedientesPorEstado.value = data.expedientes_por_estado || []
        proximosVencimientos.value = data.proximos_vencimientos || []
        alertasActivas.value = data.alertas_activas || []
        ultimosExpedientes.value = data.ultimos_expedientes || []
        ultimasActuaciones.value = data.ultimas_actuaciones || []
    } catch (e) {
        error.value = 'No se pudo cargar la información del dashboard.'
    } finally {
        cargando.value = false
    }
}

const chartTipos = computed(() => ({
    labels: expedientesPorTipo.value.map((item) => item.nombre),
    datasets: [
        {
        label: 'Expedientes',
        data: expedientesPorTipo.value.map((item) => item.total),
        backgroundColor: ['#123c69', '#2563eb', '#16a34a', '#f59e0b']
        }
    ]
}))

const chartEstados = computed(() => ({
    labels: expedientesPorEstado.value.map((item) => item.nombre),
    datasets: [
        {
        label: 'Estados',
        data: expedientesPorEstado.value.map((item) => item.total),
        backgroundColor: ['#2563eb', '#f59e0b', '#16a34a', '#6b7280', '#dc2626']
        }
    ]
}))

const chartOptions = {
    plugins: {
        legend: {
        position: 'bottom'
        }
    },
    responsive: true,
    maintainAspectRatio: false
}

const formatoFecha = (fecha) => {
    if (!fecha) return 'Sin fecha'
    return new Date(fecha).toLocaleDateString('es-PE')
}

onMounted(() => {
    cargarDashboard()
})
</script>

<template>
    <div class="dashboard-page">
        <div class="page-title dashboard-title-row">
        <div>
            <h2>Dashboard institucional</h2>
            <p>Panel de control general de la Procuraduría Pública Regional.</p>
        </div>

        <button class="btn-refresh" @click="cargarDashboard">
            <i class="pi pi-refresh"></i>
            Actualizar
        </button>
        </div>

        <div v-if="error" class="alert-error">
        {{ error }}
        </div>

        <div v-if="cargando" class="loading-box">
        Cargando información del sistema...
        </div>

        <template v-else>
        <div class="stats-grid">
            <div class="stat-card primary">
            <div>
                <span>Total expedientes</span>
                <strong>{{ resumen.total_expedientes || 0 }}</strong>
            </div>
            <i class="pi pi-folder"></i>
            </div>

            <div class="stat-card success">
            <div>
                <span>Activos</span>
                <strong>{{ resumen.expedientes_activos || 0 }}</strong>
            </div>
            <i class="pi pi-check-circle"></i>
            </div>

            <div class="stat-card warning">
            <div>
                <span>Importantes</span>
                <strong>{{ resumen.expedientes_importantes || 0 }}</strong>
            </div>
            <i class="pi pi-star"></i>
            </div>

            <div class="stat-card danger">
            <div>
                <span>Alertas activas</span>
                <strong>{{ resumen.alertas_activas || 0 }}</strong>
            </div>
            <i class="pi pi-bell"></i>
            </div>

            <div class="stat-card info">
            <div>
                <span>Documentos</span>
                <strong>{{ resumen.documentos_activos || 0 }}</strong>
            </div>
            <i class="pi pi-file"></i>
            </div>

            <div class="stat-card dark">
            <div>
                <span>Usuarios activos</span>
                <strong>{{ resumen.usuarios_activos || 0 }}</strong>
            </div>
            <i class="pi pi-users"></i>
            </div>
        </div>

        <div class="dashboard-grid">
            <section class="panel-card chart-card">
            <div class="panel-header">
                <h3>Expedientes por tipo</h3>
                <span>Distribución general</span>
            </div>

            <div class="chart-box">
                <Chart type="doughnut" :data="chartTipos" :options="chartOptions" />
            </div>
            </section>

            <section class="panel-card chart-card">
            <div class="panel-header">
                <h3>Expedientes por estado</h3>
                <span>Situación procesal</span>
            </div>

            <div class="chart-box">
                <Chart type="bar" :data="chartEstados" :options="chartOptions" />
            </div>
            </section>
        </div>

        <div class="dashboard-grid">
            <section class="panel-card">
            <div class="panel-header">
                <h3>Próximos vencimientos</h3>
                <span>Próximos 15 días</span>
            </div>

            <div v-if="proximosVencimientos.length === 0" class="empty-box">
                No hay vencimientos próximos.
            </div>

            <div v-else class="list-box">
                <div
                v-for="item in proximosVencimientos"
                :key="item.id"
                class="list-item"
                >
                <div>
                    <strong>{{ item.numero_expediente }}</strong>
                    <p>{{ item.tipo_expediente?.nombre || 'Sin tipo' }}</p>
                </div>
                <span class="badge warning">
                    {{ formatoFecha(item.fecha_proximo_vencimiento) }}
                </span>
                </div>
            </div>
            </section>

            <section class="panel-card">
            <div class="panel-header">
                <h3>Alertas activas</h3>
                <span>Pendientes de atención</span>
            </div>

            <div v-if="alertasActivas.length === 0" class="empty-box">
                No hay alertas activas.
            </div>

            <div v-else class="list-box">
                <div
                v-for="alerta in alertasActivas"
                :key="alerta.id"
                class="list-item"
                >
                <div>
                    <strong>{{ alerta.tipo_alerta?.nombre || 'Alerta' }}</strong>
                    <p>{{ alerta.mensaje }}</p>
                </div>
                <span class="badge danger">
                    {{ formatoFecha(alerta.fecha_alerta) }}
                </span>
                </div>
            </div>
            </section>
        </div>

        <div class="dashboard-grid">
            <section class="panel-card">
            <div class="panel-header">
                <h3>Últimos expedientes</h3>
                <span>Registros recientes</span>
            </div>

            <div v-if="ultimosExpedientes.length === 0" class="empty-box">
                No hay expedientes registrados.
            </div>

            <div v-else class="table-simple">
                <div class="table-row table-head">
                <span>Expediente</span>
                <span>Tipo</span>
                <span>Estado</span>
                </div>

                <div
                v-for="exp in ultimosExpedientes"
                :key="exp.id"
                class="table-row"
                >
                <span>{{ exp.numero_expediente }}</span>
                <span>{{ exp.tipo_expediente?.nombre || '-' }}</span>
                <span>
                    <small class="badge primary">
                    {{ exp.estado_expediente?.nombre || '-' }}
                    </small>
                </span>
                </div>
            </div>
            </section>

            <section class="panel-card">
            <div class="panel-header">
                <h3>Últimas actuaciones</h3>
                <span>Movimientos recientes</span>
            </div>

            <div v-if="ultimasActuaciones.length === 0" class="empty-box">
                No hay actuaciones registradas.
            </div>

            <div v-else class="list-box">
                <div
                v-for="act in ultimasActuaciones"
                :key="act.id"
                class="list-item"
                >
                <div>
                    <strong>{{ act.tipo_actuacion?.nombre || 'Actuación' }}</strong>
                    <p>{{ act.descripcion }}</p>
                    </div>
                    <span class="badge primary">
                        {{ formatoFecha(act.fecha_actuacion) }}
                    </span>
                </div>
            </div>
            </section>
        </div>
        </template>
    </div>
</template>