<script setup>
import { onMounted, ref } from 'vue'
import api from '@/services/api'

const cargando = ref(false)
const error = ref('')

const registros = ref([])
const usuarios = ref([])
const modalDetalle = ref(false)
const auditoriaSeleccionada = ref(null)

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 15
})

const filtros = ref({
    tabla_afectada: '',
    accion: '',
    usuario_id: '',
    fecha_desde: '',
    fecha_hasta: '',
    per_page: 15,
    page: 1
})

const tablas = [
    'usuarios',
    'expedientes',
    'expediente_partes',
    'actuaciones',
    'documentos',
    'alertas',
    'contratos',
    'expediente_judicial_detalle',
    'expediente_penal_detalle',
    'expediente_masc_detalle'
]

const acciones = [
    'CREATE',
    'UPDATE',
    'DELETE_LOGICO',
    'LOGIN',
    'LOGOUT',
    'CAMBIO_ESTADO',
    'REASIGNACION',
    'SUBIDA_DOCUMENTO',
    'DESCARGA_DOCUMENTO',
    'CIERRE_EXPEDIENTE',
    'REAPERTURA_EXPEDIENTE'
]

const cargarUsuarios = async () => {
    try {
        const { data } = await api.get('/usuarios?per_page=100')
        usuarios.value = data.data || []
    } catch (e) {
        usuarios.value = []
    }
}

const cargarAuditoria = async (page = 1) => {
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

        const { data } = await api.get('/auditoria', { params })

        registros.value = data.data || []
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            per_page: data.per_page
        }
    } catch (e) {
        error.value = 'No se pudo cargar la auditoría.'
    } finally {
        cargando.value = false
    }
}

const limpiarFiltros = () => {
    filtros.value = {
        tabla_afectada: '',
        accion: '',
        usuario_id: '',
        fecha_desde: '',
        fecha_hasta: '',
        per_page: 15,
        page: 1
    }

    cargarAuditoria()
}

const cambiarPagina = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        cargarAuditoria(page)
    }
}

const verDetalle = (item) => {
    auditoriaSeleccionada.value = item
    modalDetalle.value = true
}

const cerrarDetalle = () => {
    modalDetalle.value = false
    auditoriaSeleccionada.value = null
}

const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleString('es-PE')
}

const formatoJson = (valor) => {
    if (!valor) return 'Sin datos'

    try {
        return JSON.stringify(valor, null, 2)
    } catch {
        return String(valor)
    }
}

const claseAccion = (accion) => {
    if (accion === 'CREATE') return 'success'
    if (accion === 'UPDATE') return 'primary'
    if (accion === 'DELETE_LOGICO') return 'danger'
    if (accion === 'LOGIN' || accion === 'LOGOUT') return 'warning'
    if (accion.includes('DOCUMENTO')) return 'primary'
    return 'warning'
}

onMounted(async () => {
    await cargarUsuarios()
    await cargarAuditoria()
})
</script>

