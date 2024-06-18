<template>
  <k-box class="k-patrol-item" :theme="theme">
    <div class="k-patrol-choice" :class="{ 'k-patrol-item-heading': showAsHeader }">
      <k-choice-input class="k-patrol-coice-input" name="choice" :checked="value" :label="title" @input="toggle" />

      <div v-if="showAsHeader">
        <k-button variant="filled" size="sm" @click="invert" :title="$t('beebmx.kirby-patrol.invert-selection')">{{ $t('beebmx.kirby-patrol.invert') }}</k-button>
      </div>
    </div>

    <div v-if="hasChildrens" class="k-patrol-childrens">
      <k-grid style="column-gap: var(--spacing-8); row-gap: var(--spacing-2)" :style="{ '--columns': columnsToShow }">
        <Item v-for="children in childrens" :key="children.id" :item="children" :line="hasLine" theme="empty" :shouldToggle="sendToggle" :columns="columns" :parent="value" />
      </k-grid>
    </div>
  </k-box>
</template>

<script setup>
  import { computed, inject, ref, watch } from 'vue'

  const props = defineProps({
    columns: Number,
    item: {
      type: Object,
      required: true,
    },
    line: {
      type: Boolean,
      default: false,
    },
    default: {
      type: Boolean,
      default: true,
    },
    theme: {
      type: String,
      default: 'empty',
    },
    shouldToggle: {
      type: Boolean,
      default: false,
    },
    parent: {
      type: Boolean,
      default: null,
    },
  })

  const sendToggle = ref(props.shouldToggle)
  const { permissions, updatePermissions } = inject('permissions')

  const id = computed(() => props.item?.id)
  const hasChildrens = computed(() => Object.keys(childrens.value).length > 0)
  const showAsHeader = computed(() => props.line && hasChildrens.value)
  const childrens = computed(() => (hasChildrens ? props.item?.childrens : []))
  const title = computed(() => props.item?.title)
  const childrenHasChildrens = computed(() => Object.values(childrens.value).some((children) => Object.keys(children?.childrens).length > 0))
  const hasLine = computed(() => childrenHasChildrens.value)
  const columnsToShow = computed(() => (hasChildrens.value && !childrenHasChildrens.value ? props.columns : 1))
  const value = computed(() => permissions.value[id.value])

  watch(props, () => {
    if (props.shouldToggle) {
      updatePermissions(id.value, props.parent)

      if (hasChildrens.value) {
        toggleChildrens()
      }
    }
  })

  function toggle(event) {
    updatePermissions(id.value, event)
  }

  function invert() {
    toggle(!value.value)
    toggleChildrens()
  }

  function toggleChildrens() {
    sendToggle.value = true
    setTimeout(() => {
      sendToggle.value = false
    })
  }
</script>

<style scoped>
  .k-patrol-item {
    flex-wrap: wrap;
    padding: 0;
  }
  .k-patrol-choice {
    width: 100%;
  }
  .k-patrol-choice .k-choice-input {
    flex: 1 1 0;
  }
  .k-patrol-item-heading {
    color: var(--color-black);
    align-items: baseline;
    border-bottom: solid 1px var(--color-border);
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--spacing-3);
    padding-bottom: var(--spacing-2);
  }
  .k-patrol-childrens {
    padding: 0 0 0.375rem var(--spacing-6);
    width: 100%;
  }
  .k-patrol-childrens .k-box[data-theme='empty'] {
    background-color: transparent;
    border: none;
  }
</style>
