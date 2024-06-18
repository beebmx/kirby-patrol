<template>
  <k-inside>
    <k-header class="k-patrol-view-header">
      {{ $t('beebmx.kirby-patrol.permissions') }}

      <k-button-group v-if="isDirty" layout="collapsed" slot="buttons">
        <k-button icon="undo" variant="filled" theme="notice" size="sm" :disabled="isDisabled" @click="revert">{{ $t('revert') }}</k-button>
        <k-button icon="check" variant="filled" theme="notice" size="sm" :disabled="isDisabled" @click="save">{{ $t('save') }}</k-button>
      </k-button-group>
    </k-header>

    <k-tabs :tab="role" :tabs="tabs" />

    <k-section>
      <k-grid style="gap: var(--spacing-4); --columns: 1">
        <Item v-for="item in information" :key="item.id" :item="item" :line="true" theme="white" :columns="columns" style="padding: 0.375rem var(--box-padding-inline)" />
      </k-grid>
    </k-section>
  </k-inside>
</template>

<script setup>
  import Item from './Item.vue'
  import { computed, onMounted, onUnmounted, provide, ref, watchEffect } from 'vue'

  const props = defineProps({
    columns: Number,
    content: Array,
    patrol: Object,
    role: Object,
    roles: Array,
  })

  const saving = ref(false)
  const information = getContent(props.content)
  const permissions = ref({})
  const initial = ref({})

  provide('permissions', { permissions, updatePermissions })

  const tabs = computed(() => {
    return Object.values(props.roles).map((role) => ({
      name: role.id,
      label: role.title,
      link: `/patrol?role=${role.id}`,
    }))
  })

  const isDirty = computed(() => JSON.stringify(permissions.value) !== JSON.stringify(initial.value))
  const isDisabled = computed(() => !isDirty.value || saving.value)
  const role = computed(() => props.role.id)

  watchEffect(() => {
    permissions.value = props.patrol
    initial.value = props.patrol
  })

  onMounted(() => {
    window.panel.events.on('view.save', save)
  })

  onUnmounted(() => {
    window.panel.events.off('view.save', save)
  })

  function getContent(items) {
    return Object.values(items).map((item) => {
      return {
        id: item.uri,
        title: item?.content?.title || item?.slug,
        childrens: getContent(item.children),
      }
    })
  }

  function updatePermissions(key, value) {
    permissions.value = {
      ...permissions.value,
      [key]: value,
    }
  }

  function reset() {
    permissions.value = initial.value
  }

  function revert() {
    window.panel.dialog.open({
      component: 'k-remove-dialog',
      props: {
        submitButton: {
          icon: 'undo',
          text: window.panel.$t('revert'),
        },
        text: window.panel.$t('revert.confirm'),
      },
      on: {
        submit: () => {
          reset()
          window.panel.dialog.close()
        },
      },
    })
  }

  async function save(event) {
    event?.preventDefault?.()

    if (isDirty.value) {
      const data = await window.panel.api.post(`patrol/permission/${role.value}`, {
        permissions: permissions.value,
      })

      if (data.saved) {
        initial.value = permissions.value
      }
    }
  }
</script>

<style>
  .k-patrol-view-header {
    margin-bottom: 0;
  }
</style>
