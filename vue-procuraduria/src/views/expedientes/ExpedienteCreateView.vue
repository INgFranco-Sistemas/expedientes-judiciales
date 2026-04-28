<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const cargando = ref(false)
const guardando = ref(false)
const error = ref('')
const mensaje = ref('')

const catalogos = ref({
    tipos_expediente: [],
    estados_expediente: [],
    distritos_judiciales: [],
    dependencias: [],
    especialidades: [],
    instancias: [],
    materias: [],
    etapas: [],
    prioridades: [],
    usuarios: []
})

const form = ref({
    tipo_expediente_id: '',
    numero_expediente: '',
    anio_expediente: new Date().getFullYear(),
    codigo_unico_interno: '',
    distrito_judicial_id: '',
    dependencia_id: '',
    especialidad_id: '',
    instancia_id: '',
    materia_id: '',
    etapa_id: '',
    estado_expediente_id: '',
    encargado_actual_id: '',
    prioridad_id: '',
    monto: 0,
    pretensiones: '',
    observaciones_generales: '',
    fecha_registro: new Date().toISOString().slice(0, 10),
    fecha_ingreso: new Date().toISOString().slice(0, 10),
    fecha_ultima_actuacion: '',
    fecha_proximo_vencimiento: '',
    fecha_cierre: '',
    motivo_cierre_id: '',
    importante: false,
    estado_registro: 'ACTIVO'
})

const dependenciasFiltradas = computed(() => {
    if (!form.value.distrito_judicial_id) return catalogos.value.dependencias

    return catalogos.value.dependencias.filter(
        (dep) => Number(dep.distrito_judicial_id) === Number(form.value.distrito_judicial_id)
    )
})

const cargarDatos = async () => {
    cargando.value = true
    error.value = ''

    try {
        const { data } = await api.get('/catalogos/todos')
        catalogos.value = {
        ...catalogos.value,
        ...data
        }

        const usuarios = await api.get('/usuarios?per_page=100')
        catalogos.value.usuarios = usuarios.data.data || []

        const estadoRegistrado = catalogos.value.estados_expediente.find(
        (item) => item.nombre?.toLowerCase() === 'registrado'
        )

        const prioridadMedia = catalogos.value.prioridades.find(
        (item) => item.nombre?.toLowerCase() === 'media'
        )

        if (estadoRegistrado) form.value.estado_expediente_id = estadoRegistrado.id
        if (prioridadMedia) form.value.prioridad_id = prioridadMedia.id
    } catch (e) {
        error.value = 'No se pudieron cargar los catálogos.'
    } finally {
        cargando.value = false
    }
}

const generarCodigoInterno = () => {
    const anio = form.value.anio_expediente || new Date().getFullYear()
    const numeroLimpio = form.value.numero_expediente
        ? form.value.numero_expediente.replace(/[^a-zA-Z0-9]/g, '').slice(0, 8).toUpperCase()
        : Math.floor(Math.random() * 9999).toString().padStart(4, '0')

    form.value.codigo_unico_interno = `EXP-${anio}-${numeroLimpio}`
}

const validar = () => {
    if (!form.value.tipo_expediente_id) return 'Seleccione el tipo de expediente.'
    if (!form.value.numero_expediente) return 'Ingrese el número de expediente.'
    if (!form.value.codigo_unico_interno) return 'Ingrese o genere el código interno.'
    if (!form.value.estado_expediente_id) return 'Seleccione el estado del expediente.'
    if (!form.value.encargado_actual_id) return 'Seleccione el encargado.'
    if (!form.value.fecha_registro) return 'Seleccione la fecha de registro.'
    if (Number(form.value.monto) < 0) return 'El monto no puede ser negativo.'

    return ''
}

const limpiarVacios = (obj) => {
    const limpio = {}

    Object.entries(obj).forEach(([key, value]) => {
        limpio[key] = value === '' ? null : value
    })

    limpio.importante = !!obj.importante
    limpio.monto = Number(obj.monto || 0)

    return limpio
}

const guardar = async () => {
    error.value = ''
    mensaje.value = ''

    const validacion = validar()

    if (validacion) {
        error.value = validacion
        return
    }

    guardando.value = true

    try {
        const payload = limpiarVacios(form.value)

        const { data } = await api.post('/expedientes', payload)

        mensaje.value = data.message || 'Expediente registrado correctamente.'

        setTimeout(() => {
        router.push('/expedientes')
        }, 800)
    } catch (e) {
        if (e.response?.data?.errors) {
        const firstError = Object.values(e.response.data.errors)[0]
        error.value = firstError?.[0] || 'Error de validación.'
        } else {
        error.value = e.response?.data?.message || 'No se pudo registrar el expediente.'
        }
    } finally {
        guardando.value = false
    }
}

const cancelar = () => {
    router.push('/expedientes')
}

onMounted(() => {
    cargarDatos()
})
</script>

