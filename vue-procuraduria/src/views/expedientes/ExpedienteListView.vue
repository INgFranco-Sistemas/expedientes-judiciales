<script setup>
import { onMounted, ref } from 'vue'
import api from '@/services/api'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const router = useRouter()

const cargando = ref(false)
const error = ref('')

const expedientes = ref([])
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
})

const catalogos = ref({
    tipos_expediente: [],
    estados_expediente: [],
    prioridades: [],
    usuarios: []
})

const filtros = ref({
    buscar: '',
    tipo_expediente_id: '',
    estado_expediente_id: '',
    prioridad_id: '',
    encargado_actual_id: '',
    estado_registro: '',
    fecha_desde: '',
    fecha_hasta: '',
    per_page: 10,
    page: 1
})

const cargarCatalogos = async () => {
    const { data } = await api.get('/catalogos/todos')
    catalogos.value.tipos_expediente = data.tipos_expediente || []
    catalogos.value.estados_expediente = data.estados_expediente || []
    catalogos.value.prioridades = data.prioridades || []

    const usuarios = await api.get('/catalogos/usuarios-activos')
    catalogos.value.usuarios = usuarios.data || []
}

const cargarExpedientes = async (page = 1) => {
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

        const { data } = await api.get('/expedientes', { params })

        expedientes.value = data.data || []
        pagination.value = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total
        }
    } catch (e) {
        error.value = 'No se pudo cargar el listado de expedientes.'
    } finally {
        cargando.value = false
    }
}

const limpiarFiltros = () => {
    filtros.value = {
        buscar: '',
        tipo_expediente_id: '',
        estado_expediente_id: '',
        prioridad_id: '',
        encargado_actual_id: '',
        estado_registro: '',
        fecha_desde: '',
        fecha_hasta: '',
        per_page: 10,
        page: 1
    }

    cargarExpedientes()
}

const cambiarPagina = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        cargarExpedientes(page)
    }
}

const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE')
}

const verExpediente = (id) => {
    router.push(`/expedientes/${id}`)
}

const nuevoExpediente = () => {
    router.push('/expedientes/nuevo')
}


const exportando = ref('')

const exportarReporte = async (formato) => {
    if (exportando.value) return

    exportando.value = formato
    error.value = ''

    try {
        const respuesta = await api.get(
            `/expedientes/exportar/${formato}`,
            { responseType: 'blob' }
        )

        const archivo = new Blob(
            [respuesta.data],
            {
                type: formato === 'excel'
                    ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    : 'application/pdf'
            }
        )

        const url = URL.createObjectURL(archivo)
        const enlace = document.createElement('a')

        enlace.href = url
        enlace.download = `mis_expedientes_${new Date().toISOString().slice(0, 10)}.${formato === 'excel' ? 'xlsx' : 'pdf'}`

        document.body.appendChild(enlace)
        enlace.click()
        enlace.remove()

        setTimeout(() => URL.revokeObjectURL(url), 1000)

    } catch (e) {
        error.value = 'No se pudo descargar el reporte.'
        console.error('Error al exportar:', e)
    } finally {
        exportando.value = ''
    }
}


onMounted(async () => {
    await cargarCatalogos()
    await cargarExpedientes()
})
</script>

