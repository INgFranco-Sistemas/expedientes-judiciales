<script setup>
import { onMounted, ref } from 'vue'
import api from '@/services/api'

const cargando = ref(false)
const guardando = ref(false)
const error = ref('')
const mensaje = ref('')

const usuarios = ref([])
const perfiles = ref([])
const especialidades = ref([])

const modalUsuario = ref(false)
const editando = ref(false)
const usuarioId = ref(null)

const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
    per_page: 10
})

const filtros = ref({
    buscar: '',
    estado_usuario: '',
    perfil_id: '',
    per_page: 10,
    page: 1
})

const form = ref({
    username: '',
    password: '',
    nombres: '',
    apellidos: '',
    dni: '',
    correo_institucional: '',
    telefono: '',
    especialidad_id: '',
    perfil_id: '',
    estado_usuario: 'ACTIVO',
    fecha_inicio_asignacion: '',
    fecha_termino_asignacion: ''
})

const cargarCatalogos = async () => {
    const { data } = await api.get('/catalogos/todos')
    perfiles.value = data.perfiles || []
    especialidades.value = data.especialidades || []
}

const cargarUsuarios = async (page = 1) => {
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

        const { data } = await api.get('/usuarios', { params })

        usuarios.value = data.data || []
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
            per_page: data.per_page
        }
    } catch (e) {
        error.value = 'No se pudo cargar usuarios.'
    } finally {
        cargando.value = false
    }
}

const abrirNuevo = () => {
    editando.value = false
    usuarioId.value = null
    error.value = ''
    mensaje.value = ''

    form.value = {
        username: '',
        password: '',
        nombres: '',
        apellidos: '',
        dni: '',
        correo_institucional: '',
        telefono: '',
        especialidad_id: '',
        perfil_id: '',
        estado_usuario: 'ACTIVO',
        fecha_inicio_asignacion: new Date().toISOString().slice(0, 10),
        fecha_termino_asignacion: ''
    }

    modalUsuario.value = true
}

const abrirEditar = (usuario) => {
    editando.value = true
    usuarioId.value = usuario.id
    error.value = ''
    mensaje.value = ''

    form.value = {
        username: usuario.username || '',
        password: '',
        nombres: usuario.nombres || '',
        apellidos: usuario.apellidos || '',
        dni: usuario.dni || '',
        correo_institucional: usuario.correo_institucional || '',
        telefono: usuario.telefono || '',
        especialidad_id: usuario.especialidad_id || '',
        perfil_id: usuario.perfil_id || '',
        estado_usuario: usuario.estado_usuario || 'ACTIVO',
        fecha_inicio_asignacion: usuario.fecha_inicio_asignacion
            ? String(usuario.fecha_inicio_asignacion).slice(0, 10)
            : '',
        fecha_termino_asignacion: usuario.fecha_termino_asignacion
            ? String(usuario.fecha_termino_asignacion).slice(0, 10)
            : ''
    }

    modalUsuario.value = true
}

const cerrarModal = () => {
    modalUsuario.value = false
    error.value = ''
}

const validar = () => {
    if (!form.value.username) return 'Ingrese el usuario.'
    if (!editando.value && !form.value.password) return 'Ingrese la contraseña.'
    if (!editando.value && form.value.password.length < 8) return 'La contraseña debe tener mínimo 8 caracteres.'
    if (editando.value && form.value.password && form.value.password.length < 8) return 'La contraseña debe tener mínimo 8 caracteres.'
    if (!form.value.nombres) return 'Ingrese nombres.'
    if (!form.value.apellidos) return 'Ingrese apellidos.'
    if (!form.value.dni || form.value.dni.length !== 8) return 'Ingrese un DNI válido de 8 dígitos.'
    if (!form.value.perfil_id) return 'Seleccione el perfil.'

    return ''
}

const limpiarPayload = () => {
    const payload = {}

    Object.entries(form.value).forEach(([key, value]) => {
        payload[key] = value === '' ? null : value
    })

    if (editando.value && !payload.password) {
        delete payload.password
    }

    return payload
}

