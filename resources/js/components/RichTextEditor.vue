```vue
<template>
  <div class="rich-text-editor">
    <div class="editor-wrapper">
      <div
        class="toolbar d-flex flex-wrap"
        role="toolbar"
        aria-label="Rich text formatting"
      >
        <v-btn
          class="toolbar-btn"
          icon
          density="compact"
          variant="tonal"
          @click.prevent="apply('bold')"
          title="Bold"
        >
          <v-icon class="toolbar-icon">mdi-format-bold</v-icon>
        </v-btn>

        <v-btn
          class="toolbar-btn"
          icon
          density="compact"
          variant="tonal"
          @click.prevent="apply('italic')"
          title="Italic"
        >
          <v-icon class="toolbar-icon">mdi-format-italic</v-icon>
        </v-btn>

        <v-btn
          class="toolbar-btn"
          icon
          density="compact"
          variant="tonal"
          @click.prevent="apply('underline')"
          title="Underline"
        >
          <v-icon class="toolbar-icon">mdi-format-underline</v-icon>
        </v-btn>

        <v-btn
          class="toolbar-btn"
          icon
          density="compact"
          variant="tonal"
          @click.prevent="apply('insertUnorderedList')"
          title="Bullet List"
        >
          <v-icon class="toolbar-icon">
            mdi-format-list-bulleted
          </v-icon>
        </v-btn>

        <v-btn
          class="toolbar-btn"
          icon
          density="compact"
          variant="tonal"
          @click.prevent="apply('insertOrderedList')"
          title="Numbered List"
        >
          <v-icon class="toolbar-icon">
            mdi-format-list-numbered
          </v-icon>
        </v-btn>
      </div>

      <label v-if="label" class="editor-label sr-only">
        {{ label }}
      </label>

      <div
        ref="editorRef"
        class="editor"
        contenteditable="true"
        :style="{ minHeight }"
        @input="onInput"
        @blur="onBlur"
        v-html="internalValue"
      ></div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  minHeight: {
    type: String,
    default: '140px'
  }
});

const emit = defineEmits(['update:modelValue']);

const editorRef = ref(null);
const isEditing = ref(false);

let emitTimer = null;

onMounted(() => {
  if (editorRef.value) {
    editorRef.value.innerHTML = props.modelValue || '';
  }
});

watch(
  () => props.modelValue,
  (newValue) => {
    const editor = editorRef.value;

    if (!editor) return;

    // DO NOT update while user is editing
    if (isEditing.value) return;

    const html = newValue || '';

    if (editor.innerHTML !== html) {
      editor.innerHTML = html;
    }
  }
);

function onFocus() {
  isEditing.value = true;
}

function onBlur() {
  isEditing.value = false;

  if (emitTimer) {
    clearTimeout(emitTimer);
    emitTimer = null;
  }

  emit(
    'update:modelValue',
    editorRef.value?.innerHTML || ''
  );
}

function onInput() {
  const html = editorRef.value?.innerHTML || '';

  if (emitTimer) {
    clearTimeout(emitTimer);
  }

  emitTimer = setTimeout(() => {
    emit('update:modelValue', html);
    emitTimer = null;
  }, 300);
}

function apply(command) {
  const editor = editorRef.value;

  if (!editor) return;

  editor.focus();

  document.execCommand(command, false, null);

  emit(
    'update:modelValue',
    editor.innerHTML
  );
}
</script>

<style scoped>
.rich-text-editor {
  width: 100%;
}

.editor-wrapper {
  position: relative;
}

.toolbar {
  position: absolute;
  top: 8px;
  left: 8px;
  z-index: 5;
  display: flex;
  gap: 4px;
}

.toolbar-btn {
  min-width: 28px !important;
  width: 28px !important;
  height: 28px !important;
  padding: 0 !important;
  border-radius: 6px;
}

.toolbar-icon {
  font-size: 13px !important;
  line-height: 13px;
}

.editor-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
}

.editor {
  width: 100%;
  min-height: 140px;

  padding: 20px;
  padding-top: 42px;

  border: 1px solid rgba(144, 164, 174, 0.4);
  border-radius: 12px;

  background: #fff;

  outline: none;

  white-space: pre-wrap;
  overflow-wrap: break-word;
}


.editor:focus {
  border-color: rgb(25, 118, 210);
}

.sr-only {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  border: 0 !important;
}
</style>