<template>
    <div class="auditoria-page">
        <div class="page-title dashboard-title-row">
            <div>
                <h2>Auditoría</h2>
                <p>Trazabilidad de acciones críticas realizadas en el sistema.</p>
            </div>

            <button class="btn-refresh" @click="cargarAuditoria()">
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
                    <h3>Filtros de auditoría</h3>
                    <p>Consulta acciones por tabla, usuario, tipo de evento y rango de fechas.</p>
                </div>

                <button class="btn-light" @click="limpiarFiltros">
                    <i class="pi pi-filter-slash"></i>
                    Limpiar
                </button>
            </div>

            <div class="filters-grid">
                <div class="form-group">
                    <label>Tabla afectada</label>
                    <select v-model="filtros.tabla_afectada">
                        <option value="">Todas</option>
                        <option v-for="tabla in tablas" :key="tabla" :value="tabla">
                            {{ tabla }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Acción</label>
                    <select v-model="filtros.accion">
                        <option value="">Todas</option>
                        <option v-for="accion in acciones" :key="accion" :value="accion">
                            {{ accion }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Usuario</label>
                    <select v-model="filtros.usuario_id">
                        <option value="">Todos</option>
                        <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                            {{ usuario.nombre_completo }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mostrar</label>
                    <select v-model="filtros.per_page" @change="cargarAuditoria()">
                        <option :value="15">15</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
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
                <button class="btn-primary-sm" @click="cargarAuditoria()">
                    <i class="pi pi-search"></i>
                    Buscar auditoría
                </button>
            </div>
        </section>

        <section class="table-card">
            <div class="table-toolbar">
                <div>
                    <h3>Registros de auditoría</h3>
                    <p>{{ pagination.total }} eventos encontrados</p>
                </div>
            </div>

            <div v-if="cargando" class="loading-box">
                Cargando auditoría...
            </div>

            <div v-else-if="registros.length === 0" class="empty-box">
                No se encontraron registros de auditoría.
            </div>

            <div v-else class="professional-table-wrapper">
                <table class="professional-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Acción</th>
                            <th>Tabla</th>
                            <th>Registro</th>
                            <th>Usuario</th>
                            <th>IP</th>
                            <th class="text-center">Detalle</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in registros" :key="item.id">
                            <td>
                                <strong>{{ formatoFecha(item.fecha_evento) }}</strong>
                                <small>ID auditoría: {{ item.id }}</small>
                            </td>

                            <td>
                                <span class="badge" :class="claseAccion(item.accion)">
                                    {{ item.accion }}
                                </span>
                            </td>

                            <td>{{ item.tabla_afectada }}</td>

                            <td>#{{ item.registro_id }}</td>

                            <td>
                                <span v-if="item.usuario">
                                    {{ item.usuario.nombre_completo }}
                                </span>
                                <span v-else>Sistema</span>
                            </td>

                            <td>{{ item.ip || '-' }}</td>

                            <td class="text-center">
                                <button class="btn-icon" @click="verDetalle(item)">
                                    <i class="pi pi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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

        <div v-if="modalDetalle" class="modal-overlay">
            <div class="modal-card extra-large">
                <div class="modal-header">
                    <div>
                        <h3>Detalle de auditoría</h3>
                        <p>Comparación de valores anteriores y nuevos del registro auditado.</p>
                    </div>

                    <button class="btn-icon" @click="cerrarDetalle">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <div class="audit-summary">
                    <div class="detail-item">
                        <label>Acción</label>
                        <strong>{{ auditoriaSeleccionada.accion }}</strong>
                    </div>

                    <div class="detail-item">
                        <label>Tabla</label>
                        <strong>{{ auditoriaSeleccionada.tabla_afectada }}</strong>
                    </div>

                    <div class="detail-item">
                        <label>Registro ID</label>
                        <strong>#{{ auditoriaSeleccionada.registro_id }}</strong>
                    </div>

                    <div class="detail-item">
                        <label>Usuario</label>
                        <strong>{{ auditoriaSeleccionada.usuario?.nombre_completo || 'Sistema' }}</strong>
                    </div>

                    <div class="detail-item">
                        <label>Fecha</label>
                        <strong>{{ formatoFecha(auditoriaSeleccionada.fecha_evento) }}</strong>
                    </div>

                    <div class="detail-item">
                        <label>IP</label>
                        <strong>{{ auditoriaSeleccionada.ip || '-' }}</strong>
                    </div>
                </div>

                <div class="json-grid">
                    <section class="json-card">
                        <div class="json-header danger">
                            <i class="pi pi-history"></i>
                            Valor anterior
                        </div>

                        <pre>{{ formatoJson(auditoriaSeleccionada.valor_anterior_json) }}</pre>
                    </section>

                    <section class="json-card">
                        <div class="json-header success">
                            <i class="pi pi-check-circle"></i>
                            Valor nuevo
                        </div>

                        <pre>{{ formatoJson(auditoriaSeleccionada.valor_nuevo_json) }}</pre>
                    </section>
                </div>

                <div class="modal-actions">
                    <button class="btn-light" @click="cerrarDetalle">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>