const guardarUsuario = async () => {
    error.value = ''
    mensaje.value = ''

    const validacion = validar()
    if (validacion) {
        error.value = validacion
        return
    }

    guardando.value = true

    try {
        const payload = limpiarPayload()

        if (editando.value) {
            await api.put(`/usuarios/${usuarioId.value}`, payload)
            mensaje.value = 'Usuario actualizado correctamente.'
        } else {
            await api.post('/usuarios', payload)
            mensaje.value = 'Usuario registrado correctamente.'
        }

        await cargarUsuarios()
        modalUsuario.value = false
    } catch (e) {
        if (e.response?.data?.errors) {
            const firstError = Object.values(e.response.data.errors)[0]
            error.value = firstError?.[0] || 'Error de validación.'
        } else {
            error.value = e.response?.data?.message || 'No se pudo guardar el usuario.'
        }
    } finally {
        guardando.value = false
    }
}

const cambiarEstado = async (usuario, estado) => {
    const confirmar = confirm(`¿Seguro que deseas cambiar el estado a ${estado}?`)
    if (!confirmar) return

    try {
        await api.patch(`/usuarios/${usuario.id}/estado`, {
            estado_usuario: estado
        })

        await cargarUsuarios(pagination.value.current_page)
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo cambiar el estado.')
    }
}

const eliminarUsuario = async (usuario) => {
    const confirmar = confirm(`¿Seguro que deseas eliminar al usuario "${usuario.nombre_completo}"?`)
    if (!confirmar) return

    try {
        await api.delete(`/usuarios/${usuario.id}`)
        await cargarUsuarios(pagination.value.current_page)
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo eliminar el usuario.')
    }
}

const limpiarFiltros = () => {
    filtros.value = {
        buscar: '',
        estado_usuario: '',
        perfil_id: '',
        per_page: 10,
        page: 1
    }

    cargarUsuarios()
}

const cambiarPagina = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        cargarUsuarios(page)
    }
}

onMounted(async () => {
    await cargarCatalogos()
    await cargarUsuarios()
})
</script>

