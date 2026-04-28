<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()

const expediente = ref(null)
const cargando = ref(true)
const error = ref('')
const tab = ref('generales')

const modalParte = ref(false)
const editandoParte = ref(false)
const parteSeleccionadaId = ref(null)
const guardandoParte = ref(false)
const errorParte = ref('')

const parteForm = ref({
    tipo_parte: '',
    tipo_persona: '',
    nombres_razon_social: '',
    documento_identidad: '',
    correo: '',
    telefono: '',
    direccion: '',
    observaciones: ''
})

const cargarExpediente = async () => {
    cargando.value = true
    error.value = ''

    try {
        const { data } = await api.get(`/expedientes/${route.params.id}`)
        expediente.value = data.expediente
    } catch (e) {
        error.value = 'No se pudo cargar la ficha del expediente.'
    } finally {
        cargando.value = false
    }
}

const formatoFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE')
}

const volver = () => {
    router.push('/expedientes')
}   

const abrirNuevaParte = () => {
    editandoParte.value = false
    parteSeleccionadaId.value = null
    errorParte.value = ''

    parteForm.value = {
        tipo_parte: '',
        tipo_persona: '',
        nombres_razon_social: '',
        documento_identidad: '',
        correo: '',
        telefono: '',
        direccion: '',
        observaciones: ''
    }

    modalParte.value = true
}

const abrirEditarParte = (parte) => {
    editandoParte.value = true
    parteSeleccionadaId.value = parte.id
    errorParte.value = ''

    parteForm.value = {
        tipo_parte: parte.tipo_parte || '',
        tipo_persona: parte.tipo_persona || '',
        nombres_razon_social: parte.nombres_razon_social || '',
        documento_identidad: parte.documento_identidad || '',
        correo: parte.correo || '',
        telefono: parte.telefono || '',
        direccion: parte.direccion || '',
        observaciones: parte.observaciones || ''
    }

    modalParte.value = true
}

const cerrarModalParte = () => {
    modalParte.value = false
    errorParte.value = ''
}

const validarParte = () => {
    if (!parteForm.value.tipo_parte) return 'Seleccione el tipo de parte.'
    if (!parteForm.value.tipo_persona) return 'Seleccione el tipo de persona.'
    if (!parteForm.value.nombres_razon_social) return 'Ingrese el nombre o razón social.'

    return ''
}

const guardarParte = async () => {
    errorParte.value = ''

    const validacion = validarParte()

    if (validacion) {
        errorParte.value = validacion
        return
    }

    guardandoParte.value = true

    try {
        const payload = {}

        Object.entries(parteForm.value).forEach(([key, value]) => {
        payload[key] = value === '' ? null : value
        })

        if (editandoParte.value) {
        await api.put(
            `/expedientes/${expediente.value.id}/partes/${parteSeleccionadaId.value}`,
            payload
        )
        } else {
        await api.post(`/expedientes/${expediente.value.id}/partes`, payload)
        }

        await cargarExpediente()
        modalParte.value = false
        tab.value = 'partes'
    } catch (e) {
        if (e.response?.data?.errors) {
        const firstError = Object.values(e.response.data.errors)[0]
        errorParte.value = firstError?.[0] || 'Error de validación.'
        } else {
        errorParte.value = e.response?.data?.message || 'No se pudo guardar la parte.'
        }
    } finally {
        guardandoParte.value = false
    }
    }

    const eliminarParte = async (parte) => {
    const confirmar = confirm(`¿Seguro que deseas eliminar a "${parte.nombres_razon_social}"?`)

    if (!confirmar) return

    try {
        await api.delete(`/expedientes/${expediente.value.id}/partes/${parte.id}`)
        await cargarExpediente()
        tab.value = 'partes'
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo eliminar la parte.')
    }
}

onMounted(() => {
    cargarExpediente()
})
</script>

