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

const modalActuacion = ref(false)
const editandoActuacion = ref(false)
const actuacionSeleccionadaId = ref(null)
const guardandoActuacion = ref(false)
const errorActuacion = ref('')

const tiposActuacion = ref([])
const estadosExpediente = ref([])

const modalDocumento = ref(false)
const subiendoDocumento = ref(false)
const errorDocumento = ref('')

const tiposDocumento = ref([])

const modalAlerta = ref(false)
const creandoAlerta = ref(false)
const errorAlerta = ref('')

const tiposAlerta = ref([])
const usuariosDestino = ref([])

const modalEditarExpediente = ref(false)
const modalCerrarExpediente = ref(false)
const modalReabrirExpediente = ref(false)

const guardandoExpediente = ref(false)
const errorExpediente = ref('')

const catalogosExpediente = ref({
    tipos_expediente: [],
    estados_expediente: [],
    distritos_judiciales: [],
    dependencias: [],
    especialidades: [],
    instancias: [],
    materias: [],
    etapas: [],
    prioridades: [],
    motivos_cierre: [],
    usuarios: []
})

const expedienteForm = ref({
    tipo_expediente_id: '',
    numero_expediente: '',
    anio_expediente: '',
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
    fecha_registro: '',
    fecha_ingreso: '',
    fecha_ultima_actuacion: '',
    fecha_proximo_vencimiento: '',
    fecha_cierre: '',
    motivo_cierre_id: '',
    importante: false,
    estado_registro: 'ACTIVO'
})

const cerrarForm = ref({
    motivo_cierre_id: '',
    fecha_cierre: new Date().toISOString().slice(0, 10),
    observaciones_generales: ''
})

const reabrirForm = ref({
    justificacion: ''
})

const alertaForm = ref({
    tipo_alerta_id: '',
    fecha_alerta: '',
    mensaje: '',
    usuario_destino_id: '',
    leido: false,
    estado: 'ACTIVA'
})

const documentoForm = ref({
    tipo_documento_id: '',
    actuacion_id: '',
    archivo: null,
    observacion: '',
    version: 1
})

const actuacionForm = ref({
    tipo_actuacion_id: '',
    fecha_actuacion: '',
    descripcion: '',
    fecha_proxima_accion: '',
    resultado: '',
    observaciones: '',
    estado_resultante_id: ''
})

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

const cargarCatalogosActuacion = async () => {
    try {
        const { data } = await api.get('/catalogos/todos')
        tiposActuacion.value = data.tipos_actuacion || []
        estadosExpediente.value = data.estados_expediente || []
        tiposDocumento.value = data.tipos_documento || []
        tiposAlerta.value = data.tipos_alerta || []

        const usuarios = await api.get('/usuarios?per_page=100')
        usuariosDestino.value = usuarios.data.data || []

        catalogosExpediente.value = {
            ...catalogosExpediente.value,
            ...data
        }

        catalogosExpediente.value.usuarios = usuarios.data.data || []
    } catch (e) {
        console.error('No se pudieron cargar catálogos de actuación', e)
    }
}

const fechaHoraLocal = () => {
    const ahora = new Date()
    ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset())
    return ahora.toISOString().slice(0, 16)
}

const abrirNuevaActuacion = () => {
    editandoActuacion.value = false
    actuacionSeleccionadaId.value = null
    errorActuacion.value = ''

    actuacionForm.value = {
        tipo_actuacion_id: '',
        fecha_actuacion: fechaHoraLocal(),
        descripcion: '',
        fecha_proxima_accion: '',
        resultado: '',
        observaciones: '',
        estado_resultante_id: ''
    }

    modalActuacion.value = true
}

const abrirEditarActuacion = (actuacion) => {
    editandoActuacion.value = true
    actuacionSeleccionadaId.value = actuacion.id
    errorActuacion.value = ''

    actuacionForm.value = {
        tipo_actuacion_id: actuacion.tipo_actuacion_id || '',
        fecha_actuacion: actuacion.fecha_actuacion
            ? new Date(actuacion.fecha_actuacion).toISOString().slice(0, 16)
            : '',
        descripcion: actuacion.descripcion || '',
        fecha_proxima_accion: actuacion.fecha_proxima_accion
            ? new Date(actuacion.fecha_proxima_accion).toISOString().slice(0, 16)
            : '',
        resultado: actuacion.resultado || '',
        observaciones: actuacion.observaciones || '',
        estado_resultante_id: actuacion.estado_resultante_id || ''
    }

    modalActuacion.value = true
}

const cerrarModalActuacion = () => {
    modalActuacion.value = false
    errorActuacion.value = ''
}

const validarActuacion = () => {
    if (!actuacionForm.value.tipo_actuacion_id) return 'Seleccione el tipo de actuación.'
    if (!actuacionForm.value.fecha_actuacion) return 'Ingrese la fecha de actuación.'
    if (!actuacionForm.value.descripcion) return 'Ingrese la descripción de la actuación.'

    return ''
}

const limpiarPayloadActuacion = () => {
    const payload = {}

    Object.entries(actuacionForm.value).forEach(([key, value]) => {
        payload[key] = value === '' ? null : value
    })

    return payload
}