<template>
    <div class="expediente-form-page">
        <div class="page-title dashboard-title-row">
        <div>
            <h2>Nuevo expediente</h2>
            <p>Registra los datos principales del expediente institucional.</p>
        </div>

        <button class="btn-light" @click="cancelar">
            <i class="pi pi-arrow-left"></i>
            Volver
        </button>
        </div>

        <div v-if="error" class="alert-error">
        {{ error }}
        </div>

        <div v-if="mensaje" class="alert-success">
        {{ mensaje }}
        </div>

        <div v-if="cargando" class="loading-box">
        Cargando catálogos...
        </div>

        <form v-else class="form-card" @submit.prevent="guardar">
        <section class="form-section">
            <div class="section-title">
            <h3>Datos principales</h3>
            <p>Información base del expediente.</p>
            </div>

            <div class="filters-grid">
            <div class="form-group">
                <label>Tipo de expediente *</label>
                <select v-model="form.tipo_expediente_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.tipos_expediente"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Nro expediente *</label>
                <input
                v-model="form.numero_expediente"
                type="text"
                placeholder="Ej. 001-2026"
                @blur="!form.codigo_unico_interno && generarCodigoInterno()"
                />
            </div>

            <div class="form-group">
                <label>Año</label>
                <input v-model="form.anio_expediente" type="number" min="1900" max="2100" />
            </div>

            <div class="form-group">
                <label>Código interno *</label>
                <div class="input-action">
                <input v-model="form.codigo_unico_interno" type="text" placeholder="EXP-2026-0001" />
                <button type="button" @click="generarCodigoInterno">
                    <i class="pi pi-sync"></i>
                </button>
                </div>
            </div>

            <div class="form-group">
                <label>Estado procesal *</label>
                <select v-model="form.estado_expediente_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.estados_expediente"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Encargado *</label>
                <select v-model="form.encargado_actual_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.usuarios"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre_completo }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Prioridad</label>
                <select v-model="form.prioridad_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.prioridades"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Monto</label>
                <input v-model="form.monto" type="number" min="0" step="0.01" />
            </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-title">
            <h3>Datos jurisdiccionales</h3>
            <p>Información de distrito, dependencia, materia e instancia.</p>
            </div>

            <div class="filters-grid">
            <div class="form-group">
                <label>Distrito judicial</label>
                <select v-model="form.distrito_judicial_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.distritos_judiciales"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Dependencia</label>
                <select v-model="form.dependencia_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in dependenciasFiltradas"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Especialidad</label>
                <select v-model="form.especialidad_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.especialidades"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Instancia</label>
                <select v-model="form.instancia_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.instancias"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Materia</label>
                <select v-model="form.materia_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.materias"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>

            <div class="form-group">
                <label>Etapa</label>
                <select v-model="form.etapa_id">
                <option value="">Seleccione</option>
                <option
                    v-for="item in catalogos.etapas"
                    :key="item.id"
                    :value="item.id"
                >
                    {{ item.nombre }}
                </option>
                </select>
            </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-title">
            <h3>Fechas y control</h3>
            <p>Fechas principales para seguimiento y vencimientos.</p>
            </div>

            <div class="filters-grid">
            <div class="form-group">
                <label>Fecha registro *</label>
                <input v-model="form.fecha_registro" type="date" />
            </div>

            <div class="form-group">
                <label>Fecha ingreso</label>
                <input v-model="form.fecha_ingreso" type="date" />
            </div>

            <div class="form-group">
                <label>Próximo vencimiento</label>
                <input v-model="form.fecha_proximo_vencimiento" type="date" />
            </div>

            <div class="form-group">
                <label>Estado del registro</label>
                <select v-model="form.estado_registro">
                <option value="ACTIVO">Activo</option>
                <option value="CERRADO">Cerrado</option>
                <option value="ARCHIVADO">Archivado</option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                <input v-model="form.importante" type="checkbox" />
                Marcar como expediente importante
                </label>
            </div>
            </div>
        </section>

        <section class="form-section">
            <div class="section-title">
            <h3>Pretensiones y observaciones</h3>
            <p>Descripción general del caso.</p>
            </div>

            <div class="textarea-grid">
            <div class="form-group">
                <label>Pretensiones</label>
                <textarea
                v-model="form.pretensiones"
                rows="5"
                placeholder="Describa las pretensiones del expediente..."
                ></textarea>
            </div>

            <div class="form-group">
                <label>Observaciones generales</label>
                <textarea
                v-model="form.observaciones_generales"
                rows="5"
                placeholder="Registre observaciones internas..."
                ></textarea>
            </div>
            </div>
        </section>

        <div class="form-actions">
            <button type="button" class="btn-light" @click="cancelar">
            Cancelar
            </button>

            <button type="submit" class="btn-primary-sm" :disabled="guardando">
            <i class="pi pi-save"></i>
            {{ guardando ? 'Guardando...' : 'Guardar expediente' }}
            </button>
        </div>
        </form>
    </div>
</template>