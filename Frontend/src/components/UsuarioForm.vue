<template>
  <el-dialog :visible.sync="visible" title="Usuario" width="400px">
    <el-form :model="form" label-width="100px">
      <el-form-item label="Nombre">
        <el-input v-model="form.nombre" />
      </el-form-item>
      <el-form-item label="Correo">
        <el-input v-model="form.correo" />
      </el-form-item>
      <el-form-item label="Contraseña">
        <el-input v-model="form.password" type="password" />
      </el-form-item>
      <el-form-item label="Rol">
        <el-input v-model="form.rol_id" />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="visible = false">Cancelar</el-button>
      <el-button type="primary" @click="onSubmit">Guardar</el-button>
    </template>
  </el-dialog>
</template>
<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue'
const props = defineProps({
  visible: Boolean,
  form: Object
})
const emit = defineEmits(['update:visible', 'submit'])
const visible = ref(props.visible)
watch(() => props.visible, v => visible.value = v)
function onSubmit() {
  emit('submit', props.form)
  emit('update:visible', false)
}
</script>