const guardarActuacion = async () => {
    errorActuacion.value = ''

    const validacion = validarActuacion()

    if (validacion) {
        errorActuacion.value = validacion
        return
    }

    guardandoActuacion.value = true

    try {
        const payload = limpiarPayloadActuacion()

        if (editandoActuacion.value) {
            await api.put(
                `/expedientes/${expediente.value.id}/actuaciones/${actuacionSeleccionadaId.value}`,
                payload
            )
        } else {
            await api.post(`/expedientes/${expediente.value.id}/actuaciones`, payload)
        }

        await cargarExpediente()
        modalActuacion.value = false
        tab.value = 'actuaciones'
    } catch (e) {
        if (e.response?.data?.errors) {
            const firstError = Object.values(e.response.data.errors)[0]
            errorActuacion.value = firstError?.[0] || 'Error de validación.'
        } else {
            errorActuacion.value = e.response?.data?.message || 'No se pudo guardar la actuación.'
        }
    } finally {
        guardandoActuacion.value = false
    }
}

const eliminarActuacion = async (actuacion) => {
    const confirmar = confirm('¿Seguro que deseas eliminar esta actuación?')

    if (!confirmar) return

    try {
        await api.delete(`/expedientes/${expediente.value.id}/actuaciones/${actuacion.id}`)
        await cargarExpediente()
        tab.value = 'actuaciones'
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo eliminar la actuación.')
    }
}

const abrirSubirDocumento = () => {
    errorDocumento.value = ''

    documentoForm.value = {
        tipo_documento_id: '',
        actuacion_id: '',
        archivo: null,
        observacion: '',
        version: 1
    }

    modalDocumento.value = true
}

const cerrarModalDocumento = () => {
    modalDocumento.value = false
    errorDocumento.value = ''
}

const seleccionarArchivo = (event) => {
    documentoForm.value.archivo = event.target.files[0] || null
}

const validarDocumento = () => {
    if (!documentoForm.value.tipo_documento_id) return 'Seleccione el tipo de documento.'
    if (!documentoForm.value.archivo) return 'Seleccione un archivo.'

    return ''
}

const subirDocumento = async () => {
    errorDocumento.value = ''

    const validacion = validarDocumento()

    if (validacion) {
        errorDocumento.value = validacion
        return
    }

    subiendoDocumento.value = true

    try {
        const formData = new FormData()

        formData.append('tipo_documento_id', documentoForm.value.tipo_documento_id)
        formData.append('archivo', documentoForm.value.archivo)
        formData.append('version', documentoForm.value.version || 1)

        if (documentoForm.value.actuacion_id) {
            formData.append('actuacion_id', documentoForm.value.actuacion_id)
        }

        if (documentoForm.value.observacion) {
            formData.append('observacion', documentoForm.value.observacion)
        }

        await api.post(`/expedientes/${expediente.value.id}/documentos`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })

        await cargarExpediente()
        modalDocumento.value = false
        tab.value = 'documentos'
    } catch (e) {
        if (e.response?.data?.errors) {
            const firstError = Object.values(e.response.data.errors)[0]
            errorDocumento.value = firstError?.[0] || 'Error de validación.'
        } else {
            errorDocumento.value = e.response?.data?.message || 'No se pudo subir el documento.'
        }
    } finally {
        subiendoDocumento.value = false
    }
}

const descargarDocumento = async (doc) => {
    try {
        const response = await api.get(
            `/expedientes/${expediente.value.id}/documentos/${doc.id}/descargar`,
            {
                responseType: 'blob'
            }
        )

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')

        link.href = url
        link.setAttribute('download', doc.nombre_original)
        document.body.appendChild(link)
        link.click()
        link.remove()

        window.URL.revokeObjectURL(url)
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo descargar el documento.')
    }
}

const anularDocumento = async (doc) => {
    const motivo = prompt('Ingrese el motivo de anulación del documento:')

    if (!motivo || motivo.length < 5) {
        alert('Debe ingresar un motivo válido.')
        return
    }

    try {
        await api.patch(`/expedientes/${expediente.value.id}/documentos/${doc.id}/anular`, {
            motivo
        })

        await cargarExpediente()
        tab.value = 'documentos'
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo anular el documento.')
    }
}

const eliminarDocumento = async (doc) => {
    const confirmar = confirm(`¿Seguro que deseas eliminar el documento "${doc.nombre_original}"?`)

    if (!confirmar) return

    try {
        await api.delete(`/expedientes/${expediente.value.id}/documentos/${doc.id}`)
        await cargarExpediente()
        tab.value = 'documentos'
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo eliminar el documento.')
    }
}

const pesoDocumento = (bytes) => {
    if (!bytes) return '0 KB'

    if (bytes < 1024 * 1024) {
        return `${Math.round(bytes / 1024)} KB`
    }

    return `${(bytes / 1024 / 1024).toFixed(2)} MB`
}

const abrirNuevaAlerta = () => {
    errorAlerta.value = ''

    const ahora = new Date()
    ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset())

    alertaForm.value = {
        tipo_alerta_id: '',
        fecha_alerta: ahora.toISOString().slice(0, 16),
        mensaje: '',
        usuario_destino_id: '',
        leido: false,
        estado: 'ACTIVA'
    }

    modalAlerta.value = true
}

