<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import api from '@/services/api'

const usuarios = ref([])
const origenId = ref('')
const expedientes = ref([])
const seleccionados = ref([])
const destinos = ref({})
const destinoMasivo = ref('')
const motivo = ref('')
const cargando = ref(false)
const guardando = ref(false)
const error = ref('')
const exito = ref('')

const gestores = computed(() => usuarios.value)

const gestoresDestino = computed(() =>
  gestores.value.filter(u => Number(u.id) !== Number(origenId.value))
)

const totalSeleccionados = computed(() => seleccionados.value.length)

const todosSeleccionados = computed(() =>
  expedientes.value.length > 0 &&
  seleccionados.value.length === expedientes.value.length
)

const obtenerError = (e) =>
  e.response?.data?.message ||
  Object.values(e.response?.data?.errors || {}).flat()[0] ||
  'Ocurrió un error al procesar la solicitud.'

const cargarGestores = async () => {
  try {
    const { data } = await api.get('/reasignaciones/gestores-activos')
    usuarios.value = Array.isArray(data) ? data : (data.data || [])
  } catch (e) {
    error.value = obtenerError(e)
  }
}

const cargarExpedientes = async () => {
  expedientes.value = []
  seleccionados.value = []
  destinos.value = {}
  destinoMasivo.value = ''
  error.value = ''
  exito.value = ''

  if (!origenId.value) return

  cargando.value = true

  try {
    const { data } = await api.get(
      '/reasignaciones/expedientes-por-gestor',
      { params: { usuario_origen_id: origenId.value } }
    )
    expedientes.value = data.expedientes || []
  } catch (e) {
    error.value = obtenerError(e)
  } finally {
    cargando.value = false
  }
}

const seleccionarTodos = () => {
  seleccionados.value = todosSeleccionados.value
    ? []
    : expedientes.value.map(e => e.id)
}

const aplicarDestinoMasivo = () => {
  if (!destinoMasivo.value) return
  seleccionados.value.forEach(id => {
    destinos.value[id] = Number(destinoMasivo.value)
  })
}

const reasignar = async () => {
  error.value = ''
  exito.value = ''

  if (!origenId.value || !seleccionados.value.length) {
    error.value = 'Seleccione un gestor y al menos un expediente.'
    return
  }

  if (motivo.value.trim().length < 10) {
    error.value = 'El motivo debe contener al menos 10 caracteres.'
    return
  }

  const asignaciones = seleccionados.value.map(id => ({
    expediente_id: Number(id),
    usuario_destino_id: Number(destinos.value[id])
  }))

  if (asignaciones.some(a =>
    !a.usuario_destino_id ||
    !gestoresDestino.value.some(g => Number(g.id) === a.usuario_destino_id)
  )) {
    error.value = 'Seleccione un gestor de destino válido para cada expediente.'
    return
  }

  if (!window.confirm(
    `¿Confirma la reasignación de ${asignaciones.length} expediente(s)?`
  )) return

  guardando.value = true

  try {
    const { data } = await api.post('/reasignaciones', {
      usuario_origen_id: Number(origenId.value),
      motivo: motivo.value.trim(),
      asignaciones
    })

    await cargarExpedientes()
    motivo.value = ''
    exito.value = `${data.total_reasignados} expediente(s) reasignado(s) correctamente. Lote: ${data.lote_uuid}`
  } catch (e) {
    error.value = obtenerError(e)
  } finally {
    guardando.value = false
  }
}

watch(origenId, cargarExpedientes)
onMounted(cargarGestores)
</script>