<template>
    <div class="usuarios-page">
        <div class="page-title dashboard-title-row">
            <div>
                <h2>Usuarios</h2>
                <p>Administración de accesos, perfiles y estados del sistema.</p>
            </div>

            <button class="btn-refresh" @click="abrirNuevo">
                <i class="pi pi-user-plus"></i>
                Nuevo usuario
            </button>
        </div>

        <div v-if="error && !modalUsuario" class="alert-error">
            {{ error }}
        </div>

        <div v-if="mensaje" class="alert-success">
            {{ mensaje }}
        </div>

        <section class="filter-card">
            <div class="filter-header">
                <div>
                    <h3>Filtros</h3>
                    <p>Busca usuarios por nombre, DNI, correo o perfil.</p>
                </div>

                <button class="btn-light" @click="limpiarFiltros">
                    <i class="pi pi-filter-slash"></i>
                    Limpiar
                </button>
            </div>

            <div class="filters-grid">
                <div class="form-group">
                    <label>Búsqueda general</label>
                    <input v-model="filtros.buscar" type="text" placeholder="Nombre, usuario, DNI o correo..."
                        @keyup.enter="cargarUsuarios()" />
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <select v-model="filtros.estado_usuario">
                        <option value="">Todos</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                        <option value="BLOQUEADO">Bloqueado</option>
                        <option value="CESADO">Cesado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Perfil</label>
                    <select v-model="filtros.perfil_id">
                        <option value="">Todos</option>
                        <option v-for="perfil in perfiles" :key="perfil.id" :value="perfil.id">
                            {{ perfil.nombre }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mostrar</label>
                    <select v-model="filtros.per_page" @change="cargarUsuarios()">
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <button class="btn-primary-sm" @click="cargarUsuarios()">
                    <i class="pi pi-search"></i>
                    Buscar
                </button>
            </div>
        </section>

        <section class="table-card">
            <div class="table-toolbar">
                <div>
                    <h3>Listado de usuarios</h3>
                    <p>{{ pagination.total }} registros encontrados</p>
                </div>
            </div>

            <div v-if="cargando" class="loading-box">
                Cargando usuarios...
            </div>

            <div v-else-if="usuarios.length === 0" class="empty-box">
                No se encontraron usuarios.
            </div>

            <div v-else class="professional-table-wrapper">
                <table class="professional-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre completo</th>
                            <th>DNI</th>
                            <th>Correo</th>
                            <th>Perfil</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                            <th>Último acceso</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="usuario in usuarios" :key="usuario.id">
                            <td>
                                <strong>{{ usuario.username }}</strong>
                                <small>ID: {{ usuario.id }}</small>
                            </td>

                            <td>{{ usuario.nombre_completo }}</td>
                            <td>{{ usuario.dni }}</td>
                            <td>{{ usuario.correo_institucional || '-' }}</td>
                            <td>
                                <span class="badge primary">
                                    {{ usuario.perfil?.nombre || '-' }}
                                </span>
                            </td>
                            <td>{{ usuario.especialidad?.nombre || '-' }}</td>
                            <td>
                                <span class="badge"
                                    :class="usuario.estado_usuario === 'ACTIVO' ? 'success' : usuario.estado_usuario === 'BLOQUEADO' ? 'danger' : 'warning'">
                                    {{ usuario.estado_usuario }}
                                </span>
                            </td>
                            <td>{{ usuario.ultimo_acceso_at ? new Date(usuario.ultimo_acceso_at).toLocaleString('es-PE')
                                : '-' }}</td>

                            <td class="text-center">
                                <button class="btn-icon" title="Editar" @click="abrirEditar(usuario)">
                                    <i class="pi pi-pencil"></i>
                                </button>

                                <button class="btn-icon warning-icon" title="Inactivar"
                                    @click="cambiarEstado(usuario, 'INACTIVO')">
                                    <i class="pi pi-pause"></i>
                                </button>

                                <button class="btn-icon success-icon" title="Activar"
                                    @click="cambiarEstado(usuario, 'ACTIVO')">
                                    <i class="pi pi-check"></i>
                                </button>

                                <button class="btn-icon danger-icon" title="Eliminar" @click="eliminarUsuario(usuario)">
                                    <i class="pi pi-trash"></i>
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

                <span>Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>

                <button class="btn-light" :disabled="pagination.current_page >= pagination.last_page"
                    @click="cambiarPagina(pagination.current_page + 1)">
                    Siguiente
                </button>
            </div>
        </section>

        <div v-if="modalUsuario" class="modal-overlay">
            <div class="modal-card extra-large">
                <div class="modal-header">
                    <div>
                        <h3>{{ editando ? 'Editar usuario' : 'Nuevo usuario' }}</h3>
                        <p>Complete la información del usuario institucional.</p>
                    </div>

                    <button class="btn-icon" @click="cerrarModal">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <div v-if="error" class="alert-error">
                    {{ error }}
                </div>

                <div class="filters-grid">
                    <div class="form-group">
                        <label>Usuario *</label>
                        <input v-model="form.username" type="text" placeholder="usuario.apellido" />
                    </div>

                    <div class="form-group">
                        <label>Contraseña {{ editando ? '(opcional)' : '*' }}</label>
                        <input v-model="form.password" type="password" placeholder="Mínimo 8 caracteres" />
                    </div>

                    <div class="form-group">
                        <label>Nombres *</label>
                        <input v-model="form.nombres" type="text" />
                    </div>

                    <div class="form-group">
                        <label>Apellidos *</label>
                        <input v-model="form.apellidos" type="text" />
                    </div>

                    <div class="form-group">
                        <label>DNI *</label>
                        <input v-model="form.dni" type="text" maxlength="8" />
                    </div>

                    <div class="form-group">
                        <label>Correo institucional</label>
                        <input v-model="form.correo_institucional" type="email"
                            placeholder="usuario@regionhuanuco.gob.pe" />
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input v-model="form.telefono" type="text" />
                    </div>

                    <div class="form-group">
                        <label>Perfil *</label>
                        <select v-model="form.perfil_id">
                            <option value="">Seleccione</option>
                            <option v-for="perfil in perfiles" :key="perfil.id" :value="perfil.id">
                                {{ perfil.nombre }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Especialidad</label>
                        <select v-model="form.especialidad_id">
                            <option value="">Sin especialidad</option>
                            <option v-for="esp in especialidades" :key="esp.id" :value="esp.id">
                                {{ esp.nombre }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select v-model="form.estado_usuario">
                            <option value="ACTIVO">Activo</option>
                            <option value="INACTIVO">Inactivo</option>
                            <option value="BLOQUEADO">Bloqueado</option>
                            <option value="CESADO">Cesado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Inicio asignación</label>
                        <input v-model="form.fecha_inicio_asignacion" type="date" />
                    </div>

                    <div class="form-group">
                        <label>Término asignación</label>
                        <input v-model="form.fecha_termino_asignacion" type="date" />
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn-light" @click="cerrarModal">
                        Cancelar
                    </button>

                    <button class="btn-primary-sm" :disabled="guardando" @click="guardarUsuario">
                        <i class="pi pi-save"></i>
                        {{ guardando ? 'Guardando...' : 'Guardar usuario' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>