const cerrarModalAlerta = () => {
    modalAlerta.value = false
    errorAlerta.value = ''
}

const validarAlerta = () => {
    if (!alertaForm.value.tipo_alerta_id) return 'Seleccione el tipo de alerta.'
    if (!alertaForm.value.fecha_alerta) return 'Ingrese la fecha de alerta.'
    if (!alertaForm.value.mensaje) return 'Ingrese el mensaje de la alerta.'

    return ''
}

const limpiarPayloadAlerta = () => {
    const payload = {}

    Object.entries(alertaForm.value).forEach(([key, value]) => {
        payload[key] = value === '' ? null : value
    })

    payload.leido = !!alertaForm.value.leido

    return payload
}

const crearAlerta = async () => {
    errorAlerta.value = ''

    const validacion = validarAlerta()

    if (validacion) {
        errorAlerta.value = validacion
        return
    }

    creandoAlerta.value = true

    try {
        const payload = limpiarPayloadAlerta()

        await api.post(`/expedientes/${expediente.value.id}/alertas`, payload)

        await cargarExpediente()
        modalAlerta.value = false
        tab.value = 'alertas'
    } catch (e) {
        if (e.response?.data?.errors) {
            const firstError = Object.values(e.response.data.errors)[0]
            errorAlerta.value = firstError?.[0] || 'Error de validación.'
        } else {
            errorAlerta.value = e.response?.data?.message || 'No se pudo crear la alerta.'
        }
    } finally {
        creandoAlerta.value = false
    }
}

const marcarAlertaLeida = async (alerta) => {
    try {
        await api.patch(`/expedientes/${expediente.value.id}/alertas/${alerta.id}/leer`)
        await cargarExpediente()
        tab.value = 'alertas'
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo marcar la alerta como leída.')
    }
}

const atenderAlerta = async (alerta) => {
    const confirmar = confirm('¿Confirmas que esta alerta ya fue atendida?')

    if (!confirmar) return

    try {
        await api.patch(`/expedientes/${expediente.value.id}/alertas/${alerta.id}/atender`)
        await cargarExpediente()
        tab.value = 'alertas'
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo atender la alerta.')
    }
}

const anularAlerta = async (alerta) => {
    const motivo = prompt('Ingrese el motivo de anulación de la alerta:')

    if (!motivo || motivo.length < 5) {
        alert('Debe ingresar un motivo válido.')
        return
    }

    try {
        await api.patch(`/expedientes/${expediente.value.id}/alertas/${alerta.id}/anular`, {
            motivo
        })

        await cargarExpediente()
        tab.value = 'alertas'
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo anular la alerta.')
    }
}

const eliminarAlerta = async (alerta) => {
    const confirmar = confirm('¿Seguro que deseas eliminar esta alerta?')

    if (!confirmar) return

    try {
        await api.delete(`/expedientes/${expediente.value.id}/alertas/${alerta.id}`)
        await cargarExpediente()
        tab.value = 'alertas'
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo eliminar la alerta.')
    }
}

const fechaInput = (fecha) => {
    if (!fecha) return ''
    return String(fecha).slice(0, 10)
}

const abrirEditarExpediente = () => {
    errorExpediente.value = ''

    expedienteForm.value = {
        tipo_expediente_id: expediente.value.tipo_expediente_id || '',
        numero_expediente: expediente.value.numero_expediente || '',
        anio_expediente: expediente.value.anio_expediente || '',
        codigo_unico_interno: expediente.value.codigo_unico_interno || '',
        distrito_judicial_id: expediente.value.distrito_judicial_id || '',
        dependencia_id: expediente.value.dependencia_id || '',
        especialidad_id: expediente.value.especialidad_id || '',
        instancia_id: expediente.value.instancia_id || '',
        materia_id: expediente.value.materia_id || '',
        etapa_id: expediente.value.etapa_id || '',
        estado_expediente_id: expediente.value.estado_expediente_id || '',
        encargado_actual_id: expediente.value.encargado_actual_id || '',
        prioridad_id: expediente.value.prioridad_id || '',
        monto: expediente.value.monto || 0,
        pretensiones: expediente.value.pretensiones || '',
        observaciones_generales: expediente.value.observaciones_generales || '',
        fecha_registro: fechaInput(expediente.value.fecha_registro),
        fecha_ingreso: fechaInput(expediente.value.fecha_ingreso),
        fecha_ultima_actuacion: fechaInput(expediente.value.fecha_ultima_actuacion),
        fecha_proximo_vencimiento: fechaInput(expediente.value.fecha_proximo_vencimiento),
        fecha_cierre: fechaInput(expediente.value.fecha_cierre),
        motivo_cierre_id: expediente.value.motivo_cierre_id || '',
        importante: !!expediente.value.importante,
        estado_registro: expediente.value.estado_registro || 'ACTIVO'
    }

    modalEditarExpediente.value = true
}

const cerrarModalEditarExpediente = () => {
    modalEditarExpediente.value = false
    errorExpediente.value = ''
}