<template>
  <div class="reasignaciones-page">
    <div class="page-title">
      <h2>Reasignación de expedientes</h2>
      <p>Distribución de expedientes entre gestores de Procuraduría.</p>
    </div>

    <div v-if="error" class="mensaje error">{{ error }}</div>
    <div v-if="exito" class="mensaje exito">{{ exito }}</div>

    <section class="panel">
      <h3>1. Gestor de origen</h3>
      <select v-model="origenId" class="campo">
        <option value="">Seleccione un gestor</option>
        <option v-for="g in gestores" :key="g.id" :value="g.id">
          {{ g.nombre_completo }}
        </option>
      </select>
    </section>

    <section v-if="origenId" class="panel">
      <h3>2. Seleccionar expedientes</h3>

      <p v-if="cargando">Cargando expedientes...</p>

      <template v-else>
        <p>Total de expedientes activos: {{ expedientes.length }}</p>

        <div v-if="expedientes.length">
          <button type="button" class="boton secundario" @click="seleccionarTodos">
            {{ todosSeleccionados ? 'Deseleccionar todos' : 'Seleccionar todos' }}
          </button>

          <div class="tabla-contenedor">
            <table>
              <thead>
                <tr>
                  <th>Seleccionar</th>
                  <th>ID</th>
                  <th>Número de expediente</th>
                  <th>Gestor de destino</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="exp in expedientes" :key="exp.id">
                  <td>
                    <input
                      v-model="seleccionados"
                      type="checkbox"
                      :value="exp.id"
                    />
                  </td>
                  <td>{{ exp.id }}</td>
                  <td>{{ exp.numero_expediente }}</td>
                  <td>
                    <select
                      v-model="destinos[exp.id]"
                      class="campo"
                      :disabled="!seleccionados.includes(exp.id)"
                    >
                      <option :value="undefined">Seleccione destino</option>
                      <option
                        v-for="g in gestoresDestino"
                        :key="g.id"
                        :value="g.id"
                      >
                        {{ g.nombre_completo }}
                      </option>
                    </select>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <p v-else>No se encontraron expedientes activos para este gestor.</p>
      </template>
    </section>

    <section v-if="totalSeleccionados" class="panel">
      <h3>3. Distribución masiva</h3>
      <p>Expedientes seleccionados: {{ totalSeleccionados }}</p>

      <div class="fila">
        <select v-model="destinoMasivo" class="campo">
          <option value="">Seleccione un gestor de destino</option>
          <option v-for="g in gestoresDestino" :key="g.id" :value="g.id">
            {{ g.nombre_completo }}
          </option>
        </select>

        <button
          type="button"
          class="boton secundario"
          :disabled="!destinoMasivo"
          @click="aplicarDestinoMasivo"
        >
          Aplicar a seleccionados
        </button>
      </div>

      <h3>4. Motivo de reasignación</h3>
      <textarea
        v-model="motivo"
        class="campo"
        rows="4"
        maxlength="2000"
        placeholder="Indique el motivo de la reasignación (mínimo 10 caracteres)"
      ></textarea>

      <button
        type="button"
        class="boton principal"
        :disabled="guardando"
        @click="reasignar"
      >
        {{ guardando ? 'Procesando...' : 'Confirmar reasignación' }}
      </button>
    </section>
  </div>
</template>

<style scoped>
.reasignaciones-page { display: grid; gap: 20px; }
.page-title h2 { margin: 0 0 6px; }
.page-title p { margin: 0; opacity: .75; }
.panel {
  padding: 22px;
  border: 1px solid var(--p-content-border-color, #d4d4d8);
  border-radius: 12px;
  background: var(--p-content-background, transparent);
}
.panel h3 { margin: 0 0 15px; }
.campo {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: 1px solid #9998;
  border-radius: 7px;
  background: var(--p-content-background, transparent);
  color: inherit;
}
.campo option { color: #222; background: white; }
.fila { display: flex; gap: 12px; align-items: center; }
.fila .campo { flex: 1; }
.boton {
  padding: 11px 17px;
  border: 0;
  border-radius: 8px;
  cursor: pointer;
  margin: 8px 0;
}
.principal { background: #2563eb; color: white; }
.secundario { background: #64748b; color: white; }
.boton:disabled { opacity: .5; cursor: not-allowed; }
.tabla-contenedor { overflow-x: auto; margin-top: 12px; }
table { width: 100%; border-collapse: collapse; }
th, td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #9995;
}
.mensaje { padding: 12px; border-radius: 8px; }
.error { background: #fee2e2; color: #991b1b; }
.exito { background: #dcfce7; color: #166534; }
textarea { resize: vertical; }
@media (max-width: 650px) {
  .fila { flex-direction: column; align-items: stretch; }
  .panel { padding: 14px; }
}
</style>