<template>
    <div class="expedientes-page">
        <div class="page-title dashboard-title-row">
            <div>
                <h2>Expedientes</h2>
                <p>Consulta, seguimiento y administración de expedientes institucionales.</p>
            </div>            
            
            <div
                v-if="auth.tienePermiso('expedientes.ver')"
                class="export-actions"
            >
                <button
                    class="btn-export btn-export-excel"
                    :disabled="!!exportando"
                    @click="exportarReporte('excel')"
                >
                    <i class="pi pi-file-excel"></i>
                    {{ exportando === 'excel' ? 'Generando...' : 'Exportar Excel' }}
                </button>

                <button
                    class="btn-export btn-export-pdf"
                    :disabled="!!exportando"
                    @click="exportarReporte('pdf')"
                >
                    <i class="pi pi-file-pdf"></i>
                    {{ exportando === 'pdf' ? 'Generando...' : 'Exportar PDF' }}
                </button>
            </div>

            <button
                v-if="auth.tienePermiso('expedientes.crear')"
                class="btn-refresh"
                @click="nuevoExpediente"
            >
                <i class="pi pi-plus"></i>
                Nuevo expediente
            </button>
        </div>

        <section class="filter-card">
        <div class="filter-header">
            <div>
            <h3>Filtros de búsqueda</h3>
            <p>Filtra por cualquier dato principal del expediente.</p>
            </div>

            <button class="btn-light" @click="limpiarFiltros">
            <i class="pi pi-filter-slash"></i>
            Limpiar
            </button>
        </div>

        <div class="filters-grid">
            <div class="form-group">
            <label>Búsqueda general</label>
            <input
                v-model="filtros.buscar"
                type="text"
                placeholder="Nro expediente, código, parte, DNI, pretensión..."
                @keyup.enter="cargarExpedientes()"
            />
            </div>

            <div class="form-group">
            <label>Tipo</label>
            <select v-model="filtros.tipo_expediente_id">
                <option value="">Todos</option>
                <option
                v-for="tipo in catalogos.tipos_expediente"
                :key="tipo.id"
                :value="tipo.id"
                >
                {{ tipo.nombre }}
                </option>
            </select>
            </div>

            <div class="form-group">
            <label>Estado procesal</label>
            <select v-model="filtros.estado_expediente_id">
                <option value="">Todos</option>
                <option
                v-for="estado in catalogos.estados_expediente"
                :key="estado.id"
                :value="estado.id"
                >
                {{ estado.nombre }}
                </option>
            </select>
            </div>

            <div class="form-group">
            <label>Prioridad</label>
            <select v-model="filtros.prioridad_id">
                <option value="">Todas</option>
                <option
                v-for="prioridad in catalogos.prioridades"
                :key="prioridad.id"
                :value="prioridad.id"
                >
                {{ prioridad.nombre }}
                </option>
            </select>
            </div>

            <div class="form-group">
            <label>Encargado</label>
            <select v-model="filtros.encargado_actual_id">
                <option value="">Todos</option>
                <option
                v-for="usuario in catalogos.usuarios"
                :key="usuario.id"
                :value="usuario.id"
                >
                {{ usuario.nombre_completo }}
                </option>
            </select>
            </div>

            <div class="form-group">
            <label>Estado registro</label>
            <select v-model="filtros.estado_registro">
                <option value="">Todos</option>
                <option value="ACTIVO">Activo</option>
                <option value="CERRADO">Cerrado</option>
                <option value="ARCHIVADO">Archivado</option>
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
            <button class="btn-primary-sm" @click="cargarExpedientes()">
            <i class="pi pi-search"></i>
            Buscar expedientes
            </button>
        </div>
        </section>

        <div v-if="error" class="alert-error">
        {{ error }}
        </div>

        <section class="table-card">
        <div class="table-toolbar">
            <div>
            <h3>Listado de expedientes</h3>
            <p>{{ pagination.total }} registros encontrados</p>
            </div>

            <div class="table-size">
            <label>Mostrar</label>
            <select v-model="filtros.per_page" @change="cargarExpedientes()">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
            </select>
            </div>
        </div>

        <div v-if="cargando" class="loading-box">
            Cargando expedientes...
        </div>

        <div v-else-if="expedientes.length === 0" class="empty-box">
            No se encontraron expedientes con los filtros seleccionados.
        </div>

        <div v-else class="professional-table-wrapper">
            <table class="professional-table">
            <thead>
                <tr>
                <th>Nro expediente</th>
                <th>Código interno</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Prioridad</th>
                <th>Encargado</th>
                <th>Monto</th>
                <th>Registro</th>
                <th>Vencimiento</th>
                <th>Importante</th>
                <th class="text-center">Acciones</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="exp in expedientes" :key="exp.id">
                <td>
                    <strong>{{ exp.numero_expediente }}</strong>
                    <small>Año: {{ exp.anio_expediente || '-' }}</small>
                </td>

                <td>{{ exp.codigo_unico_interno }}</td>

                <td>
                    <span class="badge primary">
                    {{ exp.tipo_expediente?.nombre || '-' }}
                    </span>
                </td>

                <td>
                    <span class="badge success">
                    {{ exp.estado_expediente?.nombre || '-' }}
                    </span>
                </td>

                <td>
                    <span class="badge warning">
                    {{ exp.prioridad?.nombre || 'Sin prioridad' }}
                    </span>
                </td>

                <td>{{ exp.encargado_actual?.nombre_completo || '-' }}</td>

                <td>
                    S/ {{ Number(exp.monto || 0).toLocaleString('es-PE', { minimumFractionDigits: 2 }) }}
                </td>

                <td>{{ formatoFecha(exp.fecha_registro) }}</td>

                <td>{{ formatoFecha(exp.fecha_proximo_vencimiento) }}</td>

                <td>
                    <span v-if="exp.importante" class="badge danger">
                    Importante
                    </span>
                    <span v-else class="badge primary">
                    Normal
                    </span>
                </td>

                <td class="text-center">
                    <button class="btn-icon" title="Ver ficha" @click="verExpediente(exp.id)">
                    <i class="pi pi-eye"></i>
                    </button>
                </td>
                </tr>
            </tbody>
            </table>
        </div>

        <div class="pagination-box">
            <button
            class="btn-light"
            :disabled="pagination.current_page <= 1"
            @click="cambiarPagina(pagination.current_page - 1)"
            >
            Anterior
            </button>

            <span>
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>

            <button
            class="btn-light"
            :disabled="pagination.current_page >= pagination.last_page"
            @click="cambiarPagina(pagination.current_page + 1)"
            >
            Siguiente
            </button>
        </div>
        </section>
    </div>
</template>


<style scoped>
/* Contenedor de botones */
.export-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

/* Estilo general */
.btn-export {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 11px 20px;
    border: 1px solid transparent;
    border-radius: 10px;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    white-space: nowrap;
    transition: transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
}

.btn-export i {
    font-size: 16px;
}

/* Excel: verde esmeralda */
.btn-export-excel {
    background: linear-gradient(135deg, #16a34a, #15803d);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.22);
}

.btn-export-excel:hover:not(:disabled) {
    background: linear-gradient(135deg, #15803d, #166534);
    box-shadow: 0 6px 16px rgba(22, 163, 74, 0.35);
    transform: translateY(-2px);
}

/* PDF: rojo elegante */
.btn-export-pdf {
    background: linear-gradient(135deg, #ef4444, #b91c1c);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.22);
}

.btn-export-pdf:hover:not(:disabled) {
    background: linear-gradient(135deg, #dc2626, #991b1b);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
    transform: translateY(-2px);
}

/* Efecto al hacer clic */
.btn-export:active:not(:disabled) {
    transform: translateY(0);
}

/* Accesibilidad */
.btn-export:focus-visible {
    outline: 3px solid #93c5fd;
    outline-offset: 3px;
}

/* Mientras genera el archivo */
.btn-export:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    box-shadow: none;
}
</style>