const limpiarPayloadExpediente = () => {
    const payload = {}

    Object.entries(expedienteForm.value).forEach(([key, value]) => {
        payload[key] = value === '' ? null : value
    })

    payload.importante = !!expedienteForm.value.importante
    payload.monto = Number(expedienteForm.value.monto || 0)

    return payload
}

const validarExpediente = () => {
    if (!expedienteForm.value.tipo_expediente_id) return 'Seleccione el tipo de expediente.'
    if (!expedienteForm.value.numero_expediente) return 'Ingrese el número de expediente.'
    if (!expedienteForm.value.codigo_unico_interno) return 'Ingrese el código interno.'
    if (!expedienteForm.value.estado_expediente_id) return 'Seleccione el estado procesal.'
    if (!expedienteForm.value.encargado_actual_id) return 'Seleccione el encargado.'
    if (!expedienteForm.value.fecha_registro) return 'Ingrese la fecha de registro.'
    if (Number(expedienteForm.value.monto) < 0) return 'El monto no puede ser negativo.'

    return ''
}

const guardarEdicionExpediente = async () => {
    errorExpediente.value = ''

    const validacion = validarExpediente()

    if (validacion) {
        errorExpediente.value = validacion
        return
    }

    guardandoExpediente.value = true

    try {
        await api.put(`/expedientes/${expediente.value.id}`, limpiarPayloadExpediente())

        await cargarExpediente()
        modalEditarExpediente.value = false
        tab.value = 'generales'
    } catch (e) {
        if (e.response?.data?.errors) {
            const firstError = Object.values(e.response.data.errors)[0]
            errorExpediente.value = firstError?.[0] || 'Error de validación.'
        } else {
            errorExpediente.value = e.response?.data?.message || 'No se pudo actualizar el expediente.'
        }
    } finally {
        guardandoExpediente.value = false
    }
}

const cambiarImportante = async () => {
    try {
        await api.patch(`/expedientes/${expediente.value.id}/importante`, {
            importante: !expediente.value.importante
        })

        await cargarExpediente()
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo actualizar la importancia del expediente.')
    }
}

const abrirCerrarExpediente = () => {
    errorExpediente.value = ''

    cerrarForm.value = {
        motivo_cierre_id: '',
        fecha_cierre: new Date().toISOString().slice(0, 10),
        observaciones_generales: expediente.value.observaciones_generales || ''
    }

    modalCerrarExpediente.value = true
}

const cerrarModalCerrarExpediente = () => {
    modalCerrarExpediente.value = false
    errorExpediente.value = ''
}

const confirmarCerrarExpediente = async () => {
    errorExpediente.value = ''

    if (!cerrarForm.value.motivo_cierre_id) {
        errorExpediente.value = 'Seleccione el motivo de cierre.'
        return
    }

    if (!cerrarForm.value.fecha_cierre) {
        errorExpediente.value = 'Ingrese la fecha de cierre.'
        return
    }

    guardandoExpediente.value = true

    try {
        await api.patch(`/expedientes/${expediente.value.id}/cerrar`, cerrarForm.value)

        await cargarExpediente()
        modalCerrarExpediente.value = false
        tab.value = 'generales'
    } catch (e) {
        if (e.response?.data?.errors) {
            const firstError = Object.values(e.response.data.errors)[0]
            errorExpediente.value = firstError?.[0] || 'Error de validación.'
        } else {
            errorExpediente.value = e.response?.data?.message || 'No se pudo cerrar el expediente.'
        }
    } finally {
        guardandoExpediente.value = false
    }
}

const abrirReabrirExpediente = () => {
    errorExpediente.value = ''

    reabrirForm.value = {
        justificacion: ''
    }

    modalReabrirExpediente.value = true
}

const cerrarModalReabrirExpediente = () => {
    modalReabrirExpediente.value = false
    errorExpediente.value = ''
}

const confirmarReabrirExpediente = async () => {
    errorExpediente.value = ''

    if (!reabrirForm.value.justificacion || reabrirForm.value.justificacion.length < 5) {
        errorExpediente.value = 'Ingrese una justificación válida.'
        return
    }

    guardandoExpediente.value = true

    try {
        await api.patch(`/expedientes/${expediente.value.id}/reabrir`, reabrirForm.value)

        await cargarExpediente()
        modalReabrirExpediente.value = false
        tab.value = 'generales'
    } catch (e) {
        if (e.response?.data?.errors) {
            const firstError = Object.values(e.response.data.errors)[0]
            errorExpediente.value = firstError?.[0] || 'Error de validación.'
        } else {
            errorExpediente.value = e.response?.data?.message || 'No se pudo reabrir el expediente.'
        }
    } finally {
        guardandoExpediente.value = false
    }
}