<template>
    <div class="expediente-show-page">
        <div class="page-title dashboard-title-row">
        <div>
            <h2>Ficha del expediente</h2>
            <p>Consulta integral del expediente y sus componentes.</p>
        </div>

        <button class="btn-light" @click="volver">
            <i class="pi pi-arrow-left"></i>
            Volver
        </button>
        </div>

        <div v-if="error" class="alert-error">
        {{ error }}
        </div>

        <div v-if="cargando" class="loading-box">
        Cargando expediente...
        </div>

        <template v-else-if="expediente">
        <section class="expediente-hero">
            <div>
            <span class="hero-label">Expediente</span>
            <h2>{{ expediente.numero_expediente }}</h2>
            <p>{{ expediente.codigo_unico_interno }}</p>
            </div>

            <div class="hero-badges">
            <span class="badge primary">
                {{ expediente.tipo_expediente?.nombre || '-' }}
            </span>

            <span class="badge success">
                {{ expediente.estado_expediente?.nombre || '-' }}
            </span>

            <span v-if="expediente.importante" class="badge danger">
                Importante
            </span>
            </div>
        </section>

        <section class="tabs-card">
            <div class="tabs-menu">
            <button :class="{ active: tab === 'generales' }" @click="tab = 'generales'">
                <i class="pi pi-info-circle"></i>
                Datos generales
            </button>

            <button :class="{ active: tab === 'partes' }" @click="tab = 'partes'">
                <i class="pi pi-users"></i>
                Partes
            </button>

            <button :class="{ active: tab === 'detalles' }" @click="tab = 'detalles'">
                <i class="pi pi-briefcase"></i>
                Detalles
            </button>

            <button :class="{ active: tab === 'actuaciones' }" @click="tab = 'actuaciones'">
                <i class="pi pi-list"></i>
                Actuaciones
            </button>

            <button :class="{ active: tab === 'documentos' }" @click="tab = 'documentos'">
                <i class="pi pi-file"></i>
                Documentos
            </button>

            <button :class="{ active: tab === 'alertas' }" @click="tab = 'alertas'">
                <i class="pi pi-bell"></i>
                Alertas
            </button>
            </div>

            <div class="tab-content">
            <section v-if="tab === 'generales'">
                <div class="section-title">
                <h3>Datos generales</h3>
                <p>Información principal registrada del expediente.</p>
                </div>

                <div class="detail-grid">
                <div class="detail-item">
                    <label>Tipo</label>
                    <strong>{{ expediente.tipo_expediente?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Estado procesal</label>
                    <strong>{{ expediente.estado_expediente?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Encargado</label>
                    <strong>{{ expediente.encargado_actual?.nombre_completo || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Prioridad</label>
                    <strong>{{ expediente.prioridad?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Distrito judicial</label>
                    <strong>{{ expediente.distrito_judicial?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Dependencia</label>
                    <strong>{{ expediente.dependencia?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Especialidad</label>
                    <strong>{{ expediente.especialidad?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Instancia</label>
                    <strong>{{ expediente.instancia?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Materia</label>
                    <strong>{{ expediente.materia?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Etapa</label>
                    <strong>{{ expediente.etapa?.nombre || '-' }}</strong>
                </div>

                <div class="detail-item">
                    <label>Monto</label>
                    <strong>S/ {{ Number(expediente.monto || 0).toLocaleString('es-PE', { minimumFractionDigits: 2 }) }}</strong>
                </div>

                <div class="detail-item">
                    <label>Estado registro</label>
                    <strong>{{ expediente.estado_registro }}</strong>
                </div>

                <div class="detail-item">
                    <label>Fecha registro</label>
                    <strong>{{ formatoFecha(expediente.fecha_registro) }}</strong>
                </div>

                <div class="detail-item">
                    <label>Fecha ingreso</label>
                    <strong>{{ formatoFecha(expediente.fecha_ingreso) }}</strong>
                </div>

                <div class="detail-item">
                    <label>Última actuación</label>
                    <strong>{{ formatoFecha(expediente.fecha_ultima_actuacion) }}</strong>
                </div>

                <div class="detail-item">
                    <label>Próximo vencimiento</label>
                    <strong>{{ formatoFecha(expediente.fecha_proximo_vencimiento) }}</strong>
                </div>
                </div>

                <div class="detail-text-grid">
                <div class="detail-text">
                    <label>Pretensiones</label>
                    <p>{{ expediente.pretensiones || 'Sin pretensiones registradas.' }}</p>
                </div>

                <div class="detail-text">
                    <label>Observaciones</label>
                    <p>{{ expediente.observaciones_generales || 'Sin observaciones registradas.' }}</p>
                </div>
                </div>
            </section>

            <section v-if="tab === 'partes'">
                <div class="section-title section-title-row">
                    <div>
                    <h3>Partes involucradas</h3>
                    <p>Demandantes, demandados, denunciantes, contratistas u otros.</p>
                    </div>

                    <button class="btn-primary-sm" @click="abrirNuevaParte">
                    <i class="pi pi-plus"></i>
                    Agregar parte
                    </button>
                </div>

                <div v-if="!expediente.partes?.length" class="empty-box">
                    No hay partes registradas.
                </div>

                <div v-else class="professional-table-wrapper">
                    <table class="professional-table">
                    <thead>
                        <tr>
                        <th>Tipo parte</th>
                        <th>Tipo persona</th>
                        <th>Nombre / Razón social</th>
                        <th>Documento</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="parte in expediente.partes" :key="parte.id">
                        <td>
                            <span class="badge primary">{{ parte.tipo_parte }}</span>
                        </td>

                        <td>{{ parte.tipo_persona }}</td>

                        <td>
                            <strong>{{ parte.nombres_razon_social }}</strong>
                            <small>{{ parte.direccion || 'Sin dirección' }}</small>
                        </td>

                        <td>{{ parte.documento_identidad || '-' }}</td>
                        <td>{{ parte.correo || '-' }}</td>
                        <td>{{ parte.telefono || '-' }}</td>

                        <td class="text-center">
                            <button class="btn-icon" title="Editar" @click="abrirEditarParte(parte)">
                            <i class="pi pi-pencil"></i>
                            </button>

                            <button class="btn-icon danger-icon" title="Eliminar" @click="eliminarParte(parte)">
                            <i class="pi pi-trash"></i>
                            </button>
                        </td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </section>

            <section v-if="tab === 'detalles'">
                <div class="section-title">
                <h3>Detalle por tipo de expediente</h3>
                <p>Información especializada según corresponda.</p>
                </div>

                <div v-if="expediente.judicial_detalle" class="detail-text">
                <label>Detalle judicial</label>
                <p><strong>Sumilla:</strong> {{ expediente.judicial_detalle.sumilla || '-' }}</p>
                <p><strong>Observaciones:</strong> {{ expediente.judicial_detalle.observaciones || '-' }}</p>
                </div>

                <div v-else-if="expediente.penal_detalle" class="detail-text">
                <label>Detalle penal</label>
                <p><strong>Delito:</strong> {{ expediente.penal_detalle.delito || '-' }}</p>
                <p><strong>Fiscalía:</strong> {{ expediente.penal_detalle.fiscalia || '-' }}</p>
                <p><strong>Observaciones:</strong> {{ expediente.penal_detalle.observaciones || '-' }}</p>
                </div>

                <div v-else-if="expediente.masc_detalle" class="detail-text">
                <label>Detalle MASC</label>
                <p><strong>Subtipo:</strong> {{ expediente.masc_detalle.subtipo_masc }}</p>
                <p><strong>Centro:</strong> {{ expediente.masc_detalle.centro_masc || '-' }}</p>
                <p><strong>Estado especial:</strong> {{ expediente.masc_detalle.estado_especial || '-' }}</p>
                <p><strong>Observaciones:</strong> {{ expediente.masc_detalle.observaciones || '-' }}</p>
                </div>

                <div v-else class="empty-box">
                No hay detalle especializado registrado.
                </div>
            </section>

            <section v-if="tab === 'actuaciones'">
                <div class="section-title">
                <h3>Actuaciones</h3>
                <p>Historial de movimientos y seguimiento del expediente.</p>
                </div>

                <div v-if="!expediente.actuaciones?.length" class="empty-box">
                No hay actuaciones registradas.
                </div>

                <div v-else class="timeline">
                <div v-for="act in expediente.actuaciones" :key="act.id" class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-card">
                    <div class="timeline-head">
                        <strong>{{ act.tipo_actuacion?.nombre || 'Actuación' }}</strong>
                        <span>{{ formatoFecha(act.fecha_actuacion) }}</span>
                    </div>
                    <p>{{ act.descripcion }}</p>
                    <small>Registrado por: {{ act.usuario?.nombre_completo || '-' }}</small>
                    </div>
                </div>
                </div>
            </section>

            <section v-if="tab === 'documentos'">
                <div class="section-title">
                <h3>Documentos</h3>
                <p>Archivos asociados al expediente.</p>
                </div>

                <div v-if="!expediente.documentos?.length" class="empty-box">
                No hay documentos registrados.
                </div>

                <div v-else class="professional-table-wrapper">
                <table class="professional-table">
                    <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Tipo</th>
                        <th>Extensión</th>
                        <th>Peso</th>
                        <th>Estado</th>
                        <th>Subido por</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr v-for="doc in expediente.documentos" :key="doc.id">
                        <td><strong>{{ doc.nombre_original }}</strong></td>
                        <td>{{ doc.tipo_documento?.nombre || '-' }}</td>
                        <td>{{ doc.extension }}</td>
                        <td>{{ Math.round((doc.peso_bytes || 0) / 1024) }} KB</td>
                        <td><span class="badge primary">{{ doc.estado }}</span></td>
                        <td>{{ doc.subido_por?.nombre_completo || '-' }}</td>
                    </tr>
                    </tbody>
                </table>
                </div>
            </section>

            <section v-if="tab === 'alertas'">
                <div class="section-title">
                <h3>Alertas</h3>
                <p>Alertas y vencimientos relacionados al expediente.</p>
                </div>

                <div v-if="!expediente.alertas?.length" class="empty-box">
                No hay alertas registradas.
                </div>

                <div v-else class="list-box">
                <div v-for="alerta in expediente.alertas" :key="alerta.id" class="list-item">
                    <div>
                    <strong>{{ alerta.tipo_alerta?.nombre || 'Alerta' }}</strong>
                    <p>{{ alerta.mensaje }}</p>
                    </div>

                    <span class="badge danger">
                    {{ alerta.estado }}
                    </span>
                </div>
                </div>
            </section>
            </div>
        </section>
        </template>
    </div>

    <div v-if="modalParte" class="modal-overlay">
        <div class="modal-card large">
            <div class="modal-header">
            <div>
                <h3>{{ editandoParte ? 'Editar parte' : 'Agregar parte' }}</h3>
                <p>Registra la información de la parte involucrada.</p>
            </div>

            <button class="btn-icon" @click="cerrarModalParte">
                <i class="pi pi-times"></i>
            </button>
            </div>

            <div v-if="errorParte" class="alert-error">
            {{ errorParte }}
            </div>

            <div class="filters-grid">
            <div class="form-group">
                <label>Tipo de parte *</label>
                <select v-model="parteForm.tipo_parte">
                <option value="">Seleccione</option>
                <option value="DEMANDANTE">Demandante</option>
                <option value="DEMANDADO">Demandado</option>
                <option value="DENUNCIANTE">Denunciante</option>
                <option value="DENUNCIADO">Denunciado</option>
                <option value="SOLICITANTE">Solicitante</option>
                <option value="CONTRATISTA">Contratista</option>
                <option value="ENTIDAD">Entidad</option>
                <option value="TERCERO">Tercero</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tipo de persona *</label>
                <select v-model="parteForm.tipo_persona">
                <option value="">Seleccione</option>
                <option value="NATURAL">Natural</option>
                <option value="JURIDICA">Jurídica</option>
                </select>
            </div>

            <div class="form-group">
                <label>Documento</label>
                <input v-model="parteForm.documento_identidad" type="text" placeholder="DNI / RUC / CE" />
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input v-model="parteForm.telefono" type="text" placeholder="999888777" />
            </div>
            </div>

            <div class="form-group">
            <label>Nombre o razón social *</label>
            <input
                v-model="parteForm.nombres_razon_social"
                type="text"
                placeholder="Ingrese nombre completo o razón social"
            />
            </div>

            <div class="filters-grid two">
            <div class="form-group">
                <label>Correo</label>
                <input v-model="parteForm.correo" type="email" placeholder="correo@dominio.gob.pe" />
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <input v-model="parteForm.direccion" type="text" placeholder="Dirección referencial" />
            </div>
            </div>

            <div class="form-group">
            <label>Observaciones</label>
            <textarea
                v-model="parteForm.observaciones"
                rows="4"
                placeholder="Observaciones internas..."
            ></textarea>
            </div>

            <div class="modal-actions">
            <button class="btn-light" @click="cerrarModalParte">
                Cancelar
            </button>

            <button class="btn-primary-sm" :disabled="guardandoParte" @click="guardarParte">
                <i class="pi pi-save"></i>
                {{ guardandoParte ? 'Guardando...' : 'Guardar parte' }}
            </button>
            </div>
        </div>
    </div>
</template>