onMounted(async () => {
    await cargarCatalogosActuacion()
    await cargarExpediente()
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

                    <div class="hero-actions">
                        <button class="btn-hero" @click="abrirEditarExpediente">
                            <i class="pi pi-pencil"></i>
                            Editar
                        </button>

                        <button class="btn-hero" @click="cambiarImportante">
                            <i class="pi pi-star"></i>
                            {{ expediente.importante ? 'Quitar importante' : 'Marcar importante' }}
                        </button>

                        <button v-if="expediente.estado_registro !== 'CERRADO'" class="btn-hero danger"
                            @click="abrirCerrarExpediente">
                            <i class="pi pi-lock"></i>
                            Cerrar
                        </button>

                        <button v-if="expediente.estado_registro === 'CERRADO'" class="btn-hero success"
                            @click="abrirReabrirExpediente">
                            <i class="pi pi-lock-open"></i>
                            Reabrir
                        </button>
                    </div>
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
                                <strong>S/ {{ Number(expediente.monto || 0).toLocaleString('es-PE', {
                                    minimumFractionDigits: 2
                                }) }}</strong>
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

                                            <button class="btn-icon danger-icon" title="Eliminar"
                                                @click="eliminarParte(parte)">
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
                            <p><strong>Observaciones:</strong> {{ expediente.judicial_detalle.observaciones || '-' }}
                            </p>
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
                            <p><strong>Estado especial:</strong> {{ expediente.masc_detalle.estado_especial || '-' }}
                            </p>
                            <p><strong>Observaciones:</strong> {{ expediente.masc_detalle.observaciones || '-' }}</p>
                        </div>

                        <div v-else class="empty-box">
                            No hay detalle especializado registrado.
                        </div>
                    </section>

                    <section v-if="tab === 'actuaciones'">
                        <div class="section-title section-title-row">
                            <div>
                                <h3>Actuaciones</h3>
                                <p>Historial de movimientos y seguimiento del expediente.</p>
                            </div>

                            <button class="btn-primary-sm" @click="abrirNuevaActuacion">
                                <i class="pi pi-plus"></i>
                                Agregar actuación
                            </button>
                        </div>

                        <div v-if="!expediente.actuaciones?.length" class="empty-box">
                            No hay actuaciones registradas.
                        </div>

                        <div v-else class="timeline">
                            <div v-for="act in expediente.actuaciones" :key="act.id" class="timeline-item">
                                <div class="timeline-dot"></div>

                                <div class="timeline-card">
                                    <div class="timeline-head">
                                        <div>
                                            <strong>{{ act.tipo_actuacion?.nombre || 'Actuación' }}</strong>
                                            <p>{{ formatoFecha(act.fecha_actuacion) }}</p>
                                        </div>

                                        <div class="timeline-actions">
                                            <button class="btn-icon" title="Editar" @click="abrirEditarActuacion(act)">
                                                <i class="pi pi-pencil"></i>
                                            </button>

                                            <button class="btn-icon danger-icon" title="Eliminar"
                                                @click="eliminarActuacion(act)">
                                                <i class="pi pi-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <p>{{ act.descripcion }}</p>

                                    <div class="timeline-meta">
                                        <span>
                                            <i class="pi pi-user"></i>
                                            {{ act.usuario?.nombre_completo || '-' }}
                                        </span>

                                        <span v-if="act.fecha_proxima_accion">
                                            <i class="pi pi-calendar"></i>
                                            Próxima acción: {{ formatoFecha(act.fecha_proxima_accion) }}
                                        </span>

                                        <span v-if="act.estado_resultante">
                                            <i class="pi pi-flag"></i>
                                            Estado resultante: {{ act.estado_resultante?.nombre }}
                                        </span>
                                    </div>

                                    <div v-if="act.resultado || act.observaciones" class="timeline-extra">
                                        <p v-if="act.resultado">
                                            <strong>Resultado:</strong> {{ act.resultado }}
                                        </p>

                                        <p v-if="act.observaciones">
                                            <strong>Observaciones:</strong> {{ act.observaciones }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section v-if="tab === 'documentos'">
                        <div class="section-title section-title-row">
                            <div>
                                <h3>Documentos</h3>
                                <p>Archivos asociados al expediente.</p>
                            </div>

                            <button class="btn-primary-sm" @click="abrirSubirDocumento">
                                <i class="pi pi-upload"></i>
                                Subir documento
                            </button>
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
                                        <th>Actuación</th>
                                        <th>Extensión</th>
                                        <th>Peso</th>
                                        <th>Versión</th>
                                        <th>Estado</th>
                                        <th>Subido por</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="doc in expediente.documentos" :key="doc.id">
                                        <td>
                                            <strong>{{ doc.nombre_original }}</strong>
                                            <small>{{ doc.observacion || 'Sin observación' }}</small>
                                        </td>

                                        <td>{{ doc.tipo_documento?.nombre || '-' }}</td>

                                        <td>
                                            <span v-if="doc.actuacion">
                                                {{ doc.actuacion.tipo_actuacion?.nombre || 'Actuación' }}
                                            </span>
                                            <span v-else>-</span>
                                        </td>

                                        <td>
                                            <span class="badge primary">{{ doc.extension }}</span>
                                        </td>

                                        <td>{{ pesoDocumento(doc.peso_bytes) }}</td>

                                        <td>v{{ doc.version }}</td>

                                        <td>
                                            <span class="badge" :class="doc.estado === 'ACTIVO' ? 'success' : 'danger'">
                                                {{ doc.estado }}
                                            </span>
                                        </td>

                                        <td>{{ doc.subido_por?.nombre_completo || '-' }}</td>

                                        <td class="text-center">
                                            <button class="btn-icon" title="Descargar" @click="descargarDocumento(doc)">
                                                <i class="pi pi-download"></i>
                                            </button>

                                            <button v-if="doc.estado === 'ACTIVO'" class="btn-icon warning-icon"
                                                title="Anular" @click="anularDocumento(doc)">
                                                <i class="pi pi-ban"></i>
                                            </button>

                                            <button class="btn-icon danger-icon" title="Eliminar"
                                                @click="eliminarDocumento(doc)">
                                                <i class="pi pi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section v-if="tab === 'alertas'">
                        <div class="section-title section-title-row">
                            <div>
                                <h3>Alertas</h3>
                                <p>Alertas y vencimientos relacionados al expediente.</p>
                            </div>

                            <button class="btn-primary-sm" @click="abrirNuevaAlerta">
                                <i class="pi pi-plus"></i>
                                Crear alerta
                            </button>
                        </div>

                        <div v-if="!expediente.alertas?.length" class="empty-box">
                            No hay alertas registradas.
                        </div>

                        <div v-else class="alerta-list">
                            <div v-for="alerta in expediente.alertas" :key="alerta.id" class="alerta-card" :class="{
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
                                            <span class="badge"
                                                :class="alerta.estado === 'ACTIVA' ? 'danger' : alerta.estado === 'ATENDIDA' ? 'success' : 'primary'">
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
                                            <i class="pi pi-user"></i>
                                            {{ alerta.usuario_destino?.nombre_completo || 'General' }}
                                        </span>
                                    </div>

                                    <div class="alerta-actions">
                                        <button v-if="!alerta.leido" class="btn-light"
                                            @click="marcarAlertaLeida(alerta)">
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

                                        <button class="btn-light danger-button" @click="eliminarAlerta(alerta)">
                                            <i class="pi pi-trash"></i>
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
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
                <input v-model="parteForm.nombres_razon_social" type="text"
                    placeholder="Ingrese nombre completo o razón social" />
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
                <textarea v-model="parteForm.observaciones" rows="4" placeholder="Observaciones internas..."></textarea>
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

    <div v-if="modalActuacion" class="modal-overlay">
        <div class="modal-card large">
            <div class="modal-header">
                <div>
                    <h3>{{ editandoActuacion ? 'Editar actuación' : 'Agregar actuación' }}</h3>
                    <p>Registra el movimiento, seguimiento o hito procesal del expediente.</p>
                </div>

                <button class="btn-icon" @click="cerrarModalActuacion">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div v-if="errorActuacion" class="alert-error">
                {{ errorActuacion }}
            </div>

            <div class="filters-grid">
                <div class="form-group">
                    <label>Tipo de actuación *</label>
                    <select v-model="actuacionForm.tipo_actuacion_id">
                        <option value="">Seleccione</option>
                        <option v-for="tipo in tiposActuacion" :key="tipo.id" :value="tipo.id">
                            {{ tipo.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha de actuación *</label>
                    <input v-model="actuacionForm.fecha_actuacion" type="datetime-local" />
                </div>

                <div class="form-group">
                    <label>Próxima acción</label>
                    <input v-model="actuacionForm.fecha_proxima_accion" type="datetime-local" />
                </div>

                <div class="form-group">
                    <label>Estado resultante</label>
                    <select v-model="actuacionForm.estado_resultante_id">
                        <option value="">Sin cambio de estado</option>
                        <option v-for="estado in estadosExpediente" :key="estado.id" :value="estado.id">
                            {{ estado.nombre }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Descripción *</label>
                <textarea v-model="actuacionForm.descripcion" rows="4"
                    placeholder="Describe la actuación realizada..."></textarea>
            </div>

            <div class="textarea-grid">
                <div class="form-group">
                    <label>Resultado</label>
                    <textarea v-model="actuacionForm.resultado" rows="4"
                        placeholder="Resultado de la actuación..."></textarea>
                </div>

                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea v-model="actuacionForm.observaciones" rows="4"
                        placeholder="Observaciones internas..."></textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-light" @click="cerrarModalActuacion">
                    Cancelar
                </button>

                <button class="btn-primary-sm" :disabled="guardandoActuacion" @click="guardarActuacion">
                    <i class="pi pi-save"></i>
                    {{ guardandoActuacion ? 'Guardando...' : 'Guardar actuación' }}
                </button>
            </div>
        </div>
    </div>

    <div v-if="modalDocumento" class="modal-overlay">
        <div class="modal-card large">
            <div class="modal-header">
                <div>
                    <h3>Subir documento</h3>
                    <p>Adjunta un archivo al expediente y clasifícalo correctamente.</p>
                </div>

                <button class="btn-icon" @click="cerrarModalDocumento">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div v-if="errorDocumento" class="alert-error">
                {{ errorDocumento }}
            </div>

            <div class="filters-grid">
                <div class="form-group">
                    <label>Tipo de documento *</label>
                    <select v-model="documentoForm.tipo_documento_id">
                        <option value="">Seleccione</option>
                        <option v-for="tipo in tiposDocumento" :key="tipo.id" :value="tipo.id">
                            {{ tipo.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Actuación relacionada</label>
                    <select v-model="documentoForm.actuacion_id">
                        <option value="">Sin actuación</option>
                        <option v-for="act in expediente.actuaciones" :key="act.id" :value="act.id">
                            {{ act.tipo_actuacion?.nombre || 'Actuación' }} - {{ formatoFecha(act.fecha_actuacion) }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Versión</label>
                    <input v-model="documentoForm.version" type="number" min="1" />
                </div>

                <div class="form-group">
                    <label>Archivo *</label>
                    <input type="file" @change="seleccionarArchivo" />
                </div>
            </div>

            <div v-if="documentoForm.archivo" class="selected-file-box">
                <i class="pi pi-file"></i>
                <div>
                    <strong>{{ documentoForm.archivo.name }}</strong>
                    <span>{{ pesoDocumento(documentoForm.archivo.size) }}</span>
                </div>
            </div>

            <div class="form-group">
                <label>Observación</label>
                <textarea v-model="documentoForm.observacion" rows="4"
                    placeholder="Observaciones del documento..."></textarea>
            </div>

            <div class="modal-actions">
                <button class="btn-light" @click="cerrarModalDocumento">
                    Cancelar
                </button>

                <button class="btn-primary-sm" :disabled="subiendoDocumento" @click="subirDocumento">
                    <i class="pi pi-upload"></i>
                    {{ subiendoDocumento ? 'Subiendo...' : 'Subir documento' }}
                </button>
            </div>
        </div>
    </div>

    <div v-if="modalAlerta" class="modal-overlay">
        <div class="modal-card large">
            <div class="modal-header">
                <div>
                    <h3>Crear alerta</h3>
                    <p>Registra una alerta interna para seguimiento del expediente.</p>
                </div>

                <button class="btn-icon" @click="cerrarModalAlerta">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div v-if="errorAlerta" class="alert-error">
                {{ errorAlerta }}
            </div>

            <div class="filters-grid">
                <div class="form-group">
                    <label>Tipo de alerta *</label>
                    <select v-model="alertaForm.tipo_alerta_id">
                        <option value="">Seleccione</option>
                        <option v-for="tipo in tiposAlerta" :key="tipo.id" :value="tipo.id">
                            {{ tipo.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha de alerta *</label>
                    <input v-model="alertaForm.fecha_alerta" type="datetime-local" />
                </div>

                <div class="form-group">
                    <label>Usuario destino</label>
                    <select v-model="alertaForm.usuario_destino_id">
                        <option value="">Alerta general</option>
                        <option v-for="usuario in usuariosDestino" :key="usuario.id" :value="usuario.id">
                            {{ usuario.nombre_completo }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select v-model="alertaForm.estado">
                        <option value="ACTIVA">Activa</option>
                        <option value="ATENDIDA">Atendida</option>
                        <option value="ANULADA">Anulada</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Mensaje *</label>
                <textarea v-model="alertaForm.mensaje" rows="5"
                    placeholder="Ej. El expediente tiene un vencimiento próximo..."></textarea>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input v-model="alertaForm.leido" type="checkbox" />
                    Registrar como leída
                </label>
            </div>

            <div class="modal-actions">
                <button class="btn-light" @click="cerrarModalAlerta">
                    Cancelar
                </button>

                <button class="btn-primary-sm" :disabled="creandoAlerta" @click="crearAlerta">
                    <i class="pi pi-save"></i>
                    {{ creandoAlerta ? 'Guardando...' : 'Guardar alerta' }}
                </button>
            </div>
        </div>
    </div>

    <div v-if="modalEditarExpediente" class="modal-overlay">
        <div class="modal-card extra-large">
            <div class="modal-header">
                <div>
                    <h3>Editar expediente</h3>
                    <p>Modifica los datos generales del expediente.</p>
                </div>

                <button class="btn-icon" @click="cerrarModalEditarExpediente">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div v-if="errorExpediente" class="alert-error">
                {{ errorExpediente }}
            </div>

            <div class="filters-grid">
                <div class="form-group">
                    <label>Tipo expediente *</label>
                    <select v-model="expedienteForm.tipo_expediente_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.tipos_expediente" :key="item.id" :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nro expediente *</label>
                    <input v-model="expedienteForm.numero_expediente" type="text" />
                </div>

                <div class="form-group">
                    <label>Año</label>
                    <input v-model="expedienteForm.anio_expediente" type="number" />
                </div>

                <div class="form-group">
                    <label>Código interno *</label>
                    <input v-model="expedienteForm.codigo_unico_interno" type="text" />
                </div>

                <div class="form-group">
                    <label>Estado procesal *</label>
                    <select v-model="expedienteForm.estado_expediente_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.estados_expediente" :key="item.id" :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Encargado *</label>
                    <select v-model="expedienteForm.encargado_actual_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.usuarios" :key="item.id" :value="item.id">
                            {{ item.nombre_completo }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Prioridad</label>
                    <select v-model="expedienteForm.prioridad_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.prioridades" :key="item.id" :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Monto</label>
                    <input v-model="expedienteForm.monto" type="number" min="0" step="0.01" />
                </div>

                <div class="form-group">
                    <label>Distrito judicial</label>
                    <select v-model="expedienteForm.distrito_judicial_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.distritos_judiciales" :key="item.id"
                            :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Dependencia</label>
                    <select v-model="expedienteForm.dependencia_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.dependencias" :key="item.id" :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Especialidad</label>
                    <select v-model="expedienteForm.especialidad_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.especialidades" :key="item.id" :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Instancia</label>
                    <select v-model="expedienteForm.instancia_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.instancias" :key="item.id" :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Materia</label>
                    <select v-model="expedienteForm.materia_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.materias" :key="item.id" :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Etapa</label>
                    <select v-model="expedienteForm.etapa_id">
                        <option value="">Seleccione</option>
                        <option v-for="item in catalogosExpediente.etapas" :key="item.id" :value="item.id">
                            {{ item.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha registro *</label>
                    <input v-model="expedienteForm.fecha_registro" type="date" />
                </div>

                <div class="form-group">
                    <label>Fecha ingreso</label>
                    <input v-model="expedienteForm.fecha_ingreso" type="date" />
                </div>

                <div class="form-group">
                    <label>Última actuación</label>
                    <input v-model="expedienteForm.fecha_ultima_actuacion" type="date" />
                </div>

                <div class="form-group">
                    <label>Próximo vencimiento</label>
                    <input v-model="expedienteForm.fecha_proximo_vencimiento" type="date" />
                </div>

                <div class="form-group">
                    <label>Estado registro</label>
                    <select v-model="expedienteForm.estado_registro">
                        <option value="ACTIVO">Activo</option>
                        <option value="CERRADO">Cerrado</option>
                        <option value="ARCHIVADO">Archivado</option>
                    </select>
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input v-model="expedienteForm.importante" type="checkbox" />
                        Expediente importante
                    </label>
                </div>
            </div>

            <div class="textarea-grid">
                <div class="form-group">
                    <label>Pretensiones</label>
                    <textarea v-model="expedienteForm.pretensiones" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>Observaciones generales</label>
                    <textarea v-model="expedienteForm.observaciones_generales" rows="4"></textarea>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-light" @click="cerrarModalEditarExpediente">
                    Cancelar
                </button>

                <button class="btn-primary-sm" :disabled="guardandoExpediente" @click="guardarEdicionExpediente">
                    <i class="pi pi-save"></i>
                    {{ guardandoExpediente ? 'Guardando...' : 'Guardar cambios' }}
                </button>
            </div>
        </div>
    </div>

    <div v-if="modalCerrarExpediente" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Cerrar expediente</h3>
                    <p>Registra el motivo y fecha de cierre.</p>
                </div>

                <button class="btn-icon" @click="cerrarModalCerrarExpediente">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div v-if="errorExpediente" class="alert-error">
                {{ errorExpediente }}
            </div>

            <div class="form-group">
                <label>Motivo de cierre *</label>
                <select v-model="cerrarForm.motivo_cierre_id">
                    <option value="">Seleccione</option>
                    <option v-for="item in catalogosExpediente.motivos_cierre" :key="item.id" :value="item.id">
                        {{ item.nombre }}
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label>Fecha de cierre *</label>
                <input v-model="cerrarForm.fecha_cierre" type="date" />
            </div>

            <div class="form-group">
                <label>Observaciones</label>
                <textarea v-model="cerrarForm.observaciones_generales" rows="4"></textarea>
            </div>

            <div class="modal-actions">
                <button class="btn-light" @click="cerrarModalCerrarExpediente">
                    Cancelar
                </button>

                <button class="btn-primary-sm" :disabled="guardandoExpediente" @click="confirmarCerrarExpediente">
                    <i class="pi pi-lock"></i>
                    {{ guardandoExpediente ? 'Cerrando...' : 'Cerrar expediente' }}
                </button>
            </div>
        </div>
    </div>

    <div v-if="modalReabrirExpediente" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h3>Reabrir expediente</h3>
                    <p>Registra la justificación para reabrir el expediente.</p>
                </div>

                <button class="btn-icon" @click="cerrarModalReabrirExpediente">
                    <i class="pi pi-times"></i>
                </button>
            </div>

            <div v-if="errorExpediente" class="alert-error">
                {{ errorExpediente }}
            </div>

            <div class="form-group">
                <label>Justificación *</label>
                <textarea v-model="reabrirForm.justificacion" rows="5"
                    placeholder="Ingrese la justificación de reapertura..."></textarea>
            </div>

            <div class="modal-actions">
                <button class="btn-light" @click="cerrarModalReabrirExpediente">
                    Cancelar
                </button>

                <button class="btn-primary-sm" :disabled="guardandoExpediente" @click="confirmarReabrirExpediente">
                    <i class="pi pi-lock-open"></i>
                    {{ guardandoExpediente ? 'Reabriendo...' : 'Reabrir expediente' }}
                </button>
            </div>
        </div>
    </div>
</template>